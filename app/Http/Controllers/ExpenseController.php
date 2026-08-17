<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Support\DatatableHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ExpenseController extends Controller
{
    public function index(): View
    {
        $this->authorize('expense_view');

        return view('expense.list', ['page_title' => 'Expenses List']);
    }

    public function add(): View
    {
        $this->authorize('expense_add');

        return view('expense.form', ['page_title' => 'Expense', 'expense' => null]);
    }

    public function edit(Expense $expense): View
    {
        $this->authorize('expense_edit');

        return view('expense.form', ['page_title' => 'Expense', 'expense' => $expense]);
    }

    protected function validateExpense(Request $request)
    {
        return Validator::make($request->all(), [
            'expense_date' => 'required',
            'category_id' => 'required',
            'expense_amt' => 'required',
            'expense_for' => 'required',
        ]);
    }

    public function store(Request $request)
    {
        $validator = $this->validateExpense($request);

        if ($validator->fails()) {
            return response('Please Fill Compulsory(* marked) Fields.');
        }

        $nextId = (Expense::max('id') ?? 0) + 1;
        $expenseInit = \App\Models\Company::query()->value('expense_init') ?? 'EX';

        Expense::create([
            'expense_code' => $expenseInit.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT),
            'category_id' => $request->input('category_id'),
            'expense_date' => \Illuminate\Support\Carbon::parse($request->input('expense_date')),
            'reference_no' => $request->input('reference_no'),
            'expense_for' => $request->input('expense_for'),
            'expense_amt' => $request->input('expense_amt'),
            'note' => $request->input('note'),
            'created_by' => $request->user()->username,
            'status' => true,
        ]);

        return response('success');
    }

    public function update(Request $request)
    {
        $validator = $this->validateExpense($request);

        if ($validator->fails()) {
            return response('Please Fill Compulsory(* marked) Fields.');
        }

        Expense::whereKey($request->input('q_id'))->update([
            'category_id' => $request->input('category_id'),
            'expense_date' => \Illuminate\Support\Carbon::parse($request->input('expense_date')),
            'reference_no' => $request->input('reference_no'),
            'expense_for' => $request->input('expense_for'),
            'expense_amt' => $request->input('expense_amt'),
            'note' => $request->input('note'),
        ]);

        return response('success');
    }

    public function ajaxList(Request $request)
    {
        $this->authorize('expense_view');

        return DataTables::of(Expense::query()->with('category'))
            ->addColumn('checkbox', fn (Expense $e) => DatatableHtml::checkbox($e->id))
            ->editColumn('expense_date', fn (Expense $e) => show_date($e->expense_date))
            ->addColumn('category_name', fn (Expense $e) => $e->category->category_name ?? '')
            ->editColumn('expense_amt', fn (Expense $e) => app_number_format($e->expense_amt))
            ->editColumn('created_by', fn (Expense $e) => ucfirst((string) $e->created_by))
            ->addColumn('actions', fn (Expense $e) => DatatableHtml::actionMenu([
                ['label' => 'Edit', 'icon' => 'fa-edit text-blue', 'url' => route('expense.edit', $e), 'can' => $request->user()->can('expense_edit')],
                ['label' => 'Delete', 'icon' => 'fa-trash text-red', 'onclick' => "delete_expense({$e->id})", 'can' => $request->user()->can('expense_delete')],
            ]))
            ->rawColumns(['checkbox', 'actions'])
            ->make(true);
    }

    public function updateStatus(Request $request)
    {
        $this->authorize('expense_edit');

        Expense::whereKey($request->input('id'))->update(['status' => $request->input('status')]);

        return response('success');
    }

    public function destroy(Request $request)
    {
        $this->authorize('expense_delete');

        Expense::whereIn('id', explode(',', $request->input('q_id')))->delete();

        return response('success');
    }

    public function multiDestroy(Request $request)
    {
        $this->authorize('expense_delete');

        Expense::whereIn('id', $request->input('checkbox', []))->delete();

        return response('success');
    }
}
