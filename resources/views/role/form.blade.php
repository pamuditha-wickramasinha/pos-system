@extends('layouts.app')
@php($activeMenu = 'roles-list')

@section('content')
<section class="content-header">
    <h1>{{ $page_title }} <small>Add/Update Role</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{ route('roles.index') }}">Roles List</a></li>
        <li class="active">{{ $page_title }}</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        @include('partials.flashdata')
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Please Enter Valid Data</h3>
                </div>
                <form class="form-horizontal" id="roles-form" onkeypress="return event.keyCode != 13;">
                    @csrf
                    <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="role_name" class="col-sm-2 control-label">Role Name<label class="text-danger">*</label></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control input-sm" id="role_name" name="role_name" onkeyup="shift_cursor(event,'description')" value="{{ $role_name ?? '' }}" autofocus>
                                <span id="role_name_msg" style="display:none" class="text-danger"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description" class="col-sm-2 control-label">Description</label>
                            <div class="col-sm-4">
                                <textarea class="form-control" id="description" name="description">{{ $description ?? '' }}</textarea>
                                <span id="description_msg" style="display:none" class="text-danger"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <table class="table table-bordered">
                                    <thead class="bg-primary">
                                    <tr>
                                        <th>#</th>
                                        <th>Modules</th>
                                        <th>Select All</th>
                                        <th>Specific Permissions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php($i = 1)
                                    @foreach ($permissionGroups as $key => $group)
                                        <tr>
                                            <td>{{ $i++ }}</td>
                                            <td>{{ $group['label'] }}</td>
                                            <td>
                                                <div class="checkbox icheck"><label>
                                                    <input type="checkbox" class="change_me" id="{{ $key }}"> Select All
                                                </label></div>
                                            </td>
                                            <td>
                                                @foreach ($group['permissions'] as $permKey => $permLabel)
                                                    <div class="checkbox icheck"><label>
                                                        <input type="checkbox" class="{{ $key }}_all" id="{{ $permKey }}" name="permission[{{ $permKey }}]" @checked(in_array($permKey, $assignedPermissions))> {{ $permLabel }}
                                                    </label></div>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="col-sm-8 col-sm-offset-2 text-center">
                            @if (! empty($q_id))
                                <input type="hidden" name="q_id" id="q_id" value="{{ $q_id }}">
                                @php($btn_id = 'update') @php($btn_name = 'Update')
                            @else
                                @php($btn_id = 'save') @php($btn_name = 'Save')
                            @endif
                            <div class="col-md-3 col-md-offset-3">
                                <button type="button" id="{{ $btn_id }}" class="btn btn-block btn-success">{{ $btn_name }}</button>
                            </div>
                            <div class="col-sm-3">
                                <a href="{{ route('roles.index') }}"><button type="button" class="col-sm-3 btn btn-block btn-warning close_btn">Close</button></a>
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
<script src="{{ $theme_link }}js/roles.js"></script>
@endpush
