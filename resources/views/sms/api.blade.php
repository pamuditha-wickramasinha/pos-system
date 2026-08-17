@extends('layouts.app')
@php($activeMenu = 'sms-api')

@section('content')
<section class="content-header">
    <h1>{{ $page_title }} <small>Add/Update SMS API</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">{{ $page_title }}</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        @include('partials.flashdata')
        <div class="col-md-12">
            <div class="box box-info">
                <form class="form-horizontal" id="api-form">
                    <input type="hidden" name="hidden_rowcount" id="hidden_rowcount" value="{{ $rows->count() }}">
                    @csrf
                    <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
                    <div class="box-body">
                        <div class="form-group">
                            <div class="col-sm-12">
                                <div class="callout callout-info">
                                    <h4>HTTP/URL</h4>
                                    <p>
                                        <u>Example</u>: <br>https://www.example.com/api/mt/SendSMS?APIKey=QWERTYUIOP123456&senderid=ABCDEF&channel=2&DCS=0&flashsms=0&<b class="bg-yellow">mobiles</b>=91989xxxxxxx&<b class="bg-yellow">message</b>=test message&route=1
                                        <br>Note: You need to verify the message key & mobile number key from your API, each SMS service provider may have different keys.<br>
                                        In the above example the API's 'message' & 'mobiles' keys refer to Message & Mobile number, where you need to change the values of the Message & Number.
                                    </p>
                                </div>
                            </div>
                            <div class="col-sm-12 table-responsive">
                                <table class="table" id="api_table">
                                    <thead>
                                    <tr>
                                        <th width="15%"></th>
                                        <th width="20%" class="text-center">Key</th>
                                        <th width="40%" class="text-center">Key Value</th>
                                        <th><input type="button" class="btn btn-success" onclick="addrow();" value="+" title="New Line"></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($rows as $i => $row)
                                        @php($rownum = $i + 1)
                                        <tr id="row_{{ $rownum }}" data-row="{{ $rownum }}">
                                            @if($row->info === 'url')
                                                <td class="text-right"><label class="control-label">URL<label class="text-danger">*</label></label>
                                                    <input type="hidden" id="info_{{ $rownum }}" name="info_{{ $rownum }}" value="{{ $row->info }}">
                                                </td>
                                                <td><input id="key_{{ $rownum }}" name="key_{{ $rownum }}" type="text" class="form-control" value="{{ $row->key }}" readonly></td>
                                                <td><input id="key_val_{{ $rownum }}" name="key_val_{{ $rownum }}" type="text" class="form-control" value="{{ $row->key_value }}"></td>
                                                <td><input type="button" class="btn btn-danger" value="-" title="Can't Remove" disabled></td>
                                            @elseif($row->info === 'mobile')
                                                <td class="text-right"><label class="control-label">Mobile Key<label class="text-danger">*</label></label>
                                                    <input type="hidden" id="info_{{ $rownum }}" name="info_{{ $rownum }}" value="{{ $row->info }}">
                                                </td>
                                                <td><input id="key_{{ $rownum }}" name="key_{{ $rownum }}" type="text" class="form-control" value="{{ $row->key }}"></td>
                                                <td><input id="key_val_{{ $rownum }}" name="key_val_{{ $rownum }}" type="text" class="form-control" readonly value="{{ $row->key_value }}"></td>
                                                <td><input type="button" class="btn btn-danger" value="-" title="Can't Remove" disabled></td>
                                            @elseif($row->info === 'message')
                                                <td class="text-right"><label class="control-label">Message Key<label class="text-danger">*</label></label>
                                                    <input type="hidden" id="info_{{ $rownum }}" name="info_{{ $rownum }}" value="{{ $row->info }}">
                                                </td>
                                                <td><input id="key_{{ $rownum }}" name="key_{{ $rownum }}" type="text" class="form-control" value="{{ $row->key }}"></td>
                                                <td><input id="key_val_{{ $rownum }}" name="key_val_{{ $rownum }}" type="text" class="form-control" readonly value="{{ $row->key_value }}"></td>
                                                <td><input type="button" class="btn btn-danger" value="-" title="Can't Remove" disabled></td>
                                            @else
                                                <td><input type="hidden" id="info_{{ $rownum }}" name="info_{{ $rownum }}" value="{{ $row->info }}"></td>
                                                <td><input id="key_{{ $rownum }}" name="key_{{ $rownum }}" type="text" class="form-control" value="{{ $row->key }}"></td>
                                                <td><input id="key_val_{{ $rownum }}" name="key_val_{{ $rownum }}" type="text" class="form-control" value="{{ $row->key_value }}"></td>
                                                <td><input type="button" class="btn btn-danger" value="-" title="Remove?" onclick="removerow('{{ $rownum }}')"></td>
                                            @endif
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="form-group">
                                <label for="sms_status" class="col-sm-2 control-label">SMS Status<label class="text-danger">*</label></label>
                                <div class="col-sm-4">
                                    <select class="form-control" id="sms_status" name="sms_status" style="width: 100%;">
                                        <option value="1" @selected($smsStatus)>Active</option>
                                        <option value="0" @selected(! $smsStatus)>In-Active</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer col-sm-12">
                        <div class="col-sm-6">
                            <div class="col-sm-4"></div>
                            <div class="col-sm-8">
                                <button type="button" class="btn bg-orange" title="Back to List" onclick="history.back();">Back</button>
                                <button type="button" class="btn btn-success" id="update">Update</button>
                                <a href="{{ url('dashboard') }}"><button type="button" class="btn btn-danger" title="Go Dashboard">Close</button></a>
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
<script src="{{ $theme_link }}js/sms.js"></script>
<script>
function removerow(id) { $("#row_" + id).remove(); }
function addrow() {
    var rowcount = $("#hidden_rowcount").val();
    rowcount = parseInt(rowcount) + 1;
    $("#hidden_rowcount").val(rowcount);
    var str = '<tr id="row_' + rowcount + '" data-row="' + rowcount + '">';
    str += '<td><input type="hidden" id="info_' + rowcount + '" name="info_' + rowcount + '" value=""></td>';
    str += '<td><input id="key_' + rowcount + '" name="key_' + rowcount + '" type="text" class="form-control" /></td>';
    str += '<td><input id="key_val_' + rowcount + '" name="key_val_' + rowcount + '" type="text" class="form-control" /></td>';
    str += '<td><input type="button" class="btn btn-danger" value="-" title="Remove Record" onclick="removerow(' + rowcount + ')"></td>';
    str += '</tr>';
    $('#api_table tbody').append(str);
}
</script>
@endpush
