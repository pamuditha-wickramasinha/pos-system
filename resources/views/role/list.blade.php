@extends('layouts.app')
@php($activeMenu = 'roles-list')

@push('styles')
@include('partials.datatable-styles')
@endpush

@section('content')
<section class="content-header">
    <h1>{{ $page_title }} <small>View/Search Roles</small></h1>
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
                    <h3 class="box-title">{{ $page_title }}</h3>
                    @can('roles_add')
                    <div class="box-tools">
                        <a class="btn btn-block btn-info" href="{{ route('roles.add') }}"><i class="fa fa-plus"></i> New Role</a>
                    </div>
                    @endcan
                </div>
                <div class="box-body">
                    <table id="example2" class="table table-bordered table-striped" width="100%">
                        <thead class="bg-primary">
                        <tr>
                            <th class="text-center">#</th>
                            <th>Role Name</th>
                            <th>Description</th>
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
<script type="text/javascript">
$(document).ready(function() {
    var table = $('#example2').DataTable({
        dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>tip',
        buttons: { buttons: [
            { className: 'btn bg-red color-palette btn-flat hidden delete_btn pull-left', text: 'Delete', action: function (e, dt, node, config) { multi_delete(); } },
            { extend: 'copy', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3] } },
            { extend: 'excel', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3] } },
            { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3] } },
            { extend: 'print', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3] } },
            { extend: 'csv', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3] } },
            { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat', text: 'Columns' },
        ]},
        processing: true, serverSide: true, order: [], responsive: true,
        language: { processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>' },
        ajax: {
            url: "{{ route('roles.ajax_list') }}", type: "POST",
            complete: function (data) { $('.column_checkbox').iCheck({ checkboxClass: 'icheckbox_square-orange', radioClass: 'iradio_square-orange', increaseArea: '10%' }); call_code(); },
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'description', name: 'description' },
            { data: 'status_badge', name: 'status' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        columnDefs: [ { targets: [0], className: 'text-center' } ],
    });
    new $.fn.dataTable.FixedHeader(table);
});
</script>
<script src="{{ $theme_link }}js/roles.js"></script>
@endpush
