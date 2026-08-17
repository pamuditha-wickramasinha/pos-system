@extends('layouts.app')
@php($activeMenu = 'purchase-returns')

@section('content')
<section class="content-header">
    <h1>Invoice <small>Purchase Return Invoice</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{ route('purchase_return.index') }}">Purchase Return List</a></li>
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
                                {{ $returnEntry->supplier->supplier_name }}<br>
                                {{ $returnEntry->supplier->mobile }}<br>
                                {{ $returnEntry->supplier->address }}
                            </address>
                        </div>
                        <div class="col-sm-4">
                            <h4>Return Info</h4>
                            <b>Invoice #{{ $returnEntry->return_code }}</b><br>
                            Date: {{ show_date($returnEntry->return_date) }}<br>
                            Status: {{ $returnEntry->return_status }}<br>
                            Reference: {{ $returnEntry->reference_no }}
                        </div>
                        <div class="col-sm-4">
                            <b>Grand Total: {{ currency($returnEntry->grand_total) }}</b><br>
                            Paid: {{ currency($returnEntry->paid_amount) }}<br>
                            Due: {{ currency($returnEntry->grand_total - $returnEntry->paid_amount) }}<br>
                            Status: {{ $returnEntry->payment_status }}
                        </div>
                    </div>
                    <table class="table table-bordered">
                        <thead>
                        <tr><th>#</th><th>Item</th><th>Qty</th><th>Price</th><th>Tax</th><th>Discount</th><th>Total</th></tr>
                        </thead>
                        <tbody>
                        @foreach ($returnEntry->items as $i => $ri)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $ri->item->item_name ?? '' }}</td>
                                <td>{{ $ri->return_qty }}</td>
                                <td class="text-right">{{ currency($ri->price_per_unit) }}</td>
                                <td class="text-right">{{ currency($ri->tax_amt) }}</td>
                                <td class="text-right">{{ currency($ri->discount_amt) }}</td>
                                <td class="text-right">{{ currency($ri->total_cost) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <a class="btn btn-info" target="_blank" href="{{ route('purchase_return.print_invoice', $returnEntry) }}"><i class="fa fa-print"></i> Print</a>
                    <a class="btn btn-danger" target="_blank" href="{{ route('purchase_return.pdf', $returnEntry) }}"><i class="fa fa-file-pdf-o"></i> PDF</a>
                    <a class="btn btn-default" href="{{ route('purchase_return.index') }}">Back</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
