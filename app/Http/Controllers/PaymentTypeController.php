<?php

namespace App\Http\Controllers;

use App\Models\PaymentType;
use App\Support\DatatableHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PaymentTypeController extends Controller
{
    public function index(): View
    {
        $this->authorize('payment_types_view');

        return view('payment-type.list', ['page_title' => 'Payment Types List']);
    }

    public function add(): View
    {
        $this->authorize('payment_types_add');

        return view('payment-type.form', ['page_title' => 'Payment Types']);
    }

    public function edit(PaymentType $paymentType): View
    {
        $this->authorize('payment_types_edit');

        return view('payment-type.form', [
            'page_title' => 'Payment Types',
            'q_id' => $paymentType->id,
            'payment_type_name' => $paymentType->payment_type,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), ['payment_type_name' => 'required']);

        if ($validator->fails()) {
            return response('Please Enter Payment Type Name.');
        }

        if (PaymentType::whereRaw('upper(payment_type) = upper(?)', [$request->input('payment_type_name')])->exists()) {
            return response('This Payment Type Name Already Exist.');
        }

        PaymentType::create([
            'payment_type' => $request->input('payment_type_name'),
            'status' => true,
        ]);

        session()->flash('success', 'Success!! Record Added Successfully!');

        return response('success');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), ['payment_type_name' => 'required', 'q_id' => 'required']);

        if ($validator->fails()) {
            return response('Please Enter Payment Type Name.');
        }

        $id = $request->input('q_id');

        if (PaymentType::whereRaw('upper(payment_type) = upper(?)', [$request->input('payment_type_name')])->where('id', '!=', $id)->exists()) {
            return response('This Payment Type Name Already Exist.');
        }

        PaymentType::whereKey($id)->update(['payment_type' => $request->input('payment_type_name')]);

        session()->flash('success', 'Success!! Record Updated Successfully!');

        return response('success');
    }

    public function ajaxList(Request $request)
    {
        $this->authorize('payment_types_view');

        return DataTables::of(PaymentType::query())
            ->addColumn('status_badge', fn (PaymentType $p) => DatatableHtml::statusBadge($p->id, $p->status))
            ->addColumn('actions', fn (PaymentType $p) => DatatableHtml::actionMenu([
                ['label' => 'Edit', 'icon' => 'fa-edit text-blue', 'url' => route('payment_types.edit', $p), 'can' => $request->user()->can('payment_types_edit')],
                ['label' => 'Delete', 'icon' => 'fa-trash text-red', 'onclick' => "delete_payment_type({$p->id})", 'can' => $request->user()->can('payment_types_delete')],
            ]))
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function updateStatus(Request $request)
    {
        $this->authorize('payment_types_edit');

        PaymentType::whereKey($request->input('id'))->update(['status' => $request->input('status')]);

        return response('success');
    }

    public function destroy(Request $request)
    {
        $this->authorize('payment_types_delete');

        PaymentType::whereKey($request->input('q_id'))->delete();

        return response('success');
    }
}
