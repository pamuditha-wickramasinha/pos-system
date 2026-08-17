<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\SalesReturn;
use App\Models\SalesReturnPayment;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        if (! $request->user()->can('dashboard_view')) {
            return view('dashboard.empty', ['page_title' => 'Dashboard']);
        }

        $today = now()->toDateString();
        $year = now()->year;

        $totSalesGrandTotal = (float) Sale::where('sales_status', 'Final')->sum('grand_total');
        $totSalesReturn = (float) SalesReturn::sum('grand_total');
        $todaysPaymentReceived = (float) SalePayment::where('payment_date', $today)->sum('payment');
        $todaysPaymentPaid = (float) SalesReturnPayment::where('payment_date', $today)->sum('payment');
        $todaysTotalSales = (float) Sale::where('sales_status', 'Final')->where('sales_date', $today)->sum('grand_total');
        $todaysTotalSalesReturn = (float) SalesReturn::where('return_date', $today)->sum('grand_total');

        $purchaseMonthly = DB::table('purchases')
            ->selectRaw('coalesce(sum(grand_total),0) as total, month(purchase_date) as m')
            ->where('purchase_status', 'Received')
            ->whereYear('purchase_date', $year)
            ->groupBy('m')
            ->pluck('total', 'm');

        $salesMonthly = DB::table('sales')
            ->selectRaw('coalesce(sum(grand_total),0) as total, month(sales_date) as m')
            ->where('sales_status', 'Final')
            ->whereYear('sales_date', $year)
            ->groupBy('m')
            ->pluck('total', 'm');

        $purchaseByMonth = [];
        $salesByMonth = [];
        for ($m = 1; $m <= 12; $m++) {
            $purchaseByMonth[] = (float) ($purchaseMonthly[$m] ?? 0);
            $salesByMonth[] = (float) ($salesMonthly[$m] ?? 0);
        }

        $topItems = DB::table('sales_items')
            ->join('items', 'items.id', '=', 'sales_items.item_id')
            ->join('sales', 'sales.id', '=', 'sales_items.sales_id')
            ->where('sales.sales_status', 'Final')
            ->selectRaw('items.item_name, coalesce(sum(sales_items.sales_qty),0) as qty')
            ->groupBy('items.id', 'items.item_name')
            ->havingRaw('coalesce(sum(sales_items.sales_qty),0) > 0')
            ->limit(10)
            ->get();

        return view('dashboard.index', [
            'page_title' => 'Dashboard',
            'totSup' => Supplier::count(),
            'totPro' => Item::count(),
            'totCust' => Customer::count(),
            'totPur' => Purchase::where('purchase_status', 'Received')->count(),
            'totSal' => Sale::where('sales_status', 'Final')->count(),
            'totSalGrandTotal' => $totSalesGrandTotal - $totSalesReturn,
            'totExp' => (float) Expense::sum('expense_amt'),
            'salesDue' => (float) Sale::where('sales_status', 'Final')->selectRaw('coalesce(sum(grand_total),0)-coalesce(sum(paid_amount),0) as due')->value('due'),
            'purchaseDue' => (float) Purchase::where('purchase_status', 'Received')->selectRaw('coalesce(sum(grand_total),0)-coalesce(sum(paid_amount),0) as due')->value('due'),
            'todayPaymentReceived' => $todaysPaymentReceived - $todaysPaymentPaid,
            'todaysTotalPurchase' => (float) Purchase::where('purchase_date', $today)->sum('grand_total'),
            'todaysTotalSales' => $todaysTotalSales - $todaysTotalSalesReturn,
            'todaysTotalExpense' => (float) Expense::where('expense_date', $today)->sum('expense_amt'),
            'recentItems' => Item::where('status', true)->orderByDesc('id')->limit(5)->get(['id', 'item_name', 'sales_price']),
            'expiredItems' => Item::with('category')->where('status', true)->whereNotNull('expire_date')->where('expire_date', '<=', $today)->limit(10)->get(),
            'stockAlertItems' => Item::with('category')->where('status', true)->whereColumn('stock', '<=', 'alert_qty')->orderByDesc('id')->limit(10)->get(),
            'purchaseByMonth' => $purchaseByMonth,
            'salesByMonth' => $salesByMonth,
            'topItems' => $topItems,
        ]);
    }
}
