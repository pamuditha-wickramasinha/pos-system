<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Support\DatatableHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class BrandController extends Controller
{
    public function view(): View
    {
        $this->authorize('brand_view');

        return view('brand.list', ['page_title' => 'Brands List']);
    }

    public function add(): View
    {
        $this->authorize('brand_add');

        return view('brand.form', ['page_title' => 'Brand']);
    }

    public function edit(Brand $brand): View
    {
        $this->authorize('brand_edit');

        return view('brand.form', [
            'page_title' => 'Brand',
            'q_id' => $brand->id,
            'brand_code' => $brand->brand_code,
            'brand_name' => $brand->brand_name,
            'description' => $brand->description,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), ['brand' => 'required']);

        if ($validator->fails()) {
            return response('Please Enter Brand name.');
        }

        if (Brand::whereRaw('upper(brand_name) = upper(?)', [$request->input('brand')])->exists()) {
            return response('This Brand Name Already Exist.');
        }

        $companyInit = DB::table('companies')->value('category_init') ?? 'BR';
        $nextId = (Brand::max('id') ?? 0) + 1;

        Brand::create([
            'brand_code' => $companyInit.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT),
            'brand_name' => $request->input('brand'),
            'description' => $request->input('description'),
            'status' => true,
        ]);

        session()->flash('success', 'Success!! New Brand Added Successfully!');

        return response('success');
    }

    /**
     * Quick-add from the "+" button next to Brand dropdowns (e.g. the Items form),
     * so a missing brand can be created without leaving the page. Returns JSON
     * (unlike store()) so the calling dropdown can be updated in place.
     */
    public function addModal(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), ['brand' => 'required']);

        if ($validator->fails()) {
            return response()->json(['result' => 'Please Enter Brand name.']);
        }

        if (Brand::whereRaw('upper(brand_name) = upper(?)', [$request->input('brand')])->exists()) {
            return response()->json(['result' => 'This Brand Name Already Exist.']);
        }

        $companyInit = DB::table('companies')->value('category_init') ?? 'BR';
        $nextId = (Brand::max('id') ?? 0) + 1;

        $brand = Brand::create([
            'brand_code' => $companyInit.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT),
            'brand_name' => $request->input('brand'),
            'description' => $request->input('description'),
            'status' => true,
        ]);

        return response()->json(['result' => 'success', 'id' => $brand->id, 'brand' => $brand->brand_name]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), ['brand' => 'required', 'q_id' => 'required']);

        if ($validator->fails()) {
            return response('Please Enter Brand name.');
        }

        $id = $request->input('q_id');

        if (Brand::whereRaw('upper(brand_name) = upper(?)', [$request->input('brand')])->where('id', '!=', $id)->exists()) {
            return response('This Brand Name Already Exist.');
        }

        Brand::whereKey($id)->update([
            'brand_name' => $request->input('brand'),
            'description' => $request->input('description'),
        ]);

        session()->flash('success', 'Success!! Brand Updated Successfully!');

        return response('success');
    }

    public function ajaxList(Request $request)
    {
        $this->authorize('brand_view');

        return DataTables::of(Brand::query())
            ->addColumn('checkbox', fn (Brand $b) => DatatableHtml::checkbox($b->id))
            ->addColumn('status_badge', fn (Brand $b) => DatatableHtml::statusBadge($b->id, $b->status))
            ->addColumn('actions', fn (Brand $b) => DatatableHtml::actionMenu([
                ['label' => 'Edit', 'icon' => 'fa-edit text-blue', 'url' => route('brands.edit', $b), 'can' => $request->user()->can('brand_edit')],
                ['label' => 'Delete', 'icon' => 'fa-trash text-red', 'onclick' => "delete_brand({$b->id})", 'can' => $request->user()->can('brand_delete')],
            ]))
            ->rawColumns(['checkbox', 'status_badge', 'actions'])
            ->make(true);
    }

    public function updateStatus(Request $request)
    {
        $this->authorize('brand_edit');

        Brand::whereKey($request->input('id'))->update(['status' => $request->input('status')]);

        return response('success');
    }

    public function destroy(Request $request)
    {
        $this->authorize('brand_delete');

        return $this->deleteIds($request->input('q_id'));
    }

    public function multiDestroy(Request $request)
    {
        $this->authorize('brand_delete');

        return $this->deleteIds(implode(',', $request->input('checkbox', [])));
    }

    protected function deleteIds(string $ids): \Illuminate\Http\Response
    {
        Brand::whereIn('id', explode(',', $ids))->delete();

        if (Schema::hasTable('items')) {
            DB::table('items')->whereIn('brand_id', explode(',', $ids))->update(['brand_id' => null]);
        }

        return response('success');
    }
}
