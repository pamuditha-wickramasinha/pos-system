@extends('layouts.app')
@php($activeMenu = 'sms')

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
        <div class="col-md-12">
            <div class="box box-info">
                <form class="form-horizontal" id="sms-form">
                    @csrf
                    <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="mobile" class="col-sm-2 control-label">Mobile</label>
                            <div class="col-sm-6">
                                <input type="tel" class="form-control" id="mobile" name="mobile" placeholder="Mobile 1,Mobile 2,...">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="message" class="col-sm-2 control-label">Message</label>
                            <div class="col-sm-6">
                                <textarea class="form-control" id="message" name="message"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="col-sm-8 col-sm-offset-2 text-center">
                            <div class="col-md-3 col-md-offset-3"><button type="button" id="send" class="btn btn-block btn-success">Send</button></div>
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
<script src="{{ $theme_link }}js/sms.js"></script>
@endpush
