@php($itemAmount = ((float) $salesPrice * (float) $qty) + (float) $taxAmt)
<tr id="row_{{ $rowcount }}" data-row="{{ $rowcount }}">
    <td id="td_{{ $rowcount }}_1">
        <label class="form-control" style="height:auto;" data-toggle="tooltip" title="Edit ?">
            <a id="td_data_{{ $rowcount }}_1" href="javascript:void(0)" onclick="show_sales_item_modal({{ $rowcount }})">{{ $item->item_name }}</a>
            <i onclick="show_sales_item_modal({{ $rowcount }})" class="fa fa-edit pointer"></i>
        </label>
    </td>
    <td id="td_{{ $rowcount }}_3">
        <div class="input-group ">
            <span class="input-group-btn"><button onclick="decrement_qty({{ $rowcount }})" type="button" class="btn btn-default btn-flat"><i class="fa fa-minus text-danger"></i></button></span>
            <input type="text" value="{{ $qty }}" class="form-control no-padding text-center" onchange="item_qty_input({{ $rowcount }})" id="td_data_{{ $rowcount }}_3" name="td_data_{{ $rowcount }}_3">
            <span class="input-group-btn"><button onclick="increment_qty({{ $rowcount }})" type="button" class="btn btn-default btn-flat"><i class="fa fa-plus text-success"></i></button></span>
        </div>
    </td>
    <td id="td_{{ $rowcount }}_10"><input type="text" name="td_data_{{ $rowcount }}_10" id="td_data_{{ $rowcount }}_10" class="form-control text-right no-padding only_currency text-center" onkeyup="calculate_tax({{ $rowcount }})" value="{{ $salesPrice }}"></td>
    <td id="td_{{ $rowcount }}_8">
        <input type="text" data-toggle="tooltip" title="Click to Change" onclick="show_sales_item_modal({{ $rowcount }})" name="td_data_{{ $rowcount }}_8" id="td_data_{{ $rowcount }}_8" class="pointer form-control text-right no-padding only_currency text-center item_discount" value="{{ $discount }}" onkeyup="calculate_tax({{ $rowcount }})" readonly>
    </td>
    <td id="td_{{ $rowcount }}_11" class="{{ tax_disable_class() }}">
        <input type="text" name="td_data_{{ $rowcount }}_11" id="td_data_{{ $rowcount }}_11" class="form-control text-right no-padding only_currency text-center" value="{{ $taxAmt }}" readonly>
    </td>
    <td id="td_{{ $rowcount }}_12" class="{{ tax_disable_class() }}">
        <label class="form-control" style="width:100%;padding-left:0px;padding-right:0px;">
            <a id="td_data_{{ $rowcount }}_12" href="javascript:void(0)" data-toggle="tooltip" title="Click to Change" onclick="show_sales_item_modal({{ $rowcount }})">{{ $tax->tax_name ?? '' }}</a>
        </label>
    </td>
    <td id="td_{{ $rowcount }}_9"><input type="text" name="td_data_{{ $rowcount }}_9" id="td_data_{{ $rowcount }}_9" class="form-control text-right no-padding only_currency text-center" style="border-color: #f39c12;" readonly value="{{ $itemAmount }}"></td>
    <td id="td_{{ $rowcount }}_16" style="text-align: center;">
        <a class=" fa fa-fw fa-minus-square text-red" style="cursor: pointer;font-size: 34px;" onclick="removerow({{ $rowcount }})" title="Delete ?" name="td_data_{{ $rowcount }}_16" id="td_data_{{ $rowcount }}_16"></a>
    </td>
    <input type="hidden" id="td_data_{{ $rowcount }}_4" name="td_data_{{ $rowcount }}_4" value="{{ $salesPrice }}">
    <input type="hidden" id="td_data_{{ $rowcount }}_15" name="td_data_{{ $rowcount }}_15" value="{{ $item->tax_id }}">
    <input type="hidden" id="td_data_{{ $rowcount }}_5" name="td_data_{{ $rowcount }}_5" value="{{ $taxAmt }}">
    <input type="hidden" id="tr_available_qty_{{ $rowcount }}_13" value="{{ $item->stock }}">
    <input type="hidden" id="tr_item_id_{{ $rowcount }}" name="tr_item_id_{{ $rowcount }}" value="{{ $item->id }}">
    <input type="hidden" id="tr_tax_type_{{ $rowcount }}" name="tr_tax_type_{{ $rowcount }}" value="{{ $item->tax_type }}">
    <input type="hidden" id="tr_tax_id_{{ $rowcount }}" name="tr_tax_id_{{ $rowcount }}" value="{{ $item->tax_id }}">
    <input type="hidden" id="tr_tax_value_{{ $rowcount }}" name="tr_tax_value_{{ $rowcount }}" value="{{ $tax->tax ?? '' }}">
    <input type="hidden" id="description_{{ $rowcount }}" name="description_{{ $rowcount }}" value="{{ $description }}">
    <input type="hidden" id="item_discount_type_{{ $rowcount }}" name="item_discount_type_{{ $rowcount }}" value="{{ $discountType }}">
    <input type="hidden" id="item_discount_input_{{ $rowcount }}" name="item_discount_input_{{ $rowcount }}" value="{{ $discountInput }}">
    <input type="hidden" id="pur_price_{{ $rowcount }}" name="pur_price_{{ $rowcount }}" value="{{ $purchasePrice }}">
</tr>
