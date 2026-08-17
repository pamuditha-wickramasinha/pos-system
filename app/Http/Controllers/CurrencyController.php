<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Support\DatatableHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CurrencyController extends Controller
{
    public function view(): View
    {
        $this->authorize('currency_view');

        return view('currency.list', ['page_title' => 'Currencies List']);
    }

    public function add(): View
    {
        $this->authorize('currency_add');

        return view('currency.form', ['page_title' => 'Currency']);
    }

    public function edit(Currency $currency): View
    {
        $this->authorize('currency_edit');

        return view('currency.form', [
            'page_title' => 'Currency',
            'q_id' => $currency->id,
            'currency_code' => $currency->currency_code,
            'currency_name' => $currency->currency_name,
            'currency' => $currency->currency,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), ['currency_name' => 'required', 'currency' => 'required']);

        if ($validator->fails()) {
            return response('Please Enter Compulsory(*) Fields!');
        }

        if (Currency::whereRaw('upper(currency_name) = upper(?)', [$request->input('currency_name')])->exists()) {
            return response('This Currency Name or Symbol Already Exist!');
        }

        Currency::create([
            'currency_code' => $request->input('currency_code'),
            'currency_name' => $request->input('currency_name'),
            'currency' => $request->input('currency'),
            'status' => true,
        ]);

        session()->flash('success', 'Success!! New Currency Added Successfully!');

        return response('success');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), ['currency_name' => 'required', 'currency' => 'required', 'q_id' => 'required']);

        if ($validator->fails()) {
            return response('Please Enter Compulsory(*) Fields!');
        }

        $id = $request->input('q_id');

        if (Currency::whereRaw('upper(currency_name) = upper(?)', [$request->input('currency_name')])->where('id', '!=', $id)->exists()) {
            return response('This Currency Name Already Exist!');
        }

        Currency::whereKey($id)->update([
            'currency_code' => $request->input('currency_code'),
            'currency_name' => $request->input('currency_name'),
            'currency' => $request->input('currency'),
        ]);

        session()->flash('success', 'Success!! Currency Updated Successfully!');

        return response('success');
    }

    public function ajaxList(Request $request)
    {
        $this->authorize('currency_view');

        return DataTables::of(Currency::query())
            ->addColumn('checkbox', fn (Currency $c) => DatatableHtml::checkbox($c->id))
            ->addColumn('status_badge', fn (Currency $c) => DatatableHtml::statusBadge($c->id, $c->status))
            ->addColumn('actions', fn (Currency $c) => DatatableHtml::actionMenu([
                ['label' => 'Edit', 'icon' => 'fa-edit text-blue', 'url' => route('currency.edit', $c), 'can' => $request->user()->can('currency_edit')],
                ['label' => 'Delete', 'icon' => 'fa-trash text-red', 'onclick' => "delete_currency({$c->id})", 'can' => $request->user()->can('currency_delete')],
            ]))
            ->rawColumns(['checkbox', 'status_badge', 'actions'])
            ->make(true);
    }

    public function updateStatus(Request $request)
    {
        $this->authorize('currency_edit');

        Currency::whereKey($request->input('id'))->update(['status' => $request->input('status')]);

        return response('success');
    }

    public function destroy(Request $request)
    {
        $this->authorize('currency_delete');

        Currency::whereKey($request->input('q_id'))->delete();

        return response('success');
    }

    public function multiDestroy(Request $request)
    {
        $this->authorize('currency_delete');

        Currency::whereIn('id', explode(',', implode(',', $request->input('checkbox', []))))->delete();

        return response('success');
    }
}
