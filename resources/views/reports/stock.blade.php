@extends('layouts.app')
@php($activeMenu = 'report-stock')

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
                            <label for="brand_id" class="col-sm-2 control-label">Brand</label>
                            <div class="col-sm-3">
                                <select class="form-control select2" id="brand_id" name="brand_id" style="width: 100%;">
                                    <option value="">-All-</option>
                                    @foreach (\App\Models\Brand::where('status', true)->get() as $b)
                                        <option value="{{ $b->id }}">{{ $b->brand_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <label for="category_id" class="col-sm-2 control-label">Category</label>
                            <div class="col-sm-3">
                                <select class="form-control select2" id="category_id" name="category_id" style="width: 100%;">
                                    <option value="">-All-</option>
                                    @foreach (\App\Models\Category::where('status', true)->get() as $c)
                                        <option value="{{ $c->id }}">{{ $c->category_name }}</option>
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
                    <li class="active"><a href="#tab_1" data-toggle="tab">Item Wise</a></li>
                    <li><a href="#tab_2" data-toggle="tab">Brand Wise</a></li>
                    <li><a href="#tab_3" data-toggle="tab">Category Wise</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_1">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="item_id" class="col-sm-2 control-label text-right">Item Name</label>
                                    <div class="col-sm-6">
                                        <select class="form-control select2" id="item_id" name="item_id" style="width: 100%;"></select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            @include('partials.export-btn', ['tableId' => 'report-data'])
                            <br><br>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="report-data">
                                    <thead>
                                    <tr class="bg-blue">
                                        <th>#</th><th>Item Code</th><th>Item Name</th><th>Brand</th><th>Category</th>
                                        <th>Unit Price({{ currency() }})</th><th>Tax</th><th>Sales Price({{ currency() }})</th><th>Current Stock</th><th>Stock Value</th>
                                    </tr>
                                    </thead>
                                    <tbody id="tbodyid"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="tab_2">
                        <div class="col-md-12">
                            @include('partials.export-btn', ['tableId' => 'brand_wise_stock'])
                            <br><br>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="brand_wise_stock">
                                    <thead><tr class="bg-blue"><th>#</th><th>Brand Name</th><th>Current Stock</th></tr></thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="tab_3">
                        <div class="col-md-12">
                            @include('partials.export-btn', ['tableId' => 'category_wise_stock'])
                            <br><br>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="category_wise_stock">
                                    <thead><tr class="bg-blue"><th>#</th><th>Category Name</th><th>Current Stock</th></tr></thead>
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
<script src="{{ $theme_link }}js/ajaxselect/item_select_ajax.js"></script>
<script>
function getItemSelectionId() { return '#item_id'; }
$(".select2").select2();

$("#item_id").on("change", function () { load_reports(); });

function load_reports() {
    var base_url = $("#base_url").val().trim();
    var brand_id = document.getElementById("brand_id").value.trim();
    var category_id = document.getElementById("category_id").value.trim();

    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $.post(base_url + "reports/get_stock_report", { brand_id: brand_id, category_id: category_id, item_id: $("#item_id").val() }, function (result) {
        $.each(result, function (key, val) {
            if (key == 'item_wise_report') { $("#tbodyid").empty().append(val); }
            if (key == 'brand_wise_stock') { $("#brand_wise_stock tbody").empty().append(val); }
            if (key == 'category_wise_stock') { $("#category_wise_stock tbody").empty().append(val); }
        });
        $(".overlay").remove();
    });
}

$("#view,#view_all").on("click", function () { load_reports(); });
</script>
@endpush
