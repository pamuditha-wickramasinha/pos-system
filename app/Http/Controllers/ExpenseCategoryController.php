<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Support\DatatableHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ExpenseCategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('expense_category_view');

        return view('expense_category.list', ['page_title' => 'Expense Category List']);
    }

    public function add(): View
    {
        $this->authorize('expense_category_add');

        return view('expense_category.form', ['page_title' => 'Expense Category', 'q_id' => null]);
    }

    public function edit(ExpenseCategory $expenseCategory): View
    {
        $this->authorize('expense_category_edit');

        return view('expense_category.form', [
            'page_title' => 'Expense Category',
            'q_id' => $expenseCategory->id,
            'category_name' => $expenseCategory->category_name,
            'description' => $expenseCategory->description,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), ['category' => 'required']);

        if ($validator->fails()) {
            return response('Please Enter Category name.');
        }

        if (ExpenseCategory::whereRaw('upper(category_name) = upper(?)', [$request->input('category')])->exists()) {
            return response('This Category Name already Exist.');
        }

        $nextId = (ExpenseCategory::max('id') ?? 0) + 1;

        ExpenseCategory::create([
            'category_code' => 'EC'.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT),
            'category_name' => $request->input('category'),
            'description' => $request->input('description'),
            'created_by' => $request->user()->username,
            'status' => true,
        ]);

        session()->flash('success', 'Success!! Record Added Successfully!');

        return response('success');
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

        if (ExpenseCategory::whereRaw('upper(category_name) = upper(?)', [$request->input('category')])->where('id', '!=', $id)->exists()) {
            return response('This Category Name already Exist.');
        }

        ExpenseCategory::whereKey($id)->update([
            'category_name' => $request->input('category'),
            'description' => $request->input('description'),
        ]);

        session()->flash('success', 'Success!! Record Updated Successfully!');

        return response('success');
    }

    public function ajaxList(Request $request)
    {
        $this->authorize('expense_category_view');

        return DataTables::of(ExpenseCategory::query())
            ->addColumn('checkbox', fn (ExpenseCategory $c) => DatatableHtml::checkbox($c->id))
            ->addColumn('status_badge', fn (ExpenseCategory $c) => DatatableHtml::statusBadge($c->id, $c->status))
            ->addColumn('actions', fn (ExpenseCategory $c) => DatatableHtml::actionMenu([
                ['label' => 'Edit', 'icon' => 'fa-edit text-blue', 'url' => route('expense.category_edit', $c), 'can' => $request->user()->can('expense_category_edit')],
                ['label' => 'Delete', 'icon' => 'fa-trash text-red', 'onclick' => "delete_category({$c->id})", 'can' => $request->user()->can('expense_category_delete')],
            ]))
            ->rawColumns(['checkbox', 'status_badge', 'actions'])
            ->make(true);
    }

    public function updateStatus(Request $request)
    {
        $this->authorize('expense_category_edit');

        ExpenseCategory::whereKey($request->input('id'))->update(['status' => $request->input('status')]);

        return response('success');
    }

    public function destroy(Request $request)
    {
        $this->authorize('expense_category_delete');

        return $this->deleteIds($request->input('q_id'));
    }

    public function multiDestroy(Request $request)
    {
        $this->authorize('expense_category_delete');

        return $this->deleteIds(implode(',', $request->input('checkbox', [])));
    }

    protected function deleteIds(string $ids): \Illuminate\Http\Response
    {
        $idList = explode(',', $ids);

        $inUse = DB::table('expenses')
            ->join('expense_categories', 'expense_categories.id', '=', 'expenses.category_id')
            ->whereIn('expenses.category_id', $idList)
            ->select('expense_categories.category_name')
            ->distinct()
            ->pluck('category_name');

        if ($inUse->isNotEmpty()) {
            return response("Sorry! Can't Delete,<br>Category Name {".$inUse->implode(',').'} already in use in Expenses!');
        }

        ExpenseCategory::whereIn('id', $idList)->delete();

        return response('success');
    }
}
