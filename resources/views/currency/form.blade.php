@extends('layouts.app')
@php($activeMenu = 'currency')

@section('content')
<section class="content-header">
    <h1>{{ $page_title }} <small>Add/Update Currency</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{ route('currency.view') }}">Currencies List</a></li>
        <li class="active">{{ $page_title }}</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        @include('partials.flashdata')
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border"><h3 class="box-title">Please Enter Valid Data</h3></div>
                <form class="form-horizontal" id="currency-form" onkeypress="return event.keyCode != 13;">
                    @csrf
                    <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="currency_name" class="col-sm-2 control-label">Currency Name<label class="text-danger">*</label></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control input-sm" id="currency_name" name="currency_name" value="{{ $currency_name ?? '' }}">
                                <span id="currency_name_msg" style="display:none" class="text-danger"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="currency_code" class="col-sm-2 control-label">Currency Code</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control input-sm" id="currency_code" name="currency_code" value="{{ $currency_code ?? '' }}">
                                <span id="currency_code_msg" style="display:none" class="text-danger"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="currency" class="col-sm-2 control-label">Currency Symbol<label class="text-danger">*</label></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control input-sm" id="currency" name="currency" value="{{ $currency ?? '' }}">
                                <span id="currency_msg" style="display:none" class="text-danger"></span>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="col-sm-8 col-sm-offset-2 text-center">
                            @if (! empty($currency_name) && isset($q_id))
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

    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header"><h3 class="box-title">Currency Codes</h3></div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr class="bg-blue"><th>Country</th><th>Currency Code</th><th>Currency Symbol</th></tr>
                        </thead>
                        <tbody>
                        @foreach ([
                            ['United Arab Emirates','AED','د.إ'],['Afghanistan','AFN','؋'],['Netherlands Antilles','ANG','ƒ'],
                            ['Argentina','ARS','$'],['Australia','AUD','$'],['Brazil','BRL','R$'],['Canada','CAD','$'],
                            ['Switzerland','CHF','CHF'],['China','CNY','¥'],['Denmark','DKK','kr'],['Algeria','DZD','د.ج'],
                            ['Egypt','EGP','£'],['European Union','EUR','€'],['United Kingdom','GBP','£'],['Ghana','GHC, GHS','₵'],
                            ['Hong Kong','HKD','$'],['Israel','ILS','₪'],['India','INR','₹'],['Iraq','IQD','ع.د'],
                            ['Iran','IRR','﷼'],['Jamaica','JMD','J$'],['Jordan','JOD','د.ا'],['Japan','JPY','¥'],
                            ['Kenya','KES','KSh'],['North Korea','KPW','₩'],['South Korea','KRW','₩'],['Kuwait','KWD','د.ك'],
                            ['Libya','LYD','ل.د'],['Mexico','MXN','$'],['Morocco','MAD','د.م.'],['Mauritius','MUR','₨'],
                            ['Nigeria','NGN','₦'],['New Zealand','NZD','$'],['Peru','PEN','S/.'],['Philippines','PHP','₱'],
                            ['Paraguay','PYG','₲'],['Qatar','QAR','ر.ق'],['Romania','RON','lei'],['Serbia','RSD','РСД'],
                            ['Russia','RUB','₽'],['Rwanda','RWF','FRw'],['Saudi Arabia','SAR','ر.س'],['Sudan','SDG','ج.س.'],
                            ['Sweden','SEK','kr'],['Singapore','SGD','$'],['Saint Helena','SHP','£'],['Syria','SYP','£S'],
                            ['Thailand','THB','฿'],['Tunisia','TND','د.ت'],['Turkey','TRY','₺'],['Taiwan','TWD','$'],
                            ['Uganda','UGX','USh'],['United States','USD','$'],['Venezuela','VES','Bs.'],
                            ['Central African CFA franc','XAF','FCFA'],['Eastern Caribbean dollar','XCD','$'],
                            ['West African CFA franc','XOF','CFA'],['CFP franc','XPF','F'],['Yemen','YER','﷼'],['South Africa','ZAR','R'],
                        ] as [$country, $code, $symbol])
                            <tr><td>{{ $country }}</td><td>{{ $code }}</td><td>{{ $symbol }}</td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ $theme_link }}js/currency.js"></script>
@endpush
