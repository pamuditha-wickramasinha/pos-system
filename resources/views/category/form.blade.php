@extends('layouts.app')
@php($activeMenu = 'category')

@section('content')
<section class="content-header">
    <h1>{{ $page_title }} <small>Add/Update Category</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{ route('category.view') }}">Categories List</a></li>
        <li class="active">{{ $page_title }}</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Please Enter Valid Data</h3>
                </div>
                <form class="form-horizontal" id="category-form" onkeypress="return event.keyCode != 13;">
                    @csrf
                    <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="category" class="col-sm-2 control-label">Category Name<label class="text-danger">*</label></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control input-sm" id="category" name="category" onkeyup="shift_cursor(event,'description')" value="{{ $category_name ?? '' }}" autofocus>
                                <span id="category_msg" style="display:none" class="text-danger"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description" class="col-sm-2 control-label">Description</label>
                            <div class="col-sm-4">
                                <textarea class="form-control" id="description" name="description">{{ $description ?? '' }}</textarea>
                                <span id="description_msg" style="display:none" class="text-danger"></span>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="col-sm-8 col-sm-offset-2 text-center">
                            @if (! empty($category_code))
                                <input type="hidden" name="q_id" id="q_id" value="{{ $q_id }}">
                                @php($btn_id = 'update')
                                @php($btn_name = 'Update')
                            @else
                                @php($btn_id = 'save')
                                @php($btn_name = 'Save')
                            @endif
                            <div class="col-md-3 col-md-offset-3">
                                <button type="button" id="{{ $btn_id }}" class="btn btn-block btn-success" title="Save Data">{{ $btn_name }}</button>
                            </div>
                            <div class="col-sm-3">
                                <a href="{{ url('dashboard') }}">
                                    <button type="button" class="col-sm-3 btn btn-block btn-warning close_btn" title="Go Dashboard">Close</button>
                                </a>
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
<script src="{{ $theme_link }}js/category.js"></script>
@endpush
