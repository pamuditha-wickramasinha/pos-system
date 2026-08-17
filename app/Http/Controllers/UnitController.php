<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Support\DatatableHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class UnitController extends Controller
{
    public function index(): View
    {
        $this->authorize('units_view');

        return view('unit.list', ['page_title' => 'Units List']);
    }

    public function add(): View
    {
        $this->authorize('units_add');

        return view('unit.form', ['page_title' => 'Units']);
    }

    public function edit(Unit $unit): View
    {
        $this->authorize('units_edit');

        return view('unit.form', [
            'page_title' => 'Units',
            'q_id' => $unit->id,
            'unit_name' => $unit->unit_name,
            'description' => $unit->description,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), ['unit_name' => 'required']);

        if ($validator->fails()) {
            return response('Please Enter Unit Name.');
        }

        if (Unit::whereRaw('upper(unit_name) = upper(?)', [$request->input('unit_name')])->exists()) {
            return response('This units Name already Exist.');
        }

        Unit::create([
            'unit_name' => $request->input('unit_name'),
            'description' => $request->input('description'),
            'status' => true,
        ]);

        session()->flash('success', 'Success!! Units Added Successfully!');

        return response('success');
    }

    /**
     * Quick-add from the "+" button next to Unit dropdowns (e.g. the Items form), so
     * a missing unit can be created without leaving the page. Returns JSON (unlike
     * store()) so the calling dropdown can be updated in place.
     */
    public function addModal(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), ['unit_name' => 'required']);

        if ($validator->fails()) {
            return response()->json(['result' => 'Please Enter Unit Name.']);
        }

        if (Unit::whereRaw('upper(unit_name) = upper(?)', [$request->input('unit_name')])->exists()) {
            return response()->json(['result' => 'This units Name already Exist.']);
        }

        $unit = Unit::create([
            'unit_name' => $request->input('unit_name'),
            'description' => $request->input('description'),
            'status' => true,
        ]);

        return response()->json(['result' => 'success', 'id' => $unit->id, 'unit' => $unit->unit_name]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), ['unit_name' => 'required', 'q_id' => 'required']);

        if ($validator->fails()) {
            return response('Please Enter Unit name.');
        }

        $id = $request->input('q_id');

        if (Unit::whereRaw('upper(unit_name) = upper(?)', [$request->input('unit_name')])->where('id', '!=', $id)->exists()) {
            return response('This units Name already Exist.');
        }

        Unit::whereKey($id)->update([
            'unit_name' => $request->input('unit_name'),
            'description' => $request->input('description'),
        ]);

        session()->flash('success', 'Success!! units Updated Successfully!');

        return response('success');
    }

    public function ajaxList(Request $request)
    {
        $this->authorize('units_view');

        return DataTables::of(Unit::query())
            ->addColumn('status_badge', fn (Unit $u) => DatatableHtml::statusBadge($u->id, $u->status))
            ->addColumn('actions', fn (Unit $u) => DatatableHtml::actionMenu([
                ['label' => 'Edit', 'icon' => 'fa-edit text-blue', 'url' => route('units.edit', $u), 'can' => $request->user()->can('units_edit')],
                ['label' => 'Delete', 'icon' => 'fa-trash text-red', 'onclick' => "delete_unit({$u->id})", 'can' => $request->user()->can('units_delete')],
            ]))
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function updateStatus(Request $request)
    {
        $this->authorize('units_edit');

        Unit::whereKey($request->input('id'))->update(['status' => $request->input('status')]);

        return response('success');
    }

    public function destroy(Request $request)
    {
        $this->authorize('units_delete');

        Unit::whereKey($request->input('q_id'))->delete();

        return response('success');
    }
}
