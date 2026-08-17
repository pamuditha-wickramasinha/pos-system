@extends('layouts.app')
@php($activeMenu = 'items-list')

@push('styles')
@include('partials.datatable-styles')
<link rel="stylesheet" href="{{ $theme_link }}plugins/lightbox/ekko-lightbox.css">
@endpush

@section('content')
<section class="content-header">
    <h1>{{ $page_title }} <small>View/Search Items</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">{{ $page_title }}</li>
    </ol>
</section>

<form id="table_form">
@csrf
<input type="hidden" id="base_url" value="{{ url('/').'/' }}">
<section class="content">
    <div class="row">
        @include('partials.flashdata')
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-3">
                                <label for="brand_id">Brand</label>
                                <select class="form-control select2" id="brand_id" name="brand_id" style="width: 100%;">
                                    <option value="">-Select-</option>
                                    @foreach (\App\Models\Brand::where('status', true)->get() as $b)
                                        <option value="{{ $b->id }}">{{ $b->brand_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="category_id">Category</label>
                                <select class="form-control select2" id="category_id" name="category_id" style="width: 100%;">
                                    <option value="">-Select-</option>
                                    @foreach (\App\Models\Category::where('status', true)->get() as $c)
                                        <option value="{{ $c->id }}">{{ $c->category_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    @can('items_add')
                    <div class="box-tools">
                        <a class="btn btn-block btn-info" href="{{ route('items.add') }}"><i class="fa fa-plus"></i> New Item</a>
                    </div>
                    @endcan
                </div>
                <div class="box-body">
                    <table id="example2" class="table table-bordered table-striped" width="100%">
                        <thead class="bg-primary">
                        <tr>
                            <th class="text-center"><input type="checkbox" class="group_check checkbox"></th>
                            <th>Barcode</th>
                            <th>Item Name</th>
                            <th>Brand (Category)</th>
                            <th>Unit</th>
                            <th>Stock Qty (Minimum Qty)</th>
                            <th>Purchase Price</th>
                            <th>Final Sales Price</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
</form>
@endsection

@push('scripts')
@include('partials.datatable-scripts')
<script src="{{ $theme_link }}plugins/lightbox/ekko-lightbox.js"></script>
<script type="text/javascript">
function load_datatable() {
    var table = $('#example2').DataTable({
        dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>tip',
        buttons: { buttons: [
            { className: 'btn bg-red color-palette btn-flat hidden delete_btn pull-left', text: 'Delete', action: function (e, dt, node, config) { multi_delete(); } },
            { extend: 'copy', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6,7] } },
            { extend: 'excel', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6,7] } },
            { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6,7] } },
            { extend: 'print', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6,7] } },
            { extend: 'csv', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6,7] } },
            { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat', text: 'Columns' },
        ]},
        processing: true, serverSide: true, order: [], responsive: true,
        language: { processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>' },
        ajax: {
            url: "{{ route('items.ajax_list') }}", type: "POST",
            data: { brand_id: $("#brand_id").val(), category_id: $("#category_id").val() },
            complete: function (data) { $('.column_checkbox').iCheck({ checkboxClass: 'icheckbox_square-orange', radioClass: 'iradio_square-orange', increaseArea: '10%' }); call_code(); },
        },
        columns: [
            { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
            { data: 'barcode', name: 'barcode', orderable: false },
            { data: 'item_name', name: 'item_name' },
            { data: 'brand_category', name: 'brand_category', orderable: false },
            { data: 'unit_name', name: 'unit_name', orderable: false },
            { data: 'stock_qty', name: 'stock_qty', orderable: false },
            { data: 'purchase_price', name: 'purchase_price' },
            { data: 'final_price', name: 'final_price' },
            { data: 'status_badge', name: 'status' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        columnDefs: [ { targets: [0], className: 'text-center' } ],
    });
    new $.fn.dataTable.FixedHeader(table);
}
$(document).ready(function() { load_datatable(); });
$("#brand_id,#category_id").on("change", function() { $('#example2').DataTable().destroy(); load_datatable(); });
</script>
<script src="{{ $theme_link }}js/items.js"></script>
@endpush
