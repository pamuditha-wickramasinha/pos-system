<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use App\Support\DatatableHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TaxController extends Controller
{
    public function index(Request $request): View
    {
        if (site()->isTaxDisabled()) {
            $request->session()->flash('info', 'Note: Tax has been Enabled in application. You can disable it from SIDEBAR->SITE SETTINGS->DISABLE TAX(Checkmark it).');
        }

        $this->authorize('tax_view');

        return view('tax.list', ['page_title' => 'Tax List']);
    }

    public function add(): View
    {
        $this->authorize('tax_add');

        return view('tax.form', ['page_title' => 'New Tax']);
    }

    public function edit(Tax $tax): View
    {
        $this->authorize('tax_edit');

        return view('tax.form', [
            'page_title' => 'Tax',
            'q_id' => $tax->id,
            'tax_name' => $tax->tax_name,
            'tax' => $tax->tax,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), ['tax_name' => 'required', 'tax' => 'required']);

        if ($validator->fails()) {
            return response('Please Enter Tax Name & Tax Percentage!');
        }

        if (Tax::whereRaw('upper(tax_name) = upper(?)', [$request->input('tax_name')])->exists()) {
            return response('Tax Name Already Exist.');
        }

        Tax::create([
            'tax_name' => $request->input('tax_name'),
            'tax' => $request->input('tax'),
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

        $id = $request->input('q_id');

        if (Tax::whereRaw('upper(tax_name) = upper(?)', [$request->input('tax_name')])->where('id', '!=', $id)->exists()) {
            return response('Tax Name Already Exist.');
        }

        $tax = Tax::findOrFail($id);

        if ($tax->undelete_bit) {
            return response("Sorry! Can't Update Status,<br><b>This Record is Restricted!</b>");
        }

        $tax->update([
            'tax_name' => $request->input('tax_name'),
            'tax' => $request->input('tax'),
        ]);

        session()->flash('success', 'Success!! tax Percentage Updated Successfully!');

        return response('success');
    }

    public function ajaxList(Request $request)
    {
        $this->authorize('tax_view');

        return DataTables::of(Tax::whereNull('group_bit')->orWhere('group_bit', false))
            ->addColumn('checkbox', fn (Tax $t) => $t->id === 1 ? '<span class="text-blue">NA</span>' : DatatableHtml::checkbox($t->id, (bool) $t->undelete_bit))
            ->addColumn('status_badge', fn (Tax $t) => DatatableHtml::statusBadge($t->id, $t->status))
            ->addColumn('actions', function (Tax $t) use ($request) {
                if ($t->undelete_bit) {
                    return '<button type="button" class="btn btn-default disabled">Default</button>';
                }

                return DatatableHtml::actionMenu([
                    ['label' => 'Edit', 'icon' => 'fa-edit text-blue', 'url' => route('tax.edit', $t), 'can' => $request->user()->can('tax_edit')],
                    ['label' => 'Delete', 'icon' => 'fa-trash text-red', 'onclick' => "delete_tax({$t->id})", 'can' => $request->user()->can('tax_delete')],
                ]);
            })
            ->rawColumns(['checkbox', 'status_badge', 'actions'])
            ->make(true);
    }

    public function updateStatus(Request $request)
    {
        $this->authorize('tax_edit');

        $tax = Tax::findOrFail($request->input('id'));

        if ($tax->undelete_bit) {
            return response("Sorry! Can't Update Status,<br><b>This Record is Restricted!</b>");
        }

        $tax->update(['status' => $request->input('status')]);

        return response('success');
    }

    public function destroy(Request $request)
    {
        $this->authorize('tax_delete');

        return $this->deleteIds($request->input('q_id'));
    }

    public function multiDestroy(Request $request)
    {
        $this->authorize('tax_delete');

        return $this->deleteIds(implode(',', $request->input('checkbox', [])));
    }

    protected function deleteIds(string $ids): \Illuminate\Http\Response
    {
        if (Schema::hasTable('items')) {
            $inUse = DB::table('items')
                ->join('taxes', 'taxes.id', '=', 'items.tax_id')
                ->whereIn('items.tax_id', explode(',', $ids))
                ->select('taxes.tax_name')
                ->distinct()
                ->pluck('tax_name');

            if ($inUse->isNotEmpty()) {
                return response("Sorry! Can't Delete,<br>Tax Name {".$inUse->implode(',').'} already in use in Items List!');
            }
        }

        Tax::whereIn('id', explode(',', $ids))->where('undelete_bit', false)->delete();

        return response('success');
    }
}
