<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Hold;
use App\Models\HoldItem;
use App\Models\Item;
use App\Models\PaymentType;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\Tax;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(protected StockService $stock) {}

    public function index(Request $request): View
    {
        $this->authorize('pos');

        return view('pos.index', [
            'page_title' => 'POS',
            'salesId' => null,
            'customerId' => null,
            'holdCount' => Hold::count(),
            'holdListHtml' => view('pos.hold-rows', ['holds' => Hold::orderByDesc('id')->get()])->render(),
            'categories' => Category::where('status', true)->get(),
            'brands' => Brand::where('status', true)->get(),
        ]);
    }

    public function edit(Sale $sale): View
    {
        $this->authorize('sales_edit');

        return view('pos.index', [
            'page_title' => 'POS Update',
            'salesId' => $sale->id,
            'customerId' => $sale->customer_id,
            'holdCount' => Hold::count(),
            'holdListHtml' => view('pos.hold-rows', ['holds' => Hold::orderByDesc('id')->get()])->render(),
            'categories' => Category::where('status', true)->get(),
            'brands' => Brand::where('status', true)->get(),
        ]);
    }

    public function getDetails(Request $request)
    {
        $query = Item::query()->with(['tax'])->where('status', true)->orderBy('id');

        if ($request->filled('id')) {
            $query->where('category_id', $request->input('id'));
        }
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->input('brand_id'));
        }
        if ($request->filled('item_name')) {
            $term = $request->input('item_name');
            $query->where(function ($q) use ($term) {
                $q->whereRaw('upper(item_name) like upper(?)', ["%{$term}%"])
                    ->orWhereRaw('upper(item_sing_name) like upper(?)', ["%{$term}%"]);
            });
        }
        if ($request->filled('last_id')) {
            $query->where('id', '>', $request->input('last_id'));
        }

        $items = $query->limit(30)->get();

        return view('pos.item-tiles', ['items' => $items]);
    }

    public function getItemDetails(Request $request)
    {
        $item = Item::with('tax')->find($request->input('item_id'));

        if (! $item) {
            return response()->json([]);
        }

        $taxAmt = $item->tax_type === 'Inclusive'
            ? calculate_inclusive($item->sales_price, $item->tax?->tax ?? 0)
            : calculate_exclusive($item->sales_price, $item->tax?->tax ?? 0);

        return response()->json([
            'id' => $item->id,
            'item_name' => $item->item_name,
            'stock' => $item->stock,
            'sales_price' => $item->sales_price,
            'purchase_price' => $item->price,
            'tax_id' => $item->tax_id,
            'tax_type' => $item->tax_type,
            'tax' => $item->tax?->tax,
            'tax_name' => $item->tax?->tax_name,
            'item_tax_amt' => $taxAmt,
            'discount_type' => $item->discount_type,
            'discount' => $item->discount,
        ]);
    }

    public function fetchSales(int $salesId)
    {
        $sale = Sale::with('items.item', 'items.tax')->find($salesId);

        if (! $sale) {
            return response('Record Not Available');
        }

        $rowsHtml = '';
        foreach ($sale->items as $i => $si) {
            $rowsHtml .= view('pos.edit-row', ['rowcount' => $i, 'saleItem' => $si])->render();
        }

        return response(implode('<<<###>>>', [
            $rowsHtml,
            $sale->discount_to_all_input,
            $sale->discount_to_all_type,
            $sale->customer_id,
            $sale->other_charges_amt,
            show_date($sale->sales_date),
        ]));
    }

    public function posSaveUpdate(Request $request)
    {
        $isUpdate = $request->input('command') === 'update';
        $salesDate = $request->filled('sales_date') ? \Illuminate\Support\Carbon::parse($request->input('sales_date')) : now();
        $totGrand = (float) $request->input('tot_grand', 0);
        $totAmt = (float) $request->input('tot_amt', 0);
        $roundOff = number_format($totGrand - $totAmt, 2, '.', '');

        DB::beginTransaction();
        try {
            if ($isUpdate) {
                $sale = Sale::findOrFail($request->input('sales_id'));
                $oldItemIds = $sale->items()->pluck('item_id')->all();
                $sale->items()->delete();
                $sale->payments()->delete();
            } else {
                $companyInit = \App\Models\Company::query()->value('sales_init') ?? 'SL';
                $nextId = (Sale::max('id') ?? 0) + 1;
                $sale = new Sale(['sales_code' => $companyInit.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT)]);
                $oldItemIds = [];
            }

            $sale->fill([
                'sales_date' => $salesDate,
                'sales_status' => 'Final',
                'customer_id' => $request->input('customer_id'),
                'discount_to_all_input' => $request->input('discount_input') ?: null,
                'discount_to_all_type' => $request->input('discount_type'),
                'tot_discount_to_all_amt' => $request->input('tot_disc') ?: null,
                'other_charges_input' => $request->input('other_charges') ?: null,
                'other_charges_amt' => $request->input('other_charges') ?: null,
                'subtotal' => $totAmt,
                'round_off' => $roundOff,
                'grand_total' => $totGrand,
                'pos' => true,
                'status' => true,
                'created_by' => $request->user()->username,
            ])->save();

            $rowCount = (int) $request->input('hidden_rowcount', 0);
            $touchedItemIds = [];

            for ($i = 0; $i < $rowCount; $i++) {
                $itemId = $request->input("tr_item_id_{$i}");
                if (empty($itemId)) {
                    continue;
                }

                $salesQty = (float) $request->input("item_qty_{$itemId}");
                $pricePerUnit = (float) $request->input("sales_price_{$i}");
                $taxType = $request->input("tr_tax_type_{$i}");
                $taxValue = (float) $request->input("tr_tax_value_{$i}");
                $discountAmt = (float) $request->input("item_discount_{$i}", 0);
                $discountAmtPerUnit = $salesQty > 0 ? $discountAmt / $salesQty : 0;

                $unitTotalCost = $taxType === 'Exclusive'
                    ? $pricePerUnit + ($taxValue * $pricePerUnit / 100)
                    : $pricePerUnit;
                $unitTotalCost -= $discountAmtPerUnit;

                SaleItem::create([
                    'sales_id' => $sale->id,
                    'sales_status' => 'Final',
                    'item_id' => $itemId,
                    'description' => $request->input("description_{$i}"),
                    'sales_qty' => $salesQty,
                    'price_per_unit' => $pricePerUnit,
                    'tax_id' => $request->input("tr_tax_id_{$i}") ?: null,
                    'tax_amt' => $request->input("td_data_{$i}_11") ?: null,
                    'tax_type' => $taxType,
                    'discount_type' => $request->input("item_discount_type_{$i}"),
                    'discount_input' => $request->input("item_discount_input_{$i}") ?: null,
                    'discount_amt' => $discountAmt,
                    'unit_total_cost' => $unitTotalCost,
                    'total_cost' => $request->input("td_data_{$i}_4") ?: null,
                    'purchase_price' => $request->input("purchase_price_{$i}", 0) ?: 0,
                    'status' => true,
                ]);

                $touchedItemIds[] = $itemId;
            }

            foreach (array_unique(array_merge($oldItemIds, $touchedItemIds)) as $itemId) {
                $this->stock->recalculate((int) $itemId);
            }

            $payAll = $request->input('pay_all') === 'true';
            $paymentRowCount = (int) $request->input('payment_row_count', 1);

            if ($payAll) {
                $this->insertSalePayment($sale, $totGrand, $totGrand, 'Cash', 'Paid By Cash', $request);
            } else {
                for ($i = 1; $i <= $paymentRowCount; $i++) {
                    if ($request->filled("amount_{$i}")) {
                        $amount = (float) $request->input("amount_{$i}");
                        $paymentType = $request->input("payment_type_{$i}");
                        $paymentNote = $request->input("payment_note_{$i}");
                        $this->insertSalePayment($sale, $amount, $totGrand, $paymentType, $paymentNote, $request);
                    }
                }
            }

            $this->updatePaymentStatus($sale->id, (int) $request->input('customer_id'));

            if ($request->filled('hidden_invoice_id')) {
                $hold = Hold::find($request->input('hidden_invoice_id'));
                $hold?->items()->delete();
                $hold?->delete();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response('failed');
        }

        $holdRows = view('pos.hold-rows', ['holds' => Hold::orderByDesc('id')->get()])->render();
        $holdCount = Hold::count();

        return response(implode('<<<###>>>', ['success', (string) $sale->id, $holdRows, (string) $holdCount]));
    }

    protected function insertSalePayment(Sale $sale, float $amount, float $totGrand, ?string $paymentType, ?string $paymentNote, Request $request): void
    {
        $changeReturn = 0;
        if ($amount > $totGrand) {
            $changeReturn = $amount - $totGrand;
            $amount = $totGrand;
        }

        SalePayment::create([
            'sales_id' => $sale->id,
            'payment_date' => $sale->sales_date,
            'payment_type' => $paymentType,
            'payment' => $amount,
            'payment_note' => $paymentNote,
            'change_return' => $changeReturn,
            'created_by' => $request->user()->username,
            'status' => true,
        ]);
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

        \App\Models\Customer::whereKey($customerId)->update(['sales_due' => $salesDue]);
    }

    public function holdInvoice(Request $request)
    {
        $referenceId = $request->input('reference_id');

        $existing = Hold::where('reference_id', $referenceId)->first();
        if ($existing) {
            $existing->items()->delete();
            $existing->delete();
        }

        $hold = Hold::create([
            'reference_id' => $referenceId,
            'sales_date' => now(),
            'sales_status' => 'Hold',
            'customer_id' => $request->input('customer_id'),
            'other_charges_input' => $request->input('other_charges') ?: null,
            'other_charges_amt' => $request->input('other_charges') ?: null,
            'discount_to_all_input' => $request->input('discount_input') ?: null,
            'discount_to_all_type' => $request->input('discount_type'),
            'tot_discount_to_all_amt' => $request->input('tot_disc') ?: null,
            'subtotal' => $request->input('tot_amt'),
            'grand_total' => $request->input('tot_grand'),
            'pos' => true,
            'created_by' => $request->user()->username,
        ]);

        $rowCount = (int) $request->input('hidden_rowcount', 0);

        for ($i = 0; $i < $rowCount; $i++) {
            $itemId = $request->input("tr_item_id_{$i}");
            if (empty($itemId)) {
                continue;
            }

            HoldItem::create([
                'hold_id' => $hold->id,
                'item_id' => $itemId,
                'description' => $request->input("description_{$i}"),
                'sales_qty' => (float) $request->input("item_qty_{$itemId}"),
                'price_per_unit' => (float) $request->input("sales_price_{$i}"),
                'tax_id' => $request->input("tr_tax_id_{$i}") ?: null,
                'tax_amt' => $request->input("td_data_{$i}_11") ?: null,
                'tax_type' => $request->input("tr_tax_type_{$i}"),
                'discount_type' => $request->input("item_discount_type_{$i}"),
                'discount_input' => $request->input("item_discount_input_{$i}") ?: null,
                'discount_amt' => (float) $request->input("item_discount_{$i}", 0),
                'total_cost' => $request->input("td_data_{$i}_4") ?: 0,
            ]);
        }

        return response("success<<<###>>>{$hold->id}");
    }

    public function holdInvoiceList()
    {
        $holds = Hold::orderByDesc('id')->get();

        return response()->json([
            'result' => view('pos.hold-rows', ['holds' => $holds])->render(),
            'tot_count' => $holds->count(),
        ]);
    }

    public function holdInvoiceDelete(int $id)
    {
        $hold = Hold::find($id);

        if (! $hold) {
            return response('failed');
        }

        $hold->items()->delete();
        $hold->delete();

        return response('success');
    }

    public function holdInvoiceEdit(Request $request)
    {
        $hold = Hold::with('items.item', 'items.tax')->find($request->input('hold_id'));

        if (! $hold) {
            return response('Record Not Available');
        }

        $rowsHtml = '';
        foreach ($hold->items as $i => $hi) {
            $rowsHtml .= view('pos.edit-row', ['rowcount' => $i, 'saleItem' => $hi])->render();
        }

        return response(implode('<<<###>>>', [
            $rowsHtml,
            $hold->discount_to_all_input,
            $hold->discount_to_all_type,
            $hold->customer_id,
            $hold->other_charges_amt,
            $hold->id,
        ]));
    }

    public function addPaymentRow(Request $request)
    {
        $rowNumber = (int) $request->input('payment_row_count', 1) + 1;

        return view('pos.payment-row', ['rowNumber' => $rowNumber, 'paymentTypes' => PaymentType::where('status', true)->get()]);
    }
}
