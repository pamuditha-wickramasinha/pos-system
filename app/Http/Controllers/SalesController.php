<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\Tax;
use App\Services\StockService;
use App\Support\DatatableHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class SalesController extends Controller
{
    public function __construct(protected StockService $stock) {}

    public function index(): View
    {
        $this->authorize('sales_view');

        return view('sales.list', ['page_title' => 'Sales List']);
    }

    public function add(): View
    {
        $this->authorize('sales_add');

        return view('sales.form', ['page_title' => 'Sales', 'sale' => null]);
    }

    public function update(int $id): View
    {
        $this->authorize('sales_edit');

        $sale = Sale::findOrFail($id);

        return view('sales.form', [
            'page_title' => 'Sales',
            'sale' => $sale,
            'itemsCount' => $sale->items()->count(),
        ]);
    }

    public function saveAndUpdate(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'sales_date' => 'required',
            'customer_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response('Please Fill Compulsory(* marked) Fields.');
        }

        $isUpdate = $request->input('command') === 'update';
        $salesDate = \Illuminate\Support\Carbon::parse($request->input('sales_date'));

        DB::beginTransaction();
        try {
            if ($isUpdate) {
                $sale = Sale::findOrFail($request->input('sales_id'));
                $oldItemIds = $sale->items()->pluck('item_id')->all();
                $sale->items()->delete();
            } else {
                $companyInit = \App\Models\Company::query()->value('sales_init') ?? 'SL';
                $nextId = (Sale::max('id') ?? 0) + 1;
                $sale = new Sale(['sales_code' => $companyInit.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT), 'pos' => false]);
                $oldItemIds = [];
            }

            $sale->fill([
                'reference_no' => $request->input('reference_no'),
                'sales_date' => $salesDate,
                'sales_status' => $request->input('sales_status'),
                'customer_id' => $request->input('customer_id'),
                'other_charges_input' => $request->input('other_charges_input') ?: null,
                'other_charges_tax_id' => $request->input('other_charges_tax_id') ?: null,
                'other_charges_amt' => $request->input('other_charges_amt') ?: null,
                'discount_to_all_input' => $request->input('discount_to_all_input') ?: null,
                'discount_to_all_type' => $request->input('discount_to_all_type'),
                'tot_discount_to_all_amt' => $request->input('tot_discount_to_all_amt') ?: null,
                'subtotal' => $request->input('tot_subtotal_amt'),
                'round_off' => $request->input('tot_round_off_amt') ?: null,
                'grand_total' => $request->input('tot_total_amt'),
                'sales_note' => $request->input('sales_note'),
                'created_by' => $request->user()->username,
                'status' => true,
            ])->save();

            $rowCount = (int) $request->input('rowcount', 0);
            $touchedItemIds = [];

            for ($i = 1; $i <= $rowCount; $i++) {
                $itemId = $request->input("tr_item_id_{$i}");
                if (empty($itemId)) {
                    continue;
                }

                $salesQty = (float) $request->input("td_data_{$i}_3");
                $item = Item::find($itemId);

                if ($item && $item->stock < $salesQty) {
                    DB::rollBack();

                    return response("{$item->item_name} has only {$item->stock} in Stock!!");
                }

                $pricePerUnit = (float) $request->input("td_data_{$i}_4");
                $taxType = $request->input("tr_tax_type_{$i}");
                $unitTax = (float) $request->input("tr_tax_value_{$i}");
                $discountAmt = (float) $request->input("td_data_{$i}_8");
                $discountAmtPerUnit = $salesQty > 0 ? $discountAmt / $salesQty : 0;

                $unitTotalCost = $taxType === 'Exclusive'
                    ? $pricePerUnit + ($unitTax * $pricePerUnit / 100)
                    : $pricePerUnit;
                $unitTotalCost -= $discountAmtPerUnit;

                SaleItem::create([
                    'sales_id' => $sale->id,
                    'sales_status' => $request->input('sales_status'),
                    'item_id' => $itemId,
                    'description' => $request->input("description_{$i}"),
                    'sales_qty' => $salesQty,
                    'price_per_unit' => $pricePerUnit,
                    'tax_type' => $taxType,
                    'tax_id' => $request->input("tr_tax_id_{$i}") ?: null,
                    'tax_amt' => $request->input("td_data_{$i}_11") ?: null,
                    'discount_type' => $request->input("item_discount_type_{$i}"),
                    'discount_input' => $request->input("item_discount_input_{$i}") ?: null,
                    'discount_amt' => $discountAmt,
                    'unit_total_cost' => $unitTotalCost,
                    'total_cost' => $request->input("td_data_{$i}_9") ?: null,
                    'purchase_price' => $request->input("pur_price_{$i}", 0) ?: 0,
                    'status' => true,
                ]);

                $touchedItemIds[] = $itemId;
            }

            foreach (array_unique(array_merge($oldItemIds, $touchedItemIds)) as $itemId) {
                $this->stock->recalculate((int) $itemId);
            }

            $amount = (float) $request->input('amount', 0);
            if ($amount > 0 && $request->filled('payment_type')) {
                SalePayment::create([
                    'sales_id' => $sale->id,
                    'payment_date' => $salesDate,
                    'payment_type' => $request->input('payment_type'),
                    'payment' => $amount,
                    'payment_note' => $request->input('payment_note'),
                    'created_by' => $request->user()->username,
                    'status' => true,
                ]);
            }

            $this->updatePaymentStatus($sale->id, (int) $request->input('customer_id'));

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response('failed');
        }

        return response("success<<<###>>>{$sale->id}");
    }

    protected function updatePaymentStatus(int $salesId, int $customerId): void
    {
        $sumPayments = (float) SalePayment::where('sales_id', $salesId)->sum('payment');
        $grandTotal = (float) Sale::whereKey($salesId)->value('grand_total');

        $status = 'Unpaid';
        if ($sumPayments == $grandTotal) {
            $status = 'Paid';
        } elseif ($sumPayments != 0 && $sumPayments < $grandTotal) {
            $status = 'Partial';
        }

        Sale::whereKey($salesId)->update(['payment_status' => $status, 'paid_amount' => $sumPayments]);

        $salesDue = (float) Sale::where('customer_id', $customerId)
            ->where('sales_status', 'Final')
            ->selectRaw('coalesce(sum(grand_total),0) - coalesce(sum(paid_amount),0) as due')
            ->value('due');

        Customer::whereKey($customerId)->update(['sales_due' => $salesDue]);
    }

    public function ajaxList(Request $request)
    {
        $this->authorize('sales_view');

        $query = Sale::query()->with('customer')
            ->selectRaw('sales.*, coalesce(grand_total,0) - coalesce(paid_amount,0) as sales_due');

        if (! $request->user()->can('view_all_users_sales_invoices')) {
            $query->where('created_by', $request->user()->username);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        return DataTables::of($query)
            ->addColumn('checkbox', fn (Sale $s) => DatatableHtml::checkbox($s->id))
            ->editColumn('sales_date', fn (Sale $s) => show_date($s->sales_date))
            ->addColumn('sales_code_display', function (Sale $s) {
                $info = $s->return_bit ? "\n<span class='label label-danger' style='cursor:pointer'><i class='fa fa-fw fa-undo'></i>Return Raised</span>" : '';

                return $s->sales_code.$info;
            })
            ->addColumn('customer_name', fn (Sale $s) => $s->customer?->customer_name)
            ->editColumn('grand_total', fn (Sale $s) => app_number_format($s->grand_total))
            ->editColumn('paid_amount', fn (Sale $s) => app_number_format($s->paid_amount))
            ->editColumn('sales_due', fn (Sale $s) => app_number_format($s->sales_due))
            ->addColumn('payment_status_badge', function (Sale $s) {
                return match ($s->payment_status) {
                    'Unpaid' => "<span class='label label-danger' style='cursor:pointer'>Unpaid </span>",
                    'Partial' => "<span class='label label-warning' style='cursor:pointer'> Partial </span>",
                    'Paid' => "<span class='label label-success' style='cursor:pointer'> Paid </span>",
                    default => '',
                };
            })
            ->editColumn('created_by', fn (Sale $s) => ucfirst((string) $s->created_by))
            ->addColumn('actions', function (Sale $s) use ($request) {
                $user = $request->user();
                $editUrl = ($s->pos && \Illuminate\Support\Facades\Route::has('pos.edit')) ? route('pos.edit', $s) : route('sales.update', $s);

                return DatatableHtml::actionMenu([
                    ['label' => 'View Sales', 'icon' => 'fa-eye text-blue', 'url' => route('sales.invoice', $s), 'can' => $user->can('sales_view')],
                    ['label' => 'Edit', 'icon' => 'fa-edit text-blue', 'url' => $editUrl, 'can' => $user->can('sales_edit')],
                    ['label' => 'View Payments', 'icon' => 'fa-money text-blue', 'onclick' => "view_payments({$s->id})", 'can' => $user->can('sales_payment_view')],
                    ['label' => 'Payment Receive', 'icon' => 'fa-hourglass-half text-blue', 'onclick' => "pay_now({$s->id})", 'can' => $user->can('sales_payment_add')],
                    ['label' => 'Print', 'icon' => 'fa-print text-blue', 'url' => route('sales.print_invoice', $s), 'target' => '_blank', 'can' => $user->can('sales_add') || $user->can('sales_edit')],
                    ['label' => 'PDF', 'icon' => 'fa-file-pdf-o text-blue', 'url' => route('sales.pdf', $s), 'target' => '_blank', 'can' => $user->can('sales_add') || $user->can('sales_edit')],
                    ['label' => 'POS Invoice', 'icon' => 'fa-file-text text-blue', 'onclick' => "print_invoice({$s->id})", 'can' => true],
                    ['label' => 'Sales Return', 'icon' => 'fa-undo text-blue', 'url' => \Illuminate\Support\Facades\Route::has('sales_return.add') ? route('sales_return.add', $s) : '#', 'can' => $s->sales_status === 'Final' && $user->can('sales_return') && \Illuminate\Support\Facades\Route::has('sales_return.add')],
                    ['label' => 'Delete', 'icon' => 'fa-trash text-red', 'onclick' => "delete_sales('{$s->id}')", 'can' => $user->can('sales_delete')],
                ]);
            })
            ->rawColumns(['checkbox', 'sales_code_display', 'payment_status_badge', 'actions'])
            ->make(true);
    }

    public function destroy(Request $request)
    {
        $this->authorize('sales_delete');

        return $this->deleteIds($request->input('q_id'));
    }

    public function multiDestroy(Request $request)
    {
        $this->authorize('sales_delete');

        return $this->deleteIds(implode(',', $request->input('checkbox', [])));
    }

    protected function deleteIds(string $ids): \Illuminate\Http\Response
    {
        $idArray = explode(',', $ids);

        if (\Illuminate\Support\Facades\Schema::hasTable('sales_returns')) {
            $returns = DB::table('sales_returns')
                ->join('sales', 'sales.id', '=', 'sales_returns.sales_id')
                ->whereIn('sales_returns.sales_id', $idArray)
                ->select('sales.sales_code')
                ->get();

            if ($returns->isNotEmpty()) {
                $msg = '';
                foreach ($returns as $r) {
                    $msg .= '<br>Invoice Code: '.$r->sales_code;
                }
                $msg .= '<br>Already Raised Returns, Please Delete Before Deleting Original Invoice';

                return response($msg);
            }
        }

        $sales = Sale::whereIn('id', $idArray)->get(['id', 'customer_id']);
        $itemIds = SaleItem::whereIn('sales_id', $idArray)->pluck('item_id')->unique();

        SalePayment::whereIn('sales_id', $idArray)->delete();
        SaleItem::whereIn('sales_id', $idArray)->delete();
        Sale::whereIn('id', $idArray)->delete();

        foreach ($itemIds as $itemId) {
            $this->stock->recalculate((int) $itemId);
        }
        foreach ($sales->unique('customer_id') as $s) {
            $this->updatePaymentStatus($s->id, $s->customer_id);
        }

        return response('success');
    }

    public function searchItem(Request $request)
    {
        $q = $request->input('q');

        $items = Item::query()
            ->whereRaw('(upper(item_name) like upper(?) or upper(item_code) like upper(?))', ["%{$q}%", "%{$q}%"])
            ->get(['id', 'item_name'])
            ->map(fn ($i) => ['id' => (int) $i->id, 'text' => $i->item_name]);

        return response()->json($items);
    }

    public function findItemDetails(Request $request)
    {
        $item = Item::find($request->input('id'));

        if (! $item) {
            return response()->json([]);
        }

        return response()->json([
            'id' => $item->id,
            'hsn' => $item->hsn,
            'alert_qty' => $item->alert_qty,
            'unit_name' => $item->unit?->unit_name,
            'sales_price' => $item->sales_price,
            'gst_percentage' => $item->tax?->tax,
            'available_qty' => $item->stock,
        ]);
    }

    public function returnRowWithData(int $rowcount, Item $item)
    {
        $tax = Tax::find($item->tax_id);
        $stock = (float) $item->stock;

        return view('sales.row', [
            'rowcount' => $rowcount,
            'item' => $item,
            'tax' => $tax,
            'qty' => $stock > 1 ? 1 : $stock,
            'taxAmt' => $item->tax_type === 'Inclusive'
                ? calculate_inclusive($item->sales_price, $tax->tax ?? 0)
                : calculate_exclusive($item->sales_price, $tax->tax ?? 0),
            'salesPrice' => $item->sales_price,
            'purchasePrice' => $item->price,
            'discount' => 0,
            'discountType' => $item->discount_type,
            'discountInput' => $item->discount,
            'description' => '',
        ]);
    }

    public function returnSalesList(Sale $sale)
    {
        $html = '';
        foreach ($sale->items()->with('item')->get() as $i => $si) {
            $tax = Tax::find($si->tax_id);
            $html .= view('sales.row', [
                'rowcount' => $i + 1,
                'item' => $si->item,
                'tax' => $tax,
                'qty' => $si->sales_qty,
                'taxAmt' => $si->tax_amt,
                'salesPrice' => $si->price_per_unit,
                'purchasePrice' => $si->purchase_price,
                'discount' => $si->discount_input,
                'discountType' => $si->discount_type,
                'discountInput' => $si->discount_input,
                'description' => $si->description,
            ])->render();
        }

        return response($html);
    }

    public function deletePayment(Request $request)
    {
        $this->authorize('sales_payment_delete');

        $payment = SalePayment::findOrFail($request->input('payment_id'));
        $salesId = $payment->sales_id;
        $customerId = Sale::whereKey($salesId)->value('customer_id');
        $payment->delete();

        $this->updatePaymentStatus($salesId, $customerId);

        return response('success');
    }

    public function showPayNowModal(Request $request)
    {
        $this->authorize('sales_payment_add');

        $sale = Sale::with('customer')->findOrFail($request->input('sales_id'));

        return view('sales.pay-now-modal', ['sale' => $sale]);
    }

    public function savePayment(Request $request)
    {
        $this->authorize('sales_payment_add');

        $amount = (float) $request->input('amount', 0);
        if ($amount <= 0 || ! $request->filled('payment_type')) {
            return response('Please Enter Valid Amount!');
        }

        $sale = Sale::findOrFail($request->input('sales_id'));

        SalePayment::create([
            'sales_id' => $sale->id,
            'payment_date' => \Illuminate\Support\Carbon::parse($request->input('payment_date')),
            'payment_type' => $request->input('payment_type'),
            'payment' => $amount,
            'payment_note' => $request->input('payment_note'),
            'created_by' => $request->user()->username,
            'status' => true,
        ]);

        $this->updatePaymentStatus($sale->id, $sale->customer_id);

        return response('success');
    }

    public function viewPaymentsModal(Request $request)
    {
        $this->authorize('sales_view');

        $sale = Sale::with(['customer', 'payments'])->findOrFail($request->input('sales_id'));

        return view('sales.view-payments-modal', ['sale' => $sale]);
    }

    public function newCustomer(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), ['customer_name' => 'required']);

        if ($validator->fails()) {
            return response()->json(['result' => 'Please Fill Compulsory(* marked) Fields.']);
        }

        $companyInit = \App\Models\Company::query()->value('customer_init') ?? 'CU';
        $nextId = (Customer::max('id') ?? 0) + 1;

        $customer = Customer::create([
            'customer_code' => $companyInit.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT),
            'customer_name' => $request->input('customer_name'),
            'mobile' => $request->input('mobile'),
            'email' => $request->input('email'),
            'address' => $request->input('address'),
            'created_by' => $request->user()->username,
            'status' => true,
        ]);

        return response()->json(['id' => $customer->id, 'customer_name' => $customer->customer_name, 'result' => 'success']);
    }

    public function invoice(Sale $sale, Request $request): View
    {
        if (! $request->user()->can('sales_add') && ! $request->user()->can('sales_edit')) {
            abort(403);
        }

        return view('sales.invoice', ['page_title' => 'Sales Invoice', 'sale' => $sale]);
    }

    public function printInvoicePos(Sale $sale, Request $request): View
    {
        if (! $request->user()->can('sales_add') && ! $request->user()->can('sales_edit')) {
            abort(403);
        }

        return view('sales.print-invoice-pos', ['page_title' => 'Sales Invoice', 'sale' => $sale->load('items.item', 'customer')]);
    }

    public function printInvoice(Sale $sale, Request $request): View
    {
        if (! $request->user()->can('sales_add') && ! $request->user()->can('sales_edit')) {
            abort(403);
        }

        return view('sales.print-invoice', ['page_title' => 'Sales Invoice', 'sale' => $sale]);
    }

    public function pdf(Sale $sale, Request $request)
    {
        if (! $request->user()->can('sales_add') && ! $request->user()->can('sales_edit')) {
            abort(403);
        }

        $html = view('sales.print-invoice', ['page_title' => 'Sales Invoice', 'sale' => $sale])->render();

        return \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'portrait')->stream("Sales_Invoice_{$sale->id}");
    }
}
