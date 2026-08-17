<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Purchase Invoice #{{ $purchase->purchase_code }}</title>
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
            <h3>Purchase Invoice</h3>
            <p>Invoice #: {{ $purchase->purchase_code }}<br>
            Date: {{ show_date($purchase->purchase_date) }}<br>
            Reference: {{ $purchase->reference_no }}<br>
            Status: {{ $purchase->purchase_status }}</p>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-xs-6">
            <h4>Supplier</h4>
            <p>{{ $purchase->supplier->supplier_name }}<br>
            {{ $purchase->supplier->mobile }}<br>
            {{ $purchase->supplier->address }}</p>
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
    <div class="row">
        <div class="col-xs-6 col-xs-offset-6">
            <table class="table">
                <tr><th class="text-right">Subtotal</th><td class="text-right">{{ currency($purchase->subtotal) }}</td></tr>
                <tr><th class="text-right">Other Charges</th><td class="text-right">{{ currency($purchase->other_charges_amt) }}</td></tr>
                <tr><th class="text-right">Discount</th><td class="text-right">{{ currency($purchase->tot_discount_to_all_amt) }}</td></tr>
                <tr><th class="text-right">Grand Total</th><td class="text-right"><b>{{ currency($purchase->grand_total) }}</b></td></tr>
                <tr><th class="text-right">Paid Amount</th><td class="text-right">{{ currency($purchase->paid_amount) }}</td></tr>
                <tr><th class="text-right">Due</th><td class="text-right">{{ currency($purchase->grand_total - $purchase->paid_amount) }}</td></tr>
            </table>
        </div>
    </div>
    @if($purchase->purchase_note)
        <p><b>Note:</b> {{ $purchase->purchase_note }}</p>
    @endif
</div>
</body>
</html>
