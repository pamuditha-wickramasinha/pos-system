<div class="sales_item_modal">
    <div class="modal fade in" id="sales_item" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header header-custom">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title text-center">Manage Sales Item</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row invoice-info">
                                <div class="col-sm-6 invoice-col"><b>Item Name : </b> <span id="popup_item_name"></span></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="col-md-12">
                                <div class="box box-solid bg-gray">
                                    <div class="box-body">
                                        <div class="row">
                                            <div style="display:none" class="col-md-6 {{ tax_disable_class() }}">
                                                <div class="form-group">
                                                    <label for="popup_tax_type">Tax Type</label>
                                                    <select class="form-control" id="popup_tax_type" name="popup_tax_type" style="width: 100%;">
                                                        <option value="Exclusive">Exclusive</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div style="display:none" class="col-md-6 {{ tax_disable_class() }}">
                                                <div class="form-group">
                                                    <label for="popup_tax_id">Tax</label>
                                                    <select class="form-control" id="popup_tax_id" name="popup_tax_id" style="width: 100%;">
                                                        @foreach (\App\Models\Tax::where('status', true)->get() as $t)
                                                            <option data-tax="{{ $t->tax }}" data-tax-value="{{ $t->tax_name }}" value="{{ $t->id }}">{{ $t->tax_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="item_discount_type">Discount Type</label>
                                                    <select class="form-control" id="item_discount_type" name="item_discount_type" style="width: 100%;">
                                                        <option value="Fixed">Fixed({{ currency() }})</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="item_discount_input">Discount</label>
                                                    <input type="text" class="form-control only_currency" id="item_discount_input" name="item_discount_input" value="0">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="popup_description">Description</label>
                                                    <textarea class="form-control" id="popup_description"></textarea>
                                                </div>
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="popup_row_id">
                    <button type="button" class="btn btn-default btn-lg" data-dismiss="modal">Close</button>
                    <button type="button" onclick="set_info()" class="btn bg-green btn-lg place_order btn-lg">Set<i class="fa  fa-check "></i></button>
                </div>
            </div>
        </div>
    </div>
</div>
