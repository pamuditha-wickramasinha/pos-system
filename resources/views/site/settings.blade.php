@extends('layouts.app')
@php($activeMenu = 'site-settings')

@section('content')
<section class="content-header">
    <h1>{{ $page_title }} <small>Add/Update Site Settings</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">{{ $page_title }}</li>
    </ol>
</section>

<form class="form-horizontal" id="site-form" enctype="multipart/form-data">
@csrf
<section class="content">
    <div class="row">
        @include('partials.flashdata')
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#tab_1" data-toggle="tab">Site</a></li>
                    <li><a href="#tab_2" data-toggle="tab">Sales</a></li>
                    <li><a href="#tab_3" data-toggle="tab">Prefixes</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_1">
                        <div class="box-body">
                            <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="site_name" class="col-sm-4 control-label">Site Name<label class="text-danger">*</label></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="site_name" name="site_name" value="{{ $settings->site_name }}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="timezone" class="col-sm-4 control-label">Timezone</label>
                                        <div class="col-sm-8">
                                            <select class="form-control select2" id="timezone" name="timezone" style="width: 100%;">
                                                @foreach (timezone_identifiers_list() as $tz)
                                                    <option @selected($settings->timezone === $tz) value="{{ $tz }}">{{ $tz }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="date_format" class="col-sm-4 control-label">Date Format<label class="text-danger">*</label></label>
                                        <div class="col-sm-8">
                                            <select class="form-control select2" id="date_format" name="date_format" style="width: 100%;">
                                                <option @selected($settings->date_format === 'dd-mm-yyyy') value="dd-mm-yyyy">dd-mm-yyyy</option>
                                                <option @selected($settings->date_format === 'mm/dd/yyyy') value="mm/dd/yyyy">mm/dd/yyyy</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="time_format" class="col-sm-4 control-label">Time Format<label class="text-danger">*</label></label>
                                        <div class="col-sm-8">
                                            <select class="form-control select2" id="time_format" name="time_format" style="width: 100%;">
                                                <option @selected($settings->time_format == 12) value="12">12 Hours</option>
                                                <option @selected($settings->time_format == 24) value="24">24 Hours</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="currency" class="col-sm-4 control-label">Currency<label class="text-danger">*</label></label>
                                        <div class="col-sm-8">
                                            <select class="form-control select2" id="currency" name="currency" style="width: 100%;">
                                                @forelse ($currencies as $c)
                                                    <option @selected($settings->currency_id == $c->id) value="{{ $c->id }}">{{ $c->currency_name }} {{ $c->currency_code }} ({{ $c->currency }})</option>
                                                @empty
                                                    <option value="">No Records Found</option>
                                                @endforelse
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="number_to_words" class="col-sm-4 control-label">Number to Words Format</label>
                                        <div class="col-sm-8">
                                            <select class="form-control select2" id="number_to_words" name="number_to_words" style="width: 100%;">
                                                <option @selected($settings->number_to_words === 'Default') value="Default">Default</option>
                                                <option @selected($settings->number_to_words === 'Indian') value="Indian">Indian</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="currency_placement" class="col-sm-4 control-label">Currency Symbol Placement</label>
                                        <div class="col-sm-8">
                                            <select class="form-control select2" id="currency_placement" name="currency_placement" style="width: 100%;">
                                                <option @selected($settings->currency_placement === 'Right') value="Right">After Amount</option>
                                                <option @selected($settings->currency_placement === 'Left') value="Left">Before Amount</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="round_off" class="col-sm-4 control-label">Enable Round Off</label>
                                        <div class="col-sm-4"><input type="checkbox" @checked($settings->round_off) id="round_off" name="round_off"></div>
                                    </div>
                                    <div class="form-group">
                                        <label for="disable_tax" class="col-sm-4 control-label">Disable Tax</label>
                                        <div class="col-sm-4"><input type="checkbox" @checked($settings->disable_tax) id="disable_tax" name="disable_tax"></div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="logo" class="col-sm-4 control-label">Site Logo</label>
                                        <div class="col-sm-8">
                                            <input type="file" id="logo" name="logo">
                                            <span class="text-danger">Max Width/Height: 300px * 300px & Size: 300px</span>
                                        </div>
                                    </div>
                                    @if($settings->logo)
                                        <div class="form-group">
                                            <div class="col-sm-8 col-sm-offset-4">
                                                <img class="img-responsive" style="border:3px solid #d2d6de;" src="{{ asset('storage/'.$settings->logo) }}">
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="tab_2">
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="sales_discount" class="col-sm-4 control-label">Default Sales Discount</label>
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control" id="sales_discount" name="sales_discount" value="{{ $settings->sales_discount }}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="change_return" class="col-sm-4 control-label">Show Change Return in POS</label>
                                        <div class="col-sm-4"><input type="checkbox" @checked($settings->change_return) id="change_return" name="change_return"></div>
                                    </div>
                                    <div class="form-group">
                                        <label for="show_upi_code" class="col-sm-4 control-label">Show UPI Code on Invoice</label>
                                        <div class="col-sm-4"><input type="checkbox" @checked($settings->show_upi_code) id="show_upi_code" name="show_upi_code"></div>
                                    </div>
                                    <div class="form-group">
                                        <label for="sales_invoice_format_id" class="col-sm-4 control-label">Sales Invoice Format</label>
                                        <div class="col-sm-4">
                                            <select class="form-control select2" id="sales_invoice_format_id" name="sales_invoice_format_id" style="width: 100%;">
                                                <option @selected($settings->sales_invoice_format_id == 1) value="1">Format 1</option>
                                                <option @selected($settings->sales_invoice_format_id == 2) value="2">Format 2</option>
                                                <option @selected($settings->sales_invoice_format_id == 3) value="3">Format 3</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="sales_invoice_footer_text" class="col-sm-4 control-label">Sales Invoice Footer Text</label>
                                        <div class="col-sm-6">
                                            <textarea class="form-control" id="sales_invoice_footer_text" name="sales_invoice_footer_text">{{ $settings->sales_invoice_footer_text }}</textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="sales_terms_and_conditions" class="col-sm-4 control-label">Sales Terms &amp; Conditions</label>
                                        <div class="col-sm-6">
                                            <textarea class="form-control" id="sales_terms_and_conditions" name="sales_terms_and_conditions">{{ $company->sales_terms_and_conditions }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="tab_3">
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-6">
                                    @foreach ([
                                        'category_init' => 'Category Prefix',
                                        'item_init' => 'Item Prefix',
                                        'supplier_init' => 'Supplier Prefix',
                                        'purchase_init' => 'Purchase Prefix',
                                        'purchase_return_init' => 'Purchase Return Prefix',
                                        'customer_init' => 'Customer Prefix',
                                        'sales_init' => 'Sales Prefix',
                                        'sales_return_init' => 'Sales Return Prefix',
                                        'expense_init' => 'Expense Prefix',
                                    ] as $field => $label)
                                        <div class="form-group">
                                            <label for="{{ $field }}" class="col-sm-4 control-label">{{ $label }}</label>
                                            <div class="col-sm-4">
                                                <input type="text" class="form-control" id="{{ $field }}" name="{{ $field }}" value="{{ $company->{$field} }}">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box-footer">
                <div class="col-sm-8 col-sm-offset-2 text-center">
                    <div class="col-md-3 col-md-offset-3"><button type="button" id="update" class="btn btn-block btn-success">Update</button></div>
                    <div class="col-sm-3"><a href="{{ url('dashboard') }}"><button type="button" class="col-sm-3 btn btn-block btn-warning">Close</button></a></div>
                </div>
            </div>
        </div>
    </div>
</section>
</form>
@endsection

@push('scripts')
<script>
$(".select2").select2();
$("#update").on("click", function () {
    var base_url = $("#base_url").val().trim();
    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $("#update").attr('disabled', true);
    var data = new FormData($('#site-form')[0]);
    $.ajax({
        type: 'POST',
        url: base_url + 'site/update_site',
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        success: function (result) {
            result = result.trim();
            if (result == "success") {
                window.location = base_url + "site";
            } else {
                toastr["error"](result);
            }
            $("#update").attr('disabled', false);
            $(".overlay").remove();
        }
    });
});
</script>
@endpush
