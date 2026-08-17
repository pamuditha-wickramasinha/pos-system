@extends('layouts.app')
@php($activeMenu = 'company-profile')

@section('content')
<section class="content-header">
    <h1>Company Profile <small>Add/Update Company Profile</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Company Profile</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        @include('partials.flashdata')
        <div class="col-md-12">
            <div class="box box-info">
                <form class="form-horizontal" id="company-form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="company_name" class="col-sm-4 control-label">Company Name<label class="text-danger">*</label></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="company_name" name="company_name" value="{{ $company_name }}" onkeyup="shift_cursor(event,'mobile')">
                                        <span id="company_name_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="mobile" class="col-sm-4 control-label">Mobile<label class="text-danger">*</label></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control no_special_char_no_space" id="mobile" name="mobile" value="{{ $mobile }}" onkeyup="shift_cursor(event,'email')">
                                        <span id="mobile_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="email" class="col-sm-4 control-label">Email<label class="text-danger">*</label></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="email" name="email" value="{{ $email }}" onkeyup="shift_cursor(event,'phone')">
                                        <span id="email_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="phone" class="col-sm-4 control-label">Phone</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control no_special_char_no_space" id="phone" name="phone" value="{{ $phone }}" onkeyup="shift_cursor(event,'gstin')">
                                        <span id="phone_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="gstin" class="col-sm-4 control-label">GST Number</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="gstin" name="gstin" value="{{ $gstin }}" onkeyup="shift_cursor(event,'vat')">
                                        <span id="gstin_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="vat" class="col-sm-4 control-label">VAT Number</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="vat" name="vat" value="{{ $vat }}" onkeyup="shift_cursor(event,'website')">
                                        <span id="vat_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="pan" class="col-sm-4 control-label">PAN Number</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="pan" name="pan" value="{{ $pan }}" onkeyup="shift_cursor(event,'website')">
                                        <span id="pan_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="website" class="col-sm-4 control-label">Website</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="website" name="website" value="{{ $website }}" onkeyup="shift_cursor(event,'country')">
                                        <span id="website_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="show_signature" class="col-sm-4 control-label">Show Signature</label>
                                    <div class="col-sm-8">
                                        <input type="checkbox" @checked($show_signature) class="form-control" id="show_signature" name="show_signature">
                                        <br>
                                        <span class="label label-success">Only available in Sales Invoice Format 3</span>
                                        <span id="show_signature_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="signature" class="col-sm-4 control-label">Signature</label>
                                    <div class="col-sm-8">
                                        <input type="file" id="signature" name="signature">
                                        <span id="signature_msg" style="display:block;" class="text-danger">Max Width/Height: 1000px * 1000px & Size: 1024kb </span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-8 col-sm-offset-4">
                                        <img class="img-responsive" style="border:3px solid #d2d6de;" src="{{ $signature ? asset('storage/'.$signature) : asset('theme/images/noimage.png') }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="upi_id" class="col-sm-4 control-label">UPI ID</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="upi_id" name="upi_id" value="{{ $upi_id }}">
                                        <span class="label label-success">Only available in Sales Invoice Format 3</span>
                                        <span id="upi_id_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="upi_code" class="col-sm-4 control-label">UPI Code</label>
                                    <div class="col-sm-8">
                                        <input type="file" id="upi_code" name="upi_code">
                                        <span id="upi_code_msg" style="display:block;" class="text-danger">Max Width/Height: 1000px * 1000px & Size: 1024kb </span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-8 col-sm-offset-4">
                                        <img class="img-responsive" style="border:3px solid #d2d6de;" src="{{ $upi_code ? asset('storage/'.$upi_code) : asset('theme/images/noimage.png') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="bank_details" class="col-sm-4 control-label">Bank Details</label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control" id="bank_details" name="bank_details">{{ $bank_details }}</textarea>
                                        <span id="bank_details_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="country" class="col-sm-4 control-label">Country</label>
                                    <div class="col-sm-8">
                                        <select class="form-control select2" id="country" name="country" style="width: 100%;">
                                            <option value="">-Select-</option>
                                            @foreach (\App\Models\Country::where('status', true)->get() as $c)
                                                <option @selected($c->country === $country) value="{{ $c->country }}">{{ $c->country }}</option>
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
                                                <option @selected($s->state === $state) value="{{ $s->state }}">{{ $s->state }}</option>
                                            @endforeach
                                        </select>
                                        <span id="state_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="city" class="col-sm-4 control-label">City<label class="text-danger">*</label></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="city" name="city" value="{{ $city }}" onkeyup="shift_cursor(event,'postcode')">
                                        <span id="city_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="postcode" class="col-sm-4 control-label">Postcode</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control no_special_char_no_space" id="postcode" name="postcode" value="{{ $postcode }}" onkeyup="shift_cursor(event,'address')" maxlength="6">
                                        <span id="postcode_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="address" class="col-sm-4 control-label">Address<label class="text-danger">*</label></label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control" id="address" name="address">{{ $address }}</textarea>
                                        <span id="address_msg" style="display:none" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="company_logo" class="col-sm-4 control-label">Company Logo</label>
                                    <div class="col-sm-8">
                                        <input type="file" id="company_logo" name="company_logo">
                                        <span id="company_logo_msg" style="display:block;" class="text-danger">Max Width/Height: 1000px * 1000px & Size: 1024kb </span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-8 col-sm-offset-4">
                                        <img class="img-responsive" style="border:3px solid #d2d6de;" src="{{ $company_logo ? asset('storage/'.$company_logo) : asset('theme/images/no_image2.png') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="col-sm-8 col-sm-offset-2 text-center">
                            <input type="hidden" name="q_id" id="q_id" value="{{ $q_id }}">
                            <div class="col-md-3 col-md-offset-3">
                                <button type="button" id="update" class="btn btn-block btn-success">Update</button>
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
<script src="{{ $theme_link }}js/company-profile.js"></script>
@endpush
