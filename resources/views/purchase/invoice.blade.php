@extends('layouts.app')
@php($activeMenu = 'purchase-list')

@section('content')
<section class="content-header">
    <h1>Invoice <small>Purchase Invoice</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{ route('purchase.index') }}">Purchase List</a></li>
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
                            <h4>Supplier</h4>
                            <address>
                                {{ $purchase->supplier->supplier_name }}<br>
                                {{ $purchase->supplier->mobile }}<br>
                                {{ $purchase->supplier->address }}
                            </address>
                        </div>
                        <div class="col-sm-4">
                            <h4>Purchase Info</h4>
                            <b>Invoice #{{ $purchase->purchase_code }}</b><br>
                            Date: {{ show_date($purchase->purchase_date) }}<br>
                            Status: {{ $purchase->purchase_status }}<br>
                            Reference: {{ $purchase->reference_no }}
                        </div>
                        <div class="col-sm-4">
                            <b>Grand Total: {{ currency($purchase->grand_total) }}</b><br>
                            Paid: {{ currency($purchase->paid_amount) }}<br>
                            Due: {{ currency($purchase->grand_total - $purchase->paid_amount) }}<br>
                            Status: {{ $purchase->payment_status }}
                        </div>
                    </div>
                    <table class="table table-bordered">
                        <thead>
                        <tr><th>#</th><th>Item</th><th>Qty</th><th>Price</th><th>Tax</th><th>Discount</th><th>Total</th></tr>
                        </thead>
                        <tbody>
                        @foreach ($purchase->items as $i => $pi)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $pi->item->item_name ?? '' }}</td>
                                <td>{{ $pi->purchase_qty }}</td>
                                <td class="text-right">{{ currency($pi->price_per_unit) }}</td>
                                <td class="text-right">{{ currency($pi->tax_amt) }}</td>
                                <td class="text-right">{{ currency($pi->discount_amt) }}</td>
                                <td class="text-right">{{ currency($pi->total_cost) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <a class="btn btn-info" target="_blank" href="{{ route('purchase.print_invoice', $purchase) }}"><i class="fa fa-print"></i> Print</a>
                    <a class="btn btn-danger" target="_blank" href="{{ route('purchase.pdf', $purchase) }}"><i class="fa fa-file-pdf-o"></i> PDF</a>
                    <a class="btn btn-default" href="{{ route('purchase.index') }}">Back</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
