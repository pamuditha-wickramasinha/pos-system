<tr id="row_{{ $rowcount }}" data-row="{{ $rowcount }}">
    <td id="td_{{ $rowcount }}_1">
        <input type="text" style="font-weight: bold;" id="td_data_{{ $rowcount }}_1" class="form-control no-padding" value="{{ $item->item_name }}" readonly>
    </td>
    <td id="td_{{ $rowcount }}_3">
        <div class="input-group ">
            <span class="input-group-btn">
                <button onclick="decrement_qty({{ $rowcount }})" type="button" class="btn btn-default btn-flat"><i class="fa fa-minus text-danger"></i></button></span>
            <input type="text" value="{{ $qty ?? 1 }}" class="form-control no-padding text-center" onkeyup="calculate_tax({{ $rowcount }})" id="td_data_{{ $rowcount }}_3" name="td_data_{{ $rowcount }}_3">
            <span class="input-group-btn">
                <button onclick="increment_qty({{ $rowcount }})" type="button" class="btn btn-default btn-flat"><i class="fa fa-plus text-success"></i></button></span>
        </div>
    </td>
    <td id="td_{{ $rowcount }}_16" style="text-align: center;">
        <a class=" fa fa-fw fa-minus-square text-red" style="cursor: pointer;font-size: 34px;" onclick="removerow({{ $rowcount }})" title="Delete ?" name="td_data_{{ $rowcount }}_16" id="td_data_{{ $rowcount }}_16"></a>
    </td>
    <input type="hidden" id="tr_available_qty_{{ $rowcount }}_13" value="{{ $item->stock }}">
    <input type="hidden" id="tr_item_id_{{ $rowcount }}" name="tr_item_id_{{ $rowcount }}" value="{{ $item->id }}">
</tr>
