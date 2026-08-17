@extends('layouts.app')
@php($activeMenu = 'import_customers')

@section('content')
<section class="content-header">
    <h1>{{ $page_title }}</h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{ route('customers.index') }}">Customers List</a></li>
        <li class="active">{{ $page_title }}</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        @include('partials.flashdata')
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border"><h3 class="box-title">Please Upload a Valid CSV File</h3></div>
                <form class="form-horizontal" id="import-form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
                    <div class="box-body">
                        <div class="callout callout-info">
                            <h4>Column Order</h4>
                            <p>customer_name, mobile, email, phone, gstin, tax_number, country, state, city, postcode, address, opening_balance</p>
                        </div>
                        <div class="form-group">
                            <label for="import_file" class="col-sm-2 control-label">CSV File</label>
                            <div class="col-sm-6">
                                <input type="file" id="import_file" name="import_file" accept=".csv">
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="col-sm-8 col-sm-offset-2 text-center">
                            <div class="col-md-3 col-md-offset-3"><button type="button" id="import" class="btn btn-block btn-success">Import</button></div>
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
<script>
$("#import").on("click", function (e) {
    var base_url = $("#base_url").val().trim();
    if ($("#import_file").val() == '') {
        toastr["warning"]("Please Select a CSV File!");
        return;
    }

    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $("#import").attr('disabled', true);
    var data = new FormData($('#import-form')[0]);
    $.ajax({
        type: 'POST',
        url: base_url + 'import/import_customers_csv',
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        success: function (result) {
            result = result.trim();
            if (result == "success") {
                window.location = base_url + "customers";
            } else if (result == "failed") {
                toastr["error"]("Sorry! Failed to Import.Try again!");
            } else {
                toastr["error"](result);
            }
            $("#import").attr('disabled', false);
            $(".overlay").remove();
        }
    });
});
</script>
@endpush
