@extends('layouts.app')
@php($activeMenu = 'report-purchase-payments')

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
                            <label for="supplier_id" class="col-sm-2 control-label">Supplier Name</label>
                            <div class="col-sm-3">
                                <select class="form-control select2" id="supplier_id" name="supplier_id" style="width: 100%;"></select>
                            </div>
                            <label for="payment_type" class="col-sm-2 control-label">Payment Type</label>
                            <div class="col-sm-3">
                                <select class="form-control select2" id="payment_type" name="payment_type" style="width: 100%;">
                                    <option value="">-All-</option>
                                    @foreach (\App\Models\PaymentType::where('status', true)->get() as $pt)
                                        <option value="{{ $pt->payment_type }}">{{ $pt->payment_type }}</option>
                                    @endforeach
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
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#tab_1" data-toggle="tab">Purchase Payments</a></li>
                    <li><a href="#tab_2" data-toggle="tab">Supplier Payments</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_1">
                        <div class="col-md-12">
                            @include('partials.export-btn', ['tableId' => 'report-data-1'])
                            <br><br>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="report-data-1">
                                    <thead>
                                    <tr class="bg-blue">
                                        <th>#</th><th>Purchase<br>Invoice No</th><th>Payment Date</th><th>Supplier Code</th><th>Supplier Name</th><th>Payment Type</th><th>Payment Note</th><th>Paid Amount({{ currency() }})</th>
                                    </tr>
                                    </thead>
                                    <tbody id="tbodyid"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="tab_2">
                        <div class="col-md-12">
                            @include('partials.export-btn', ['tableId' => 'report-data-2'])
                            <br><br>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="report-data-2">
                                    <thead>
                                    <tr class="bg-blue">
                                        <th>#</th><th>Payment Date</th><th>Supplier Name</th><th>Payment Type</th><th>Payment Note</th><th>Paid Amount({{ currency() }})</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
@include('partials.export-scripts')
<script src="{{ $theme_link }}js/ajaxselect/supplier_select_ajax.js"></script>
<script>
function getsupplierSelectionId() { return '#supplier_id'; }
$(".select2").select2();
$('.datepicker').datepicker({ autoclose: true, format: 'dd-mm-yyyy', todayHighlight: true });

$("#view,#view_all").on("click", function () {
    var from_date = document.getElementById("from_date").value.trim();
    var to_date = document.getElementById("to_date").value.trim();
    var supplier_id = document.getElementById("supplier_id").value.trim();
    var payment_type = document.getElementById("payment_type").value.trim();
    if (from_date == "") { toastr["warning"]("Select From Date!"); return; }
    if (to_date == "") { toastr["warning"]("Select To Date!"); return; }

    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $.post("show_purchase_payments_report", { payment_type: payment_type, supplier_id: supplier_id, from_date: from_date, to_date: to_date }, function (result) {
        $("#report-data-1 tbody").empty().append(result);
        $(".overlay").remove();
    });
    $.post("supplier_payments_report", { payment_type: payment_type, supplier_id: supplier_id, from_date: from_date, to_date: to_date }, function (result) {
        $("#report-data-2 tbody").empty().append(result);
    });
});
</script>
@endpush
