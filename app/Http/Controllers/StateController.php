<?php

namespace App\Http\Controllers;

use App\Models\State;
use App\Support\DatatableHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class StateController extends Controller
{
    public function index(): View
    {
        $this->authorize('places_view');

        return view('state.list', ['page_title' => 'States List']);
    }

    public function add(): View
    {
        $this->authorize('places_add');

        return view('state.form', ['page_title' => 'State']);
    }

    public function edit(State $state): View
    {
        $this->authorize('places_edit');

        return view('state.form', [
            'page_title' => 'State',
            'q_id' => $state->id,
            'country' => $state->country,
            'state' => $state->state,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), ['state' => 'required', 'country' => 'required']);

        if ($validator->fails()) {
            return response('Please enter compulsary(* marked) fields!');
        }

        if (State::whereRaw('upper(state) = upper(?)', [$request->input('state')])->whereRaw('upper(country) = upper(?)', [$request->input('country')])->exists()) {
            return response('State Name already Exist in '.$request->input('country').'.');
        }

        State::create([
            'state' => $request->input('state'),
            'country' => $request->input('country'),
            'status' => true,
        ]);

        session()->flash('success', 'Success!! New State Name Added Successfully!');

        return response('success');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), ['state' => 'required', 'country' => 'required', 'q_id' => 'required']);

        if ($validator->fails()) {
            return response('Please Enter state name.');
        }

        $id = $request->input('q_id');

        if (State::whereRaw('upper(state) = upper(?)', [$request->input('state')])->whereRaw('upper(country) = upper(?)', [$request->input('country')])->where('id', '!=', $id)->exists()) {
            return response('State Name already Exist in '.$request->input('country').'.');
        }

        State::whereKey($id)->update([
            'state' => $request->input('state'),
            'country' => $request->input('country'),
        ]);

        session()->flash('success', 'Success!! State Name Updated Successfully!');

        return response('success');
    }

    public function ajaxList(Request $request)
    {
        $this->authorize('places_view');

        return DataTables::of(State::with('country')->select('states.*'))
            ->addColumn('country_name', fn (State $s) => $s->country?->country)
            ->addColumn('status_badge', fn (State $s) => DatatableHtml::statusBadge($s->id, $s->status))
            ->addColumn('actions', fn (State $s) => DatatableHtml::actionMenu([
                ['label' => 'Edit', 'icon' => 'fa-edit text-blue', 'url' => route('state.edit', $s), 'can' => $request->user()->can('places_edit')],
                ['label' => 'Delete', 'icon' => 'fa-trash text-red', 'onclick' => "delete_state({$s->id})", 'can' => $request->user()->can('places_delete')],
            ]))
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function updateStatus(Request $request)
    {
        $this->authorize('places_edit');

        State::whereKey($request->input('id'))->update(['status' => $request->input('status')]);

        return response('success');
    }

    public function destroy(Request $request)
    {
        $this->authorize('places_delete');

        State::whereKey($request->input('q_id'))->delete();

        return response('success');
    }
}
