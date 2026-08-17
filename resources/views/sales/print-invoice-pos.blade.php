<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>{{ $page_title }}</title>
    <link rel="stylesheet" href="{{ $theme_link }}bootstrap/css/bootstrap.min.css">
    <style type="text/css">
    body {
        font-family: arial;
        font-size: 12px;
        padding-top: 15px;
    }

    @media print {
        .no-print {
            display: none;
        }

        @page {
            margin: 0.5in;
        }
    }
    </style>
    <script>
    function printAndClose() {
        window.print();
        setTimeout(function() {
            window.close();
        }, 1000);
    }

    function printOnly() {
        window.print();
    }
    window.onload = function() {
        window.print();
        setTimeout(function() {
            window.close();
        }, 1500);
    };
    </script>
</head>

<body>
    @php($company = \App\Models\Company::where('id', 1)->where('status', true)->first())
    @php($footerText = \App\Models\SiteSetting::query()->value('sales_invoice_footer_text'))
    @php($customer = $sale->customer)
    @php($customerCountry = optional(\App\Models\Country::find($customer?->country_id))->country)
    @php($customerState = optional(\App\Models\State::find($customer?->state_id))->state)
    @php($totalDiscount = (float) $sale->tot_discount_to_all_amt)
    @php($i = 0)
    <table width="98%" align="center">
        <tr>
            <td align="center">
                <span>
                    <strong style="font-size: 25px;">{{ $company?->company_name }}</strong><br>
                    @if(trim((string) $company?->address) !== '')
                    Address: {{ $company->address }}<br>
                    @endif
                    {{ $company?->city }}
                    @if(trim((string) $company?->postcode) !== '')
                    -{{ $company->postcode }}
                    @endif
                    <br>
                    @if(trim((string) $company?->gst_no) !== '')
                    GST Number: {{ $company->gst_no }}<br>
                    @endif
                    @if(trim((string) $company?->vat_no) !== '')
                    VAT Number: {{ $company->vat_no }}<br>
                    @endif
                    @if(trim((string) $company?->mobile) !== '')
                    {{ $company->mobile }}{{ $company->phone ? ','.$company->phone : '' }}<br>
                    @endif
                </span>
            </td>
        </tr>
        <tr style="height: 15px;"></tr>
        <tr>
            <td>
                <table width="100%">
                    <tr>
                        <td width="40%">Invoice</td>
                        <td><b>#{{ $sale->sales_code }}</b></td>
                    </tr>
                    <tr>
                        <td>Seller</td>
                        <td>{{ ucfirst((string) $sale->created_by) }}</td>
                    </tr>
                    <tr>
                        <td>Date:{{ show_date($sale->sales_date) }}</td>
                        <td style="text-align: right;">Time:{{ show_time($sale->created_at) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <table width="100%" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr
                            style="border-top-style: dashed;border-bottom-style: dashed;border-width: 0.1px; background-color:rgb(0, 0, 0);">
                            <th
                                style="font-size: 12px; text-align: left;padding-left: 2px; padding-right: 2px; color:white; font-weight: bold;">
                                #</th>
                            <th
                                style="font-size: 12px; text-align: left;padding-left: 2px; padding-right: 2px; color:white; font-weight: bold;">
                                විස්තරය</th>
                            <th
                                style="font-size: 12px; text-align: center;padding-left: 2px; padding-right: 2px; color:white; font-weight: bold;">
                                ප්‍රමාණය</th>
                            <th
                                style="font-size: 12px; text-align: left;padding-left: 2px; padding-right: 2px; color:white; font-weight: bold;">
                                සිල්ලර මිල</th>
                            <th
                                style="font-size: 12px; text-align: right;padding-left: 2px; padding-right: 2px; color:white; font-weight: bold;">
                                අපේ මිල</th>
                            <th
                                style="font-size: 12px; text-align: right;padding-left: 2px; padding-right: 2px; color:white; font-weight: bold;">
                                වටිනාකම</th>
                        </tr>
                    </thead>
                    <tbody style="border-bottom-style: dashed;border-width: 0.1px;">
                        @foreach ($sale->items as $si)
                        @php($i++)
                        @php($totalDiscount += (float) $si->discount_amt)
                        <tr>
                            <td style="padding-left: 2px; padding-right: 2px;" valign="top">{{ $i }}</td>
                            <td style="padding-left: 2px; padding-right: 2px;">{{ $si->item->item_name ?? '' }}</td>
                            <td style="text-align: center;padding-left: 2px; padding-right: 2px;">{{ $si->sales_qty }}
                            </td>
                            <td style="padding-left: 2px; padding-right: 2px;">{{ $si->price_per_unit }}</td>
                            <td style="text-align: right;padding-left: 2px; padding-right: 2px;">
                                {{ number_format($si->price_per_unit - ($si->discount_amt / max((float) $si->sales_qty, 1)), 2, '.', '') }}
                            </td>
                            <td style="text-align: right;padding-left: 2px; padding-right: 2px;">
                                {{ number_format($si->total_cost, 2, '.', '') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @php($otherCharges = (float) $sale->other_charges_amt)
                        @if($otherCharges > 0)
                        <tr>
                            <td style="padding-left: 2px; padding-right: 2px;" colspan="5" align="right">වෙනත් ගාස්තු(රු):</td>
                            <td style="padding-left: 2px; padding-right: 2px;" align="right">
                                {{ number_format($otherCharges, 2, '.', '') }}</td>
                        </tr>
                        @endif
                        @php($invoiceDiscount = (float) $sale->tot_discount_to_all_amt)
                        @if($invoiceDiscount > 0)
                        <tr>
                            <td style="padding-left: 2px; padding-right: 2px;" colspan="5" align="right">වට්ටම(රු):</td>
                            <td style="padding-left: 2px; padding-right: 2px;" align="right">
                                -{{ number_format($invoiceDiscount, 2, '.', '') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td style="padding-left: 2px; padding-right: 2px;font-weight: bold;" colspan="5"
                                align="right">එකතුව(රු):</td>
                            <td style="padding-left: 2px; padding-right: 2px;font-weight: bold;" align="right">
                                {{ $sale->grand_total }}</td>
                        </tr>
                        <tr style="height: 5px;"></tr>
                        @if(change_return_status())
                        @php($changeReturnAmount = get_change_return_amount($sale->id))
                        <tr>
                            <td style="padding-left: 2px; padding-right: 2px;" colspan="5" align="right">ලබාදුන්
                                මුදල(රු):</td>
                            <td style="padding-left: 2px; padding-right: 2px;" align="right">
                                {{ number_format($sale->paid_amount + $changeReturnAmount, 2, '.', '') }}</td>
                        </tr>
                        <tr>
                            <td style="padding-left: 2px; padding-right: 2px;" colspan="5" align="right">ඉතිරි මුදල(රු):
                            </td>
                            <td style="padding-left: 2px; padding-right: 2px;" align="right">
                                {{ number_format($changeReturnAmount, 2, '.', '') }}</td>
                        </tr>
                        @else
                        <tr>
                            <td style="padding-left: 2px; padding-right: 2px;" colspan="5" align="right">Paid Amount
                            </td>
                            <td style="padding-left: 2px; padding-right: 2px;" align="right">
                                {{ number_format($sale->paid_amount, 2, '.', '') }}</td>
                        </tr>
                        @endif
                        <tr style="height: 15px;"></tr>
                    </tfoot>
                </table>
            </td>
        </tr>
    </table>

    <div style="text-align:center; border-top: 1px solid black; border-bottom: 1px solid black;">
        <span style="font-weight: bold; font-size: 17px">ඔබට ලැබුනු මුළු ලාභය(රු):</span>
        <span style="font-weight: bold; font-size: 16px">{{ number_format($totalDiscount, 2) }}</span>
    </div>
    @if(! empty($footerText))
    <div style="text-align:center">
        <br><br>
        <b>-----------------------------------------</b>
        <br>
        <b><i>{{ $footerText }}</i></b>
    </div>
    @endif

    <center>
        <div class="row no-print">
            <div class="col-md-12">
                <div class="col-md-2 col-md-offset-5 col-xs-4 col-xs-offset-4 form-group">
                    <button type="button" class="btn btn-block btn-success btn-xs" onclick="printOnly();"
                        title="Print">Print</button>
                    @if(request()->filled('redirect'))
                    <a href="{{ url(request()->query('redirect')) }}"><button type="button"
                            class="btn btn-block btn-danger btn-xs" title="Back">Back</button></a>
                    @endif
                </div>
            </div>
        </div>
    </center>
</body>

</html>