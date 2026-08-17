<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchasePayment;
use App\Models\Supplier;
use App\Models\Tax;
use App\Services\StockService;
use App\Support\DatatableHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PurchaseController extends Controller
{
    public function __construct(protected StockService $stock) {}

    public function index(): View
    {
        $this->authorize('purchase_view');

        return view('purchase.list', [
            'page_title' => 'Purchase List',
            'totalInvoice' => Purchase::count(),
            'purTotal' => Purchase::sum('grand_total'),
            'totPaidAmt' => Purchase::sum('paid_amount'),
            'purchaseDueTotal' => Purchase::where('purchase_status', 'Received')
                ->selectRaw('coalesce(sum(grand_total),0) - coalesce(sum(paid_amount),0) as due')->value('due'),
        ]);
    }

    public function add(): View
    {
        $this->authorize('purchase_add');

        return view('purchase.form', ['page_title' => 'Purchase', 'purchase' => null]);
    }

    public function update(int $id): View
    {
        $this->authorize('purchase_edit');

        $purchase = Purchase::findOrFail($id);

        return view('purchase.form', [
            'page_title' => 'Purchase',
            'purchase' => $purchase,
            'itemsCount' => $purchase->items()->count(),
        ]);
    }

    public function saveAndUpdate(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'pur_date' => 'required',
            'supplier_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response('Please Fill Compulsory(* marked) Fields.');
        }

        $isUpdate = $request->input('command') === 'update';
        $purchaseDate = \Illuminate\Support\Carbon::parse($request->input('pur_date'));

        DB::beginTransaction();
        try {
            if ($isUpdate) {
                $purchase = Purchase::findOrFail($request->input('purchase_id'));
                $oldItemIds = $purchase->items()->pluck('item_id')->all();
                $purchase->items()->delete();
            } else {
                $companyInit = \App\Models\Company::query()->value('purchase_init') ?? 'PU';
                $nextId = (Purchase::max('id') ?? 0) + 1;
                $purchase = new Purchase(['purchase_code' => $companyInit.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT)]);
                $oldItemIds = [];
            }

            $purchase->fill([
                'reference_no' => $request->input('reference_no'),
                'purchase_date' => $purchaseDate,
                'purchase_status' => $request->input('purchase_status'),
                'supplier_id' => $request->input('supplier_id'),
                'other_charges_input' => $request->input('other_charges_input') ?: null,
                'other_charges_tax_id' => $request->input('other_charges_tax_id') ?: null,
                'other_charges_amt' => $request->input('other_charges_amt') ?: null,
                'discount_to_all_input' => $request->input('discount_to_all_input') ?: null,
                'discount_to_all_type' => $request->input('discount_to_all_type'),
                'tot_discount_to_all_amt' => $request->input('tot_discount_to_all_amt') ?: null,
                'subtotal' => $request->input('tot_subtotal_amt'),
                'round_off' => $request->input('tot_round_off_amt') ?: null,
                'grand_total' => $request->input('tot_total_amt'),
                'purchase_note' => $request->input('purchase_note'),
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

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'purchase_status' => $request->input('purchase_status'),
                    'item_id' => $itemId,
                    'purchase_qty' => $request->input("td_data_{$i}_3"),
                    'price_per_unit' => $request->input("td_data_{$i}_4"),
                    'tax_id' => $request->input("tr_tax_id_{$i}") ?: null,
                    'tax_amt' => $request->input("td_data_{$i}_5") ?: null,
                    'tax_type' => $request->input("tr_tax_type_{$i}"),
                    'unit_total_cost' => $request->input("td_data_{$i}_10"),
                    'total_cost' => $request->input("td_data_{$i}_9"),
                    'status' => true,
                    'description' => $request->input("description_{$i}"),
                    'discount_amt' => $request->input("td_data_{$i}_8"),
                    'discount_type' => $request->input("item_discount_type_{$i}"),
                    'discount_input' => $request->input("item_discount_input_{$i}"),
                ]);

                $touchedItemIds[] = $itemId;
            }

            foreach (array_unique(array_merge($oldItemIds, $touchedItemIds)) as $itemId) {
                $this->stock->recalculate((int) $itemId);
            }

            $amount = (float) $request->input('amount', 0);
            if ($amount > 0 && $request->filled('payment_type')) {
                PurchasePayment::create([
                    'purchase_id' => $purchase->id,
                    'payment_date' => $purchaseDate,
                    'payment_type' => $request->input('payment_type'),
                    'payment' => $amount,
                    'payment_note' => $request->input('payment_note'),
                    'created_by' => $request->user()->username,
                    'status' => true,
                ]);
            }

            $this->updatePaymentStatus($purchase->id, (int) $request->input('supplier_id'));

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response('failed');
        }

        return response("success<<<###>>>{$purchase->id}");
    }

    protected function updatePaymentStatus(int $purchaseId, int $supplierId): void
    {
        $sumPayments = (float) PurchasePayment::where('purchase_id', $purchaseId)->sum('payment');
        $grandTotal = (float) Purchase::whereKey($purchaseId)->value('grand_total');

        $status = 'Unpaid';
        if ($sumPayments == $grandTotal) {
            $status = 'Paid';
        } elseif ($sumPayments != 0 && $sumPayments < $grandTotal) {
            $status = 'Partial';
        }

        Purchase::whereKey($purchaseId)->update(['payment_status' => $status, 'paid_amount' => $sumPayments]);

        $purchaseDue = (float) Purchase::where('supplier_id', $supplierId)
            ->where('purchase_status', 'Received')
            ->selectRaw('coalesce(sum(grand_total),0) - coalesce(sum(paid_amount),0) as due')
            ->value('due');

        Supplier::whereKey($supplierId)->update(['purchase_due' => $purchaseDue]);
    }

    public function ajaxList(Request $request)
    {
        $this->authorize('purchase_view');

        $query = Purchase::query()->with('supplier')
            ->selectRaw('purchases.*, coalesce(grand_total,0) - coalesce(paid_amount,0) as purchase_due');

        if (! $request->user()->can('view_all_users_purchase_invoices')) {
            $query->where('created_by', $request->user()->username);
        }

        return DataTables::of($query)
            ->addColumn('checkbox', fn (Purchase $p) => DatatableHtml::checkbox($p->id))
            ->editColumn('purchase_date', fn (Purchase $p) => show_date($p->purchase_date))
            ->addColumn('purchase_code_display', function (Purchase $p) {
                $info = $p->return_bit ? "\n<span class='label label-danger' style='cursor:pointer'><i class='fa fa-fw fa-undo'></i>Return Raised</span>" : '';

                return $p->purchase_code.$info;
            })
            ->addColumn('supplier_name', fn (Purchase $p) => $p->supplier?->supplier_name)
            ->editColumn('grand_total', fn (Purchase $p) => app_number_format($p->grand_total))
            ->editColumn('paid_amount', fn (Purchase $p) => app_number_format($p->paid_amount))
            ->editColumn('purchase_due', fn (Purchase $p) => app_number_format($p->purchase_due))
            ->addColumn('payment_status_badge', function (Purchase $p) {
                return match ($p->payment_status) {
                    'Unpaid' => "<span class='label label-danger' style='cursor:pointer'>Unpaid </span>",
                    'Partial' => "<span class='label label-warning' style='cursor:pointer'> Partial </span>",
                    'Paid' => "<span class='label label-success' style='cursor:pointer'> Paid </span>",
                    default => '',
                };
            })
            ->editColumn('created_by', fn (Purchase $p) => ucfirst((string) $p->created_by))
            ->addColumn('actions', function (Purchase $p) use ($request) {
                $user = $request->user();

                return DatatableHtml::actionMenu([
                    ['label' => 'View Purchase', 'icon' => 'fa-eye text-blue', 'url' => route('purchase.invoice', $p), 'can' => $user->can('purchase_view')],
                    ['label' => 'Edit', 'icon' => 'fa-edit text-blue', 'url' => route('purchase.update', $p), 'can' => $user->can('purchase_edit')],
                    ['label' => 'View Payments', 'icon' => 'fa-money text-blue', 'onclick' => "view_payments({$p->id})", 'can' => $user->can('purchase_payment_view')],
                    ['label' => 'Pay Now', 'icon' => 'fa-hourglass-half text-blue', 'onclick' => "pay_now({$p->id})", 'can' => $user->can('purchase_payment_add')],
                    ['label' => 'Print', 'icon' => 'fa-print text-blue', 'url' => route('purchase.print_invoice', $p), 'target' => '_blank', 'can' => $user->can('purchase_add') || $user->can('purchase_edit')],
                    ['label' => 'PDF', 'icon' => 'fa-file-pdf-o text-blue', 'url' => route('purchase.pdf', $p), 'target' => '_blank', 'can' => $user->can('purchase_add') || $user->can('purchase_edit')],
                    ['label' => 'Purchase Return', 'icon' => 'fa-undo text-blue', 'url' => \Illuminate\Support\Facades\Route::has('purchase_return.add') ? route('purchase_return.add', $p) : '#', 'can' => $user->can('purchase_return') && \Illuminate\Support\Facades\Route::has('purchase_return.add')],
                    ['label' => 'Delete', 'icon' => 'fa-trash text-red', 'onclick' => "delete_purchase('{$p->id}')", 'can' => $user->can('purchase_delete')],
                ]);
            })
            ->rawColumns(['checkbox', 'purchase_code_display', 'payment_status_badge', 'actions'])
            ->make(true);
    }

    public function destroy(Request $request)
    {
        $this->authorize('purchase_delete');

        return $this->deleteIds($request->input('q_id'));
    }

    public function multiDestroy(Request $request)
    {
        $this->authorize('purchase_delete');

        return $this->deleteIds(implode(',', $request->input('checkbox', [])));
    }

    protected function deleteIds(string $ids): \Illuminate\Http\Response
    {
        $idArray = explode(',', $ids);

        if (\Illuminate\Support\Facades\Schema::hasTable('purchase_returns')) {
            $returns = DB::table('purchase_returns')
                ->join('purchases', 'purchases.id', '=', 'purchase_returns.purchase_id')
                ->whereIn('purchase_returns.purchase_id', $idArray)
                ->select('purchases.purchase_code')
                ->get();

            if ($returns->isNotEmpty()) {
                $msg = 'Sorry! Records Not Deleted! Return Invoices Found Against Purchases:';
                foreach ($returns as $i => $r) {
                    $msg .= '<br>'.($i + 1).'.Return Invoice Against Purchase Id:'.$r->purchase_code;
                }
                $msg .= '<br>To Delete Purchase! You need to Delete Purchase Return Invoices!';

                return response($msg);
            }
        }

        $purchases = Purchase::whereIn('id', $idArray)->get(['id', 'supplier_id']);
        $itemIds = PurchaseItem::whereIn('purchase_id', $idArray)->pluck('item_id')->unique();

        Purchase::whereIn('id', $idArray)->delete();
        PurchasePayment::whereIn('purchase_id', $idArray)->delete();
        PurchaseItem::whereIn('purchase_id', $idArray)->delete();

        foreach ($itemIds as $itemId) {
            $this->stock->recalculate((int) $itemId);
        }
        foreach ($purchases->unique('supplier_id') as $p) {
            $this->updatePaymentStatus($p->id, $p->supplier_id);
        }

        return response('success');
    }

    public function newSupplier(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), ['supplier_name' => 'required']);

        if ($validator->fails()) {
            return response('Please Fill Compulsory(* marked) Fields.');
        }

        if ($request->filled('mobile') && Supplier::where('mobile', $request->input('mobile'))->exists()) {
            $result = 'Sorry!This Mobile Number already Exist.';
        } else {
            $companyInit = \App\Models\Company::query()->value('supplier_init') ?? 'SU';
            $nextId = (Supplier::max('id') ?? 0) + 1;

            Supplier::create([
                'supplier_code' => $companyInit.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT),
                'supplier_name' => $request->input('supplier_name'),
                'mobile' => $request->input('mobile'),
                'phone' => $request->input('phone'),
                'email' => $request->input('email'),
                'gstin' => $request->input('gstin'),
                'tax_number' => $request->input('tax_number'),
                'opening_balance' => $request->input('opening_balance', 0),
                'country_id' => $request->input('country'),
                'state_id' => $request->input('state') ?: null,
                'city' => $request->input('city'),
                'postcode' => $request->input('postcode'),
                'address' => $request->input('address'),
                'created_by' => $request->user()->username,
                'status' => true,
            ]);

            $result = 'success';
        }

        $latest = Supplier::orderByDesc('id')->first();

        return response()->json([
            'id' => $latest?->id,
            'supplier_name' => $latest?->supplier_name,
            'result' => $result,
        ]);
    }

    public function searchItem(Request $request)
    {
        $q = $request->input('q');

        $items = Item::query()
            ->whereRaw('(upper(item_name) like upper(?) or upper(item_code) like upper(?))', ["%{$q}%", "%{$q}%"])
            ->get(['id', 'item_name'])
            ->map(fn ($i) => ['id' => $i->id, 'text' => $i->item_name]);

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
            'item_name' => $item->item_name,
            'purchase_price' => $item->price,
            'sales_price' => $item->sales_price,
            'tax_id' => $item->tax_id,
            'stock' => $item->stock,
            'profit_margin' => $item->profit_margin,
            'tax_type' => $item->tax_type,
        ]);
    }

    public function returnRowWithData(int $rowcount, Item $item)
    {
        $tax = Tax::find($item->tax_id);

        return view('purchase.row', [
            'rowcount' => $rowcount,
            'item' => $item,
            'tax' => $tax,
            'qty' => 1,
            'taxAmt' => $item->tax_type === 'Inclusive'
                ? calculate_inclusive($item->sales_price, $tax->tax ?? 0)
                : calculate_exclusive($item->sales_price, $tax->tax ?? 0),
            'purchasePrice' => $item->price,
            'discount' => '',
            'discountType' => 'Percentage',
            'discountInput' => 0,
            'description' => '',
        ]);
    }

    public function returnPurchaseList(Purchase $purchase)
    {
        $html = '';
        foreach ($purchase->items()->with('item')->get() as $i => $pi) {
            $tax = Tax::find($pi->tax_id);
            $html .= view('purchase.row', [
                'rowcount' => $i + 1,
                'item' => $pi->item,
                'tax' => $tax,
                'qty' => $pi->purchase_qty,
                'taxAmt' => $pi->tax_amt,
                'purchasePrice' => $pi->price_per_unit,
                'discount' => $pi->unit_discount_per,
                'discountType' => $pi->discount_type,
                'discountInput' => $pi->discount_input,
                'description' => $pi->description,
            ])->render();
        }

        return response($html);
    }

    public function deletePayment(Request $request)
    {
        $this->authorize('purchase_payment_delete');

        $payment = PurchasePayment::findOrFail($request->input('payment_id'));
        $purchaseId = $payment->purchase_id;
        $supplierId = Purchase::whereKey($purchaseId)->value('supplier_id');
        $payment->delete();

        $this->updatePaymentStatus($purchaseId, $supplierId);

        return response('success');
    }

    public function showPayNowModal(Request $request)
    {
        $this->authorize('purchase_payment_add');

        $purchase = Purchase::with('supplier')->findOrFail($request->input('purchase_id'));

        return view('purchase.pay-now-modal', ['purchase' => $purchase]);
    }

    public function savePayment(Request $request)
    {
        $this->authorize('purchase_add');

        $amount = (float) $request->input('amount', 0);
        if ($amount <= 0 || ! $request->filled('payment_type')) {
            return response('Please Enter Valid Amount!');
        }

        $purchase = Purchase::findOrFail($request->input('purchase_id'));

        PurchasePayment::create([
            'purchase_id' => $purchase->id,
            'payment_date' => \Illuminate\Support\Carbon::parse($request->input('payment_date')),
            'payment_type' => $request->input('payment_type'),
            'payment' => $amount,
            'payment_note' => $request->input('payment_note'),
            'created_by' => $request->user()->username,
            'status' => true,
        ]);

        $this->updatePaymentStatus($purchase->id, $purchase->supplier_id);

        return response('success');
    }

    public function viewPaymentsModal(Request $request)
    {
        $this->authorize('purchase_view');

        $purchase = Purchase::with(['supplier', 'payments'])->findOrFail($request->input('purchase_id'));

        return view('purchase.view-payments-modal', ['purchase' => $purchase]);
    }

    public function invoice(Purchase $purchase, Request $request): View
    {
        if (! $request->user()->can('purchase_add') && ! $request->user()->can('purchase_edit')) {
            abort(403);
        }

        return view('purchase.invoice', ['page_title' => 'Purchase Invoice', 'purchase' => $purchase]);
    }

    public function printInvoice(Purchase $purchase, Request $request): View
    {
        if (! $request->user()->can('purchase_add') && ! $request->user()->can('purchase_edit')) {
            abort(403);
        }

        return view('purchase.print-invoice', ['page_title' => 'Purchase Invoice', 'purchase' => $purchase]);
    }

    public function pdf(Purchase $purchase, Request $request)
    {
        if (! $request->user()->can('purchase_add') && ! $request->user()->can('purchase_edit')) {
            abort(403);
        }

        $html = view('purchase.print-invoice', ['page_title' => 'Purchase Invoice', 'purchase' => $purchase])->render();

        return \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'portrait')->stream("Purchase_invoice_{$purchase->id}");
    }
}
