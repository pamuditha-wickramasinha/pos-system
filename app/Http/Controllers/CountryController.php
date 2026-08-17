<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Support\DatatableHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CountryController extends Controller
{
    public function index(): View
    {
        $this->authorize('places_view');

        return view('country.list', ['page_title' => 'Countries List']);
    }

    public function add(): View
    {
        $this->authorize('places_add');

        return view('country.form', ['page_title' => 'Country']);
    }

    public function edit(Country $country): View
    {
        $this->authorize('places_edit');

        return view('country.form', [
            'page_title' => 'Country',
            'q_id' => $country->id,
            'country' => $country->country,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), ['country_name' => 'required']);

        if ($validator->fails()) {
            return response('Please Enter country name.');
        }

        if (Country::whereRaw('upper(country) = upper(?)', [$request->input('country_name')])->exists()) {
            return response('Country Name already Exist.');
        }

        Country::create(['country' => $request->input('country_name'), 'status' => true]);

        session()->flash('success', 'Success!! New Country Name Added Successfully!');

        return response('success');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), ['country_name' => 'required', 'q_id' => 'required']);

        if ($validator->fails()) {
            return response('Please Enter country name.');
        }

        $id = $request->input('q_id');

        if (Country::whereRaw('upper(country) = upper(?)', [$request->input('country_name')])->where('id', '!=', $id)->exists()) {
            return response('Country Name already Exist.');
        }

        Country::whereKey($id)->update(['country' => $request->input('country_name')]);

        session()->flash('success', 'Success!! Country Name Updated Successfully!');

        return response('success');
    }

    public function ajaxList(Request $request)
    {
        $this->authorize('places_view');

        return DataTables::of(Country::query())
            ->addColumn('status_badge', fn (Country $c) => DatatableHtml::statusBadge($c->id, $c->status))
            ->addColumn('actions', fn (Country $c) => DatatableHtml::actionMenu([
                ['label' => 'Edit', 'icon' => 'fa-edit text-blue', 'url' => route('country.edit', $c), 'can' => $request->user()->can('places_edit')],
                ['label' => 'Delete', 'icon' => 'fa-trash text-red', 'onclick' => "delete_country({$c->id})", 'can' => $request->user()->can('places_delete')],
            ]))
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function updateStatus(Request $request)
    {
        $this->authorize('places_edit');

        Country::whereKey($request->input('id'))->update(['status' => $request->input('status')]);

        return response('success');
    }

    public function destroy(Request $request)
    {
        $this->authorize('places_delete');

        Country::whereKey($request->input('q_id'))->delete();

        return response('success');
    }
}
