<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Sales Invoice #{{ $sale->sales_code }}</title>
<link rel="stylesheet" href="{{ $theme_link }}bootstrap/css/bootstrap.min.css">
<style>body{padding:20px;} @media print { .no-print { display:none; } }</style>
</head>
<body onload="window.print();">
@php($company = \App\Models\Company::first())
<div class="container">
    <div class="row">
        <div class="col-xs-6">
            <h3>{{ $company->company_name ?? '' }}</h3>
            <p>{{ $company->address ?? '' }}, {{ $company->city ?? '' }}<br>
            {{ $company->mobile ?? '' }} {{ $company->email ?? '' }}</p>
        </div>
        <div class="col-xs-6 text-right">
            <h3>Sales Invoice</h3>
            <p>Invoice #: {{ $sale->sales_code }}<br>
            Date: {{ show_date($sale->sales_date) }}<br>
            Reference: {{ $sale->reference_no }}<br>
            Status: {{ $sale->sales_status }}</p>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-xs-6">
            <h4>Customer</h4>
            <p>{{ $sale->customer->customer_name }}<br>
            {{ $sale->customer->mobile }}<br>
            {{ $sale->customer->address }}</p>
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
    <div class="row">
        <div class="col-xs-6 col-xs-offset-6">
            <table class="table">
                <tr><th class="text-right">Subtotal</th><td class="text-right">{{ currency($sale->subtotal) }}</td></tr>
                <tr><th class="text-right">Other Charges</th><td class="text-right">{{ currency($sale->other_charges_amt) }}</td></tr>
                <tr><th class="text-right">Discount</th><td class="text-right">{{ currency($sale->tot_discount_to_all_amt) }}</td></tr>
                <tr><th class="text-right">Grand Total</th><td class="text-right"><b>{{ currency($sale->grand_total) }}</b></td></tr>
                <tr><th class="text-right">Paid Amount</th><td class="text-right">{{ currency($sale->paid_amount) }}</td></tr>
                <tr><th class="text-right">Due</th><td class="text-right">{{ currency($sale->grand_total - $sale->paid_amount) }}</td></tr>
            </table>
        </div>
    </div>
    @if($sale->sales_note)
        <p><b>Note:</b> {{ $sale->sales_note }}</p>
    @endif
</div>
</body>
</html>
