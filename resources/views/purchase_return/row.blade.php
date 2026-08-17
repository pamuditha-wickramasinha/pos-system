@php($qty = $qtyOverride ?? 1)
@php($itemUnitCost = (float) $purchasePrice + (float) $taxAmt)
@php($itemAmount = $itemUnitCost * (float) $qty)
<tr id="row_{{ $rowcount }}" data-row="{{ $rowcount }}">
    <td id="td_{{ $rowcount }}_1">
        <label class="form-control" style="height:auto;">
            <a id="td_data_{{ $rowcount }}_1" href="javascript:void(0)" onclick="show_purchase_item_modal({{ $rowcount }})">{{ $item->item_name }}</a>
        </label>
    </td>
    <td id="td_{{ $rowcount }}_3">
        <div class="input-group">
            <span class="input-group-btn"><button onclick="decrement_qty({{ $rowcount }})" type="button" class="btn btn-default btn-flat"><i class="fa fa-minus text-danger"></i></button></span>
            <input typ="text" value="{{ $qty }}" class="form-control no-padding text-center" onkeyup="calculate_tax({{ $rowcount }})" id="td_data_{{ $rowcount }}_3" name="td_data_{{ $rowcount }}_3">
            <span class="input-group-btn"><button onclick="increment_qty({{ $rowcount }})" type="button" class="btn btn-default btn-flat"><i class="fa fa-plus text-success"></i></button></span>
        </div>
    </td>
    <td id="td_{{ $rowcount }}_4"><input type="text" name="td_data_{{ $rowcount }}_4" id="td_data_{{ $rowcount }}_4" class="form-control text-right no-padding only_currency text-center" onkeyup="calculate_tax({{ $rowcount }})" value="{{ $purchasePrice }}"></td>
    <td id="td_{{ $rowcount }}_8">
        <input type="text" name="td_data_{{ $rowcount }}_8" id="td_data_{{ $rowcount }}_8" class="pointer form-control text-right no-padding only_currency text-center item_discount" value="{{ $discount }}" onclick="show_purchase_item_modal({{ $rowcount }})" readonly>
    </td>
    <td id="td_{{ $rowcount }}_15" class="{{ tax_disable_class() }}">
        <label class="form-control">
            <a id="td_data_{{ $rowcount }}_15" data-toggle="tooltip" title="Click to Change" class="pointer" onclick="show_purchase_item_modal({{ $rowcount }})">{{ $item->tax?->tax_name }}</a>
        </label>
    </td>
    <td id="td_{{ $rowcount }}_5" class="{{ tax_disable_class() }}">
        <input type="text" name="td_data_{{ $rowcount }}_5" id="td_data_{{ $rowcount }}_5" class="form-control text-right no-padding only_currency text-center" readonly value="{{ $taxAmt }}">
    </td>
    <td id="td_{{ $rowcount }}_10"><input type="text" name="td_data_{{ $rowcount }}_10" id="td_data_{{ $rowcount }}_10" class="form-control text-right no-padding only_currency text-center" readonly value="{{ $itemUnitCost }}"></td>
    <td id="td_{{ $rowcount }}_9"><input type="text" name="td_data_{{ $rowcount }}_9" id="td_data_{{ $rowcount }}_9" class="form-control text-right no-padding only_currency text-center" style="border-color: #f39c12;" readonly value="{{ $itemAmount }}"></td>
    <td id="td_{{ $rowcount }}_16" style="text-align: center;">
        <a class=" fa fa-fw fa-minus-square text-red" style="cursor: pointer;font-size: 34px;" onclick="removerow({{ $rowcount }})" title="Delete ?" name="td_data_{{ $rowcount }}_16" id="td_data_{{ $rowcount }}_16"></a>
    </td>
    <input type="hidden" id="tr_available_qty_{{ $rowcount }}_13" value="{{ $itemAvailableQty }}">
    <input type="hidden" id="tr_item_id_{{ $rowcount }}" name="tr_item_id_{{ $rowcount }}" value="{{ $item->id }}">
    <input type="hidden" id="tr_tax_type_{{ $rowcount }}" name="tr_tax_type_{{ $rowcount }}" value="{{ $item->tax_type }}">
    <input type="hidden" id="tr_tax_id_{{ $rowcount }}" name="tr_tax_id_{{ $rowcount }}" value="{{ $item->tax_id }}">
    <input type="hidden" id="tr_tax_value_{{ $rowcount }}" name="tr_tax_value_{{ $rowcount }}" value="{{ $item->tax?->tax }}">
    <input type="hidden" id="description_{{ $rowcount }}" name="description_{{ $rowcount }}" value="{{ $description }}">
    <input type="hidden" id="item_discount_type_{{ $rowcount }}" name="item_discount_type_{{ $rowcount }}" value="{{ $discountType }}">
    <input type="hidden" id="item_discount_input_{{ $rowcount }}" name="item_discount_input_{{ $rowcount }}" value="{{ $discountInput }}">
</tr>
