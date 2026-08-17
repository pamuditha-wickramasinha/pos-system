@extends('layouts.app')
@php($activeMenu = 'users-view')

@push('styles')
@include('partials.datatable-styles')
@endpush

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
        @include('partials.flashdata')
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ $page_title }}</h3>
                    @can('users_add')
                    <div class="box-tools">
                        <a class="btn btn-block btn-info" href="{{ route('users.create') }}"><i class="fa fa-plus"></i> New User</a>
                    </div>
                    @endcan
                </div>
                <div class="box-body">
                    <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
                    <table id="example2" class="table table-bordered table-striped" width="100%">
                        <thead class="bg-primary">
                        <tr>
                            <th>#</th>
                            <th>User Name</th>
                            <th>Mobile</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created On</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($users as $i => $u)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $u->username }}</td>
                                <td>{{ $u->mobile }}</td>
                                <td>{{ $u->email }}</td>
                                <td>{{ $u->roles->first()?->name }}</td>
                                <td>{{ $u->created_at?->format('d-m-Y') }}</td>
                                <td>
                                    @if ($u->id === 1)
                                        <span class="label label-default" style="cursor:not-allowed">Restricted</span>
                                    @elseif ($u->status)
                                        <span onclick="update_status({{ $u->id }},0)" id="span_{{ $u->id }}" class="label label-success" style="cursor:pointer">Active </span>
                                    @else
                                        <span onclick="update_status({{ $u->id }},1)" id="span_{{ $u->id }}" class="label label-danger" style="cursor:pointer"> Inactive </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" title="View Account">
                                        <a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">Action <span class="caret"></span></a>
                                        <ul role="menu" class="dropdown-menu dropdown-light pull-right">
                                            @can('users_edit')
                                            <li><a title="Update Record ?" href="{{ route('users.edit', $u) }}"><i class="fa fa-fw fa-edit text-blue"></i>Edit</a></li>
                                            @endcan
                                            @if ($u->id !== 1)
                                                @can('users_delete')
                                                <li><a style="cursor:pointer" title="Delete Record ?" onclick="delete_user({{ $u->id }})"><i class="fa fa-fw fa-trash text-red"></i>Delete</a></li>
                                                @endcan
                                            @endif
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
<script src="{{ $theme_link }}js/users.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    var table = $('#example2').DataTable({
        dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>tip',
        buttons: { buttons: [
            { extend: 'copy', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [0,1,2,3,4,5,6] } },
            { extend: 'excel', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [0,1,2,3,4,5,6] } },
            { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [0,1,2,3,4,5,6] } },
            { extend: 'print', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6] } },
            { extend: 'csv', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [0,1,2,3,4,5,6] } },
            { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat', text: 'Columns' },
        ]},
        processing: true, serverSide: false, order: [], responsive: true,
        language: { processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>' },
        columnDefs: [ { targets: [7], orderable: false } ],
    });
    new $.fn.dataTable.FixedHeader(table);
});
</script>
@endpush
