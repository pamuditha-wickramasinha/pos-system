@php
    $quickItemInit = \App\Models\Company::query()->value('item_init') ?? 'IT';
    $quickNextId = (\App\Models\Item::max('id') ?? 0) + 1;
    $quickItemCode = $quickItemInit.str_pad((string) $quickNextId, 4, '0', STR_PAD_LEFT);
@endphp
<div class="modal fade" id="quick_add_item" tabindex="-1">
    <form id="items-form" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="quick_add" value="1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header header-custom">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title text-center">Quick Add New Item</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-solid bg-gray">
                                <div class="box-body">
                                    <div class="row">
                                        <div class="hidden">
                                            <input type="text" class="form-control" id="item_code" name="item_code" value="{{ $quickItemCode }}">
                                            <input type="text" class="form-control" id="item_sing_name" name="item_sing_name" value="">
                                            <input type="text" class="form-control" id="brand_id" name="brand_id" value="0">
                                            <input type="text" class="form-control" id="category_id" name="category_id" value="0">
                                            <input type="text" class="form-control" id="unit_id" name="unit_id" value="0">
                                            <input type="text" class="form-control" id="sku" name="sku" value="">
                                            <input type="text" class="form-control" id="hsn" name="hsn" value="">
                                            <input type="number" class="form-control" id="alert_qty" name="alert_qty" min="0" value="1">
                                            <input type="text" class="form-control no_special_char" id="lot_number" name="lot_number" value="">
                                            <input type="text" class="form-control pull-right datepicker" id="expire_date" name="expire_date" value="">
                                            <textarea class="form-control" id="description" name="description"></textarea>
                                            <select class="form-control" id="tax_id" name="tax_id" style="width: 100%;">
                                                @foreach (\App\Models\Tax::where('status', true)->orderByDesc('undelete_bit')->get() as $t)
                                                    <option data-tax="{{ $t->tax }}" value="{{ $t->id }}">{{ $t->tax_name }}({{ $t->tax }}%)</option>
                                                @endforeach
                                            </select>
                                            <input type="text" class="form-control only_currency" id="purchase_price" name="purchase_price" placeholder="Total Price with Tax Amount" value="1" readonly>
                                            <select class="form-control" id="tax_type" name="tax_type" style="width: 100%;">
                                                <option selected value="Exclusive">Exclusive</option>
                                            </select>
                                            <input type="text" class="form-control only_currency" id="profit_margin" name="profit_margin" placeholder="Profit in %" value="0">
                                            <input type="text" class="form-control only_currency" id="sales_price" name="sales_price" placeholder="Sales Price" value="0">
                                            <input type="text" class="form-control only_currency" id="final_price" name="final_price" placeholder="Final Price" value="0" readonly>
                                            <select class="form-control" id="discount_type" name="discount_type" style="width: 100%;">
                                                <option value="Fixed">Fixed({{ currency() }})</option>
                                            </select>
                                            <input type="text" class="form-control only_currency" id="discount" name="discount" value="0">
                                            <input type="text" class="form-control only_currency" id="wholesale_discount" name="wholesale_discount" value="0">
                                            <input type="text" class="form-control only_currency" id="current_opening_stock" name="current_opening_stock" readonly value="0">
                                            <input type="text" class="form-control" id="new_opening_stock" name="new_opening_stock" placeholder="-/+" value="5">
                                            <textarea class="form-control" id="adjustment_note" name="adjustment_note"></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="custom_barcode">Barcode</label>
                                                <input type="text" class="form-control" id="custom_barcode" name="custom_barcode" value="">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="item_name">Item Name</label>
                                                <input type="text" class="form-control" id="item_name" name="item_name" value="වෙනත්">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="price">Price</label>
                                                <input type="text" class="form-control only_currency" id="price" name="price" value="0">
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-lg" data-dismiss="modal">Close</button>
                    <button type="button" id="quick_add_save" class="btn bg-green btn-lg btn-lg">Set<i class="fa fa-check"></i></button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
$('#price').on('change', function () {
    var price = $('#price').val();
    $('#final_price').val(price);
    $('#sales_price').val(price);
});

$('#quick_add_save').on('click', function (e) {
    var flag = true;

    function check_field(id) {
        if (!$('#' + id).val().toString().trim()) {
            flag = false;
        }
    }

    check_field('item_name');
    check_field('category_id');
    check_field('unit_id');
    check_field('price');
    check_field('alert_qty');
    check_field('tax_id');
    check_field('purchase_price');
    check_field('tax_type');
    check_field('sales_price');

    if (!$.isNumeric($('#alert_qty').val())) {
        toastr['error']('Miminum Quantity must be a number');
        $('#alert_qty').focus();
        return false;
    }

    if (flag == false) {
        toastr['warning']('You have Missed Something to Fillup!');
        return;
    }

    if (confirm('Do You Wants to Save Record ?')) {
        var data = new FormData($('#items-form')[0]);

        var $modal = $('#quick_add_item');
        $('#quick_add_save').attr('disabled', true);
        $.ajax({
            type: 'POST',
            url: $('#base_url').val() + 'items/newitems',
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            success: function (result) {
                if (result == 'success') {
                    var barcode = $('#custom_barcode').val().trim();
                    var item_name = $('#item_name').val().trim();

                    $('#item_search').val('');
                    $('#item_name').val('වෙනත්');
                    $('#custom_barcode').val('');
                    $('#price').val(0);
                    toastr['success']('Record Saved Successfully!');

                    var searchValue = barcode || item_name;
                    if (searchValue) {
                        $('#item_search').val(searchValue);
                        $('#item_search').autocomplete('search');
                    }
                } else if (result == 'failed') {
                    toastr['error']('Sorry! Failed to save Record.Try again!');
                } else {
                    toastr['error'](result);
                }
                $('#quick_add_save').attr('disabled', false);

                $modal.modal('hide');
                setTimeout(function () {
                    $modal.removeClass('in');
                    $modal.css('display', 'none');
                    $('.modal-backdrop').remove();
                }, 300);
            }
        });
    }
    return;
});
</script>
