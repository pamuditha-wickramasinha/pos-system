@extends('layouts.app')
@php($activeMenu = 'dashboard')

@push('styles')
<link rel="stylesheet" href="{{ $theme_link }}plugins/jvectormap/jquery-jvectormap-1.2.2.css">
@endpush

@section('content')
<section class="content-header">
    <h1>{{ $page_title }} <small>Overall Information on Single Screen</small></h1>
    <ol class="breadcrumb">
        <li class="active"><i class="fa fa-dashboard"></i> Home</li>
    </ol>
</section>
<br>
<div class="row">
    <div class="col-md-12">
        @include('partials.flashdata')
    </div>
</div>

<section class="content">
    <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="ion ion-bag"></i></span>
                <div class="info-box-content">
                    <span class="text-bold text-uppercase">Total Purchase Due</span>
                    <span class="info-box-number">{{ currency(app_number_format($purchaseDue)) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="fa fa-dollar"></i></span>
                <div class="info-box-content">
                    <span class="text-bold text-uppercase">Total Sales Due</span>
                    <span class="info-box-number">{{ currency(app_number_format($salesDue)) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-cart-plus"></i></span>
                <div class="info-box-content">
                    <span class="text-bold text-uppercase">Total Sales Amount</span>
                    <span class="info-box-number">{{ currency(app_number_format($totSalGrandTotal)) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-minus-square-o"></i></span>
                <div class="info-box-content">
                    <span class="text-bold text-uppercase">Total Expense Amount</span>
                    <span class="info-box-number">{{ currency(app_number_format($totExp)) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="ion ion-bag"></i></span>
                <div class="info-box-content">
                    <span class="text-bold text-uppercase">Today's Total Purchase</span>
                    <span class="info-box-number">{{ currency(app_number_format($todaysTotalPurchase)) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="fa fa-dollar"></i></span>
                <div class="info-box-content">
                    <span class="text-bold text-uppercase">Today's Payment Received (Sales)</span>
                    <span class="info-box-number">{{ currency(app_number_format($todayPaymentReceived)) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-cart-plus"></i></span>
                <div class="info-box-content">
                    <span class="text-bold text-uppercase">Today's Total Sales</span>
                    <span class="info-box-number">{{ currency(app_number_format($todaysTotalSales)) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-minus-square-o"></i></span>
                <div class="info-box-content">
                    <span class="text-bold text-uppercase">Today's Total Expense</span>
                    <span class="info-box-number">{{ currency(app_number_format($todaysTotalExpense)) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-dream-pink">
                <div class="inner text-uppercase">
                    <h3>{{ $totCust }}</h3><p>Customers</p>
                </div>
                <div class="icon"><i class="fa fa-group"></i></div>
                @if(auth()->id() === 1)
                    <a href="{{ url('customers') }}" class="small-box-footer text-uppercase">View <i class="fa fa-arrow-circle-right"></i></a>
                @endif
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-dream-purple">
                <div class="inner text-uppercase">
                    <h3>{{ $totSup }}</h3><p>Suppliers</p>
                </div>
                <div class="icon"><i class="fa fa-group"></i></div>
                @if(auth()->id() === 1)
                    <a href="{{ url('suppliers') }}" class="small-box-footer text-uppercase">View <i class="fa fa-arrow-circle-right"></i></a>
                @endif
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-dream-maroon">
                <div class="inner text-uppercase">
                    <h3>{{ $totPur }}</h3><p>Purchase Invoice</p>
                </div>
                <div class="icon"><i class="ion ion-ios-paper-outline"></i></div>
                @if(auth()->id() === 1)
                    <a href="{{ url('purchase') }}" class="small-box-footer text-uppercase">View <i class="fa fa-arrow-circle-right"></i></a>
                @endif
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="small-box bg-dream-green">
                <div class="inner text-uppercase">
                    <h3>{{ $totSal }}</h3><p>Sales Invoice</p>
                </div>
                <div class="icon"><i class="ion ion-ios-paper-outline"></i></div>
                @if(auth()->id() === 1)
                    <a href="{{ url('sales') }}" class="small-box-footer text-uppercase">View <i class="fa fa-arrow-circle-right"></i></a>
                @endif
            </div>
        </div>
        <div class="clearfix visible-sm-block"></div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title text-uppercase">Purchase and Sales Bar Chart</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="chart"><canvas id="barChart" style="height:230px"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title text-uppercase">Recently Added Items</h3>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-bordered table-responsive">
                        <tr class="bg-blue"><td>Sl.No</td><td>Item Name</td><td>Sales Price</td></tr>
                        <tbody>
                        @foreach ($recentItems as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item->item_name }}</td>
                                <td>{{ currency(app_number_format($item->sales_price), true) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        @if(auth()->id() === 1)
                            <tfoot><tr><td colspan="3" class="text-center"><a href="{{ url('items') }}" class="uppercase">View All</a></td></tr></tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header"><h3 class="box-title text-uppercase">Expired Items</h3></div>
                <div class="box-body table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr class="bg-blue"><th>#</th><th>Item Code</th><th>Item Name</th><th>Category</th><th>Expire Date</th></tr>
                        </thead>
                        <tbody>
                        @foreach ($expiredItems as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item->item_code }}</td>
                                <td>{{ $item->item_name }}</td>
                                <td>{{ $item->category->category_name ?? '' }}</td>
                                <td>{{ show_date($item->expire_date) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot><tr><td colspan="5" class="text-center"><a href="{{ url('reports/expired_items') }}" class="uppercase">View All</a></td></tr></tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header"><h3 class="box-title text-uppercase">Stock Alert</h3></div>
                <div class="box-body table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr class="bg-blue"><th>#</th><th>Item Name</th><th>Category</th><th>Stock</th></tr>
                        </thead>
                        <tbody>
                        @foreach ($stockAlertItems as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item->item_name }}</td>
                                <td>{{ $item->category->category_name ?? '' }}</td>
                                <td>{{ $item->stock }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot><tr><td colspan="4" class="text-center"><a href="{{ url('reports/stock') }}" class="uppercase">View All</a></td></tr></tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xs-12">
            <div class="box box-primary">
                <div class="box-body">
                    <div id="bar_container" style="min-width: 310px; height: 400px; max-width: 600px; margin: 0 auto"></div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ $theme_link }}plugins/chartjs/Chart.min.js"></script>
<script src="{{ $theme_link }}plugins/sparkline/jquery.sparkline.min.js"></script>
<script src="{{ $theme_link }}plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script src="{{ $theme_link }}plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
<script src="{{ $theme_link }}plugins/highcharts/highcharts.js"></script>
<script src="{{ $theme_link }}plugins/highcharts/highcharts-more.js"></script>
<script src="{{ $theme_link }}plugins/highcharts/exporting.js"></script>
<script src="{{ $theme_link }}plugins/highcharts/export-data.js"></script>
<script>
$(function () {
    var barChartData = {
        labels: ["January","February","March","April","May","June","July","August","September","October","November","December"],
        datasets: [
            {
                label: "Purchase Amt(in {{ currency() }})",
                fillColor: "rgba(210, 214, 222, 1)",
                strokeColor: "rgba(210, 214, 222, 1)",
                pointColor: "rgba(210, 214, 222, 1)",
                pointStrokeColor: "#c1c7d1",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(220,220,220,1)",
                data: @json($purchaseByMonth)
            },
            {
                label: "Sales Amt(in {{ currency() }})",
                fillColor: "rgba(60,141,188,0.9)",
                strokeColor: "rgba(60,141,188,0.8)",
                pointColor: "#3b8bba",
                pointStrokeColor: "rgba(60,141,188,1)",
                pointHighlightFill: "#fff",
                pointHighlightStroke: "rgba(60,141,188,1)",
                data: @json($salesByMonth)
            }
        ]
    };
    var barChartCanvas = $("#barChart").get(0).getContext("2d");
    var barChart = new Chart(barChartCanvas);
    barChartData.datasets[1].fillColor = "#00a65a";
    barChartData.datasets[1].strokeColor = "#00a65a";
    barChartData.datasets[1].pointColor = "#00a65a";
    var barChartOptions = {
        scaleBeginAtZero: true,
        scaleShowGridLines: true,
        scaleGridLineColor: "rgba(0,0,0,.05)",
        scaleGridLineWidth: 1,
        scaleShowHorizontalLines: true,
        scaleShowVerticalLines: true,
        barShowStroke: true,
        barStrokeWidth: 2,
        barValueSpacing: 5,
        barDatasetSpacing: 1,
        legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].fillColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>",
        responsive: true,
        maintainAspectRatio: true
    };
    barChartOptions.datasetFill = false;
    barChart.Bar(barChartData, barChartOptions);
});

Highcharts.chart('bar_container', {
    chart: { plotBackgroundColor: null, plotBorderWidth: null, plotShadow: false, type: 'pie' },
    title: { text: 'Top 10 Trending Items %' },
    tooltip: { pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>' },
    plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
                enabled: true,
                format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                style: { color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black' }
            }
        }
    },
    series: [{
        name: 'Item',
        colorByPoint: true,
        data: [
            @foreach ($topItems as $ti)
                { name: {!! json_encode($ti->item_name) !!}, y: {{ $ti->qty }} },
            @endforeach
        ]
    }]
});
</script>
@endpush
