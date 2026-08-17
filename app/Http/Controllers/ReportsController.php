<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Item;
use App\Models\PaymentType;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchasePayment;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    protected function dateRange(Request $request): array
    {
        $from = Carbon::parse($request->input('from_date'))->toDateString();
        $to = Carbon::parse($request->input('to_date'))->toDateString();

        return [$from, $to];
    }

    protected function noRecords(int $colspan): string
    {
        return "<tr><td class='text-center text-danger' colspan={$colspan}>No Records Found</td></tr>";
    }

    // ---------------- Sales Report ----------------
    public function sales(Request $request)
    {
        $this->authorize('sales_report');

        return view('reports.sales', ['page_title' => 'Sales Report']);
    }

    public function showSalesReport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        $viewAll = $request->input('view_all') === 'yes';

        $query = Sale::query()->with('customer')->where('sales_status', 'Final');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }
        if (! $viewAll) {
            $query->whereBetween('sales_date', [$from, $to]);
        }
        match ($request->input('payment_status')) {
            'Paid' => $query->whereColumn('grand_total', '=', 'paid_amount'),
            'Unpaid' => $query->where('paid_amount', 0),
            'Partial' => $query->whereColumn('grand_total', '!=', 'paid_amount')->where('paid_amount', '>', 0),
            default => null,
        };

        $rows = $query->get();
        if ($rows->isEmpty()) {
            return response($this->noRecords(9));
        }

        $html = '';
        $i = 0;
        $totGrand = $totPaid = $totDue = 0;
        foreach ($rows as $s) {
            $i++;
            $due = $s->grand_total - $s->paid_amount;
            $dateDiff = ($s->paid_amount == 0 || ($s->grand_total != $s->paid_amount && $s->paid_amount > 0))
                ? now()->startOfDay()->diffInDays(Carbon::parse($s->sales_date)->startOfDay())
                : 0;
            $html .= '<tr>'
                ."<td>{$i}</td>"
                ."<td><a title='View Invoice' href='".route('sales.invoice', $s)."'>{$s->sales_code}</a></td>"
                .'<td>'.show_date($s->sales_date).'</td>'
                ."<td>{$s->customer?->customer_code}</td>"
                ."<td>{$s->customer?->customer_name}</td>"
                ."<td class='text-right'>".app_number_format($s->grand_total).'</td>'
                ."<td class='text-right'>".app_number_format($s->paid_amount).'</td>'
                ."<td class='text-right'>".app_number_format($due).'</td>'
                ."<td class='text-left'>{$dateDiff}</td>"
                .'</tr>';
            $totGrand += $s->grand_total;
            $totPaid += $s->paid_amount;
            $totDue += $due;
        }

        $html .= "<tr><td class='text-right text-bold' colspan='5'><b>Total :</b></td>"
            ."<td class='text-right text-bold'>".app_number_format($totGrand).'</td>'
            ."<td class='text-right text-bold'>".app_number_format($totPaid).'</td>'
            ."<td class='text-right text-bold'>".app_number_format($totDue).'</td><td></td></tr>';

        return response($html);
    }

    // ---------------- Sales Return Report ----------------
    public function salesReturn(Request $request)
    {
        $this->authorize('sales_return_report');

        return view('reports.sales-return', ['page_title' => 'Sales Return Report']);
    }

    public function showSalesReturnReport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        $viewAll = $request->input('view_all') === 'yes';

        $query = SalesReturn::query()->with('customer', 'sale');
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }
        if (! $viewAll) {
            $query->whereBetween('return_date', [$from, $to]);
        }

        $rows = $query->get();
        if ($rows->isEmpty()) {
            return response($this->noRecords(8));
        }

        $html = '';
        $i = 0;
        $totGrand = $totPaid = $totDue = 0;
        foreach ($rows as $r) {
            $i++;
            $due = $r->grand_total - $r->paid_amount;
            $salesLink = $r->sale
                ? "<td><a title='Return Raised Against this Invoice' href='".route('sales.invoice', $r->sale)."'>{$r->sale->sales_code}</a></td>"
                : '<td>-NA-</td>';
            $html .= '<tr>'
                ."<td>{$i}</td>"
                ."<td><a title='View Invoice' href='".route('sales_return.invoice', $r)."'>{$r->return_code}</a></td>"
                .'<td>'.show_date($r->return_date).'</td>'
                .$salesLink
                ."<td>{$r->customer?->customer_name}</td>"
                ."<td class='text-right'>".app_number_format($r->grand_total).'</td>'
                ."<td class='text-right'>".app_number_format($r->paid_amount).'</td>'
                ."<td class='text-right'>".app_number_format($due).'</td>'
                .'</tr>';
            $totGrand += $r->grand_total;
            $totPaid += $r->paid_amount;
            $totDue += $due;
        }

        $html .= "<tr><td class='text-right text-bold' colspan='5'><b>Total :</b></td>"
            ."<td class='text-right text-bold'>".app_number_format($totGrand).'</td>'
            ."<td class='text-right text-bold'>".app_number_format($totPaid).'</td>'
            ."<td class='text-right text-bold'>".app_number_format($totDue).'</td></tr>';

        return response($html);
    }

    // ---------------- Purchase Report ----------------
    public function purchase(Request $request)
    {
        $this->authorize('purchase_report');

        return view('reports.purchase', ['page_title' => 'Purchase Report']);
    }

    public function showPurchaseReport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        $viewAll = $request->input('view_all') === 'yes';

        $query = Purchase::query()->with('supplier')->where('purchase_status', 'Received');
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
        }
        if (! $viewAll) {
            $query->whereBetween('purchase_date', [$from, $to]);
        }
        match ($request->input('payment_status')) {
            'Paid' => $query->whereColumn('grand_total', '=', 'paid_amount'),
            'Unpaid' => $query->where('paid_amount', 0),
            'Partial' => $query->whereColumn('grand_total', '!=', 'paid_amount')->where('paid_amount', '>', 0),
            default => null,
        };

        $rows = $query->get();
        if ($rows->isEmpty()) {
            return response($this->noRecords(9));
        }

        $html = '';
        $i = 0;
        $totGrand = $totPaid = $totDue = 0;
        foreach ($rows as $p) {
            $i++;
            $due = $p->grand_total - $p->paid_amount;
            $dateDiff = ($p->paid_amount == 0 || ($p->grand_total != $p->paid_amount && $p->paid_amount > 0))
                ? now()->startOfDay()->diffInDays(Carbon::parse($p->purchase_date)->startOfDay())
                : 0;
            $html .= '<tr>'
                ."<td>{$i}</td>"
                ."<td><a title='View Invoice' href='".route('purchase.invoice', $p)."'>{$p->purchase_code}</a></td>"
                .'<td>'.show_date($p->purchase_date).'</td>'
                ."<td>{$p->supplier?->supplier_code}</td>"
                ."<td>{$p->supplier?->supplier_name}</td>"
                ."<td class='text-right'>".app_number_format($p->grand_total).'</td>'
                ."<td class='text-right'>".app_number_format($p->paid_amount).'</td>'
                ."<td class='text-right'>".app_number_format($due).'</td>'
                ."<td class='text-left'>{$dateDiff}</td>"
                .'</tr>';
            $totGrand += $p->grand_total;
            $totPaid += $p->paid_amount;
            $totDue += $due;
        }

        $html .= "<tr><td class='text-right text-bold' colspan='5'><b>Total :</b></td>"
            ."<td class='text-right text-bold'>".app_number_format($totGrand).'</td>'
            ."<td class='text-right text-bold'>".app_number_format($totPaid).'</td>'
            ."<td class='text-right text-bold'>".app_number_format($totDue).'</td><td></td></tr>';

        return response($html);
    }

    // ---------------- Purchase Return Report ----------------
    public function purchaseReturn(Request $request)
    {
        $this->authorize('purchase_return_report');

        return view('reports.purchase-return', ['page_title' => 'Purchase Return Report']);
    }

    public function showPurchaseReturnReport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        $viewAll = $request->input('view_all') === 'yes';

        $query = PurchaseReturn::query()->with('supplier', 'purchase');
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
        }
        if (! $viewAll) {
            $query->whereBetween('return_date', [$from, $to]);
        }

        $rows = $query->get();
        if ($rows->isEmpty()) {
            return response($this->noRecords(7));
        }

        $html = '';
        $i = 0;
        $totGrand = $totPaid = $totDue = 0;
        foreach ($rows as $r) {
            $i++;
            $due = $r->grand_total - $r->paid_amount;
            $purchaseLink = $r->purchase
                ? "<td><a title='Return Raised Against this Invoice' href='".route('purchase.invoice', $r->purchase)."'>{$r->purchase->purchase_code}</a></td>"
                : '<td>-NA-</td>';
            $html .= '<tr>'
                ."<td>{$i}</td>"
                ."<td><a title='View Invoice' href='".route('purchase_return.invoice', $r)."'>{$r->return_code}</a></td>"
                .'<td>'.show_date($r->return_date).'</td>'
                .$purchaseLink
                ."<td>{$r->supplier?->supplier_name}</td>"
                ."<td class='text-right'>".app_number_format($r->grand_total).'</td>'
                ."<td class='text-right'>".app_number_format($r->paid_amount).'</td>'
                ."<td class='text-right'>".app_number_format($due).'</td>'
                .'</tr>';
            $totGrand += $r->grand_total;
            $totPaid += $r->paid_amount;
            $totDue += $due;
        }

        $html .= "<tr><td class='text-right text-bold' colspan='5'><b>Total :</b></td>"
            ."<td class='text-right text-bold'>".app_number_format($totGrand).'</td>'
            ."<td class='text-right text-bold'>".app_number_format($totPaid).'</td>'
            ."<td class='text-right text-bold'>".app_number_format($totDue).'</td></tr>';

        return response($html);
    }

    // ---------------- Expense Report ----------------
    public function expense(Request $request)
    {
        $this->authorize('expense_report');

        return view('reports.expense', ['page_title' => 'Expense Report']);
    }

    public function showExpenseReport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        $viewAll = $request->input('view_all') === 'yes';

        $query = \App\Models\Expense::query()->with('category');
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        if (! $viewAll) {
            $query->whereBetween('expense_date', [$from, $to]);
        }

        $rows = $query->get();
        if ($rows->isEmpty()) {
            return response($this->noRecords(9));
        }

        $html = '';
        $i = 0;
        $totAmt = 0;
        foreach ($rows as $e) {
            $i++;
            $html .= '<tr>'
                ."<td>{$i}</td>"
                ."<td>{$e->expense_code}</td>"
                ."<td>{$e->expense_date->format('Y-m-d')}</td>"
                ."<td>{$e->category?->category_name}</td>"
                ."<td>{$e->reference_no}</td>"
                ."<td>{$e->expense_for}</td>"
                ."<td class='text-right'>".app_number_format($e->expense_amt).'</td>'
                ."<td>{$e->note}</td>"
                .'<td>'.ucfirst((string) $e->created_by).'</td>'
                .'</tr>';
            $totAmt += $e->expense_amt;
        }

        $html .= "<tr><td class='text-right text-bold' colspan='6'><b>Total Expense :</b></td>"
            ."<td class='text-right text-bold'>".app_number_format($totAmt).'</td></tr>';

        return response($html);
    }

    // ---------------- Stock Report ----------------
    public function stock(Request $request)
    {
        $this->authorize('stock_report');

        return view('reports.stock', ['page_title' => 'Stock Report']);
    }

    public function getStockReport(Request $request)
    {
        return response()->json([
            'item_wise_report' => $this->showStockReportHtml($request),
            'brand_wise_stock' => $this->brandWiseStockHtml($request),
            'category_wise_stock' => $this->categoryWiseStockHtml($request),
        ]);
    }

    protected function showStockReportHtml(Request $request): string
    {
        $query = Item::query()->with('tax', 'brand', 'category')->orderBy('id');
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->input('brand_id'));
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        if ($request->filled('item_id')) {
            $query->where('id', $request->input('item_id'));
        }

        $rows = $query->get();
        if ($rows->isEmpty()) {
            return $this->noRecords(10);
        }

        $html = '';
        $i = 0;
        $totPur = $totSal = $totStock = $totStockValue = 0;
        foreach ($rows as $item) {
            $i++;
            $taxType = $item->tax_type === 'Inclusive' ? 'Inc.' : 'Exc.';
            $stockValue = $item->price * $item->stock;
            $html .= '<tr>'
                ."<td>{$i}</td>"
                ."<td>{$item->item_code}</td>"
                ."<td>{$item->item_name}</td>"
                ."<td>{$item->brand?->brand_name}</td>"
                ."<td>{$item->category?->category_name}</td>"
                ."<td class='text-right'>".app_number_format($item->price).'</td>'
                ."<td>{$item->tax?->tax_name}[{$taxType}]</td>"
                ."<td class='text-right'>".app_number_format($item->sales_price).'</td>'
                ."<td>{$item->stock}</td>"
                ."<td class='text-right'>{$stockValue}</td>"
                .'</tr>';
            $totPur += $item->price;
            $totSal += $item->sales_price;
            $totStockValue += $stockValue;
            $totStock += $item->stock;
        }

        $html .= "<tr><td class='text-right text-bold' colspan='5'><b>Total :</b></td>"
            ."<td class='text-right text-bold'>".app_number_format($totPur).'</td>'
            ."<td class='text-right text-bold'></td>"
            ."<td class='text-right text-bold'>".app_number_format($totSal).'</td>'
            ."<td class='text-bold'>".app_number_format($totStock).'</td>'
            ."<td class='text-right text-bold'>".app_number_format($totStockValue).'</td></tr>';

        return $html;
    }

    protected function brandWiseStockHtml(Request $request): string
    {
        $query = Brand::query()->withSum('items', 'stock')->orderBy('brand_name');
        if ($request->filled('brand_id')) {
            $query->where('id', $request->input('brand_id'));
        }

        $rows = $query->get();
        if ($rows->isEmpty()) {
            return $this->noRecords(13);
        }

        $html = '';
        $i = 0;
        foreach ($rows as $b) {
            $i++;
            $html .= "<tr><td>{$i}</td><td>{$b->brand_name}</td><td>".($b->items_sum_stock ?? 0).'</td></tr>';
        }

        return $html;
    }

    protected function categoryWiseStockHtml(Request $request): string
    {
        $query = Category::query()->withSum('items', 'stock')->orderBy('id');
        if ($request->filled('category_id')) {
            $query->where('id', $request->input('category_id'));
        }

        $rows = $query->get();
        if ($rows->isEmpty()) {
            return $this->noRecords(13);
        }

        $html = '';
        $i = 0;
        foreach ($rows as $c) {
            $i++;
            $html .= "<tr><td>{$i}</td><td>{$c->category_name}</td><td>".($c->items_sum_stock ?? 0).'</td></tr>';
        }

        return $html;
    }

    // ---------------- Item Sales Report ----------------
    public function itemSales(Request $request)
    {
        $this->authorize('item_sales_report');

        return view('reports.item-sales', ['page_title' => 'Item Sales Report']);
    }

    public function showItemSalesReport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        $viewAll = $request->input('view_all') === 'yes';

        $query = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sales_items.sales_id')
            ->join('items', 'items.id', '=', 'sales_items.item_id')
            ->join('customers', 'customers.id', '=', 'sales.customer_id')
            ->where('sales.sales_status', 'Final')
            ->orderBy('sales.sales_date')
            ->orderBy('sales.sales_code')
            ->select('sales.id', 'sales.sales_code', 'sales.sales_date', 'customers.customer_name', 'sales_items.total_cost', 'sales_items.sales_qty', 'items.item_name');

        if (! $viewAll) {
            $query->whereBetween('sales.sales_date', [$from, $to]);
        }
        if ($request->filled('item_id')) {
            $query->where('sales_items.item_id', $request->input('item_id'));
        }
        if ($request->filled('category_id')) {
            $query->where('items.category_id', $request->input('category_id'));
        }

        $rows = $query->get();
        if ($rows->isEmpty()) {
            return response($this->noRecords(7));
        }

        $html = '';
        $i = 0;
        $totCost = 0;
        foreach ($rows as $r) {
            $i++;
            $html .= '<tr>'
                ."<td>{$i}</td>"
                ."<td><a title='View Invoice' href='".route('sales.invoice', $r->id)."'>{$r->sales_code}</a></td>"
                .'<td>'.show_date($r->sales_date).'</td>'
                ."<td>{$r->customer_name}</td>"
                ."<td>{$r->item_name}</td>"
                ."<td>{$r->sales_qty}</td>"
                ."<td class='text-right'>".app_number_format($r->total_cost).'</td>'
                .'</tr>';
            $totCost += $r->total_cost;
        }

        $html .= "<tr><td class='text-right text-bold' colspan='6'><b>Total :</b></td>"
            ."<td class='text-right text-bold'>".app_number_format($totCost).'</td></tr>';

        return response($html);
    }

    // ---------------- Item Purchase Report ----------------
    public function itemPurchase(Request $request)
    {
        $this->authorize('item_purchase_report');

        return view('reports.item-purchase', ['page_title' => 'Item Purchase Report']);
    }

    public function showItemPurchaseReport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        $viewAll = $request->input('view_all') === 'yes';

        $query = PurchaseItem::query()
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->join('items', 'items.id', '=', 'purchase_items.item_id')
            ->join('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
            ->where('purchases.purchase_status', 'Received')
            ->orderBy('purchases.purchase_date')
            ->orderBy('purchases.purchase_code')
            ->select('purchases.id', 'purchases.purchase_code', 'purchases.purchase_date', 'suppliers.supplier_name', 'purchase_items.total_cost', 'purchase_items.purchase_qty', 'items.item_name');

        if (! $viewAll) {
            $query->whereBetween('purchases.purchase_date', [$from, $to]);
        }
        if ($request->filled('item_id')) {
            $query->where('purchase_items.item_id', $request->input('item_id'));
        }

        $rows = $query->get();
        if ($rows->isEmpty()) {
            return response($this->noRecords(7));
        }

        $html = '';
        $i = 0;
        $totCost = 0;
        foreach ($rows as $r) {
            $i++;
            $html .= '<tr>'
                ."<td>{$i}</td>"
                ."<td><a title='View Invoice' href='".route('purchase.invoice', $r->id)."'>{$r->purchase_code}</a></td>"
                .'<td>'.show_date($r->purchase_date).'</td>'
                ."<td>{$r->supplier_name}</td>"
                ."<td>{$r->item_name}</td>"
                ."<td>{$r->purchase_qty}</td>"
                ."<td class='text-right'>".app_number_format($r->total_cost).'</td>'
                .'</tr>';
            $totCost += $r->total_cost;
        }

        $html .= "<tr><td class='text-right text-bold' colspan='6'><b>Total :</b></td>"
            ."<td class='text-right text-bold'>".app_number_format($totCost).'</td></tr>';

        return response($html);
    }

    // ---------------- Purchase Payments Report ----------------
    public function purchasePayments(Request $request)
    {
        $this->authorize('purchase_payments_report');

        return view('reports.purchase-payments', ['page_title' => 'Purchase Payments Report']);
    }

    public function showPurchasePaymentsReport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $query = PurchasePayment::query()
            ->join('purchases', 'purchases.id', '=', 'purchase_payments.purchase_id')
            ->join('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
            ->where('purchases.purchase_status', 'Received')
            ->whereBetween('purchase_payments.payment_date', [$from, $to])
            ->select('purchases.id', 'purchases.purchase_code', 'purchase_payments.payment_date', 'suppliers.supplier_name', 'suppliers.supplier_code', 'purchase_payments.payment_type', 'purchase_payments.payment_note', 'purchase_payments.payment');

        if ($request->filled('supplier_id')) {
            $query->where('purchases.supplier_id', $request->input('supplier_id'));
        }
        if ($request->filled('payment_type')) {
            $query->where('purchase_payments.payment_type', $request->input('payment_type'));
        }

        $rows = $query->get();
        if ($rows->isEmpty()) {
            return response($this->noRecords(8));
        }

        $html = '';
        $i = 0;
        $totPayment = 0;
        foreach ($rows as $r) {
            $i++;
            $html .= '<tr>'
                ."<td>{$i}</td>"
                ."<td><a title='View Invoice' href='".route('purchase.invoice', $r->id)."'>{$r->purchase_code}</a></td>"
                .'<td>'.show_date($r->payment_date).'</td>'
                ."<td>{$r->supplier_code}</td>"
                ."<td>{$r->supplier_name}</td>"
                ."<td>{$r->payment_type}</td>"
                ."<td>{$r->payment_note}</td>"
                ."<td class='text-right'>".app_number_format($r->payment).'</td>'
                .'</tr>';
            $totPayment += $r->payment;
        }

        $html .= "<tr><td class='text-right text-bold' colspan='7'><b>Total :</b></td>"
            ."<td class='text-right text-bold'>".app_number_format($totPayment).'</td></tr>';

        return response($html);
    }

    public function supplierPaymentsReport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $query = PurchasePayment::query()
            ->join('purchases', 'purchases.id', '=', 'purchase_payments.purchase_id')
            ->join('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
            ->where('purchase_payments.payment', '>', 0)
            ->whereBetween('purchase_payments.payment_date', [$from, $to])
            ->select('purchase_payments.payment_date', 'suppliers.supplier_name', 'purchase_payments.payment_type', 'purchase_payments.payment_note', 'purchase_payments.payment');

        if ($request->filled('supplier_id')) {
            $query->where('purchases.supplier_id', $request->input('supplier_id'));
        }
        if ($request->filled('payment_type')) {
            $query->where('purchase_payments.payment_type', $request->input('payment_type'));
        }

        $rows = $query->get();
        if ($rows->isEmpty()) {
            return response($this->noRecords(6));
        }

        $html = '';
        $i = 0;
        $totPayment = 0;
        foreach ($rows as $r) {
            $i++;
            $html .= '<tr>'
                ."<td>{$i}</td>"
                .'<td>'.show_date($r->payment_date).'</td>'
                ."<td>{$r->supplier_name}</td>"
                ."<td>{$r->payment_type}</td>"
                ."<td>{$r->payment_note}</td>"
                ."<td class='text-right'>".app_number_format($r->payment).'</td>'
                .'</tr>';
            $totPayment += $r->payment;
        }

        $html .= "<tr><td class='text-right text-bold' colspan='5'><b>Total :</b></td>"
            ."<td class='text-right text-bold'>".app_number_format($totPayment).'</td></tr>';

        return response($html);
    }

    // ---------------- Sales Payments Report ----------------
    public function salesPayments(Request $request)
    {
        $this->authorize('sales_payments_report');

        return view('reports.sales-payments', ['page_title' => 'Sales Payments Report']);
    }

    public function showSalesPaymentsReport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $query = SalePayment::query()
            ->join('sales', 'sales.id', '=', 'sales_payments.sales_id')
            ->join('customers', 'customers.id', '=', 'sales.customer_id')
            ->where('sales.sales_status', 'Final')
            ->where('sales_payments.payment', '>', 0)
            ->whereBetween('sales_payments.payment_date', [$from, $to])
            ->select('sales.id', 'sales.sales_code', 'sales_payments.payment_date', 'customers.customer_name', 'customers.customer_code', 'sales_payments.payment_type', 'sales_payments.payment_note', 'sales_payments.payment', 'sales_payments.created_by');

        if ($request->filled('customer_id')) {
            $query->where('sales.customer_id', $request->input('customer_id'));
        }
        if ($request->filled('payment_type')) {
            $query->where('sales_payments.payment_type', $request->input('payment_type'));
        }

        $rows = $query->get();
        if ($rows->isEmpty()) {
            return response($this->noRecords(9));
        }

        $html = '';
        $i = 0;
        $totPayment = 0;
        foreach ($rows as $r) {
            $i++;
            $html .= '<tr>'
                ."<td>{$i}</td>"
                ."<td><a title='View Invoice' href='".route('sales.invoice', $r->id)."'>{$r->sales_code}</a></td>"
                .'<td>'.show_date($r->payment_date).'</td>'
                ."<td>{$r->customer_code}</td>"
                ."<td>{$r->customer_name}</td>"
                ."<td>{$r->payment_type}</td>"
                ."<td>{$r->payment_note}</td>"
                ."<td class='text-right'>".app_number_format($r->payment).'</td>'
                ."<td>{$r->created_by}</td>"
                .'</tr>';
            $totPayment += $r->payment;
        }

        $html .= "<tr><td class='text-right text-bold' colspan='7'><b>Total :</b></td>"
            ."<td class='text-right text-bold'>".app_number_format($totPayment).'</td><td></td></tr>';

        return response($html);
    }

    public function customerPaymentsReport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $query = SalePayment::query()
            ->join('sales', 'sales.id', '=', 'sales_payments.sales_id')
            ->join('customers', 'customers.id', '=', 'sales.customer_id')
            ->where('sales_payments.payment', '>', 0)
            ->whereBetween('sales_payments.payment_date', [$from, $to])
            ->select('sales_payments.payment_date', 'customers.customer_name', 'sales_payments.payment_type', 'sales_payments.payment_note', 'sales_payments.payment', 'sales_payments.created_by');

        if ($request->filled('customer_id')) {
            $query->where('sales.customer_id', $request->input('customer_id'));
        }
        if ($request->filled('payment_type')) {
            $query->where('sales_payments.payment_type', $request->input('payment_type'));
        }

        $rows = $query->get();
        if ($rows->isEmpty()) {
            return response($this->noRecords(7));
        }

        $html = '';
        $i = 0;
        $totPayment = 0;
        foreach ($rows as $r) {
            $i++;
            $html .= '<tr>'
                ."<td>{$i}</td>"
                .'<td>'.show_date($r->payment_date).'</td>'
                ."<td>{$r->customer_name}</td>"
                ."<td>{$r->payment_type}</td>"
                ."<td>{$r->payment_note}</td>"
                ."<td class='text-right'>".app_number_format($r->payment).'</td>'
                ."<td>{$r->created_by}</td>"
                .'</tr>';
            $totPayment += $r->payment;
        }

        $html .= "<tr><td class='text-right text-bold' colspan='5'><b>Total :</b></td>"
            ."<td class='text-right text-bold'>".app_number_format($totPayment).'</td><td></td></tr>';

        return response($html);
    }

    // ---------------- Expired Items Report ----------------
    public function expiredItems(Request $request)
    {
        $this->authorize('expired_items_report');

        return view('reports.expired-items', ['page_title' => 'Expired Items Report']);
    }

    public function showExpiredItemsReport(Request $request)
    {
        $to = Carbon::parse($request->input('to_date'))->toDateString();
        $viewAll = $request->input('view_all') === 'yes';

        $query = Item::query();
        if ($request->filled('item_id')) {
            $query->where('id', $request->input('item_id'));
        }
        if (! $viewAll) {
            $query->where('expire_date', '<=', $to);
        }

        $rows = $query->get();
        if ($rows->isEmpty()) {
            return response($this->noRecords(6));
        }

        $html = '';
        $i = 0;
        foreach ($rows as $item) {
            $i++;
            $html .= '<tr>'
                ."<td>{$i}</td>"
                ."<td>{$item->item_code}</td>"
                ."<td>{$item->item_name}</td>"
                ."<td>{$item->lot_number}</td>"
                .'<td>'.show_date($item->expire_date).'</td>'
                ."<td>{$item->stock}</td>"
                .'</tr>';
        }

        return response($html);
    }

    // ---------------- Profit & Loss Report ----------------
    public function profitLoss(Request $request)
    {
        $this->authorize('profit_report');

        return view('reports.profit-loss', ['page_title' => 'Profit and Loss Report']);
    }

    public function getProfitLossReport(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $openingStockPrice = (float) DB::table('items')
            ->join('stock_entries', 'stock_entries.item_id', '=', 'items.id')
            ->selectRaw('coalesce(sum(stock_entries.qty * items.price),0) as total')
            ->value('total');

        $purchaseTaxAmt = (float) PurchaseItem::join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->where('purchases.purchase_status', 'Received')
            ->whereBetween('purchases.purchase_date', [$from, $to])
            ->sum('purchase_items.tax_amt');

        $purTotal = (float) Purchase::where('purchase_status', 'Received')
            ->whereBetween('purchase_date', [$from, $to])
            ->sum('grand_total') - $purchaseTaxAmt;

        $purOtherCharges = (float) Purchase::where('purchase_status', 'Received')
            ->whereBetween('purchase_date', [$from, $to])
            ->sum('other_charges_amt');

        $purchaseDiscountAmt = (float) Purchase::where('purchase_status', 'Received')
            ->whereBetween('purchase_date', [$from, $to])
            ->sum('tot_discount_to_all_amt');

        $purchasePaidAmount = (float) Purchase::where('purchase_status', 'Received')
            ->whereBetween('purchase_date', [$from, $to])
            ->sum('paid_amount');

        $purchaseReturnTaxAmt = (float) PurchaseReturnItem::join('purchase_returns', 'purchase_returns.id', '=', 'purchase_items_returns.return_id')
            ->whereBetween('purchase_returns.return_date', [$from, $to])
            ->sum('purchase_items_returns.tax_amt');

        $purReturnTotal = (float) PurchaseReturn::whereBetween('return_date', [$from, $to])->sum('grand_total') - $purchaseReturnTaxAmt;
        $purReturnOtherCharges = (float) PurchaseReturn::whereBetween('return_date', [$from, $to])->sum('other_charges_amt');
        $purchaseReturnDiscountAmt = (float) PurchaseReturn::whereBetween('return_date', [$from, $to])->sum('tot_discount_to_all_amt');
        $purchaseReturnPaidAmount = (float) PurchaseReturn::whereBetween('return_date', [$from, $to])->sum('paid_amount');

        $salesTaxAmt = (float) SaleItem::join('sales', 'sales.id', '=', 'sales_items.sales_id')
            ->where('sales.sales_status', 'Final')
            ->whereBetween('sales.sales_date', [$from, $to])
            ->sum('sales_items.tax_amt');

        $salOtherCharges = (float) Sale::where('sales_status', 'Final')->whereBetween('sales_date', [$from, $to])->sum('other_charges_amt');
        $salesDiscountAmt = (float) Sale::where('sales_status', 'Final')->whereBetween('sales_date', [$from, $to])->sum('tot_discount_to_all_amt');
        $salTotal = (float) Sale::where('sales_status', 'Final')->whereBetween('sales_date', [$from, $to])->sum('grand_total') - $salesTaxAmt;
        $salesPaidAmount = (float) Sale::where('sales_status', 'Final')->whereBetween('sales_date', [$from, $to])->sum('paid_amount');

        $salesReturnTaxAmt = (float) SalesReturnItem::join('sales_returns', 'sales_returns.id', '=', 'sales_items_returns.return_id')
            ->whereBetween('sales_returns.return_date', [$from, $to])
            ->sum('sales_items_returns.tax_amt');

        $salReturnTotal = (float) SalesReturn::whereBetween('return_date', [$from, $to])->sum('grand_total') - $salesReturnTaxAmt;
        $salReturnOtherCharges = (float) SalesReturn::whereBetween('return_date', [$from, $to])->sum('other_charges_amt');
        $salesReturnDiscountAmt = (float) SalesReturn::whereBetween('return_date', [$from, $to])->sum('tot_discount_to_all_amt');
        $salesReturnPaidAmount = (float) SalesReturn::whereBetween('return_date', [$from, $to])->sum('paid_amount');

        $expTotal = (float) \App\Models\Expense::whereBetween('expense_date', [$from, $to])->sum('expense_amt');

        $purchaseDueTotal = (float) Purchase::where('purchase_status', 'Received')->whereBetween('purchase_date', [$from, $to])->selectRaw('coalesce(sum(grand_total),0)-coalesce(sum(paid_amount),0) as due')->value('due');
        $purchaseReturnDueTotal = (float) PurchaseReturn::whereBetween('return_date', [$from, $to])->selectRaw('coalesce(sum(grand_total),0)-coalesce(sum(paid_amount),0) as due')->value('due');
        $salesDueTotal = (float) Sale::where('sales_status', 'Final')->whereBetween('sales_date', [$from, $to])->selectRaw('coalesce(sum(grand_total),0)-coalesce(sum(paid_amount),0) as due')->value('due');
        $salesReturnDueTotal = (float) SalesReturn::whereBetween('return_date', [$from, $to])->selectRaw('coalesce(sum(grand_total),0)-coalesce(sum(paid_amount),0) as due')->value('due');

        $salesIds = Sale::where('sales_status', 'Final')->whereBetween('sales_date', [$from, $to])->pluck('id');
        $salesReturnIds = SalesReturn::whereBetween('return_date', [$from, $to])->pluck('id');

        $salesItemSums = SaleItem::whereIn('sales_id', $salesIds)->selectRaw('coalesce(sum(sales_qty * purchase_price),0) as pur_price, coalesce(sum(total_cost),0) as sales_price, coalesce(sum(tax_amt),0) as tax_amt')->first();
        $returnItemSums = SalesReturnItem::whereIn('return_id', $salesReturnIds)->selectRaw('coalesce(sum(return_qty * purchase_price),0) as pur_price, coalesce(sum(total_cost),0) as return_price, coalesce(sum(tax_amt),0) as tax_amt')->first();

        $netSales = (float) $salesItemSums->sales_price - (float) $salesItemSums->pur_price;
        $netReturn = (float) $returnItemSums->return_price - (float) $returnItemSums->pur_price;
        $grossProfit = $netSales - $netReturn;

        $totTax = (float) $salesItemSums->tax_amt - (float) $returnItemSums->tax_amt;
        $netProfit = ($grossProfit - $totTax) - $expTotal;

        return response()->json([
            'opening_stock_price' => number_format($openingStockPrice, 2, '.', ''),
            'purchase_tax_amt' => number_format($purchaseTaxAmt, 2, '.', ''),
            'pur_total' => number_format($purTotal, 2, '.', ''),
            'pur_other_charges_amt' => number_format($purOtherCharges, 2, '.', ''),
            'purchase_discount_amt' => number_format($purchaseDiscountAmt, 2, '.', ''),
            'purchase_paid_amount' => number_format($purchasePaidAmount, 2, '.', ''),
            'purchase_due_total' => number_format($purchaseDueTotal, 2, '.', ''),
            'pur_return_total' => number_format($purReturnTotal, 2, '.', ''),
            'purchase_return_tax_amt' => number_format($purchaseReturnTaxAmt, 2, '.', ''),
            'pur_return_other_charges_amt' => number_format($purReturnOtherCharges, 2, '.', ''),
            'purchase_return_discount_amt' => number_format($purchaseReturnDiscountAmt, 2, '.', ''),
            'purchase_return_paid_amount' => number_format($purchaseReturnPaidAmount, 2, '.', ''),
            'purchase_return_due_total' => number_format($purchaseReturnDueTotal, 2, '.', ''),
            'exp_total' => number_format($expTotal, 2, '.', ''),
            'sal_total' => number_format($salTotal, 2, '.', ''),
            'sales_tax_amt' => number_format($salesTaxAmt, 2, '.', ''),
            'sal_other_charges_amt' => number_format($salOtherCharges, 2, '.', ''),
            'sales_discount_amt' => number_format($salesDiscountAmt, 2, '.', ''),
            'sales_paid_amount' => number_format($salesPaidAmount, 2, '.', ''),
            'sales_due_total' => number_format($salesDueTotal, 2, '.', ''),
            'sal_return_total' => number_format($salReturnTotal, 2, '.', ''),
            'sales_return_tax_amt' => number_format($salesReturnTaxAmt, 2, '.', ''),
            'sal_return_other_charges_amt' => number_format($salReturnOtherCharges, 2, '.', ''),
            'sales_return_discount_amt' => number_format($salesReturnDiscountAmt, 2, '.', ''),
            'sales_return_paid_amount' => number_format($salesReturnPaidAmount, 2, '.', ''),
            'sales_return_due_total' => number_format($salesReturnDueTotal, 2, '.', ''),
            'net_sales' => number_format($salesItemSums->sales_price, 2, '.', ''),
            'sales_return_total' => number_format($returnItemSums->return_price, 2, '.', ''),
            'gross_profit' => number_format($grossProfit, 2, '.', ''),
            'tot_net_profit' => number_format($netProfit, 2, '.', ''),
        ]);
    }

    public function getProfitByItem(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $rows = SaleItem::join('sales', 'sales.id', '=', 'sales_items.sales_id')
            ->join('items', 'items.id', '=', 'sales_items.item_id')
            ->whereBetween('sales.sales_date', [$from, $to])
            ->groupBy('sales_items.item_id', 'items.item_name')
            ->select(
                'sales_items.item_id',
                'items.item_name',
                DB::raw('coalesce(sum(sales_items.sales_qty),0) as sales_qty'),
                DB::raw('coalesce(sum(sales_items.tax_amt),0) as tax_amt'),
                DB::raw('coalesce(sum(sales_items.total_cost),0) as total_cost'),
                DB::raw('coalesce(sum(sales_items.purchase_price * sales_items.sales_qty),0) as purchase_price_sum')
            )
            ->get();

        if ($rows->isEmpty()) {
            return response($this->noRecords(6));
        }

        $html = '';
        $i = 0;
        $totSalesQty = $totSalesCost = $totPurchasePrice = $grossProfit = 0;
        foreach ($rows as $r) {
            $i++;
            $salesReturn = SalesReturnItem::where('item_id', $r->item_id)
                ->selectRaw('coalesce(sum(total_cost),0) as total_cost, coalesce(sum(return_qty),0) as return_qty')
                ->first();

            $qty = $r->sales_qty - $salesReturn->return_qty;
            $purchasePrice = $r->sales_qty > 0 ? ($r->purchase_price_sum / $r->sales_qty) * $qty : 0;
            $totalCost = $r->total_cost - $salesReturn->total_cost;
            $profit = $totalCost - $purchasePrice;

            $html .= '<tr>'
                ."<td>{$i}</td>"
                ."<td>{$r->item_name}</td>"
                ."<td>{$qty}</td>"
                ."<td style='text-align:right;'>".app_number_format($totalCost).'</td>'
                ."<td style='text-align:right;'>".app_number_format($purchasePrice).'</td>'
                ."<td style='text-align:right;'>".app_number_format($profit).'</td>'
                .'</tr>';

            $totSalesQty += $qty;
            $totSalesCost += $totalCost;
            $totPurchasePrice += $purchasePrice;
            $grossProfit += $profit;
        }

        $html .= "<tr><td class='text-right text-bold' colspan='2'><b>Total :</b></td>"
            ."<td class='text-bold'>{$totSalesQty}</td>"
            ."<td class='text-right text-bold'>".app_number_format($totSalesCost).'</td>'
            ."<td class='text-right text-bold'>".app_number_format($totPurchasePrice).'</td>'
            ."<td class='text-right text-bold'>".app_number_format($grossProfit).'</td></tr>';

        return response($html);
    }

    public function getProfitByInvoice(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $rows = Sale::with('customer')
            ->where('sales_status', 'Final')
            ->whereBetween('sales_date', [$from, $to])
            ->get();

        if ($rows->isEmpty()) {
            return response($this->noRecords(8));
        }

        $html = '';
        $i = 0;
        $totPurchasePrice = $totSalesCost = $totProfit = $totDiscount = 0;
        foreach ($rows as $s) {
            $i++;
            $itemSums = SaleItem::where('sales_id', $s->id)
                ->selectRaw('coalesce(sum(purchase_price * sales_qty),0) as purchase_price, coalesce(sum(total_cost),0) as total_cost')
                ->first();

            $returnSums = SalesReturnItem::where('sales_id', $s->id)
                ->selectRaw('coalesce(sum(purchase_price * return_qty),0) as purchase_price, coalesce(sum(total_cost),0) as total_cost')
                ->first();

            $discount = (float) ($s->tot_discount_to_all_amt ?? 0);
            $purchasePrice = $itemSums->purchase_price - $returnSums->purchase_price;
            $salesPrice = $itemSums->total_cost - $returnSums->total_cost;
            $profit = ($salesPrice - $purchasePrice) - $discount;

            $html .= '<tr>'
                ."<td>{$i}</td>"
                ."<td>{$s->sales_code}</td>"
                .'<td>'.show_date($s->sales_date).'</td>'
                ."<td>{$s->customer?->customer_name}</td>"
                ."<td style='text-align:right;'>".app_number_format($salesPrice).'</td>'
                ."<td style='text-align:right;'>".app_number_format($purchasePrice).'</td>'
                ."<td style='text-align:right;'>".app_number_format($discount).'</td>'
                ."<td style='text-align:right;'>".app_number_format($profit).'</td>'
                .'</tr>';

            $totPurchasePrice += $purchasePrice;
            $totSalesCost += $salesPrice;
            $totProfit += $profit;
            $totDiscount += $discount;
        }

        $html .= "<tr><td class='text-right text-bold' colspan='4'><b>Total :</b></td>"
            ."<td class='text-right text-bold'>".app_number_format($totSalesCost).'</td>'
            ."<td class='text-right text-bold'>".app_number_format($totPurchasePrice).'</td>'
            ."<td class='text-right text-bold'>".app_number_format($totDiscount).'</td>'
            ."<td class='text-right text-bold'>".app_number_format($totProfit).'</td></tr>';

        return response($html);
    }
}
