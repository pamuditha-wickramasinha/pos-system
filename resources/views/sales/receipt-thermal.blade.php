{{--
    Thermal-printer receipt, rendered to a fixed pixel width and screenshotted by a
    headless browser (App\Services\ReceiptImageRenderer) before being sent to the
    printer as an ESC/POS raster image.

    It mirrors sales/print-invoice-pos.blade.php, minus the on-screen chrome (print
    button, auto-print script), and sized in px for the paper rather than for a page.
--}}
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    /* Bundled with the app so receipts do not depend on the OS having a Sinhala font. */
    @font-face {
        font-family: 'ReceiptSinhala';
        src: url('font.ttf') format('truetype');
        font-weight: normal;
    }
    html, body { margin: 0; padding: 0; background: #fff; }
    body {
        width: {{ $widthPx }}px;
        font-family: 'ReceiptSinhala', arial, sans-serif;
        font-size: {{ $base + 5 }}px;
        line-height: 1.45;
        color: #000;
        -webkit-font-smoothing: none;
    }
    .sheet { padding: {{ $pad }}px; }
    .center { text-align: center; }
    .right { text-align: right; }
    .bold { font-weight: bold; }
    .shop { font-size: {{ $base + 40 }}px; font-weight: bold; }
    table { width: 100%; border-collapse: collapse; }
    .meta td { padding: 1px 0; vertical-align: top; }
    .items { margin-top: 8px; }
    .items thead th {
        /* background: #000; */
        color: #000000;
        font-size: {{ $base + 5 }}px;
        font-weight: bold;
        padding: 3px 2px;
        text-align: left;
    }
    .items tbody td {
        padding: 3px 2px;
        border-bottom: 1px dashed #000;
        vertical-align: top;
    }
    .items tfoot td { padding: 2px 2px; }
    .c { text-align: center; }
    .r { text-align: right; }
    .rule { border-top: 1px dashed #000; height: 1px; margin: 6px 0; }
    .profit {
        margin-top: 10px;
        padding: 6px 0;
        border-top: 1px solid #000;
        border-bottom: 1px solid #000;
        text-align: center;
        font-weight: bold;
        font-size: {{ $base + 20 }}px;
    }
    .footer { margin-top: 14px; text-align: center; font-weight: bold; }
</style>
</head>
<body>
<div class="sheet">

    <div class="center bold">
        <div class="shop">{{ $company?->company_name }}</div>
        @if(trim((string) $company?->address) !== '')
            Address: {{ $company->address }}<br>
        @endif
        {{ $company?->city }}@if(trim((string) $company?->postcode) !== '')-{{ $company->postcode }}@endif<br>
        @if(trim((string) $company?->gst_no) !== '')
            GST Number: {{ $company->gst_no }}<br>
        @endif
        @if(trim((string) $company?->vat_no) !== '')
            VAT Number: {{ $company->vat_no }}<br>
        @endif
        @if(trim((string) $company?->mobile) !== '')
            {{ $company->mobile }}{{ $company->phone ? ','.$company->phone : '' }}
        @endif
    </div>

    <div class="rule"></div>

    <table class="meta">
        <tr><td style="width:38%">Invoice</td><td class="bold">#{{ $sale->sales_code }}</td></tr>
        <tr><td>Seller</td><td>{{ ucfirst((string) $sale->created_by) }}</td></tr>
        <tr>
            <td>Date:{{ show_date($sale->sales_date) }}</td>
            <td class="right">Time:{{ show_time($sale->created_at) }}</td>
        </tr>
    </table>

    @php($totalDiscount = (float) $sale->tot_discount_to_all_amt)
    @php($i = 0)

    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>විස්තරය</th>
                <th class="c">ප්‍රමාණය</th>
                <th>සිල්ලර මිල</th>
                <th class="r">අපේ මිල</th>
                <th class="r">වටිනාකම</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $si)
                @php($i++)
                @php($totalDiscount += (float) $si->discount_amt)
                <tr>
                    <td>{{ $i }}</td>
                    <td>{{ $si->item->item_name ?? '' }}</td>
                    <td class="c">{{ $si->sales_qty }}</td>
                    <td>{{ $si->price_per_unit }}</td>
                    <td class="r">{{ number_format($si->price_per_unit - ($si->discount_amt / max((float) $si->sales_qty, 1)), 2, '.', '') }}</td>
                    <td class="r">{{ number_format($si->total_cost, 2, '.', '') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            @php($otherCharges = (float) $sale->other_charges_amt)
            @if($otherCharges > 0)
                <tr>
                    <td colspan="5" class="r">වෙනත් ගාස්තු(රු):</td>
                    <td class="r">{{ number_format($otherCharges, 2, '.', '') }}</td>
                </tr>
            @endif
            @php($invoiceDiscount = (float) $sale->tot_discount_to_all_amt)
            @if($invoiceDiscount > 0)
                <tr>
                    <td colspan="5" class="r">වට්ටම(රු):</td>
                    <td class="r">-{{ number_format($invoiceDiscount, 2, '.', '') }}</td>
                </tr>
            @endif
            <tr>
                <td colspan="5" class="r bold">එකතුව(රු):</td>
                <td class="r bold">{{ $sale->grand_total }}</td>
            </tr>
            <tr><td colspan="6" style="height:15px"></td></tr>
            @if(change_return_status())
                @php($changeReturnAmount = get_change_return_amount($sale->id))
                <tr>
                    <td colspan="5" class="r">ලබාදුන් මුදල(රු):</td>
                    <td class="r">{{ number_format($sale->paid_amount + $changeReturnAmount, 2, '.', '') }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="r">ඉතිරි මුදල(රු):</td>
                    <td class="r">{{ number_format($changeReturnAmount, 2, '.', '') }}</td>
                </tr>
            @else
                <tr>
                    <td colspan="5" class="r">Paid Amount</td>
                    <td class="r">{{ number_format($sale->paid_amount, 2, '.', '') }}</td>
                </tr>
            @endif
        </tfoot>
    </table>

    <br/>    
    <div class="profit">ඔබට ලැබුනු මුළු ලාභය(රු): {{ number_format($totalDiscount, 2) }}</div>
    <br/>     

    @if(! empty($footerText))
        <div class="footer">
            <i>{{ $footerText }}</i>
        </div>
    @endif

</div>
</body>
</html>
