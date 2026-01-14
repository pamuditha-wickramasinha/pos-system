<?php
 $qs5="select item_init from db_company";
 $q5=$this->db->query($qs5);
 $item_init=$q5->row()->item_init;
 
 $this->db->query("ALTER TABLE db_items AUTO_INCREMENT = 1");
 $qs4="select coalesce(max(id),0)+1 as maxid from db_items";
 $q1=$this->db->query($qs4);
 $maxid=$q1->row()->maxid;
 $item_code=$item_init.str_pad($maxid, 4, '0', STR_PAD_LEFT);

 $item_sing_name="";
 $brand_id=0;
 $category_id=0;
 $unit_id=0;
 $sku="";
 $hsn="";
 $alert_qty=1;
 $lot_number="";
 $expire_date="";
 $description="";
 $purchase_price=1;
 $profit_margin=0;
 $sales_price=0;
 $final_price=0;
 $discount=0;
 $wholesale_discount=0;
 $stock=0;
 $new_opening_stock=5;
 $adjustment_note="";
 $custom_barcode="";
 $item_name="වෙනත්";
 $custom_barcode="";
?>
<?= form_open('#', array('class' => 'form', 'id' => 'items-form', 'enctype'=>'multipart/form-data', 'method'=>'POST'));?>
<div class="sales_quick_add_item_modal" id="sales_quick_add_item_modal">
    <div class="modal fade in" id="quick_add_item" tabindex='-1'>
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header header-custom">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title text-center">Quick Add New Item</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-12 ">
                                <div class="box box-solid bg-gray">
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="hidden">
                                                <input type=" text" class="form-control" id="item_code" name="item_code"
                                                    placeholder="" value="<?php print $item_code; ?>">

                                                <input type="text" class="form-control" id="item_sing_name"
                                                    name="item_sing_name" placeholder=""
                                                    value="<?php print $item_sing_name; ?>">

                                                <input type="text" class="form-control" id="brand_id" name="brand_id"
                                                    placeholder="" value="<?php print $brand_id; ?>">

                                                <input type="text" class="form-control" id="category_id"
                                                    name="category_id" placeholder=""
                                                    value="<?php print $category_id; ?>">

                                                <input type="text" class="form-control" id="unit_id" name="unit_id"
                                                    placeholder="" value="<?php print $unit_id; ?>">

                                                <input type="text" class="form-control" id="sku" name="sku"
                                                    placeholder="" value="<?php print $sku; ?>">

                                                <input type="text" class="form-control" id="hsn" name="hsn"
                                                    placeholder="" value="<?php print $hsn; ?>">

                                                <input type="number" class="form-control" id="alert_qty"
                                                    name="alert_qty" placeholder="" min="0"
                                                    value="<?php print $alert_qty; ?>">

                                                <input type="text" class="form-control no_special_char" id="lot_number"
                                                    name="lot_number" placeholder=""
                                                    value="<?php print $lot_number; ?>">

                                                <input type="text" class="form-control pull-right datepicker"
                                                    id="expire_date" name="expire_date" value="<?= $expire_date;?>">

                                                <textarea type="text" class="form-control" id="description"
                                                    name="description"
                                                    placeholder=""><?php print $description; ?></textarea>

                                                <select class="form-control" id="tax_id" name="tax_id"
                                                    style="width: 100%;">
                                                    <?php
                                       $query1="SELECT * FROM db_tax WHERE STATUS=1 ORDER BY undelete_bit DESC";
                                       $q1=$this->db->query($query1);
                                       if($q1->num_rows($q1)>0)
                                        {
                                            //echo '<option data-tax="0" value="">-Select-</option>'; 
                                            foreach($q1->result() as $res1)
                                          { 
                                            $selected = ($tax_id==$res1->id)? 'selected' : '';
                                            echo "<option $selected data-tax='".$res1->tax."' value='".$res1->id."'>".$res1->tax_name."(".$res1->tax."%)</option>";
                                          }
                                        }
                                        else
                                        {
                                           ?>
                                                    <option value="">No Records Found</option>
                                                    <?php
                                       }
                                       ?>
                                                </select>

                                                <input type="text" class="form-control only_currency"
                                                    id="purchase_price" name="purchase_price"
                                                    placeholder="Total Price with Tax Amount"
                                                    value="<?php print $purchase_price; ?>" readonly=''>

                                                <select class="form-control" id="tax_type" name="tax_type"
                                                    style="width: 100%;">
                                                    <?php 
                                    $inclusive_selected=$exclusive_selected='';
                                    if($tax_type =='Inclusive') { $inclusive_selected='selected'; }
                                    if($tax_type =='Exclusive') { $exclusive_selected='selected'; }

                                  ?>
                                                    <option <?= $exclusive_selected ?> value="Exclusive">Exclusive
                                                    </option>
                                                    <!-- <option <?= $inclusive_selected ?> value="Inclusive">Inclusive</option> -->
                                                </select>

                                                <input type="text" class="form-control only_currency" id="profit_margin"
                                                    name="profit_margin" placeholder="Profit in %"
                                                    value="<?php print $profit_margin; ?>">

                                                <input type="text" class="form-control only_currency " id="sales_price"
                                                    name="sales_price" placeholder="Sales Price"
                                                    value="<?php print $sales_price; ?>">

                                                <input type="text" class="form-control only_currency " id="final_price"
                                                    name="final_price" placeholder="Final Price"
                                                    value="<?php print $price; ?>" readonly>

                                                <select class="form-control" id="discount_type" name="discount_type"
                                                    style="width: 100%;">
                                                    <!-- <option value='Percentage'>Percentage(%)</option> -->
                                                    <option value='Fixed'>Fixed(<?= $CI->currency() ?>)</option>
                                                </select>

                                                <input type="text" class="form-control only_currency" id="discount"
                                                    name="discount" value="<?php print $discount; ?>">

                                                <input type="text" class="form-control only_currency"
                                                    id="wholesale_discount" name="wholesale_discount"
                                                    value="<?php print $wholesale_discount; ?>">

                                                <input type="text" class="form-control only_currency"
                                                    id="current_opening_stock" name="current_opening_stock"
                                                    placeholder="" readonly="" value="<?php print $stock; ?>">

                                                <input type="text" class="form-control" id="new_opening_stock"
                                                    name="new_opening_stock" placeholder="-/+"
                                                    value="<?php print $new_opening_stock; ?>">

                                                <textarea type="text" class="form-control" id="adjustment_note"
                                                    name="adjustment_note"
                                                    placeholder=""><?php print $adjustment_note; ?></textarea>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label
                                                        for="custom_barcode"><?= $this->lang->line('barcode'); ?></label>
                                                    <input type="text" class="form-control" id="custom_barcode"
                                                        name="custom_barcode" placeholder=""
                                                        value="<?php print $custom_barcode; ?>">

                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label
                                                        for="item_name"><?= $this->lang->line('item_name'); ?></label>
                                                    <input type="text" class="form-control" id="item_name"
                                                        name="item_name" placeholder=""
                                                        value="<?php print $item_name; ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="price"><?= $this->lang->line('price'); ?></label>
                                                    <input type="text" class="form-control only_currency" id="price"
                                                        name="price" placeholder="" value="0"
                                                        value="<?php print $price; ?>">
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
                    <input type="hidden" id="telegram_bot_token" value="8363259721:AAEibJqJ21ju0FLsoP9UsxXlJ-XQfYfUmrU">
                    <input type="hidden" id="telegram_chat_id" value="-1003507505038">
                    <button type="button" class="btn btn-default btn-lg" data-dismiss="modal">Close</button>
                    <button type="button" id="save" class="btn bg-green btn-lg place_order btn-lg">Set<i
                            class="fa  fa-check "></i></button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
</div>
<?= form_close(); ?>