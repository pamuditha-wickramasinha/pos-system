<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Item;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\SalesReturnPayment;
use App\Services\StockService;
use App\Support\DatatableHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class SalesReturnController extends Controller
{
    public function __construct(protected StockService $stock) {}

    public function index(): View
    {
        $this->authorize('sales_return_view');

        return view('sales_return.list', ['page_title' => 'Sales Returns List']);
    }

    public function create(): View
    {
        $this->authorize('sales_return_add');

        return view('sales_return.form', [
            'page_title' => 'Sales Return',
            'subtitle' => 'Create New Return',
            'oper' => 'create_new_return',
            'returnEntry' => null,
            'sale' => null,
        ]);
    }

    public function add(int $salesId)
    {
        $this->authorize('sales_return_edit');

        $sale = Sale::findOrFail($salesId);

        if ($sale->sales_status === 'Quotation') {
            return redirect()->back()->with('warning', 'Sorry! Quotation could not be returned!');
        }

        $existing = SalesReturn::where('sales_id', $salesId)->first();
        if ($existing) {
            return redirect()->route('sales_return.edit', $existing)->with('success', 'Sales Return Invoice Already Generated!');
        }

        return view('sales_return.form', [
            'page_title' => 'Sales Return',
            'subtitle' => 'Return Against Sales',
            'oper' => 'return_against_sales',
            'returnEntry' => null,
            'sale' => $sale,
            'itemsCount' => $sale->items()->count(),
        ]);
    }

    public function edit(SalesReturn $salesReturn): View
    {
        $this->authorize('sales_return_edit');

        return view('sales_return.form', [
            'page_title' => 'Sales Return',
            'subtitle' => 'Edit Return Sales Entry',
            'oper' => 'edit_existing_return',
            'returnEntry' => $salesReturn,
            'sale' => $salesReturn->sale,
            'itemsCount' => $salesReturn->items()->count(),
        ]);
    }

    public function salesSaveAndUpdate(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'return_date' => 'required',
            'customer_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response('Please Fill Compulsory(* marked) Fields.');
        }

        $isUpdate = $request->input('command') === 'update';
        $returnDate = \Illuminate\Support\Carbon::parse($request->input('return_date'));

        DB::beginTransaction();
        try {
            if ($isUpdate) {
                $return = SalesReturn::findOrFail($request->input('return_id'));
                $return->items()->delete();
            } else {
                $returnInit = \App\Models\Company::query()->value('sales_return_init') ?? 'SR';
                $nextId = (SalesReturn::max('id') ?? 0) + 1;
                $return = new SalesReturn(['return_code' => $returnInit.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT)]);
            }

            $return->fill([
                'sales_id' => $request->input('sales_id') ?: null,
                'reference_no' => $request->input('reference_no'),
                'return_date' => $returnDate,
                'return_status' => $request->input('return_status'),
                'customer_id' => $request->input('customer_id'),
                'other_charges_input' => $request->input('other_charges_input') ?: null,
                'other_charges_tax_id' => $request->input('other_charges_tax_id') ?: null,
                'other_charges_amt' => $request->input('other_charges_amt') ?: null,
                'discount_to_all_input' => $request->input('discount_to_all_input') ?: null,
                'discount_to_all_type' => $request->input('discount_to_all_type'),
                'tot_discount_to_all_amt' => $request->input('tot_discount_to_all_amt') ?: null,
                'subtotal' => $request->input('tot_subtotal_amt'),
                'round_off' => $request->input('tot_round_off_amt') ?: null,
                'grand_total' => $request->input('tot_total_amt') ?: 0,
                'return_note' => $request->input('return_note'),
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

                $returnQty = (float) $request->input("td_data_{$i}_3");
                $pricePerUnit = (float) $request->input("td_data_{$i}_4");
                $taxType = $request->input("tr_tax_type_{$i}");
                $unitTax = (float) $request->input("tr_tax_value_{$i}");
                $discountAmt = (float) $request->input("td_data_{$i}_8");
                $discountAmtPerUnit = $returnQty > 0 ? $discountAmt / $returnQty : 0;

                $unitTotalCost = $taxType === 'Exclusive'
                    ? $pricePerUnit + ($unitTax * $pricePerUnit / 100)
                    : $pricePerUnit;
                $unitTotalCost -= $discountAmtPerUnit;

                SalesReturnItem::create([
                    'sales_id' => $return->sales_id,
                    'return_id' => $return->id,
                    'return_status' => $request->input('return_status'),
                    'item_id' => $itemId,
                    'description' => $request->input("description_{$i}"),
                    'return_qty' => $returnQty,
                    'price_per_unit' => $pricePerUnit,
                    'tax_id' => $request->input("tr_tax_id_{$i}") ?: null,
                    'tax_amt' => $request->input("td_data_{$i}_11") ?: null,
                    'tax_type' => $taxType,
                    'discount_type' => $request->input("item_discount_type_{$i}"),
                    'discount_input' => $request->input("item_discount_input_{$i}") ?: null,
                    'discount_amt' => $discountAmt,
                    'unit_total_cost' => $unitTotalCost,
                    'total_cost' => $request->input("td_data_{$i}_9") ?: null,
                    'purchase_price' => $request->input("purchase_price_{$i}", 0) ?: 0,
                    'status' => true,
                ]);

                $touchedItemIds[] = $itemId;
            }

            foreach (array_unique($touchedItemIds) as $itemId) {
                $this->stock->recalculate((int) $itemId);
            }

            $amount = (float) $request->input('amount', 0);
            if ($amount > 0 && $request->filled('payment_type')) {
                SalesReturnPayment::create([
                    'sales_id' => $return->sales_id,
                    'return_id' => $return->id,
                    'payment_date' => $returnDate,
                    'payment_type' => $request->input('payment_type'),
                    'payment' => $amount,
                    'payment_note' => $request->input('payment_note'),
                    'created_by' => $request->user()->username,
                    'status' => true,
                ]);
            }

            if ($return->sales_id) {
                Sale::whereKey($return->sales_id)->update(['return_bit' => true]);
            }

            $this->updatePaymentStatus($return->id, (int) $request->input('customer_id'));

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response('failed');
        }

        return response("success<<<###>>>{$return->id}");
    }

    protected function updatePaymentStatus(int $returnId, int $customerId): void
    {
        $sumPayments = (float) SalesReturnPayment::where('return_id', $returnId)->sum('payment');
        $grandTotal = (float) SalesReturn::whereKey($returnId)->value('grand_total');

        $status = 'Unpaid';
        if ($sumPayments == $grandTotal) {
            $status = 'Paid';
        } elseif ($sumPayments != 0 && $sumPayments < $grandTotal) {
            $status = 'Partial';
        }

        SalesReturn::whereKey($returnId)->update(['payment_status' => $status, 'paid_amount' => $sumPayments]);

        $returnDue = (float) SalesReturn::where('customer_id', $customerId)
            ->selectRaw('coalesce(sum(grand_total),0) - coalesce(sum(paid_amount),0) as due')
            ->value('due');

        Customer::whereKey($customerId)->update(['sales_return_due' => $returnDue]);
    }

    public function ajaxList(Request $request)
    {
        $this->authorize('sales_return_view');

        $query = SalesReturn::query()->with('customer', 'sale')
            ->selectRaw('sales_returns.*, coalesce(grand_total,0) - coalesce(paid_amount,0) as return_due');

        if (! $request->user()->can('view_all_users_sales_return_invoices')) {
            $query->where('created_by', $request->user()->username);
        }

        return DataTables::of($query)
            ->addColumn('checkbox', fn (SalesReturn $r) => DatatableHtml::checkbox($r->id))
            ->editColumn('return_date', fn (SalesReturn $r) => show_date($r->return_date))
            ->addColumn('sales_code', fn (SalesReturn $r) => $r->sale->sales_code ?? '')
            ->addColumn('customer_name', fn (SalesReturn $r) => $r->customer?->customer_name)
            ->editColumn('grand_total', fn (SalesReturn $r) => app_number_format($r->grand_total))
            ->editColumn('paid_amount', fn (SalesReturn $r) => app_number_format($r->paid_amount))
            ->editColumn('return_due', fn (SalesReturn $r) => app_number_format($r->return_due))
            ->addColumn('payment_status_badge', function (SalesReturn $r) {
                return match ($r->payment_status) {
                    'Unpaid' => "<span class='label label-danger' style='cursor:pointer'>Unpaid </span>",
                    'Partial' => "<span class='label label-warning' style='cursor:pointer'> Partial </span>",
                    'Paid' => "<span class='label label-success' style='cursor:pointer'> Paid </span>",
                    default => '',
                };
            })
            ->editColumn('created_by', fn (SalesReturn $r) => ucfirst((string) $r->created_by))
            ->addColumn('actions', function (SalesReturn $r) use ($request) {
                $user = $request->user();

                return DatatableHtml::actionMenu([
                    ['label' => 'View sales', 'icon' => 'fa-eye text-blue', 'url' => route('sales_return.invoice', $r), 'can' => $user->can('sales_return_view')],
                    ['label' => 'Edit', 'icon' => 'fa-edit text-blue', 'url' => route('sales_return.edit', $r), 'can' => $user->can('sales_return_edit')],
                    ['label' => 'View Payments', 'icon' => 'fa-money text-blue', 'onclick' => "view_payments({$r->id})", 'can' => $user->can('sales_return_payment_view')],
                    ['label' => 'Pay Now', 'icon' => 'fa-hourglass-half text-blue', 'onclick' => "pay_now({$r->id})", 'can' => $user->can('sales_return_payment_add')],
                    ['label' => 'Print', 'icon' => 'fa-print text-blue', 'url' => route('sales_return.print_invoice', $r), 'target' => '_blank', 'can' => $user->can('sales_return_add') || $user->can('sales_return_edit')],
                    ['label' => 'PDF', 'icon' => 'fa-file-pdf-o text-blue', 'url' => route('sales_return.pdf', $r), 'target' => '_blank', 'can' => $user->can('sales_return_add') || $user->can('sales_return_edit')],
                    ['label' => 'Delete', 'icon' => 'fa-trash text-red', 'onclick' => "delete_return('{$r->id}')", 'can' => $user->can('sales_return_delete')],
                ]);
            })
            ->rawColumns(['checkbox', 'payment_status_badge', 'actions'])
            ->make(true);
    }

    public function destroy(Request $request)
    {
        $this->authorize('sales_return_delete');

        return $this->deleteIds($request->input('q_id'));
    }

    public function multiDestroy(Request $request)
    {
        $this->authorize('sales_return_delete');

        return $this->deleteIds(implode(',', $request->input('checkbox', [])));
    }

    protected function deleteIds(string $ids)
    {
        $idList = array_filter(array_map('trim', explode(',', $ids)));
        if (empty($idList)) {
            return response('failed');
        }

        DB::beginTransaction();
        try {
            $returns = SalesReturn::whereIn('id', $idList)->get(['id', 'customer_id']);
            $itemIds = SalesReturnItem::whereIn('return_id', $idList)->pluck('item_id')->unique();

            SalesReturnPayment::whereIn('return_id', $idList)->delete();
            SalesReturnItem::whereIn('return_id', $idList)->delete();
            SalesReturn::whereIn('id', $idList)->delete();

            foreach ($itemIds as $itemId) {
                $this->stock->recalculate((int) $itemId);
            }

            $salesIdsStillReturned = SalesReturn::whereNotNull('sales_id')->pluck('sales_id')->unique();
            Sale::query()->update(['return_bit' => false]);
            Sale::whereIn('id', $salesIdsStillReturned)->update(['return_bit' => true]);

            foreach ($returns as $r) {
                $this->updatePaymentStatus($r->id, $r->customer_id);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response('failed');
        }

        return response('success');
    }

    public function searchItem(Request $request)
    {
        $term = $request->query('q');
        $items = Item::where('item_name', 'like', "%{$term}%")->orWhere('item_code', 'like', "%{$term}%")->get(['id', 'item_name']);

        return response()->json($items->map(fn ($i) => ['id' => (int) $i->id, 'text' => $i->item_name]));
    }

    public function returnRowWithData(int $rowcount, int $itemId, Request $request)
    {
        $salesId = $request->input('sales_id');

        if (! empty($salesId)) {
            $validQty = \App\Models\SaleItem::where('item_id', $itemId)->where('sales_id', $salesId)->count();
            if ($validQty === 0) {
                return response('item_not_exist');
            }
        }

        $item = Item::with('tax')->findOrFail($itemId);
        $taxAmt = $item->tax_type === 'Inclusive'
            ? calculate_inclusive($item->sales_price, $item->tax?->tax ?? 0)
            : calculate_exclusive($item->sales_price, $item->tax?->tax ?? 0);

        return view('sales_return.row', [
            'rowcount' => $rowcount,
            'item' => $item,
            'itemAvailableQty' => ! empty($salesId) ? \App\Models\SaleItem::where('item_id', $itemId)->where('sales_id', $salesId)->sum('sales_qty') : $item->stock,
            'salesPrice' => $item->sales_price,
            'taxAmt' => $taxAmt,
            'discount' => '',
            'discountType' => $item->discount_type,
            'discountInput' => $item->discount,
            'description' => '',
            'purchasePrice' => $item->price,
        ]);
    }

    public function returnSalesList(SalesReturn $salesReturn)
    {
        $rowsHtml = '';
        $rowcount = 1;

        foreach ($salesReturn->items()->with('item', 'tax')->get() as $ri) {
            $salesQty = $ri->sales_id ? (float) \App\Models\SaleItem::where('sales_id', $ri->sales_id)->where('item_id', $ri->item_id)->value('sales_qty') : 0;
            $stockQty = (float) Item::whereKey($ri->item_id)->value('stock');

            $rowsHtml .= view('sales_return.row', [
                'rowcount' => $rowcount++,
                'item' => $ri->item,
                'itemAvailableQty' => $ri->sales_id ? $salesQty : $stockQty + $ri->return_qty,
                'salesPrice' => $ri->price_per_unit,
                'taxAmt' => $ri->tax_amt,
                'discount' => $ri->discount_input,
                'discountType' => $ri->discount_type,
                'discountInput' => $ri->discount_input,
                'description' => $ri->description,
                'purchasePrice' => $ri->purchase_price,
                'qtyOverride' => $ri->return_qty,
            ])->render();
        }

        return response($rowsHtml);
    }

    public function salesList(int $salesId)
    {
        $sale = Sale::with('items.item', 'items.tax')->findOrFail($salesId);
        $rowsHtml = '';
        $rowcount = 1;

        foreach ($sale->items as $si) {
            $rowsHtml .= view('sales_return.row', [
                'rowcount' => $rowcount++,
                'item' => $si->item,
                'itemAvailableQty' => $si->sales_qty,
                'salesPrice' => $si->price_per_unit,
                'taxAmt' => $si->tax_amt,
                'discount' => $si->discount_input,
                'discountType' => $si->discount_type,
                'discountInput' => $si->discount_input,
                'description' => $si->description,
                'purchasePrice' => $si->purchase_price,
                'qtyOverride' => $si->sales_qty,
            ])->render();
        }

        return response($rowsHtml);
    }

    public function deletePayment(Request $request)
    {
        $this->authorize('sales_return_payment_delete');

        $payment = SalesReturnPayment::find($request->input('payment_id'));
        if (! $payment) {
            return response('failed');
        }

        $returnId = $payment->return_id;
        $customerId = SalesReturn::whereKey($returnId)->value('customer_id');

        DB::beginTransaction();
        try {
            $payment->delete();
            $this->updatePaymentStatus($returnId, $customerId);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response('failed');
        }

        return response('success');
    }

    public function showPayNowModal(Request $request)
    {
        $this->authorize('sales_return_view');

        $return = SalesReturn::with('customer')->findOrFail($request->input('return_id'));

        return view('sales_return.pay-now-modal', ['returnEntry' => $return]);
    }

    public function savePayment(Request $request)
    {
        $this->authorize('sales_return_add');

        $amount = (float) $request->input('amount', 0);
        if ($amount <= 0 || ! $request->filled('payment_type')) {
            return response('Please Enter Valid Amount!');
        }

        $return = SalesReturn::findOrFail($request->input('return_id'));

        DB::beginTransaction();
        try {
            SalesReturnPayment::create([
                'sales_id' => $return->sales_id,
                'return_id' => $return->id,
                'payment_date' => \Illuminate\Support\Carbon::parse($request->input('payment_date')),
                'payment_type' => $request->input('payment_type'),
                'payment' => $amount,
                'payment_note' => $request->input('payment_note'),
                'created_by' => $request->user()->username,
                'status' => true,
            ]);

            $this->updatePaymentStatus($return->id, $return->customer_id);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response('failed');
        }

        return response('success');
    }

    public function viewPaymentsModal(Request $request)
    {
        $this->authorize('sales_return_view');

        $return = SalesReturn::with('customer', 'payments')->findOrFail($request->input('return_id'));

        return view('sales_return.view-payments-modal', ['returnEntry' => $return]);
    }

    public function invoice(SalesReturn $salesReturn, Request $request): View
    {
        if (! $request->user()->can('sales_return_add') && ! $request->user()->can('sales_return_edit')) {
            abort(403);
        }

        return view('sales_return.invoice', ['page_title' => 'Sales Return Invoice', 'returnEntry' => $salesReturn->load('items.item', 'customer')]);
    }

    public function printInvoice(SalesReturn $salesReturn, Request $request): View
    {
        if (! $request->user()->can('sales_return_add') && ! $request->user()->can('sales_return_edit')) {
            abort(403);
        }

        return view('sales_return.print-invoice', ['page_title' => 'Sales Return Invoice', 'returnEntry' => $salesReturn->load('items.item', 'customer')]);
    }

    public function pdf(SalesReturn $salesReturn, Request $request)
    {
        if (! $request->user()->can('sales_return_add') && ! $request->user()->can('sales_return_edit')) {
            abort(403);
        }

        $salesReturn->load('items.item', 'customer');
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('sales_return.print-invoice', ['page_title' => 'Sales Return Invoice', 'returnEntry' => $salesReturn]);

        return $pdf->stream("Return_Invoice_{$salesReturn->id}.pdf");
    }
}
