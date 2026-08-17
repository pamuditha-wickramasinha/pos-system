@extends('layouts.app')
@php($activeMenu = 'warehouse-list')

@push('styles')
@include('partials.datatable-styles')
@endpush

@section('content')
<section class="content-header">
    <h1>{{ $page_title }} <small>Manage Warehouse</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">{{ $page_title }}</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        @include('partials.flashdata')
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ $page_title }}</h3>
                    <div class="box-tools">
                        <a class="btn btn-block btn-info" href="{{ route('warehouse.add') }}"><i class="fa fa-plus"></i> New Warehouse</a>
                    </div>
                </div>
                <div class="box-body">
                    <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
                    <table id="example2" class="table table-bordered table-striped" width="100%">
                        <thead class="bg-primary">
                        <tr>
                            <th>Sl.No</th>
                            <th>Warehouse name</th>
                            <th>Mobile</th>
                            <th>Email</th>
                            <th>status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($warehouses as $i => $w)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $w->warehouse_name }}</td>
                                <td>{{ $w->mobile }}</td>
                                <td>{{ $w->email }}</td>
                                <td>
                                    @if ($w->status)
                                        <span onclick="update_status({{ $w->id }},0)" id="span_{{ $w->id }}" class="label label-success" style="cursor:pointer">Active </span>
                                    @else
                                        <span onclick="update_status({{ $w->id }},1)" id="span_{{ $w->id }}" class="label label-danger" style="cursor:pointer"> Inactive </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" title="View Account">
                                        <a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">Action <span class="caret"></span></a>
                                        <ul role="menu" class="dropdown-menu dropdown-light pull-right">
                                            <li><a title="Update Record ?" href="{{ route('warehouse.edit', $w) }}">Edit</a></li>
                                            <li><a style="cursor:pointer" title="Delete Record ?" onclick="delete_warehouse('{{ $w->id }}')">Delete</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
@include('partials.datatable-scripts')
<script src="{{ $theme_link }}js/warehouse.js"></script>
<script type="text/javascript">
function delete_warehouse(id) {
    if (confirm("Do You Wants to Delete Record ?")) {
        $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
        $.post("{{ url('warehouse/delete_warehouse') }}", { id: id }, function (result) {
            if (result == "success") {
                toastr["success"]("Record Deleted Successfully!");
                location.reload();
            } else if (result == "failed") {
                toastr["error"]("Failed to Delete .Try again!");
            } else {
                toastr["error"]("Error! Something Went Wrong!");
            }
            $(".overlay").remove();
        });
    }
}
$(document).ready(function() {
    var table = $('#example2').DataTable({
        dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>tip',
        buttons: { buttons: [
            { extend: 'copy', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [0,1,2,3,4] } },
            { extend: 'excel', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [0,1,2,3,4] } },
            { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [0,1,2,3,4] } },
            { extend: 'print', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [0,1,2,3,4] } },
            { extend: 'csv', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [0,1,2,3,4] } },
            { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat', text: 'Columns' },
        ]},
        processing: true, serverSide: false, order: [], responsive: true,
        language: { processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>' },
        columnDefs: [ { targets: [5], orderable: false } ],
    });
    new $.fn.dataTable.FixedHeader(table);
});
</script>
@endpush
