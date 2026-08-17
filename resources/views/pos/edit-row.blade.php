@php($stock = ($saleItem->item->stock ?? 0) + $saleItem->sales_qty)
@php($tax = $saleItem->tax)
<tr id="row_{{ $rowcount }}" data-row="0" data-item-id="{{ $saleItem->item_id }}">
    <td id="td_{{ $rowcount }}_0">
        <a data-toggle="tooltip" title="Click to Change Tax" class="pointer" id="td_data_{{ $rowcount }}_0" onclick="show_sales_item_modal({{ $rowcount }})">{{ $saleItem->item->item_name ?? '' }}<i class="fa fa-edit pointer"></i></a>
    </td>
    <td id="td_{{ $rowcount }}_1">{{ $stock }}</td>
    <td id="td_{{ $rowcount }}_2">
        <div class="input-group input-group-sm">
            <span class="input-group-btn"><button onclick="decrement_qty({{ $saleItem->item_id }},{{ $rowcount }})" type="button" class="btn btn-default btn-flat"><i class="fa fa-minus text-danger"></i></button></span>
            <input type="text" inputmode="decimal" value="{{ $saleItem->sales_qty }}" class="form-control text-center" onkeyup="item_qty_input({{ $saleItem->item_id }},{{ $rowcount }})" id="item_qty_{{ $saleItem->item_id }}" name="item_qty_{{ $saleItem->item_id }}">
            <span class="input-group-btn"><button onclick="increment_qty({{ $saleItem->item_id }},{{ $rowcount }})" type="button" class="btn btn-default btn-flat"><i class="fa fa-plus text-success"></i></button></span>
        </div>
    </td>
    <td id="td_{{ $rowcount }}_3" class="text-right">
        <input id="sales_price_{{ $rowcount }}" onblur="set_to_original({{ $rowcount }},{{ $saleItem->purchase_price ?? 0 }})" onkeyup="update_price({{ $rowcount }},{{ $saleItem->purchase_price ?? 0 }})" name="sales_price_{{ $rowcount }}" type="text" inputmode="decimal" class="form-control no-padding" value="{{ number_format($saleItem->price_per_unit, 2, '.', '') }}">
    </td>
    <td id="td_{{ $rowcount }}_6" class="text-right">
        <input data-toggle="tooltip" title="Click to Change" onclick="show_sales_item_modal({{ $rowcount }})" id="item_discount_{{ $rowcount }}" readonly name="item_discount_{{ $rowcount }}" type="text" class="form-control no-padding" value="{{ $saleItem->discount_amt }}">
    </td>
    <td id="td_{{ $rowcount }}_11" class="{{ tax_disable_class() }}">
        <input data-toggle="tooltip" title="Click to Change" id="td_data_{{ $rowcount }}_11" onclick="show_sales_item_modal({{ $rowcount }})" name="td_data_{{ $rowcount }}_11" type="text" class="form-control no-padding pointer" readonly value="{{ $saleItem->tax_amt }}">
    </td>
    <td id="td_{{ $rowcount }}_4" class="text-right">
        <input data-toggle="tooltip" title="Total" id="td_data_{{ $rowcount }}_4" name="td_data_{{ $rowcount }}_4" type="text" class="form-control no-padding pointer" readonly value="{{ number_format($saleItem->total_cost, 2, '.', '') }}">
    </td>
    <td id="td_{{ $rowcount }}_5">
        <a class=" fa fa-fw fa-trash-o text-red" style="cursor: pointer;font-size: 20px;" onclick="removerow({{ $rowcount }})" title="Delete Item?"></a>
    </td>
    <input type="hidden" name="tr_item_id_{{ $rowcount }}" id="tr_item_id_{{ $rowcount }}" value="{{ $saleItem->item_id }}">
    <input type="hidden" id="tr_item_per_{{ $rowcount }}" name="tr_item_per_{{ $rowcount }}" value="{{ $tax->tax ?? '' }}">
    <input type="hidden" id="tr_sales_price_temp_{{ $rowcount }}" name="tr_sales_price_temp_{{ $rowcount }}" value="{{ number_format($saleItem->price_per_unit, 2, '.', '') }}">
</tr>
<input type="hidden" id="tr_tax_type_{{ $rowcount }}" name="tr_tax_type_{{ $rowcount }}" value="{{ $saleItem->tax_type }}">
<input type="hidden" id="tr_tax_id_{{ $rowcount }}" name="tr_tax_id_{{ $rowcount }}" value="{{ $saleItem->tax_id }}">
<input type="hidden" id="tr_tax_value_{{ $rowcount }}" name="tr_tax_value_{{ $rowcount }}" value="{{ $tax->tax ?? '' }}">
<input type="hidden" id="description_{{ $rowcount }}" name="description_{{ $rowcount }}" value="{{ $saleItem->description }}">
<input type="hidden" id="item_discount_type_{{ $rowcount }}" name="item_discount_type_{{ $rowcount }}" value="{{ $saleItem->discount_type }}">
<input type="hidden" id="item_discount_input_{{ $rowcount }}" name="item_discount_input_{{ $rowcount }}" value="{{ $saleItem->discount_input }}">
<input type="hidden" id="purchase_price_{{ $rowcount }}" name="purchase_price_{{ $rowcount }}" value="{{ $saleItem->purchase_price ?? 0 }}">
