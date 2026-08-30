<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnPayment;
use App\Models\Supplier;
use App\Models\SupplierOpeningBalancePayment;
use App\Support\DatatableHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    public function index(): View
    {
        $this->authorize('suppliers_view');

        return view('supplier.list', ['page_title' => 'Suppliers List']);
    }

    public function add(): View
    {
        $this->authorize('suppliers_add');

        return view('supplier.form', ['page_title' => 'Suppliers']);
    }

    public function edit(Supplier $supplier): View
    {
        $this->authorize('suppliers_edit');

        return view('supplier.form', array_merge(
            ['page_title' => 'Suppliers', 'q_id' => $supplier->id],
            $supplier->only([
                'supplier_name', 'mobile', 'phone', 'email', 'country_id', 'state_id',
                'city', 'postcode', 'address', 'gstin', 'tax_number', 'opening_balance',
            ])
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), ['supplier_name' => 'required']);

        if ($validator->fails()) {
            return response('Please Fill Compulsory(* marked) Fields.');
        }

        if ($request->filled('mobile') && Supplier::where('mobile', $request->input('mobile'))->exists()) {
            return response('Sorry!This Mobile Number already Exist.');
        }

        $companyInit = Company::query()->value('supplier_init') ?? 'SU';
        $nextId = (Supplier::max('id') ?? 0) + 1;

        Supplier::create([
            'supplier_code' => $companyInit.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT),
            'supplier_name' => $request->input('supplier_name'),
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

        session()->flash('success', 'Success!! New Supplier Added Successfully!');

        return response('success');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), ['supplier_name' => 'required']);

        if ($validator->fails()) {
            return response('Please Enter suppliers name.');
        }

        Supplier::whereKey($request->input('q_id'))->update([
            'supplier_name' => $request->input('supplier_name'),
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

        session()->flash('success', 'Success!! Supplier Updated Successfully!');

        return response('success');
    }

    public function ajaxList(Request $request)
    {
        $this->authorize('suppliers_view');

        return DataTables::of(Supplier::query())
            ->addColumn('checkbox', fn (Supplier $s) => DatatableHtml::checkbox($s->id))
            ->editColumn('purchase_due', fn (Supplier $s) => $s->purchase_due != 0 ? app_number_format($s->purchase_due) : 0)
            ->editColumn('purchase_return_due', fn (Supplier $s) => $s->purchase_return_due != 0 ? app_number_format($s->purchase_return_due) : 0)
            ->addColumn('status_badge', fn (Supplier $s) => DatatableHtml::statusBadge($s->id, $s->status))
            ->addColumn('actions', fn (Supplier $s) => DatatableHtml::actionMenu([
                ['label' => 'Edit', 'icon' => 'fa-edit text-blue', 'url' => route('suppliers.edit', $s), 'can' => $request->user()->can('suppliers_edit')],
                ['label' => 'Pay Due Payments', 'icon' => 'fa-money text-blue', 'onclick' => "pay_now({$s->id})", 'can' => $request->user()->can('purchase_payment_add')],
                ['label' => 'Pay Return Due', 'icon' => 'fa-money text-blue', 'onclick' => "pay_return_due({$s->id})", 'can' => $request->user()->can('purchase_return_payment_add')],
                ['label' => 'Delete', 'icon' => 'fa-trash text-red', 'onclick' => "delete_suppliers({$s->id})", 'can' => $request->user()->can('suppliers_delete')],
            ]))
            ->rawColumns(['checkbox', 'status_badge', 'actions'])
            ->make(true);
    }

    public function updateStatus(Request $request)
    {
        $this->authorize('suppliers_edit');

        Supplier::whereKey($request->input('id'))->update(['status' => $request->input('status')]);

        return response('success');
    }

    public function destroy(Request $request)
    {
        $this->authorize('suppliers_delete');

        Supplier::whereIn('id', explode(',', $request->input('q_id')))->delete();

        return response('success');
    }

    public function multiDestroy(Request $request)
    {
        $this->authorize('suppliers_delete');

        Supplier::whereIn('id', $request->input('checkbox', []))->delete();

        return response('success');
    }

    public function deleteOpeningBalanceEntry(Request $request)
    {
        $this->authorize('sales_payment_delete');

        \App\Models\SupplierOpeningBalancePayment::whereKey($request->input('entry_id'))->delete();

        return response('success');
    }

    public function search(Request $request)
    {
        $term = strtoupper((string) $request->input('searchTerm'));

        $suppliers = Supplier::query()
            ->when($term, fn ($q) => $q->whereRaw('(upper(supplier_name) like ? or upper(mobile) like ?)', ["%{$term}%", "%{$term}%"]))
            ->limit(10)
            ->get(['id', 'supplier_code', 'supplier_name', 'mobile'])
            ->map(fn ($s) => ['id' => $s->id, 'text' => $s->supplier_name, 'mobile' => $s->mobile]);

        return response()->json($suppliers);
    }

    public function showPayNowModal(Request $request)
    {
        $this->authorize('purchase_payment_add');

        $supplier = Supplier::findOrFail($request->input('supplier_id'));

        $obPaid = (float) SupplierOpeningBalancePayment::where('supplier_id', $supplier->id)->sum('payment');
        $openingBalanceDue = (float) $supplier->opening_balance - $obPaid;
        $totalPurchaseAmount = (float) Purchase::where('supplier_id', $supplier->id)->sum('grand_total');
        $totalPaidAmount = (float) Purchase::where('supplier_id', $supplier->id)->sum('paid_amount');
        $dueAmount = number_format($supplier->purchase_due + $openingBalanceDue, 2, '.', '');

        return view('supplier.pay-now-modal', [
            'supplier' => $supplier,
            'openingBalanceDue' => $openingBalanceDue,
            'totalPurchaseAmount' => $totalPurchaseAmount,
            'totalPaidAmount' => $totalPaidAmount,
            'dueAmount' => $dueAmount,
        ]);
    }

    public function savePayment(Request $request)
    {
        $this->authorize('purchase_payment_add');

        $amount = (float) $request->input('amount', 0);
        if ($amount <= 0 || ! $request->filled('payment_type')) {
            return response('Please Enter Valid Amount!');
        }

        $supplier = Supplier::findOrFail($request->input('supplier_id'));
        $paymentDate = \Illuminate\Support\Carbon::parse($request->input('payment_date'));

        DB::beginTransaction();
        try {
            $obPaid = (float) SupplierOpeningBalancePayment::where('supplier_id', $supplier->id)->sum('payment');
            $openingBalanceDue = (float) $supplier->opening_balance - $obPaid;

            while ($amount > 0) {
                if ($openingBalanceDue > 0 && $amount <= $openingBalanceDue) {
                    SupplierOpeningBalancePayment::create([
                        'supplier_id' => $supplier->id,
                        'payment_date' => $paymentDate,
                        'payment_type' => $request->input('payment_type'),
                        'payment' => $amount,
                        'payment_note' => $request->input('payment_note'),
                        'created_by' => $request->user()->username,
                        'status' => true,
                    ]);
                    $amount = 0;

                    break;
                }

                if ($openingBalanceDue > 0 && $amount >= $openingBalanceDue) {
                    SupplierOpeningBalancePayment::create([
                        'supplier_id' => $supplier->id,
                        'payment_date' => $paymentDate,
                        'payment_type' => $request->input('payment_type'),
                        'payment' => $openingBalanceDue,
                        'payment_note' => $request->input('payment_note'),
                        'created_by' => $request->user()->username,
                        'status' => true,
                    ]);
                    $amount -= $openingBalanceDue;
                    $openingBalanceDue = 0;
                }

                if ($amount <= 0) {
                    break;
                }

                $openPurchases = Purchase::whereColumn('grand_total', '!=', 'paid_amount')
                    ->where('supplier_id', $supplier->id)
                    ->get(['id', 'grand_total', 'paid_amount']);

                if ($openPurchases->isEmpty()) {
                    break;
                }

                foreach ($openPurchases as $purchase) {
                    if ($amount <= 0) {
                        break;
                    }

                    $purchaseDue = (float) $purchase->grand_total - (float) $purchase->paid_amount;
                    if ($purchaseDue <= 0) {
                        continue;
                    }

                    $payAmount = $amount <= $purchaseDue ? $amount : $purchaseDue;

                    PurchasePayment::create([
                        'purchase_id' => $purchase->id,
                        'payment_date' => $paymentDate,
                        'payment_type' => $request->input('payment_type'),
                        'payment' => $payAmount,
                        'payment_note' => $request->input('payment_note'),
                        'created_by' => $request->user()->username,
                        'status' => true,
                    ]);

                    $amount -= $payAmount;
                    $this->updatePurchasePaymentStatus($purchase->id, $supplier->id);
                }

                break;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response('failed');
        }

        return response('success');
    }

    protected function updatePurchasePaymentStatus(int $purchaseId, int $supplierId): void
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

    public function showPayReturnDueModal(Request $request)
    {
        $this->authorize('purchase_return_payment_add');

        $supplier = Supplier::findOrFail($request->input('supplier_id'));

        $totalReturnAmount = (float) PurchaseReturn::where('supplier_id', $supplier->id)->sum('grand_total');
        $totalReturnPaidAmount = (float) PurchaseReturn::where('supplier_id', $supplier->id)->sum('paid_amount');
        $dueAmount = number_format($totalReturnAmount - $totalReturnPaidAmount, 2, '.', '');

        return view('supplier.pay-return-due-modal', [
            'supplier' => $supplier,
            'totalReturnAmount' => $totalReturnAmount,
            'totalReturnPaidAmount' => $totalReturnPaidAmount,
            'dueAmount' => $dueAmount,
        ]);
    }

    public function saveReturnDuePayment(Request $request)
    {
        $this->authorize('purchase_payment_add');

        $amount = (float) $request->input('amount', 0);
        if ($amount <= 0 || ! $request->filled('payment_type')) {
            return response('Please Enter Valid Amount!');
        }

        $supplier = Supplier::findOrFail($request->input('supplier_id'));
        $paymentDate = \Illuminate\Support\Carbon::parse($request->input('payment_date'));

        DB::beginTransaction();
        try {
            $openReturns = PurchaseReturn::whereColumn('grand_total', '!=', 'paid_amount')
                ->where('supplier_id', $supplier->id)
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

                PurchaseReturnPayment::create([
                    'return_id' => $return->id,
                    'payment_date' => $paymentDate,
                    'payment_type' => $request->input('payment_type'),
                    'payment' => $payAmount,
                    'payment_note' => $request->input('payment_note'),
                    'created_by' => $request->user()->username,
                    'status' => true,
                ]);

                $amount -= $payAmount;
                $this->updateReturnPaymentStatus($return->id, $supplier->id);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response('failed');
        }

        return response('success');
    }

    protected function updateReturnPaymentStatus(int $returnId, int $supplierId): void
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
}
