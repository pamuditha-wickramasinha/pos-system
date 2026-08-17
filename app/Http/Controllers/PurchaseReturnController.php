<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\PurchaseReturnPayment;
use App\Models\Supplier;
use App\Services\StockService;
use App\Support\DatatableHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PurchaseReturnController extends Controller
{
    public function __construct(protected StockService $stock) {}

    public function index(): View
    {
        $this->authorize('purchase_return_view');

        return view('purchase_return.list', ['page_title' => 'Purchase Returns List']);
    }

    public function create(): View
    {
        $this->authorize('purchase_return_add');

        return view('purchase_return.form', [
            'page_title' => 'Purchase Return',
            'subtitle' => 'Create New Return',
            'oper' => 'create_new_return',
            'returnEntry' => null,
            'purchase' => null,
        ]);
    }

    public function add(int $purchaseId)
    {
        $this->authorize('purchase_return_edit');

        $purchase = Purchase::findOrFail($purchaseId);

        if ($purchase->purchase_status !== 'Received') {
            return redirect()->back()->with('warning', "Sorry! {$purchase->purchase_status} Invoice could not be returned!");
        }

        $existing = PurchaseReturn::where('purchase_id', $purchaseId)->first();
        if ($existing) {
            return redirect()->route('purchase_return.edit', $existing)->with('success', 'Purchase Return Invoice Already Generated!');
        }

        return view('purchase_return.form', [
            'page_title' => 'Purchase Return',
            'subtitle' => 'Return Against Purchase',
            'oper' => 'return_against_purchase',
            'returnEntry' => null,
            'purchase' => $purchase,
            'itemsCount' => $purchase->items()->count(),
        ]);
    }

    public function edit(PurchaseReturn $purchaseReturn): View
    {
        $this->authorize('purchase_return_edit');

        return view('purchase_return.form', [
            'page_title' => 'Edit Purchase Return',
            'subtitle' => 'Edit Return Purchase Entry',
            'oper' => 'edit_existing_return',
            'returnEntry' => $purchaseReturn,
            'purchase' => $purchaseReturn->purchase,
            'itemsCount' => $purchaseReturn->items()->count(),
        ]);
    }

    public function purchaseSaveAndUpdate(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'return_date' => 'required',
            'supplier_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response('Please Fill Compulsory(* marked) Fields.');
        }

        $isUpdate = $request->input('command') === 'update';
        $returnDate = \Illuminate\Support\Carbon::parse($request->input('return_date'));

        DB::beginTransaction();
        try {
            if ($isUpdate) {
                $return = PurchaseReturn::findOrFail($request->input('return_id'));
                $return->items()->delete();
            } else {
                $returnInit = \App\Models\Company::query()->value('purchase_return_init') ?? 'PR';
                $nextId = (PurchaseReturn::max('id') ?? 0) + 1;
                $return = new PurchaseReturn(['return_code' => $returnInit.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT)]);
            }

            $return->fill([
                'purchase_id' => $request->input('purchase_id') ?: null,
                'reference_no' => $request->input('reference_no'),
                'return_date' => $returnDate,
                'return_status' => $request->input('return_status'),
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

                PurchaseReturnItem::create([
                    'purchase_id' => $return->purchase_id,
                    'return_id' => $return->id,
                    'return_status' => $request->input('return_status'),
                    'item_id' => $itemId,
                    'description' => $request->input("description_{$i}"),
                    'return_qty' => $request->input("td_data_{$i}_3"),
                    'price_per_unit' => $request->input("td_data_{$i}_4"),
                    'tax_id' => $request->input("tr_tax_id_{$i}") ?: null,
                    'tax_amt' => $request->input("td_data_{$i}_5") ?: null,
                    'tax_type' => $request->input("tr_tax_type_{$i}"),
                    'discount_amt' => $request->input("td_data_{$i}_8"),
                    'discount_type' => $request->input("item_discount_type_{$i}"),
                    'discount_input' => $request->input("item_discount_input_{$i}"),
                    'unit_total_cost' => $request->input("td_data_{$i}_10"),
                    'total_cost' => $request->input("td_data_{$i}_9"),
                    'status' => true,
                ]);

                $touchedItemIds[] = $itemId;
            }

            foreach (array_unique($touchedItemIds) as $itemId) {
                $this->stock->recalculate((int) $itemId);
            }

            $amount = (float) $request->input('amount', 0);
            if ($amount > 0 && $request->filled('payment_type')) {
                PurchaseReturnPayment::create([
                    'purchase_id' => $return->purchase_id,
                    'return_id' => $return->id,
                    'payment_date' => $returnDate,
                    'payment_type' => $request->input('payment_type'),
                    'payment' => $amount,
                    'payment_note' => $request->input('payment_note'),
                    'created_by' => $request->user()->username,
                    'status' => true,
                ]);
            }

            if ($return->purchase_id) {
                Purchase::whereKey($return->purchase_id)->update(['return_bit' => true]);
            }

            $this->updatePaymentStatus($return->id, (int) $request->input('supplier_id'));

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response('failed');
        }

        return response("success<<<###>>>{$return->id}");
    }

    protected function updatePaymentStatus(int $returnId, int $supplierId): void
    {
        $sumPayments = (float) PurchaseReturnPayment::where('return_id', $returnId)->sum('payment');
        $grandTotal = (float) PurchaseReturn::whereKey($returnId)->value('grand_total');

        $status = 'Unpaid';
        if ($sumPayments == $grandTotal) {
            $status = 'Paid';
        } elseif ($sumPayments != 0 && $sumPayments < $grandTotal) {
            $status = 'Partial';
        }

        PurchaseReturn::whereKey($returnId)->update(['payment_status' => $status, 'paid_amount' => $sumPayments]);

        $returnDue = (float) PurchaseReturn::where('supplier_id', $supplierId)
            ->selectRaw('coalesce(sum(grand_total),0) - coalesce(sum(paid_amount),0) as due')
            ->value('due');

        Supplier::whereKey($supplierId)->update(['purchase_return_due' => $returnDue]);
    }

    public function ajaxList(Request $request)
    {
        $this->authorize('purchase_return_view');

        $query = PurchaseReturn::query()->with('supplier', 'purchase')
            ->selectRaw('purchase_returns.*, coalesce(grand_total,0) - coalesce(paid_amount,0) as return_due');

        if (! $request->user()->can('view_all_users_purchase_return_invoices')) {
            $query->where('created_by', $request->user()->username);
        }

        return DataTables::of($query)
            ->addColumn('checkbox', fn (PurchaseReturn $r) => DatatableHtml::checkbox($r->id))
            ->editColumn('return_date', fn (PurchaseReturn $r) => show_date($r->return_date))
            ->addColumn('purchase_code', fn (PurchaseReturn $r) => $r->purchase->purchase_code ?? '')
            ->addColumn('supplier_name', fn (PurchaseReturn $r) => $r->supplier?->supplier_name)
            ->editColumn('grand_total', fn (PurchaseReturn $r) => app_number_format($r->grand_total))
            ->editColumn('paid_amount', fn (PurchaseReturn $r) => app_number_format($r->paid_amount))
            ->editColumn('return_due', fn (PurchaseReturn $r) => app_number_format($r->return_due))
            ->addColumn('payment_status_badge', function (PurchaseReturn $r) {
                return match ($r->payment_status) {
                    'Unpaid' => "<span class='label label-danger' style='cursor:pointer'>Unpaid </span>",
                    'Partial' => "<span class='label label-warning' style='cursor:pointer'> Partial </span>",
                    'Paid' => "<span class='label label-success' style='cursor:pointer'> Paid </span>",
                    default => '',
                };
            })
            ->editColumn('created_by', fn (PurchaseReturn $r) => ucfirst((string) $r->created_by))
            ->addColumn('actions', function (PurchaseReturn $r) use ($request) {
                $user = $request->user();

                return DatatableHtml::actionMenu([
                    ['label' => 'View Purchase', 'icon' => 'fa-eye text-blue', 'url' => route('purchase_return.invoice', $r), 'can' => $user->can('purchase_return_view')],
                    ['label' => 'Edit', 'icon' => 'fa-edit text-blue', 'url' => route('purchase_return.edit', $r), 'can' => $user->can('purchase_return_edit')],
                    ['label' => 'View Payments', 'icon' => 'fa-money text-blue', 'onclick' => "view_payments({$r->id})", 'can' => $user->can('purchase_return_payment_add')],
                    ['label' => 'Pay Now', 'icon' => 'fa-hourglass-half text-blue', 'onclick' => "pay_now({$r->id})", 'can' => $user->can('purchase_return_payment_add')],
                    ['label' => 'Print', 'icon' => 'fa-print text-blue', 'url' => route('purchase_return.print_invoice', $r), 'target' => '_blank', 'can' => $user->can('purchase_return_add') || $user->can('purchase_return_edit')],
                    ['label' => 'PDF', 'icon' => 'fa-file-pdf-o text-blue', 'url' => route('purchase_return.pdf', $r), 'target' => '_blank', 'can' => $user->can('purchase_return_add') || $user->can('purchase_return_edit')],
                    ['label' => 'Delete', 'icon' => 'fa-trash text-red', 'onclick' => "delete_return('{$r->id}')", 'can' => $user->can('purchase_return_delete')],
                ]);
            })
            ->rawColumns(['checkbox', 'payment_status_badge', 'actions'])
            ->make(true);
    }

    public function destroy(Request $request)
    {
        $this->authorize('purchase_return_delete');

        return $this->deleteIds($request->input('q_id'));
    }

    public function multiDestroy(Request $request)
    {
        $this->authorize('purchase_return_delete');

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
            $returns = PurchaseReturn::whereIn('id', $idList)->get(['id', 'supplier_id']);
            $itemIds = PurchaseReturnItem::whereIn('return_id', $idList)->pluck('item_id')->unique();

            PurchaseReturnPayment::whereIn('return_id', $idList)->delete();
            PurchaseReturnItem::whereIn('return_id', $idList)->delete();
            PurchaseReturn::whereIn('id', $idList)->delete();

            foreach ($itemIds as $itemId) {
                $this->stock->recalculate((int) $itemId);
            }

            $purchaseIdsStillReturned = PurchaseReturn::whereNotNull('purchase_id')->pluck('purchase_id')->unique();
            Purchase::query()->update(['return_bit' => false]);
            Purchase::whereIn('id', $purchaseIdsStillReturned)->update(['return_bit' => true]);

            foreach ($returns as $r) {
                $this->updatePaymentStatus($r->id, $r->supplier_id);
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
        $purchaseId = $request->input('purchase_id');

        if (! empty($purchaseId)) {
            $validQty = \App\Models\PurchaseItem::where('item_id', $itemId)->where('purchase_id', $purchaseId)->count();
            if ($validQty === 0) {
                return response('item_not_exist');
            }
        }

        $item = Item::with('tax')->findOrFail($itemId);
        $taxAmt = $item->tax_type === 'Inclusive'
            ? calculate_inclusive($item->sales_price, $item->tax?->tax ?? 0)
            : calculate_exclusive($item->sales_price, $item->tax?->tax ?? 0);

        return view('purchase_return.row', [
            'rowcount' => $rowcount,
            'item' => $item,
            'itemAvailableQty' => ! empty($purchaseId) ? \App\Models\PurchaseItem::where('item_id', $itemId)->where('purchase_id', $purchaseId)->sum('purchase_qty') : $item->stock,
            'purchasePrice' => $item->price,
            'taxAmt' => $taxAmt,
            'discount' => '',
            'discountType' => 'Percentage',
            'discountInput' => 0,
            'description' => '',
        ]);
    }

    public function returnPurchaseList(PurchaseReturn $purchaseReturn)
    {
        $rowsHtml = '';
        $rowcount = 1;

        foreach ($purchaseReturn->items()->with('item', 'tax')->get() as $ri) {
            $purchaseQty = $ri->purchase_id ? (float) \App\Models\PurchaseItem::where('purchase_id', $ri->purchase_id)->where('item_id', $ri->item_id)->value('purchase_qty') : 0;
            $stockQty = (float) Item::whereKey($ri->item_id)->value('stock');

            $rowsHtml .= view('purchase_return.row', [
                'rowcount' => $rowcount++,
                'item' => $ri->item,
                'itemAvailableQty' => $ri->purchase_id ? $purchaseQty : $stockQty + $ri->return_qty,
                'purchasePrice' => $ri->price_per_unit,
                'taxAmt' => $ri->tax_amt,
                'discount' => $ri->discount_amt,
                'discountType' => $ri->discount_type,
                'discountInput' => $ri->discount_input,
                'description' => $ri->description,
                'qtyOverride' => $ri->return_qty,
            ])->render();
        }

        return response($rowsHtml);
    }

    public function purchaseList(int $purchaseId)
    {
        $purchase = Purchase::with('items.item', 'items.tax')->findOrFail($purchaseId);
        $rowsHtml = '';
        $rowcount = 1;

        foreach ($purchase->items as $pi) {
            $rowsHtml .= view('purchase_return.row', [
                'rowcount' => $rowcount++,
                'item' => $pi->item,
                'itemAvailableQty' => $pi->purchase_qty,
                'purchasePrice' => $pi->price_per_unit,
                'taxAmt' => $pi->tax_amt,
                'discount' => $pi->discount_amt,
                'discountType' => $pi->discount_type,
                'discountInput' => $pi->discount_input,
                'description' => $pi->description,
                'qtyOverride' => $pi->purchase_qty,
            ])->render();
        }

        return response($rowsHtml);
    }

    public function deletePayment(Request $request)
    {
        $this->authorize('purchase_return_payment_delete');

        $payment = PurchaseReturnPayment::find($request->input('payment_id'));
        if (! $payment) {
            return response('failed');
        }

        $returnId = $payment->return_id;
        $supplierId = PurchaseReturn::whereKey($returnId)->value('supplier_id');

        DB::beginTransaction();
        try {
            $payment->delete();
            $this->updatePaymentStatus($returnId, $supplierId);
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
        $this->authorize('purchase_return_view');

        $return = PurchaseReturn::with('supplier')->findOrFail($request->input('purchase_id'));

        return view('purchase_return.pay-now-modal', ['returnEntry' => $return]);
    }

    public function savePayment(Request $request)
    {
        $this->authorize('purchase_return_add');

        $amount = (float) $request->input('amount', 0);
        if ($amount <= 0 || ! $request->filled('payment_type')) {
            return response('Please Enter Valid Amount!');
        }

        $return = PurchaseReturn::findOrFail($request->input('return_id'));

        DB::beginTransaction();
        try {
            PurchaseReturnPayment::create([
                'purchase_id' => $return->purchase_id,
                'return_id' => $return->id,
                'payment_date' => \Illuminate\Support\Carbon::parse($request->input('payment_date')),
                'payment_type' => $request->input('payment_type'),
                'payment' => $amount,
                'payment_note' => $request->input('payment_note'),
                'created_by' => $request->user()->username,
                'status' => true,
            ]);

            $this->updatePaymentStatus($return->id, $return->supplier_id);
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
        $this->authorize('purchase_return_view');

        $return = PurchaseReturn::with('supplier', 'payments')->findOrFail($request->input('purchase_id'));

        return view('purchase_return.view-payments-modal', ['returnEntry' => $return]);
    }

    public function invoice(PurchaseReturn $purchaseReturn, Request $request): View
    {
        if (! $request->user()->can('purchase_return_add') && ! $request->user()->can('purchase_return_edit')) {
            abort(403);
        }

        return view('purchase_return.invoice', ['page_title' => 'Purchase Return Invoice', 'returnEntry' => $purchaseReturn->load('items.item', 'supplier')]);
    }

    public function printInvoice(PurchaseReturn $purchaseReturn, Request $request): View
    {
        if (! $request->user()->can('purchase_return_add') && ! $request->user()->can('purchase_return_edit')) {
            abort(403);
        }

        return view('purchase_return.print-invoice', ['page_title' => 'Purchase Return Invoice', 'returnEntry' => $purchaseReturn->load('items.item', 'supplier')]);
    }

    public function pdf(PurchaseReturn $purchaseReturn, Request $request)
    {
        if (! $request->user()->can('purchase_return_add') && ! $request->user()->can('purchase_return_edit')) {
            abort(403);
        }

        $purchaseReturn->load('items.item', 'supplier');
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('purchase_return.print-invoice', ['page_title' => 'Purchase Return Invoice', 'returnEntry' => $purchaseReturn]);

        return $pdf->stream("Purchase_return_invoice_{$purchaseReturn->id}.pdf");
    }
}
