@extends('layouts.app')
@php($activeMenu = 'warehouse')

@section('content')
<section class="content-header">
    <h1>Warehouse <small>Enter Valid Information</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{ route('warehouse.index') }}">Warehouse List</a></li>
        <li class="active">Warehouse</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        @include('partials.flashdata')
        <div class="col-md-12">
            <div class="box box-info">
                <form class="form-horizontal" id="warehouse-form" onkeypress="return event.keyCode != 13;">
                    @csrf
                    <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="warehouse_name" class="col-sm-2 control-label">Warehouse Name<label class="text-danger">*</label></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control input-sm" id="warehouse_name" name="warehouse_name" value="{{ $warehouse_name ?? '' }}" autofocus onkeyup="shift_cursor(event,'mobile')">
                                <span id="warehouse_name_msg" style="display:none" class="text-danger"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="mobile" class="col-sm-2 control-label">Mobile<label class="text-danger">*</label></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control input-sm no_special_char_no_space" id="mobile" name="mobile" value="{{ $mobile ?? '' }}" onkeyup="shift_cursor(event,'email')">
                                <span id="mobile_msg" style="display:none" class="text-danger"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email" class="col-sm-2 control-label">Email<label class="text-danger">*</label></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control input-sm" value="{{ $email ?? '' }}" id="email" name="email">
                                <span id="email_msg" style="display:none" class="text-danger"></span>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer col-sm-12">
                        <div class="col-sm-6">
                            <div class="col-sm-4"></div>
                            <div class="col-sm-8">
                                <input type="hidden" name="q_id" id="q_id" value="{{ $q_id ?? '' }}">
                                @php($btn_id = isset($warehouse_name) ? 'update' : 'save')
                                @php($btn_name = isset($warehouse_name) ? 'Update' : 'Save')
                                <button type="button" id="{{ $btn_id }}" class="btn btn-success">{{ $btn_name }}</button>
                                <a href="{{ url('dashboard') }}"><button type="button" class="btn btn-default">Close</button></a>
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
<script src="{{ $theme_link }}js/warehouse.js"></script>
@endpush
