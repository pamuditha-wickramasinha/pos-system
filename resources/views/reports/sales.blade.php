@extends('layouts.app')
@php($activeMenu = 'report-sales')

@section('content')
<section class="content-header">
    <h1>{{ $page_title }}</h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">{{ $page_title }}</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border"><h3 class="box-title">Please Enter Valid Information</h3></div>
                <form class="form-horizontal" id="report-form" onkeypress="return event.keyCode != 13;">
                    <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
                    @csrf
                    <div class="box-body">
                        <div class="form-group">
                            <label for="from_date" class="col-sm-2 control-label">From Date</label>
                            <div class="col-sm-3">
                                <div class="input-group date">
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                    <input type="text" class="form-control pull-right datepicker" id="from_date" name="from_date" value="{{ show_date(date('d-m-Y')) }}">
                                </div>
                            </div>
                            <label for="to_date" class="col-sm-2 control-label">To Date</label>
                            <div class="col-sm-3">
                                <div class="input-group date">
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                    <input type="text" class="form-control pull-right datepicker" id="to_date" name="to_date" value="{{ show_date(date('d-m-Y')) }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="customer_id" class="col-sm-2 control-label">Customer Name</label>
                            <div class="col-sm-3">
                                <select class="form-control select2" id="customer_id" name="customer_id"></select>
                            </div>
                            <label for="payment_status" class="col-sm-2 control-label">Payment Status</label>
                            <div class="col-sm-3">
                                <select class="form-control select2" id="payment_status" name="payment_status" style="width: 100%;">
                                    <option value="">-All-</option>
                                    <option value="Paid">Paid</option>
                                    <option value="Unpaid">Unpaid</option>
                                    <option value="Partial">Partial</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="col-sm-8 col-sm-offset-2 text-center">
                            <div class="col-md-3 col-md-offset-3"><button type="button" id="view" class="btn btn-block btn-success">Show</button></div>
                            <div class="col-sm-3"><a href="{{ url('dashboard') }}"><button type="button" class="col-sm-3 btn btn-block btn-warning close_btn">Close</button></a></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Records Table</h3>
                    @include('partials.export-btn', ['tableId' => 'report-data'])
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-bordered table-hover" id="report-data">
                        <thead>
                        <tr class="bg-blue">
                            <th>#</th><th>Invoice No</th><th>Sales Date</th><th>Customer Code</th><th>Customer Name</th>
                            <th>Invoice Total({{ currency() }})</th><th>Paid Amount({{ currency() }})</th><th>Due Amount({{ currency() }})</th><th>Due Days</th>
                        </tr>
                        </thead>
                        <tbody id="tbodyid"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
@include('partials.export-scripts')
<script src="{{ $theme_link }}js/report-sales.js"></script>
<script src="{{ $theme_link }}js/ajaxselect/customer_select_ajax.js"></script>
<script>
function getCustomerSelectionId() { return '#customer_id'; }
$('.datepicker').datepicker({ autoclose: true, format: 'dd-mm-yyyy', todayHighlight: true });
</script>
@endpush
