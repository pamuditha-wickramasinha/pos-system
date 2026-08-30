<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerOpeningBalancePayment;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\SalesReturn;
use App\Models\SalesReturnPayment;
use App\Support\DatatableHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    public function index(): View
    {
        $this->authorize('customers_view');

        return view('customer.list', ['page_title' => 'Customers List']);
    }

    public function add(): View
    {
        $this->authorize('customers_add');

        return view('customer.form', ['page_title' => 'Customers']);
    }

    public function edit(Customer $customer): View
    {
        $this->authorize('customers_edit');

        return view('customer.form', array_merge(
            ['page_title' => 'Customers', 'q_id' => $customer->id],
            $customer->only([
                'customer_name', 'mobile', 'phone', 'email', 'country_id', 'state_id',
                'city', 'postcode', 'address', 'gstin', 'tax_number', 'opening_balance',
            ])
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), ['customer_name' => 'required']);

        if ($validator->fails()) {
            return response('Please Fill Compulsory(* marked) Fields.');
        }

        $companyInit = Company::query()->value('customer_init') ?? 'CU';
        $nextId = (Customer::max('id') ?? 0) + 1;

        Customer::create([
            'customer_code' => $companyInit.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT),
            'customer_name' => $request->input('customer_name'),
            'mobile' => $request->input('mobile'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'gstin' => $request->input('gstin'),
            'tax_number' => $request->input('tax_number'),
            'opening_balance' => $request->input('opening_balance') ?: 0,
            'country_id' => $request->input('country'),
            'state_id' => $request->input('state') ?: null,
            'city' => $request->input('city'),
            'postcode' => $request->input('postcode'),
            'address' => $request->input('address'),
            'created_by' => $request->user()->username,
            'status' => true,
        ]);

        session()->flash('success', 'Success!! New Customer Added Successfully!');

        return response('success');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), ['customer_name' => 'required']);

        if ($validator->fails()) {
            return response('Please Fill Compulsory(* marked) Fields.');
        }

        Customer::whereKey($request->input('q_id'))->update([
            'customer_name' => $request->input('customer_name'),
            'mobile' => $request->input('mobile'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'gstin' => $request->input('gstin'),
            'tax_number' => $request->input('tax_number'),
            'opening_balance' => $request->input('opening_balance') ?: 0,
            'country_id' => $request->input('country'),
            'state_id' => $request->input('state') ?: null,
            'city' => $request->input('city'),
            'postcode' => $request->input('postcode'),
            'address' => $request->input('address'),
        ]);

        session()->flash('success', 'Success!! Customer Updated Successfully!');

        return response('success');
    }

    public function ajaxList(Request $request)
    {
        $this->authorize('customers_view');

        return DataTables::of(Customer::query())
            ->addColumn('checkbox', fn (Customer $c) => $c->id === 1 ? '<span class="text-blue">NA</span>' : DatatableHtml::checkbox($c->id))
            ->addColumn('total_paid', function (Customer $c) {
                if (! Schema::hasTable('sales')) {
                    return app_number_format(0);
                }

                return app_number_format((float) \Illuminate\Support\Facades\DB::table('sales')->where('customer_id', $c->id)->sum('paid_amount'));
            })
            ->editColumn('sales_due', fn (Customer $c) => $c->sales_due != 0 ? app_number_format($c->sales_due) : 0)
            ->editColumn('sales_return_due', fn (Customer $c) => $c->sales_return_due != 0 ? app_number_format($c->sales_return_due) : 0)
            ->addColumn('status_badge', fn (Customer $c) => DatatableHtml::statusBadge($c->id, $c->status))
            ->addColumn('actions', fn (Customer $c) => DatatableHtml::actionMenu([
                ['label' => 'Edit', 'icon' => 'fa-edit text-blue', 'url' => route('customers.edit', $c), 'can' => $request->user()->can('customers_edit') && $c->id !== 1],
                ['label' => 'Pay Due Payments', 'icon' => 'fa-money text-blue', 'onclick' => "pay_now({$c->id})", 'can' => $request->user()->can('sales_payment_add') && $c->id !== 1],
                ['label' => 'Pay Return Due', 'icon' => 'fa-money text-blue', 'onclick' => "pay_return_due({$c->id})", 'can' => $request->user()->can('sales_return_payment_add') && $c->id !== 1],
                ['label' => 'Delete', 'icon' => 'fa-trash text-red', 'onclick' => "delete_customers({$c->id})", 'can' => $request->user()->can('customers_delete') && $c->id !== 1],
            ]))
            ->rawColumns(['checkbox', 'status_badge', 'actions'])
            ->make(true);
    }

    public function updateStatus(Request $request)
    {
        $this->authorize('customers_edit');

        Customer::whereKey($request->input('id'))->update(['status' => $request->input('status')]);

        return response('success');
    }

    public function destroy(Request $request)
    {
        $this->authorize('customers_delete');

        Customer::whereIn('id', explode(',', $request->input('q_id')))->where('id', '!=', 1)->delete();

        return response('success');
    }

    public function multiDestroy(Request $request)
    {
        $this->authorize('customers_delete');

        Customer::whereIn('id', $request->input('checkbox', []))->where('id', '!=', 1)->delete();

        return response('success');
    }

    public function deleteOpeningBalanceEntry(Request $request)
    {
        $this->authorize('sales_payment_delete');

        \App\Models\CustomerOpeningBalancePayment::whereKey($request->input('entry_id'))->delete();

        return response('success');
    }

    public function search(Request $request)
    {
        $term = strtoupper((string) $request->input('searchTerm'));

        $customers = Customer::query()
            ->when($term, fn ($q) => $q->whereRaw('(upper(customer_name) like ? or upper(mobile) like ?)', ["%{$term}%", "%{$term}%"]))
            ->limit(10)
            ->get(['id', 'customer_code', 'customer_name', 'mobile'])
            ->map(fn ($c) => ['id' => $c->id, 'text' => $c->customer_name, 'mobile' => $c->mobile]);

        return response()->json($customers);
    }

    public function showPayNowModal(Request $request)
    {
        $this->authorize('sales_payment_add');

        $customer = Customer::findOrFail($request->input('customer_id'));

        $obPaid = (float) CustomerOpeningBalancePayment::where('customer_id', $customer->id)->sum('payment');
        $openingBalanceDue = (float) $customer->opening_balance - $obPaid;
        $totalSalesAmount = (float) Sale::where('customer_id', $customer->id)->sum('grand_total');
        $totalPaidAmount = (float) Sale::where('customer_id', $customer->id)->sum('paid_amount');
        $dueAmount = number_format($customer->sales_due + $openingBalanceDue, 2, '.', '');

        return view('customer.pay-now-modal', [
            'customer' => $customer,
            'openingBalanceDue' => $openingBalanceDue,
            'totalSalesAmount' => $totalSalesAmount,
            'totalPaidAmount' => $totalPaidAmount,
            'dueAmount' => $dueAmount,
        ]);
    }

    public function savePayment(Request $request)
    {
        $this->authorize('sales_payment_add');

        $amount = (float) $request->input('amount', 0);
        if ($amount <= 0 || ! $request->filled('payment_type')) {
            return response('Please Enter Valid Amount!');
        }

        $customer = Customer::findOrFail($request->input('customer_id'));
        $paymentDate = \Illuminate\Support\Carbon::parse($request->input('payment_date'));

        DB::beginTransaction();
        try {
            $obPaid = (float) CustomerOpeningBalancePayment::where('customer_id', $customer->id)->sum('payment');
            $openingBalanceDue = (float) $customer->opening_balance - $obPaid;

            if ($openingBalanceDue > 0) {
                $payAmount = $amount <= $openingBalanceDue ? $amount : $openingBalanceDue;

                CustomerOpeningBalancePayment::create([
                    'customer_id' => $customer->id,
                    'payment_date' => $paymentDate,
                    'payment_type' => $request->input('payment_type'),
                    'payment' => $payAmount,
                    'payment_note' => $request->input('payment_note'),
                    'created_by' => $request->user()->username,
                    'status' => true,
                ]);

                $amount -= $payAmount;
            }

            if ($amount > 0) {
                $openSales = Sale::whereColumn('grand_total', '!=', 'paid_amount')
                    ->where('customer_id', $customer->id)
                    ->get(['id', 'grand_total', 'paid_amount']);

                foreach ($openSales as $sale) {
                    if ($amount <= 0) {
                        break;
                    }

                    $salesDue = (float) $sale->grand_total - (float) $sale->paid_amount;
                    if ($salesDue <= 0) {
                        continue;
                    }

                    $payAmount = $amount <= $salesDue ? $amount : $salesDue;

                    SalePayment::create([
                        'sales_id' => $sale->id,
                        'payment_date' => $paymentDate,
                        'payment_type' => $request->input('payment_type'),
                        'payment' => $payAmount,
                        'payment_note' => $request->input('payment_note'),
                        'created_by' => $request->user()->username,
                        'status' => true,
                    ]);

                    $amount -= $payAmount;
                    $this->updateSalesPaymentStatus($sale->id, $customer->id);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response('failed');
        }

        return response('success');
    }

    protected function updateSalesPaymentStatus(int $salesId, int $customerId): void
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

    public function showPayReturnDueModal(Request $request)
    {
        $this->authorize('sales_return_payment_add');

        $customer = Customer::findOrFail($request->input('customer_id'));

        $totalReturnAmount = (float) SalesReturn::where('customer_id', $customer->id)->sum('grand_total');
        $totalReturnPaidAmount = (float) SalesReturn::where('customer_id', $customer->id)->sum('paid_amount');
        $dueAmount = number_format($totalReturnAmount - $totalReturnPaidAmount, 2, '.', '');

        return view('customer.pay-return-due-modal', [
            'customer' => $customer,
            'totalReturnAmount' => $totalReturnAmount,
            'totalReturnPaidAmount' => $totalReturnPaidAmount,
            'dueAmount' => $dueAmount,
        ]);
    }

    public function saveReturnDuePayment(Request $request)
    {
        $this->authorize('sales_payment_add');

        $amount = (float) $request->input('amount', 0);
        if ($amount <= 0 || ! $request->filled('payment_type')) {
            return response('Please Enter Valid Amount!');
        }

        $customer = Customer::findOrFail($request->input('customer_id'));
        $paymentDate = \Illuminate\Support\Carbon::parse($request->input('payment_date'));

        DB::beginTransaction();
        try {
            $openReturns = SalesReturn::whereColumn('grand_total', '!=', 'paid_amount')
                ->where('customer_id', $customer->id)
                ->get(['id', 'grand_total', 'paid_amount']);

            foreach ($openReturns as $return) {
                if ($amount <= 0) {
                    break;
                }

                $returnDue = (float) $return->grand_total - (float) $return->paid_amount;
                if ($returnDue <= 0) {
                    continue;
                }

                $payAmount = $amount <= $returnDue ? $amount : $returnDue;

                SalesReturnPayment::create([
                    'return_id' => $return->id,
                    'payment_date' => $paymentDate,
                    'payment_type' => $request->input('payment_type'),
                    'payment' => $payAmount,
                    'payment_note' => $request->input('payment_note'),
                    'created_by' => $request->user()->username,
                    'status' => true,
                ]);

                $amount -= $payAmount;
                $this->updateReturnPaymentStatus($return->id, $customer->id);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response('failed');
        }

        return response('success');
    }

    protected function updateReturnPaymentStatus(int $returnId, int $customerId): void
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
}
