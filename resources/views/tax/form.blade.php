@extends('layouts.app')
@php($activeMenu = 'tax')

@section('content')
<section class="content-header">
    <h1>{{ $page_title }} <small>Add/Update Tax</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{ route('tax.index') }}">Tax List</a></li>
        <li class="active">{{ $page_title }}</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        @include('partials.flashdata')
        <div class="col-md-12">
            <div class="box box-info">
                <form class="form-horizontal" id="tax-form">
                    @csrf
                    <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="tax_name" class="col-sm-2 control-label">Tax Name<label class="text-danger">*</label></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control input-sm" id="tax_name" name="tax_name" value="{{ $tax_name ?? '' }}" autofocus onkeyup="shift_cursor(event,'tax')">
                                <span id="tax_name_msg" style="display:none" class="text-danger"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="tax" class="col-sm-2 control-label">Tax Percentage<label class="text-danger">*</label></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control input-sm only_currency" id="tax" name="tax" value="{{ $tax ?? '' }}" onkeyup="shift_cursor(event,'save')">
                                <span id="tax_msg" style="display:none" class="text-danger"></span>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="col-sm-8 col-sm-offset-2 text-center">
                            @if (! empty($tax))
                                <input type="hidden" name="q_id" id="q_id" value="{{ $q_id }}">
                                @php($btn_id = 'update') @php($btn_name = 'Update')
                            @else
                                @php($btn_id = 'save') @php($btn_name = 'Save')
                            @endif
                            <div class="col-md-3 col-md-offset-3">
                                <button type="button" id="{{ $btn_id }}" class="btn btn-block btn-success">{{ $btn_name }}</button>
                            </div>
                            <div class="col-sm-3">
                                <a href="{{ url('dashboard') }}"><button type="button" class="col-sm-3 btn btn-block btn-warning close_btn">Close</button></a>
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
<script src="{{ $theme_link }}js/tax.js"></script>
@endpush
