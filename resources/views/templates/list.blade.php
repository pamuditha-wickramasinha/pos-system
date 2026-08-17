@extends('layouts.app')
@php($activeMenu = 'sms-templates-list')

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
                    @can('sms_template_add')
                    <div class="box-tools">
                        <a class="btn btn-block btn-info" href="{{ route('templates.sms_new') }}"><i class="fa fa-plus"></i> New Template</a>
                    </div>
                    @endcan
                </div>
                <div class="box-body">
                    <table id="example2" class="table table-bordered table-striped" width="100%">
                        <thead class="bg-primary">
                        <tr><th>#</th><th>Template Name</th><th>Content</th><th>Status</th><th>Action</th></tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
@include('partials.datatable-scripts')
<script type="text/javascript">
$(document).ready(function() {
    var table = $('#example2').DataTable({
        processing: true, serverSide: true, order: [],
        ajax: { url: "{{ route('templates.ajax_list') }}", type: "POST" },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'template_name', name: 'template_name' },
            { data: 'content', name: 'content' },
            { data: 'status_badge', name: 'status' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
    });
});
</script>
<script src="{{ $theme_link }}js/templates.js"></script>
@endpush
