@extends('layouts.app')
@php($activeMenu = 'sms-template')

@section('content')
<section class="content-header">
    <h1>{{ $page_title }} <small>Add/Update SMS Template</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{ route('templates.sms') }}">SMS Templates List</a></li>
        <li class="active">{{ $page_title }}</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        @include('partials.flashdata')
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border"><h3 class="box-title">Please Enter Valid Data</h3></div>
                <form class="form-horizontal" id="template-form">
                    @csrf
                    <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="template_name" class="col-sm-2 control-label">Template Name<label class="text-danger">*</label></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control input-sm" id="template_name" name="template_name" value="{{ $template?->template_name }}" @if($template) readonly @endif autofocus>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="content" class="col-sm-2 control-label">Content<label class="text-danger">*</label></label>
                            <div class="col-sm-6">
                                <textarea spellcheck="false" class="form-control" rows="6" id="content" name="content">{{ $template?->content }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="col-sm-8 col-sm-offset-2 text-center">
                            @if($template)
                                <input type="hidden" name="q_id" id="q_id" value="{{ $template->id }}">
                                @php($btn_id = 'update') @php($btn_name = 'Update')
                            @else
                                @php($btn_id = 'save') @php($btn_name = 'Save')
                            @endif
                            <div class="col-md-3 col-md-offset-3"><button type="button" id="{{ $btn_id }}" class="btn btn-block btn-success">{{ $btn_name }}</button></div>
                            <div class="col-sm-3"><a href="{{ url('dashboard') }}"><button type="button" class="col-sm-3 btn btn-block btn-warning">Close</button></a></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ $theme_link }}js/templates.js"></script>
@endpush
