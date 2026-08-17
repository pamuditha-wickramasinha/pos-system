@extends('layouts.app')
@php($activeMenu = 'printers-list')

@section('content')
<section class="content-header">
    <h1>{{ $page_title }} <small>Configure receipt printers</small></h1>
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
                    @can('printers_add')
                    <div class="box-tools">
                        <a class="btn btn-block btn-info" href="{{ route('printers.add') }}"><i class="fa fa-plus"></i> New Printer</a>
                    </div>
                    @endcan
                </div>
                <div class="box-body">
                    <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
                    <table class="table table-bordered table-striped" width="100%">
                        <thead class="bg-primary">
                        <tr>
                            <th>Sl.No</th>
                            <th>Name</th>
                            <th>Connection</th>
                            <th>Destination</th>
                            <th>Paper</th>
                            <th>Default</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($printers as $i => $p)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $p->name }}</td>
                                <td>
                                    @if ($p->connection_type === 'network')
                                        <span class="label label-primary">Network (WiFi/LAN)</span>
                                    @else
                                        <span class="label label-warning">Windows / USB (Print Agent)</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($p->connection_type === 'network')
                                        {{ $p->ip_address }}:{{ $p->port }}
                                    @else
                                        {{ $p->windows_printer_name }}
                                    @endif
                                </td>
                                <td>{{ $p->paper_width }}mm</td>
                                <td>@if ($p->is_default) <span class="label label-success">Default</span> @endif</td>
                                <td>
                                    @if ($p->status)
                                        <span onclick="update_status({{ $p->id }},0)" id="span_{{ $p->id }}" class="label label-success" style="cursor:pointer">Active </span>
                                    @else
                                        <span onclick="update_status({{ $p->id }},1)" id="span_{{ $p->id }}" class="label label-danger" style="cursor:pointer"> Inactive </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" title="Action">
                                        <a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">Action <span class="caret"></span></a>
                                        <ul role="menu" class="dropdown-menu dropdown-light pull-right">
                                            <li><a style="cursor:pointer" onclick="test_print({{ $p->id }})">Test Print</a></li>
                                            <li><a style="cursor:pointer" onclick="use_on_this_device({{ $p->id }}, '{{ $p->connection_type }}', {{ Js::from($p->name) }})">Use on This Device</a></li>
                                            @can('printers_edit')
                                            @if ($p->connection_type === 'local_agent')
                                            <li><a href="{{ route('printers.agent_setup', $p) }}">Agent Setup</a></li>
                                            @endif
                                            <li><a href="{{ route('printers.edit', $p) }}">Edit</a></li>
                                            @endcan
                                            @can('printers_delete')
                                            <li><a style="cursor:pointer" onclick="delete_printer({{ $p->id }})">Delete</a></li>
                                            @endcan
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center">No printers configured yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                    <p class="text-muted">
                        <strong>This device's printer:</strong> <span id="this_device_printer">Not set (falls back to the print preview popup)</span>
                        &nbsp; <a style="cursor:pointer" onclick="clear_device_printer()">clear</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ $theme_link }}js/printer-bridge.js"></script>
<script src="{{ $theme_link }}js/printers.js"></script>
<script>$(document).ready(function () { render_this_device_printer(); });</script>
@endpush
