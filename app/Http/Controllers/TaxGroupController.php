<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use App\Support\DatatableHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TaxGroupController extends Controller
{
    public function index(): View
    {
        $this->authorize('tax_view');

        return view('tax.list', ['page_title' => 'Tax List']);
    }

    public function add(): View
    {
        $this->authorize('tax_add');

        return view('tax-group.form', ['page_title' => 'New Tax Group']);
    }

    public function edit(Tax $tax): View
    {
        $this->authorize('tax_edit');

        return view('tax-group.form', [
            'page_title' => 'Tax Group',
            'q_id' => $tax->id,
            'tax_name' => $tax->tax_name,
            'tax' => $tax->tax,
            'subtax_ids' => $tax->subtax_ids,
        ]);
    }

    protected function joinSubtaxIds(Request $request): string
    {
        return implode(',', array_filter((array) $request->input('subtax_ids', [])));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), ['tax_name' => 'required', 'tax' => 'required']);

        if ($validator->fails()) {
            return response('Please Enter Tax Name & Tax Percentage!');
        }

        $subtaxIds = $this->joinSubtaxIds($request);

        if (empty($subtaxIds)) {
            return response('Please Select Sub Taxes');
        }

        if (Tax::where('tax_name', $request->input('tax_name'))->exists()) {
            return response('Tax Name Already Exist!');
        }

        Tax::create([
            'tax_name' => $request->input('tax_name'),
            'tax' => $request->input('tax'),
            'group_bit' => true,
            'subtax_ids' => $subtaxIds,
            'status' => true,
        ]);

        session()->flash('success', 'Success!! New tax Percentage Added Successfully!');

        return response('success');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), ['tax_name' => 'required', 'tax' => 'required', 'q_id' => 'required']);

        if ($validator->fails()) {
            return response('Please Enter Tax Name & Tax Percentage!');
        }

        $subtaxIds = $this->joinSubtaxIds($request);

        if (empty($subtaxIds)) {
            return response('Please Select Sub Taxes');
        }

        $id = $request->input('q_id');

        if (Tax::whereRaw('upper(tax_name) = upper(?)', [$request->input('tax_name')])->where('id', '!=', $id)->exists()) {
            return response('Tax Name Already Exist.');
        }

        Tax::whereKey($id)->update([
            'tax_name' => $request->input('tax_name'),
            'tax' => $request->input('tax'),
            'subtax_ids' => $subtaxIds,
        ]);

        session()->flash('success', 'Success!! tax Percentage Updated Successfully!');

        return response('success');
    }

    public function ajaxList(Request $request)
    {
        $this->authorize('tax_view');

        return DataTables::of(Tax::where('group_bit', true))
            ->addColumn('subtax_names', fn (Tax $t) => $t->subtaxNames())
            ->addColumn('status_badge', fn (Tax $t) => DatatableHtml::statusBadge($t->id, $t->status))
            ->addColumn('actions', fn (Tax $t) => DatatableHtml::actionMenu([
                ['label' => 'Edit', 'icon' => 'fa-edit text-blue', 'url' => route('tax_group.edit', $t), 'can' => $request->user()->can('tax_edit')],
                ['label' => 'Delete', 'icon' => 'fa-trash text-red', 'onclick' => "delete_tax({$t->id})", 'can' => $request->user()->can('tax_delete')],
            ]))
            ->rawColumns(['subtax_names', 'status_badge', 'actions'])
            ->make(true);
    }
}
