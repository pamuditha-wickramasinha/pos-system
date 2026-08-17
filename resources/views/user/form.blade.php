@extends('layouts.app')
@php($activeMenu = 'users')

@section('content')
<section class="content-header">
    <h1>{{ $page_title }} <small>Enter User Information</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{ route('users.view') }}">View Users</a></li>
        <li class="active">{{ $page_title }}</li>
    </ol>
</section>

@php($isEdit = isset($q_id))
@php($adminLocked = $isEdit && (int) $q_id === 1)

<section class="content">
    <div class="row">
        @include('partials.flashdata')
        <div class="col-md-12">
            <div class="box box-info">
                <form class="form-horizontal" id="users-form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
                    <input type="hidden" name="command" value="{{ $isEdit ? 'update' : 'save' }}">
                    <input type="hidden" name="q_id" id="q_id" value="{{ $q_id ?? '' }}">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="new_user" class="col-sm-4 control-label">User Name<label class="text-danger">*</label></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control input-sm" id="new_user" name="new_user" value="{{ $username ?? '' }}" autofocus>
                                        <span id="new_user_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="mobile" class="col-sm-4 control-label">Mobile<label class="text-danger">*</label></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control input-sm no_special_char_no_space" id="mobile" name="mobile" value="{{ $mobile ?? '' }}">
                                        <span id="mobile_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="email" class="col-sm-4 control-label">Email<label class="text-danger">*</label></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control input-sm" id="email" name="email" value="{{ $email ?? '' }}">
                                        <span id="email_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="role_id" class="col-sm-4 control-label">Role<label class="text-danger">*</label></label>
                                    <div class="col-sm-8">
                                        <select class="form-control" @disabled($adminLocked) id="role_id" name="role_id" style="width: 100%;">
                                            <option value="">-Select-</option>
                                            @foreach (\App\Models\Role::where('status', true)->when((($role_id ?? null) != 1), fn ($q) => $q->where('id', '!=', 1))->get() as $r)
                                                <option @selected(($role_id ?? null) == $r->id) value="{{ $r->id }}">{{ $r->name }}</option>
                                            @endforeach
                                        </select>
                                        <span id="role_id_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="pass" class="col-sm-4 control-label">Password @unless($isEdit)<label class="text-danger">*</label>@endunless</label>
                                    <div class="col-sm-8">
                                        <input type="password" class="form-control input-sm" @disabled($adminLocked) id="pass" name="pass">
                                        <span id="pass_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="confirm" class="col-sm-4 control-label">Confirm Password @unless($isEdit)<label class="text-danger">*</label>@endunless</label>
                                    <div class="col-sm-8">
                                        <input type="password" class="form-control input-sm" @disabled($adminLocked) id="confirm" name="confirm">
                                        <span id="confirm_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="profile_picture" class="col-sm-4 control-label">Profile Picture</label>
                                    <div class="col-sm-8">
                                        <input type="file" id="profile_picture" name="profile_picture">
                                        <span id="logo_msg" style="display:block;" class="text-danger">Max Width/Height: 500px * 500px & Size: 500Kb </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="col-sm-8 col-sm-offset-4">
                                        <img width="200px" height="200px" class="img-responsive" style="border:3px solid #d2d6de;" src="{{ ! empty($profile_picture) ? asset('storage/'.$profile_picture) : asset('theme/dist/img/avatar5.png') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="col-sm-8 col-sm-offset-2 text-center">
                            @php($btn_id = $isEdit ? 'update' : 'save')
                            @php($btn_name = $isEdit ? 'Update' : 'Save')
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
<script src="{{ $theme_link }}js/users.js"></script>
@endpush
