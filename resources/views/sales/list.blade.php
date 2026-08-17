@extends('layouts.app')
@php($activeMenu = 'sales-list')

@push('styles')
@include('partials.datatable-styles')
<link rel="stylesheet" href="{{ $theme_link }}plugins/datepicker/datepicker3.css">
@endpush

@section('content')
<section class="content-header">
    <h1>{{ $page_title }} <small>View/Search Sales</small></h1>
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
        @include('partials.flashdata')
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ $page_title }}</h3>
                    @can('sales_add')
                    <div class="box-tools">
                        <a class="btn btn-block btn-info" href="{{ route('sales.add') }}"><i class="fa fa-plus"></i> New Sales</a>
                    </div>
                    @endcan
                </div>
                <div class="box-body">
                    <table id="example2" class="table table-bordered table-striped" width="100%">
                        <thead class="bg-primary">
                        <tr>
                            <th class="text-center"><input type="checkbox" class="group_check checkbox"></th>
                            <th>Sales Date</th>
                            <th>Sales Code</th>
                            <th>Sales Status</th>
                            <th>Reference No</th>
                            <th>Customer Name</th>
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
            url: "{{ route('sales.ajax_list') }}", type: "POST",
            complete: function (data) { $('.column_checkbox').iCheck({ checkboxClass: 'icheckbox_square-orange', radioClass: 'iradio_square-orange', increaseArea: '10%' }); call_code(); },
        },
        columns: [
            { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
            { data: 'sales_date', name: 'sales_date' },
            { data: 'sales_code_display', name: 'sales_code' },
            { data: 'sales_status', name: 'sales_status' },
            { data: 'reference_no', name: 'reference_no' },
            { data: 'customer_name', name: 'customer_name', orderable: false },
            { data: 'grand_total', name: 'grand_total' },
            { data: 'paid_amount', name: 'paid_amount' },
            { data: 'sales_due', name: 'sales_due', orderable: false },
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
function print_invoice(sales_id){
    window.open($("#base_url").val()+"sales/print_invoice_pos/"+sales_id, "_blank", "width=400,height=600");
}
</script>
<script src="{{ $theme_link }}js/sales.js"></script>
@endpush
