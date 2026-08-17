<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Support\DatatableHtml;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    public function view(): View
    {
        $this->authorize('items_category_view');

        return view('category.list', ['page_title' => 'Categories List']);
    }

    public function add(): View
    {
        $this->authorize('items_category_add');

        return view('category.form', ['page_title' => 'Category']);
    }

    public function edit(Category $category): View
    {
        $this->authorize('items_category_edit');

        return view('category.form', [
            'page_title' => 'Category',
            'q_id' => $category->id,
            'category_code' => $category->category_code,
            'category_name' => $category->category_name,
            'description' => $category->description,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required',
        ]);

        if ($validator->fails()) {
            return response('Please Enter Category name.');
        }

        if (Category::whereRaw('upper(category_name) = upper(?)', [$request->input('category')])->exists()) {
            return response('This Category Name already Exist.');
        }

        $companyInit = DB::table('companies')->value('category_init') ?? 'CT';
        $nextId = (Category::max('id') ?? 0) + 1;

        Category::create([
            'category_code' => $companyInit.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT),
            'category_name' => $request->input('category'),
            'description' => $request->input('description'),
            'status' => true,
        ]);

        session()->flash('success', 'Success!! New Category Added Successfully!');

        return response('success');
    }

    /**
     * Quick-add from the "+" button next to Category dropdowns (e.g. the Items form),
     * so a missing category can be created without leaving the page. Returns JSON
     * (unlike store()) so the calling dropdown can be updated in place.
     */
    public function addModal(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), ['category' => 'required']);

        if ($validator->fails()) {
            return response()->json(['result' => 'Please Enter Category name.']);
        }

        if (Category::whereRaw('upper(category_name) = upper(?)', [$request->input('category')])->exists()) {
            return response()->json(['result' => 'This Category Name already Exist.']);
        }

        $companyInit = DB::table('companies')->value('category_init') ?? 'CT';
        $nextId = (Category::max('id') ?? 0) + 1;

        $category = Category::create([
            'category_code' => $companyInit.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT),
            'category_name' => $request->input('category'),
            'description' => $request->input('description'),
            'status' => true,
        ]);

        return response()->json(['result' => 'success', 'id' => $category->id, 'category' => $category->category_name]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required',
            'q_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response('Please Enter Category name.');
        }

        $id = $request->input('q_id');

        if (Category::whereRaw('upper(category_name) = upper(?)', [$request->input('category')])->where('id', '!=', $id)->exists()) {
            return response('This Category Name already Exist.');
        }

        Category::whereKey($id)->update([
            'category_name' => $request->input('category'),
            'description' => $request->input('description'),
        ]);

        session()->flash('success', 'Success!! Category Updated Successfully!');

        return response('success');
    }

    public function ajaxList(Request $request)
    {
        $this->authorize('items_category_view');

        return DataTables::of(Category::query())
            ->addColumn('checkbox', fn (Category $c) => DatatableHtml::checkbox($c->id))
            ->addColumn('status_badge', fn (Category $c) => DatatableHtml::statusBadge($c->id, $c->status))
            ->addColumn('actions', fn (Category $c) => DatatableHtml::actionMenu([
                ['label' => 'Edit', 'icon' => 'fa-edit text-blue', 'url' => route('category.edit', $c), 'can' => $request->user()->can('items_category_edit')],
                ['label' => 'Delete', 'icon' => 'fa-trash text-red', 'onclick' => "delete_category({$c->id})", 'can' => $request->user()->can('items_category_delete')],
            ]))
            ->rawColumns(['checkbox', 'status_badge', 'actions'])
            ->make(true);
    }

    public function updateStatus(Request $request)
    {
        $this->authorize('items_category_edit');

        Category::whereKey($request->input('id'))->update(['status' => $request->input('status')]);

        return response('success');
    }

    public function destroy(Request $request)
    {
        $this->authorize('items_category_delete');

        return $this->deleteIds($request->input('q_id'));
    }

    public function multiDestroy(Request $request)
    {
        $this->authorize('items_category_delete');

        return $this->deleteIds(implode(',', $request->input('checkbox', [])));
    }

    protected function deleteIds(string $ids): \Illuminate\Http\Response
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('items')) {
            $inUse = DB::table('items')
                ->join('categories', 'categories.id', '=', 'items.category_id')
                ->whereIn('items.category_id', explode(',', $ids))
                ->select('categories.category_name')
                ->distinct()
                ->pluck('category_name');

            if ($inUse->isNotEmpty()) {
                return response("Sorry! Can't Delete,<br>Category Name {".$inUse->implode(',').'} already in use in Items!');
            }
        }

        Category::whereIn('id', explode(',', $ids))->delete();

        return response('success');
    }
}
