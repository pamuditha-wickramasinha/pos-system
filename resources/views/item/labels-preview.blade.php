@php($width = $doubleColumn ? '3.6375in' : '2.5in')
<div style="height:{{ $doubleColumn ? '11in' : '5in' }} !important; width:8.5in !important; line-height: 16px !important;">
    <div class="inner-div-2" style="height:11in !important; width:8.5in !important; line-height: 16px !important;">
        <div>
            @foreach ($rows as $row)
                @php($item = $row['item'])
                @php($code = $item->custom_barcode ?: $item->item_code)
                @for ($j = 1; $j <= $row['count']; $j++)
                    <div style="height:1in !important; line-height: 1in; width:{{ $width }} !important; display: inline-block; text-align: center; {{ ! $doubleColumn ? 'page-break-after: always;' : '' }}" class="label_border">
                        <div style="display:inline-block;vertical-align:middle;line-height:16px !important;">
                            <b style="display: block !important" class="text-uppercase">{{ $company_name }}</b>
                            <span style="display: block !important">{{ $item->item_name }}</span>
                            <b>Price:</b>
                            <span>{{ currency($item->sales_price) }}</span>
                            <img class="center-block" style="max-height: 0.35in !important; width: 100%; opacity: 1.0" src="{{ url('barcode/'.$code.'/'.rand()) }}">
                        </div>
                    </div>
                    <br>
                @endfor
            @endforeach
        </div>
    </div>
</div>
