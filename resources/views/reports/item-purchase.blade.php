@extends('layouts.app')
@php($activeMenu = 'report-purchase-item')

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
                            <label for="item_id" class="col-sm-2 control-label">Item Name</label>
                            <div class="col-sm-3">
                                <select class="form-control select2" id="item_id" name="item_id"></select>
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
                            <th>#</th><th>Invoice No</th><th>Purchase Date</th><th>Supplier Name</th><th>Item Name</th><th>Quantity</th><th>Amount({{ currency() }})</th>
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
<script src="{{ $theme_link }}js/ajaxselect/item_select_ajax.js"></script>
<script>
function getItemSelectionId() { return '#item_id'; }
$(".select2").select2();
$('.datepicker').datepicker({ autoclose: true, format: 'dd-mm-yyyy', todayHighlight: true });

$("#view,#view_all").on("click", function () {
    var base_url = $("#base_url").val().trim();
    var from_date = document.getElementById("from_date").value.trim();
    var to_date = document.getElementById("to_date").value.trim();
    var item_id = document.getElementById("item_id").value.trim();
    if (from_date == "") { toastr["warning"]("Select From Date!"); return; }
    if (to_date == "") { toastr["warning"]("Select To Date!"); return; }
    var view_all = (this.id == "view_all") ? "yes" : "no";

    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $.post(base_url + "reports/show_item_purchase_report", { item_id: item_id, view_all: view_all, from_date: from_date, to_date: to_date }, function (result) {
        $("#tbodyid").empty().append(result);
        $(".overlay").remove();
    });
});
</script>
@endpush
