<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Purchase Return Invoice #{{ $returnEntry->return_code }}</title>
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
            <h3>Purchase Return Invoice</h3>
            <p>Invoice #: {{ $returnEntry->return_code }}<br>
            Date: {{ show_date($returnEntry->return_date) }}<br>
            Reference: {{ $returnEntry->reference_no }}<br>
            Status: {{ $returnEntry->return_status }}</p>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-xs-6">
            <h4>Supplier</h4>
            <p>{{ $returnEntry->supplier->supplier_name }}<br>
            {{ $returnEntry->supplier->mobile }}<br>
            {{ $returnEntry->supplier->address }}</p>
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
    <div class="row">
        <div class="col-xs-6 col-xs-offset-6">
            <table class="table">
                <tr><th class="text-right">Subtotal</th><td class="text-right">{{ currency($returnEntry->subtotal) }}</td></tr>
                <tr><th class="text-right">Other Charges</th><td class="text-right">{{ currency($returnEntry->other_charges_amt) }}</td></tr>
                <tr><th class="text-right">Discount</th><td class="text-right">{{ currency($returnEntry->tot_discount_to_all_amt) }}</td></tr>
                <tr><th class="text-right">Grand Total</th><td class="text-right"><b>{{ currency($returnEntry->grand_total) }}</b></td></tr>
                <tr><th class="text-right">Paid Amount</th><td class="text-right">{{ currency($returnEntry->paid_amount) }}</td></tr>
                <tr><th class="text-right">Due</th><td class="text-right">{{ currency($returnEntry->grand_total - $returnEntry->paid_amount) }}</td></tr>
            </table>
        </div>
    </div>
    @if($returnEntry->return_note)
        <p><b>Note:</b> {{ $returnEntry->return_note }}</p>
    @endif
</div>
</body>
</html>
