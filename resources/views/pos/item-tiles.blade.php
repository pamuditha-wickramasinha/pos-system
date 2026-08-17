@foreach ($items as $item)
    @php($salesReal = $item->sales_price - $item->discount)
    @php($wholesaleReal = $item->sales_price - $item->wholesale_discount)
    @php($taxAmt = $item->tax_type === 'Inclusive' ? calculate_inclusive($item->sales_price, $item->tax?->tax ?? 0) : calculate_exclusive($item->sales_price, $item->tax?->tax ?? 0))
    <div class="col-md-3 col-xs-6" id="item_parent_{{ $loop->index }}" data-toggle="tooltip" title="{{ $item->item_name }}" style="padding-left:5px;padding-right:5px;">
        <div class="box box-default item_box" id="div_{{ $item->id }}" onclick="addrow({{ $item->id }})"
             data-item-id="{{ $item->id }}"
             data-item-name="{{ $item->item_name }}"
             data-item-available-qty="{{ $item->stock }}"
             data-item-sales-price="{{ $item->sales_price }}"
             data-item-cost="{{ $item->price }}"
             data-item-tax-id="{{ $item->tax_id }}"
             data-item-tax-type="{{ $item->tax_type }}"
             data-item-tax-value="{{ $item->tax?->tax }}"
             data-item-tax-name="{{ $item->tax?->tax_name }}"
             data-item-tax-amt="{{ $taxAmt }}"
             data-purchase_price="{{ $item->price }}"
             data-discount_type="{{ $item->discount_type }}"
             data-discount="{{ $item->discount }}"
             data-wholesale_discount="{{ $item->wholesale_discount }}"
             style="max-height: 120px;min-height: 120px;cursor: pointer;{{ $item->stock <= 0 ? 'background-color:#c8c8c8' : 'background-color:#a1db75' }}">
            <span class="label label-danger push-right" style="font-weight: bold;font-family: sans-serif;" data-toggle="tooltip" title="{{ $item->stock }} Quantity in Stock">Qty: {{ $item->stock }}</span>
            <div class="box-body box-profile">
                <label class="text-center search_item" style="font-weight: bold;font-family: sans-serif;" id="item_{{ $loop->index }}">{{ Str::limit($item->item_name, 25, '') }}</label><br>
                <span style="font-family: sans-serif;font-size:100%;">{{ currency(app_number_format($salesReal)) }}</span>
                <p style="font-family: sans-serif; font-size:80%">{{ currency(app_number_format($wholesaleReal)) }}</p>
            </div>
        </div>
    </div>
@endforeach
