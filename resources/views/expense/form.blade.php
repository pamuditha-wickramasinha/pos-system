@extends('layouts.app')
@php($activeMenu = 'expense')

@section('content')
<section class="content-header">
    <h1>{{ $page_title }} <small>Add/Update Expense</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{ route('expense.index') }}">Expenses List</a></li>
        <li class="active">{{ $page_title }}</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        @include('partials.flashdata')
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Please Enter Valid Data</h3>
                </div>
                <form class="form-horizontal" id="expense-form">
                    @csrf
                    <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="expense_date" class="col-sm-4 control-label">Expense Date <label class="text-danger">*</label></label>
                                    <div class="col-sm-8">
                                        <div class="input-group date">
                                            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                            <input type="text" class="form-control pull-right datepicker" value="{{ $expense?->expense_date ? show_date($expense->expense_date) : show_date(date('d-m-Y')) }}" id="expense_date" name="expense_date" readonly onkeyup="shift_cursor(event,'category_id')">
                                            <span id="expense_date_msg" style="display:none" class="text-danger"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="category_id" class="col-sm-4 control-label">Category <label class="text-danger">*</label></label>
                                    <div class="col-sm-8">
                                        <select class="form-control select2" id="category_id" name="category_id" style="width: 100%;" onkeyup="shift_cursor(event,'expense_for')">
                                            <option value="">-Select-</option>
                                            @forelse (\App\Models\ExpenseCategory::where('status', true)->get() as $c)
                                                <option @selected($expense?->category_id == $c->id) value="{{ $c->id }}">{{ $c->category_name }}</option>
                                            @empty
                                                <option value="">No Records Found</option>
                                            @endforelse
                                        </select>
                                        <span id="category_id_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="expense_for" class="col-sm-4 control-label">Expense For <label class="text-danger">*</label></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="expense_for" name="expense_for" onkeyup="shift_cursor(event,'expense_amt')" value="{{ $expense?->expense_for ?? '' }}">
                                        <span id="expense_for_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="expense_amt" class="col-sm-4 control-label">Amount <label class="text-danger">*</label></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control only_currency" id="expense_amt" name="expense_amt" value="{{ $expense?->expense_amt ?? '' }}" onkeyup="shift_cursor(event,'reference_no')">
                                        <span id="expense_amt_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="reference_no" class="col-sm-4 control-label">Reference No</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="reference_no" name="reference_no" value="{{ $expense?->reference_no ?? '' }}" onkeyup="shift_cursor(event,'note')">
                                        <span id="reference_no_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="note" class="col-sm-4 control-label">Note</label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control" id="note" name="note">{{ $expense?->note ?? '' }}</textarea>
                                        <span id="note_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="col-sm-8 col-sm-offset-2 text-center">
                            @if($expense)
                                <input type="hidden" name="q_id" id="q_id" value="{{ $expense->id }}">
                                @php($btn_id = 'update') @php($btn_name = 'Update')
                            @else
                                @php($btn_id = 'save') @php($btn_name = 'Save')
                            @endif
                            <div class="col-md-3 col-md-offset-3">
                                <button type="button" id="{{ $btn_id }}" class="btn btn-block btn-success" title="Save Data">{{ $btn_name }}</button>
                            </div>
                            <div class="col-sm-3">
                                <a href="{{ url('dashboard') }}">
                                    <button type="button" class="col-sm-3 btn btn-block btn-warning close_btn" title="Go Dashboard">Close</button>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(".select2").select2();
$('.datepicker').datepicker({ autoclose: true, format: 'dd-mm-yyyy', todayHighlight: true });
</script>
<script src="{{ $theme_link }}js/expense.js"></script>
@endpush
