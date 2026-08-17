@extends('layouts.app')
@php($activeMenu = 'sales-list')

@section('content')
<section class="content-header">
    <h1>Invoice <small>Sales Invoice</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{ route('sales.index') }}">Sales List</a></li>
        <li class="active">Invoice</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        @include('partials.flashdata')
        <div class="col-md-12">
            <div class="box box-solid">
                <div class="box-body">
                    <div class="row invoice-info">
                        <div class="col-sm-4">
                            <h4>Customer</h4>
                            <address>
                                {{ $sale->customer->customer_name }}<br>
                                {{ $sale->customer->mobile }}<br>
                                {{ $sale->customer->address }}
                            </address>
                        </div>
                        <div class="col-sm-4">
                            <h4>Sales Info</h4>
                            <b>Invoice #{{ $sale->sales_code }}</b><br>
                            Date: {{ show_date($sale->sales_date) }}<br>
                            Status: {{ $sale->sales_status }}<br>
                            Reference: {{ $sale->reference_no }}
                        </div>
                        <div class="col-sm-4">
                            <b>Grand Total: {{ currency($sale->grand_total) }}</b><br>
                            Paid: {{ currency($sale->paid_amount) }}<br>
                            Due: {{ currency($sale->grand_total - $sale->paid_amount) }}<br>
                            Status: {{ $sale->payment_status }}
                        </div>
                    </div>
                    <table class="table table-bordered">
                        <thead>
                        <tr><th>#</th><th>Item</th><th>Qty</th><th>Price</th><th>Tax</th><th>Discount</th><th>Total</th></tr>
                        </thead>
                        <tbody>
                        @foreach ($sale->items as $i => $si)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $si->item->item_name ?? '' }}</td>
                                <td>{{ $si->sales_qty }}</td>
                                <td class="text-right">{{ currency($si->price_per_unit) }}</td>
                                <td class="text-right">{{ currency($si->tax_amt) }}</td>
                                <td class="text-right">{{ currency($si->discount_amt) }}</td>
                                <td class="text-right">{{ currency($si->total_cost) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <a class="btn btn-info" target="_blank" href="{{ route('sales.print_invoice', $sale) }}"><i class="fa fa-print"></i> Print</a>
                    <a class="btn btn-danger" target="_blank" href="{{ route('sales.pdf', $sale) }}"><i class="fa fa-file-pdf-o"></i> PDF</a>
                    <a class="btn btn-default" href="{{ route('sales.index') }}">Back</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
