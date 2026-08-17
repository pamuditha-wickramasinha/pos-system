@extends('layouts.app')
@php($activeMenu = 'purchase-list')

@push('styles')
@include('partials.datatable-styles')
<link rel="stylesheet" href="{{ $theme_link }}plugins/datepicker/datepicker3.css">
@endpush

@section('content')
<section class="content-header">
    <h1>{{ $page_title }} <small>View/Search Purchase</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">{{ $page_title }}</li>
    </ol>
</section>

<div class="pay_now_modal"></div>
<div class="view_payments_modal"></div>

<form id="table_form">
@csrf
<input type="hidden" id="base_url" value="{{ url('/').'/' }}">
<section class="content">
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-aqua">
                <div class="inner"><h3>{{ $totalInvoice }}</h3><p>Total Invoices</p></div>
                <div class="icon"><i class="ion ion-bag"></i></div>
                <a href="{{ url('reports/purchase') }}" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-green">
                <div class="inner"><h3>{{ currency($purTotal, true) }}</h3><p>Total Invoices Amount</p></div>
                <div class="icon"><i class="fa fa-plus-square-o"></i></div>
                <a href="{{ url('reports/purchase') }}" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-yellow">
                <div class="inner"><h3>{{ currency($totPaidAmt, true) }}</h3><p>Total Paid Amount</p></div>
                <div class="icon"><i class="fa fa-undo"></i></div>
                <a href="{{ url('reports/purchase_return') }}" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-red">
                <div class="inner"><h3>{{ currency($purchaseDueTotal, true) }}</h3><p>Total Purchase Due</p></div>
                <div class="icon"><i class="fa fa-hourglass-2 "></i></div>
                <a href="{{ url('reports/profit_loss') }}" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
    <div class="row">
        @include('partials.flashdata')
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ $page_title }}</h3>
                    @can('purchase_add')
                    <div class="box-tools">
                        <a class="btn btn-block btn-info" href="{{ route('purchase.add') }}"><i class="fa fa-plus"></i> New Purchase</a>
                    </div>
                    @endcan
                </div>
                <div class="box-body">
                    <table id="example2" class="table table-bordered table-striped" width="100%">
                        <thead class="bg-primary">
                        <tr>
                            <th class="text-center"><input type="checkbox" class="group_check checkbox"></th>
                            <th>Purchase Date</th>
                            <th>Purchase Code</th>
                            <th>Purchase Status</th>
                            <th>Reference No</th>
                            <th>Supplier Name</th>
                            <th>Total</th>
                            <th>Paid Amount</th>
                            <th>Due</th>
                            <th>Payment Status</th>
                            <th>Created By</th>
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
<script type="text/javascript">
$(document).ready(function() {
    var table = $('#example2').DataTable({
        dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>tip',
        buttons: { buttons: [
            { className: 'btn bg-red color-palette btn-flat hidden delete_btn pull-left', text: 'Delete', action: function (e, dt, node, config) { multi_delete(); } },
            { extend: 'copy', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
            { extend: 'excel', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
            { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
            { extend: 'print', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
            { extend: 'csv', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
            { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat', text: 'Columns' },
        ]},
        processing: true, serverSide: true, order: [], responsive: true,
        language: { processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>' },
        ajax: {
            url: "{{ route('purchase.ajax_list') }}", type: "POST",
            complete: function (data) { $('.column_checkbox').iCheck({ checkboxClass: 'icheckbox_square-orange', radioClass: 'iradio_square-orange', increaseArea: '10%' }); call_code(); },
        },
        columns: [
            { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
            { data: 'purchase_date', name: 'purchase_date' },
            { data: 'purchase_code_display', name: 'purchase_code' },
            { data: 'purchase_status', name: 'purchase_status' },
            { data: 'reference_no', name: 'reference_no' },
            { data: 'supplier_name', name: 'supplier_name', orderable: false },
            { data: 'grand_total', name: 'grand_total' },
            { data: 'paid_amount', name: 'paid_amount' },
            { data: 'purchase_due', name: 'purchase_due', orderable: false },
            { data: 'payment_status_badge', name: 'payment_status', orderable: false },
            { data: 'created_by', name: 'created_by' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        columnDefs: [
            { targets: [0], className: 'text-center' },
        ],
    });
    new $.fn.dataTable.FixedHeader(table);
});
</script>
<script src="{{ $theme_link }}js/purchase.js"></script>
@endpush
