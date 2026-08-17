@extends('layouts.app')
@php($activeMenu = 'report-profit-loss')

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
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-body table-responsive no-padding">
                    <div class="form-group col-md-4">
                        <label>Select Date</label>
                        <div class="input-group">
                            <button type="button" class="btn btn-default pull-right" id="pl-daterange-btn" name="pl-daterange-btn">
                                <span><i class="fa fa-calendar"></i> Select Date Range</span>
                                <i class="fa fa-caret-down"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header">@include('partials.export-btn', ['tableId' => 'report-data'])</div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-bordered table-hover" id="report-data">
                        <tr><td>Opening Stock</td><td class="text-right text-bold opening_stock_price">{{ currency('0.00') }}</td></tr>
                        <tr><td colspan="2" class="text-bold font-italic text-primary">Purchase</td></tr>
                        <tr><td>Total Purchase</td><td class="text-right text-bold pur_total">{{ currency('0.00') }}</td></tr>
                        <tr><td>Total Purchase Tax</td><td class="text-right text-bold purchase_tax_amt">{{ currency('0.00') }}</td></tr>
                        <tr><td>Total Other Charges of Purchase</td><td class="text-right text-bold pur_other_charges_amt">{{ currency('0.00') }}</td></tr>
                        <tr><td>Total Discount on Purchase</td><td class="text-right text-bold purchase_discount_amt">{{ currency('0.00') }}</td></tr>
                        <tr><td>Paid Amount</td><td class="text-right text-bold text-success purchase_paid_amount">{{ currency('0.00') }}</td></tr>
                        <tr><td>Purchase Due</td><td class="text-right text-bold text-danger purchase_due_total">{{ currency('0.00') }}</td></tr>
                        <tr><td colspan="2" class="text-bold font-italic text-primary">Purchase Return</td></tr>
                        <tr><td>Total Purchase Return</td><td class="text-right text-bold pur_return_total">{{ currency('0.00') }}</td></tr>
                        <tr><td>Total Purchase Return Tax</td><td class="text-right text-bold purchase_return_tax_amt">{{ currency('0.00') }}</td></tr>
                        <tr><td>Total Other Charges of Purchase Return</td><td class="text-right text-bold pur_return_other_charges_amt">{{ currency('0.00') }}</td></tr>
                        <tr><td>Total Discount on Purchase Return</td><td class="text-right text-bold purchase_return_discount_amt">{{ currency('0.00') }}</td></tr>
                        <tr><td>Paid Amount</td><td class="text-right text-bold text-success purchase_return_paid_amount">{{ currency('0.00') }}</td></tr>
                        <tr><td>Purchase Return Due</td><td class="text-right text-bold text-danger purchase_return_due_total">{{ currency('0.00') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box">
                <div class="box-header">@include('partials.export-btn', ['tableId' => 'report-data-4'])</div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-bordered table-hover" id="report-data-4">
                        <tr><td>Total Expense</td><td class="text-right text-bold exp_total">{{ currency('0.00') }}</td></tr>
                        <tr><td colspan="2" class="text-bold font-italic text-primary">Sales</td></tr>
                        <tr><td>Total Sales</td><td class="text-right text-bold sal_total">{{ currency('0.00') }}</td></tr>
                        <tr><td>Total Sales Tax</td><td class="text-right text-bold sales_tax_amt">{{ currency('0.00') }}</td></tr>
                        <tr><td>Total Other Charges of Sales</td><td class="text-right text-bold sal_other_charges_amt">{{ currency('0.00') }}</td></tr>
                        <tr><td>Total Discount on Sales</td><td class="text-right text-bold sales_discount_amt">{{ currency('0.00') }}</td></tr>
                        <tr><td>Paid Amount</td><td class="text-right text-bold text-success sales_paid_amount">{{ currency('0.00') }}</td></tr>
                        <tr><td>Sales Due</td><td class="text-right text-bold text-danger sales_due_total">{{ currency('0.00') }}</td></tr>
                        <tr><td colspan="2" class="text-bold font-italic text-primary">Sales Return</td></tr>
                        <tr><td>Total Sales Return</td><td class="text-right text-bold sal_return_total">{{ currency('0.00') }}</td></tr>
                        <tr><td>Total Sales Return Tax</td><td class="text-right text-bold sales_return_tax_amt">{{ currency('0.00') }}</td></tr>
                        <tr><td>Total Other Charges of Sales Return</td><td class="text-right text-bold sal_return_other_charges_amt">{{ currency('0.00') }}</td></tr>
                        <tr><td>Total Discount on Sales Return</td><td class="text-right text-bold sales_return_discount_amt">{{ currency('0.00') }}</td></tr>
                        <tr><td>Paid Amount</td><td class="text-right text-bold text-success sales_return_paid_amount">{{ currency('0.00') }}</td></tr>
                        <tr><td>Sales Return Due</td><td class="text-right text-bold text-danger sales_return_due_total">{{ currency('0.00') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box">
                <div class="box-header">@include('partials.export-btn', ['tableId' => 'report-data-2'])</div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-bordered table-hover" id="report-data-2">
                        <tr><td>Gross Profit</td><td class="text-right text-bold gross_profit">{{ currency('0.00') }}</td></tr>
                        <tr><td>Net Profit</td><td class="text-right text-bold tot_net_profit">{{ currency('0.00') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <form class="form-horizontal" id="profit-loss-report">
                        <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Select Date</label>
                            <div class="col-sm-3">
                                <div class="input-group">
                                    <button type="button" class="btn btn-default pull-right daterange-btn" name="pl2-daterange-btn" id="pl2-daterange-btn">
                                        <span><i class="fa fa-calendar"></i> Select Date Range</span>
                                        <i class="fa fa-caret-down"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="box-body table-responsive no-padding">
                    <div class="col-md-12">
                        <div class="nav-tabs-custom">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#tab_1" data-toggle="tab">Item Wise Profit</a></li>
                                <li><a href="#tab_2" data-toggle="tab">Invoice Wise Profit</a></li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="tab_1">
                                    <div class="col-md-12">
                                        @include('partials.export-btn', ['tableId' => 'profit_by_item_table'])
                                        <br><br>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover" id="profit_by_item_table">
                                                <thead>
                                                <tr class="bg-blue">
                                                    <th>#</th><th>Item Name</th><th>Sales Quantity</th><th>Sales Price</th><th>Purchase Price</th><th>Gross Profit</th>
                                                </tr>
                                                </thead>
                                                <tbody id="tbodyid"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="tab_2">
                                    <div class="col-md-12">
                                        <div class="alert alert-info text-left">
                                            <p><strong>Note:</strong> Item Wise & Invoice wise Reports total Gross Profit may worry — Invoice wise Report deducts Discount on Invoice.</p>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        @include('partials.export-btn', ['tableId' => 'profit_by_invoice_table'])
                                        <br><br>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover" id="profit_by_invoice_table">
                                                <thead>
                                                <tr class="bg-blue">
                                                    <th>#</th><th>Invoice No</th><th>Sales Date</th><th>Customer Name</th><th>Sales Price</th><th>Purchase Price</th><th>Invoice Discount</th><th>Gross Profit</th>
                                                </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
@include('partials.export-scripts')
<script>
function get_start_date(input_id){ return $('#'+input_id).data('daterangepicker').startDate.format('{{ strtoupper($VIEW_DATE) }}'); }
function get_end_date(input_id){ return $('#'+input_id).data('daterangepicker').endDate.format('{{ strtoupper($VIEW_DATE) }}'); }

function get_reports(report_type, table_name) {
    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    var base_url = $("#base_url").val();
    return $.post(base_url + 'reports/' + report_type, { from_date: get_start_date('pl2-daterange-btn'), to_date: get_end_date('pl2-daterange-btn') }, function (result) {
        $("#" + table_name + " tbody").html(result);
        $(".overlay").remove();
    });
}
function get_all_reports() {
    get_reports('get_profit_by_item', 'profit_by_item_table');
    get_reports('get_profit_by_invoice', 'profit_by_invoice_table');
}
jQuery(document).ready(function ($) {
    get_pl_values();
    get_all_reports();
});

function get_pl_values() {
    var base_url = $("#base_url").val();
    $.post(base_url + "reports/get_profit_loss_report", { from_date: get_start_date('pl-daterange-btn'), to_date: get_end_date('pl-daterange-btn') }, function (result) {
        $.each(result, function (index, element) { $("." + index).html(element); });
    });
}

$('#pl-daterange-btn').on('apply.daterangepicker', function (ev, picker) { get_pl_values(); });
$('#pl2-daterange-btn').on('apply.daterangepicker', function (ev, picker) { get_all_reports(); });

$(function () {
    var start = moment().subtract(29, 'days');
    var end = moment();
    function cb(start, end) {
        $('.daterange-btn span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        $('#pl-daterange-btn span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }
    cb(start, end);

    $('#pl-daterange-btn').daterangepicker(
        {
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            startDate: moment().subtract(29, 'days'),
            endDate: moment()
        },
        function (start, end) {
            $('#pl-daterange-btn span').html(start.format('{{ strtoupper($VIEW_DATE) }}') + ' - ' + end.format('{{ strtoupper($VIEW_DATE) }}'));
        }
    );

    $('#pl2-daterange-btn').daterangepicker(
        {
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            startDate: moment().subtract(29, 'days'),
            endDate: moment()
        },
        function (start, end) {
            $('#pl2-daterange-btn span').html(start.format('{{ strtoupper($VIEW_DATE) }}') + ' - ' + end.format('{{ strtoupper($VIEW_DATE) }}'));
        }
    );
});
</script>
@endpush
