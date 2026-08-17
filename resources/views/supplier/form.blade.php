@extends('layouts.app')
@php($activeMenu = 'suppliers')

@section('content')
<section class="content-header">
    <h1>{{ $page_title }} <small>Add/Update Suppliers</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{ route('suppliers.index') }}">Suppliers List</a></li>
        <li class="active">{{ $page_title }}</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        @include('partials.flashdata')
        <div class="col-md-12">
            <div class="box box-info">
                <form class="form-horizontal" id="suppliers-form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="supplier_name" class="col-sm-4 control-label">Supplier Name<label class="text-danger">*</label></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="supplier_name" name="supplier_name" value="{{ $supplier_name ?? '' }}" autofocus>
                                        <span id="supplier_name_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="mobile" class="col-sm-4 control-label">Mobile</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control no_special_char_no_space" id="mobile" name="mobile" value="{{ $mobile ?? '' }}">
                                        <span id="mobile_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="email" class="col-sm-4 control-label">Email</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="email" name="email" value="{{ $email ?? '' }}">
                                        <span id="email_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="phone" class="col-sm-4 control-label">Phone</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control no_special_char_no_space" id="phone" name="phone" value="{{ $phone ?? '' }}">
                                        <span id="phone_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="gstin" class="col-sm-4 control-label">GST Number</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="gstin" name="gstin" value="{{ $gstin ?? '' }}">
                                        <span id="gstin_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="tax_number" class="col-sm-4 control-label">Tax Number</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="tax_number" name="tax_number" value="{{ $tax_number ?? '' }}">
                                        <span id="tax_number_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="opening_balance" class="col-sm-4 control-label">Opening Balance</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="opening_balance" name="opening_balance" value="{{ $opening_balance ?? '' }}">
                                        <span id="opening_balance_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="country" class="col-sm-4 control-label">Country</label>
                                    <div class="col-sm-8">
                                        <select class="form-control select2" id="country" name="country" style="width: 100%;">
                                            <option value="">-Select-</option>
                                            @foreach (\App\Models\Country::where('status', true)->get() as $c)
                                                <option @selected(($country_id ?? null) == $c->id) value="{{ $c->id }}">{{ $c->country }}</option>
                                            @endforeach
                                        </select>
                                        <span id="country_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="state" class="col-sm-4 control-label">State</label>
                                    <div class="col-sm-8">
                                        <select class="form-control select2" id="state" name="state" style="width: 100%;">
                                            <option value="">-Select-</option>
                                            @foreach (\App\Models\State::where('status', true)->get() as $s)
                                                <option @selected(($state_id ?? null) == $s->id) value="{{ $s->id }}">{{ $s->state }}</option>
                                            @endforeach
                                        </select>
                                        <span id="state_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="city" class="col-sm-4 control-label">City</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="city" name="city" value="{{ $city ?? '' }}">
                                        <span id="city_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="postcode" class="col-sm-4 control-label">Postcode</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control no_special_char_no_space" id="postcode" name="postcode" value="{{ $postcode ?? '' }}">
                                        <span id="postcode_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="address" class="col-sm-4 control-label">Address</label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control" id="address" name="address">{{ $address ?? '' }}</textarea>
                                        <span id="address_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="col-sm-8 col-sm-offset-2 text-center">
                            @if (! empty($supplier_name))
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

            <div class="box">
                <div class="box-header"><h3 class="box-title text-blue">Opening Balance Payments</h3></div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr class="bg-gray">
                            <th>#</th><th>Payment Date</th><th>Payment</th><th>Payment Type</th><th>Payment Note</th><th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php($payments = isset($q_id) ? \App\Models\SupplierOpeningBalancePayment::where('supplier_id', $q_id)->get() : collect())
                        @forelse ($payments as $i => $p)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ show_date($p->payment_date) }}</td>
                                <td class="text-right">{{ currency($p->payment) }}</td>
                                <td>{{ $p->payment_type }}</td>
                                <td>{{ $p->payment_note }}</td>
                                <td><i class="fa fa-trash text-red pointer" onclick="delete_opening_balance_entry({{ $p->id }})"> Delete</i></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-bold">No Previous Stock Entry Found!!</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ $theme_link }}js/suppliers.js"></script>
@endpush
