@extends('layouts.app')
@php($activeMenu = 'printers')

@section('content')
<section class="content-header">
    <h1>{{ $page_title }} <small>Add/Update Printer</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{ route('printers.index') }}">Printers List</a></li>
        <li class="active">{{ $page_title }}</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        @include('partials.flashdata')
        <div class="col-md-12">
            <div class="box box-info">
                <form class="form-horizontal" id="printer-form" onkeypress="return event.keyCode != 13;">
                    @csrf
                    <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="name" class="col-sm-2 control-label">Printer Name<label class="text-danger">*</label></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control input-sm" id="name" name="name" value="{{ $printer->name ?? '' }}" autofocus onkeyup="shift_cursor(event,'connection_type')">
                                <span id="name_msg" style="display:none" class="text-danger"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="connection_type" class="col-sm-2 control-label">Connection Type<label class="text-danger">*</label></label>
                            <div class="col-sm-4">
                                <select class="form-control input-sm" id="connection_type" name="connection_type" onchange="toggle_connection_fields()">
                                    <option value="local_agent" @selected(($printer->connection_type ?? 'local_agent') === 'local_agent')>Windows / USB on the counter PC (via Print Agent)</option>
                                    <option value="network" @selected(($printer->connection_type ?? '') === 'network')>Network (WiFi/LAN printer with its own IP)</option>
                                </select>
                                <p class="help-block" id="connection_type_help"></p>
                            </div>
                        </div>

                        <div class="form-group field-network">
                            <label for="ip_address" class="col-sm-2 control-label">Printer IP Address</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control input-sm" id="ip_address" name="ip_address" placeholder="e.g. 192.168.1.50" value="{{ $printer->ip_address ?? '' }}">
                            </div>
                        </div>
                        <div class="form-group field-network">
                            <label for="port" class="col-sm-2 control-label">Port</label>
                            <div class="col-sm-4">
                                <input type="number" class="form-control input-sm" id="port" name="port" placeholder="9100" value="{{ $printer->port ?? 9100 }}">
                            </div>
                        </div>

                        <div class="form-group field-local_agent">
                            <label for="windows_printer_name" class="col-sm-2 control-label">Windows Printer Name</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control input-sm" id="windows_printer_name" name="windows_printer_name" placeholder="e.g. XP-80C" value="{{ $printer->windows_printer_name ?? '' }}">
                                <p class="help-block">Exactly as it appears in Windows Settings &rarr; Printers &amp; scanners on the counter PC. No sharing needed.</p>
                            </div>
                        </div>

                        <div class="form-group field-local_agent">
                            <p class="col-sm-2"></p>
                            <div class="col-sm-6">
                                <p class="help-block">The <strong>POS Print Agent</strong> must be running on the counter PC &mdash; it collects queued receipts from this server and passes them to the USB printer. Save this printer first, then use <strong>Agent Setup</strong> on the printers list to get its token and setup steps. Finally click "Use on This Device" from that PC's browser.</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="paper_width" class="col-sm-2 control-label">Paper Width</label>
                            <div class="col-sm-4">
                                <select class="form-control input-sm" id="paper_width" name="paper_width">
                                    <option value="58" @selected((string) ($printer->paper_width ?? 80) === '58')>58mm</option>
                                    <option value="80" @selected((string) ($printer->paper_width ?? 80) === '80')>80mm</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Options</label>
                            <div class="col-sm-6">
                                <label class="checkbox-inline"><input type="checkbox" name="cut_paper" value="1" @checked($printer->cut_paper ?? true)> Cut paper after printing</label>
                                &nbsp;
                                <label class="checkbox-inline"><input type="checkbox" name="open_cash_drawer" value="1" @checked($printer->open_cash_drawer ?? false)> Open cash drawer</label>
                                &nbsp;
                                <label class="checkbox-inline"><input type="checkbox" name="is_default" value="1" @checked($printer->is_default ?? false)> Make this the default printer</label>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="col-sm-8 col-sm-offset-2 text-center">
                            @if (isset($printer) && isset($q_id))
                                <input type="hidden" name="q_id" id="q_id" value="{{ $q_id }}">
                                @php($btn_id = 'update') @php($btn_name = 'Update')
                            @else
                                @php($btn_id = 'save') @php($btn_name = 'Save')
                            @endif
                            <div class="col-md-3 col-md-offset-2">
                                <button type="button" id="test_connection" class="btn btn-block btn-primary">Test</button>
                            </div>
                            <div class="col-md-3">
                                <button type="button" id="{{ $btn_id }}" class="btn btn-block btn-success">{{ $btn_name }}</button>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('printers.index') }}"><button type="button" class="col-sm-3 btn btn-block btn-warning close_btn">Close</button></a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ $theme_link }}js/printer-bridge.js"></script>
<script src="{{ $theme_link }}js/printers.js"></script>
<script>$(document).ready(function () { toggle_connection_fields(); });</script>
@endpush
