@extends('layouts.app')
@php($activeMenu = 'purchase-returns')
@php($supplierId = $returnEntry?->supplier_id ?? $purchase?->supplier_id)
@php($supplierName = $supplierId ? \App\Models\Supplier::find($supplierId)?->supplier_name : null)

@section('content')
<section class="content-header">
    <h1>{{ $page_title }} <small>{{ $subtitle }}</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{ route('purchase_return.index') }}">Purchase Return List</a></li>
        <li><a href="{{ route('purchase_return.create') }}">New Purchase</a></li>
        <li class="active">{{ $page_title }}</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        @include('partials.flashdata')
        <div class="col-md-12">
            <div class="box box-info">
                <form class="form-horizontal" id="purchase-form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
                    <input type="hidden" value="1" id="hidden_rowcount" name="hidden_rowcount">
                    <input type="hidden" value="0" id="hidden_update_rowid" name="hidden_update_rowid">

                    <div class="box-body">
                        <div class="form-group">
                            @if($purchase)
                                <label class="col-sm-2 control-label">Purchase Code<label class="text-danger">*</label></label>
                                <label class="col-sm-3 control-label" style="text-align: left;">{{ $purchase->purchase_code }}</label>
                            @endif
                            @if($supplierName)
                                <label class="col-sm-2 control-label">Supplier Name<label class="text-danger">*</label></label>
                                <label class="col-sm-3 control-label" style="text-align: left;">{{ $supplierName }}</label>
                                <input type="hidden" name="supplier_id" id="supplier_id" value="{{ $supplierId }}">
                            @endif
                        </div>
                        <div class="form-group">
                            @if($returnEntry)
                                <label class="col-sm-2 control-label">Invoice<label class="text-danger">*</label></label>
                                <label class="col-sm-3 control-label" style="text-align: left;">#{{ $returnEntry->return_code }}</label>
                            @endif
                            @if(! $supplierId)
                                <label for="supplier_id" class="col-sm-2 control-label">Supplier Name<label class="text-danger">*</label></label>
                                <div class="col-sm-3">
                                    <div class="input-group">
                                        <select class="form-control select2" id="supplier_id" name="supplier_id" style="width: 100%;"></select>
                                        <span class="input-group-addon pointer" data-toggle="modal" data-target="#supplier-modal" title="New Supplier?"><i class="fa fa-user-plus text-primary fa-lg"></i></span>
                                    </div>
                                    <span id="supplier_id_msg" style="display:none" class="text-danger"></span>
                                </div>
                            @endif
                            <label for="return_date" class="col-sm-2 control-label">Date <label class="text-danger">*</label></label>
                            <div class="col-sm-3">
                                <div class="input-group date">
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                    <input type="text" class="form-control pull-right datepicker" id="return_date" name="return_date" readonly value="{{ show_date(date('d-m-Y')) }}">
                                </div>
                                <span id="return_date_msg" style="display:none" class="text-danger"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="return_status" class="col-sm-2 control-label">Status <label class="text-danger">*</label></label>
                            <div class="col-sm-3">
                                <select class="form-control select2" id="return_status" name="return_status" style="width: 100%;">
                                    <option @selected(($returnEntry?->return_status ?? '') === 'Return') value="Return">Return</option>
                                    <option @selected(($returnEntry?->return_status ?? '') === 'Cancel') value="Cancel">Cancel</option>
                                </select>
                                <span id="return_status_msg" style="display:none" class="text-danger"></span>
                            </div>
                            <label for="reference_no" class="col-sm-2 control-label">Reference No</label>
                            <div class="col-sm-3">
                                <input type="text" value="{{ $returnEntry?->reference_no ?? '' }}" class="form-control" id="reference_no" name="reference_no">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-12">
                                <div class="box">
                                    <div class="box-info">
                                        <div class="box-header">
                                            <div class="col-md-8 col-md-offset-2">
                                                <div class="input-group">
                                                    <span class="input-group-addon" title="Select Items"><i class="fa fa-barcode"></i></span>
                                                    <input type="text" class="form-control" placeholder="Item name/Barcode/Itemcode" id="item_search">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="box-body">
                                            <div class="table-responsive" style="width: 100%">
                                                <table class="table table-hover table-bordered" style="width:100%" id="purchase_table">
                                                    <thead class="custom_thead">
                                                    <tr class="bg-primary">
                                                        <th rowspan="2" style="width:15%">Item Name</th>
                                                        <th rowspan="2" style="width:10%;min-width: 180px;">Quantity</th>
                                                        <th rowspan="2" style="width:10%">Purchase Price</th>
                                                        <th rowspan="2" style="width:10%">Discount</th>
                                                        <th rowspan="2" class="{{ tax_disable_class() }}" style="width:5%">Tax</th>
                                                        <th rowspan="2" class="{{ tax_disable_class() }}" style="width:10%">Tax Amount</th>
                                                        <th rowspan="2" style="width:7.5%">Unit Cost</th>
                                                        <th rowspan="2" style="width:7.5%">Total Amount</th>
                                                        <th rowspan="2" style="width:7.5%">Action</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">Total Quantities</label>
                                        <div class="col-sm-4"><label class="control-label total_quantity text-success" style="font-size: 15pt;">0</label></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="other_charges_input" class="col-sm-4 control-label">Other Charges</label>
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control text-right only_currency" id="other_charges_input" name="other_charges_input" onkeyup="final_total();" value="{{ $returnEntry?->other_charges_input ?? '' }}">
                                        </div>
                                        <div class="col-sm-4">
                                            <select class="form-control" id="other_charges_tax_id" name="other_charges_tax_id" onchange="final_total();" style="width: 100%;">
                                                <option>None</option>
                                                @foreach (\App\Models\Tax::where('status', true)->get() as $t)
                                                    <option @selected(($returnEntry?->other_charges_tax_id ?? null) == $t->id) data-tax="{{ $t->tax }}" value="{{ $t->id }}">{{ $t->tax_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="discount_to_all_input" class="col-sm-4 control-label">Discount on All</label>
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control text-right only_currency" id="discount_to_all_input" name="discount_to_all_input" onkeyup="enable_or_disable_item_discount();" value="{{ $returnEntry?->discount_to_all_input ?? '' }}">
                                        </div>
                                        <div class="col-sm-4">
                                            <select class="form-control" onchange="final_total();" id="discount_to_all_type" name="discount_to_all_type">
                                                <option value="in_percentage">Per%</option>
                                                <option value="in_fixed">Fixed</option>
                                            </select>
                                        </div>
                                        <script type="text/javascript">
                                        @if(! empty($returnEntry?->discount_to_all_type ?? ''))
                                            document.getElementById('discount_to_all_type').value='{{ $returnEntry?->discount_to_all_type }}';
                                        @endif
                                        </script>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="return_note" class="col-sm-4 control-label">Note</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control text-left" id="return_note" name="return_note">{{ $returnEntry?->return_note ?? '' }}</textarea>
                                            <span id="return_note_msg" style="display:none" class="text-danger"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <table class="col-md-9">
                                            <tr>
                                                <th class="text-right" style="font-size: 17px;">Subtotal</th>
                                                <th class="text-right" style="padding-left:10%;font-size: 17px;"><h4><b id="subtotal_amt" name="subtotal_amt">0.00</b></h4></th>
                                            </tr>
                                            <tr>
                                                <th class="text-right" style="font-size: 17px;">Other Charges</th>
                                                <th class="text-right" style="padding-left:10%;font-size: 17px;"><h4><b id="other_charges_amt" name="other_charges_amt">0.00</b></h4></th>
                                            </tr>
                                            <tr>
                                                <th class="text-right" style="font-size: 17px;">Discount on All</th>
                                                <th class="text-right" style="padding-left:10%;font-size: 17px;"><h4><b id="discount_to_all_amt" name="discount_to_all_amt">0.00</b></h4></th>
                                            </tr>
                                            <tr style="{{ ! is_enabled_round_off() ? 'display: none;' : '' }}">
                                                <th class="text-right" style="font-size: 17px;">Round Off</th>
                                                <th class="text-right" style="padding-left:10%;font-size: 17px;"><h4><b id="round_off_amt" name="tot_round_off_amt">0.00</b></h4></th>
                                            </tr>
                                            <tr>
                                                <th class="text-right" style="font-size: 17px;">Grand Total</th>
                                                <th class="text-right" style="padding-left:10%;font-size: 17px;"><h4><b id="total_amt" name="total_amt">0.00</b></h4></th>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12">
                            <div class="col-sm-12">
                                <div class="box-body">
                                    <div class="col-md-12">
                                        <table class="table table-hover table-bordered" style="width:100%" id="payments_table">
                                            <h4 class="box-title text-info">Previous Payments Information :</h4>
                                            <thead>
                                            <tr class="bg-gray">
                                                <th>#</th><th>Date</th><th>Payment Type</th><th>Payment Note</th><th>Payment</th><th>Action</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if($returnEntry)
                                                @forelse ($returnEntry->payments as $i => $p)
                                                    <tr class="text-center text-bold" id="payment_row_{{ $p->id }}">
                                                        <td>{{ $i + 1 }}</td>
                                                        <td>{{ show_date($p->payment_date) }}</td>
                                                        <td>{{ $p->payment_type }}</td>
                                                        <td>{{ $p->payment_note }}</td>
                                                        <td class="text-right" id="paid_amt_{{ $i + 1 }}">{{ $p->payment }}</td>
                                                        <td><i class="fa fa-trash text-red pointer" onclick="delete_payment({{ $p->id }})"> Delete</i></td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="6" class="text-center text-bold">No Previous Payments Found!!</td></tr>
                                                @endforelse
                                            @else
                                                <tr><td colspan="6" class="text-center text-bold">Payments Pending!!</td></tr>
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12">
                            <div class="col-sm-12">
                                <div class="box-body">
                                    <div class="col-md-12 payments_div payments_div_">
                                        <h4 class="box-title text-info">Subtotal :</h4>
                                        <div class="box box-solid bg-gray">
                                            <div class="box-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label for="amount">Amount</label>
                                                        <input type="text" class="form-control text-right paid_amt only_currency" id="amount" name="amount">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="payment_type">Payment Type</label>
                                                        <select class="form-control select2" id="payment_type" name="payment_type">
                                                            <option value="">-Select-</option>
                                                            @foreach (\App\Models\PaymentType::where('status', true)->get() as $pt)
                                                                <option value="{{ $pt->payment_type }}">{{ $pt->payment_type }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label for="payment_note">Payment Note</label>
                                                        <textarea class="form-control" id="payment_note" name="payment_note"></textarea>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="box-footer col-sm-12">
                        <center>
                            @if($oper === 'return_against_purchase')
                                @php($btnId = 'save') @php($btnName = 'Save')
                                <input type="hidden" name="purchase_id" id="purchase_id" value="{{ $purchase->id }}">
                            @elseif($oper === 'edit_existing_return')
                                @php($btnId = 'update') @php($btnName = 'Update')
                                <input type="hidden" name="return_id" id="return_id" value="{{ $returnEntry->id }}">
                                <input type="hidden" name="purchase_id" id="purchase_id" value="{{ $returnEntry->purchase_id }}">
                            @else
                                @php($btnId = 'create') @php($btnName = 'Create')
                            @endif
                            <div class="col-md-3 col-md-offset-3">
                                <button type="button" id="{{ $btnId }}" class="btn bg-maroon btn-block btn-flat btn-lg payments_modal">{{ $btnName }}</button>
                            </div>
                            <div class="col-sm-3"><a href="{{ url('dashboard') }}"><button type="button" class="btn bg-gray btn-block btn-flat btn-lg">Close</button></a></div>
                        </center>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

@include('modals.supplier')
@include('modals.purchase-item')
@endsection

@push('scripts')
<script src="{{ $theme_link }}js/modals.js"></script>
<script src="{{ $theme_link }}js/purchase_return.js"></script>
<script src="{{ $theme_link }}js/ajaxselect/supplier_select_ajax.js"></script>
<script>
function getsupplierSelectionId() { return '#supplier_id'; }

$(document).ready(function () {
    var supplier_id = "{{ $supplierId ?? '' }}";
    if (supplier_id != '') { autoLoadFirstsupplier(supplier_id); }
});

$(".close_btn").on("click",function(){
    if (confirm('Are you sure you want to navigate away from this page?')) { window.location='{{ url('/').'/' }}dashboard'; }
});
$(".select2").select2();
$('.datepicker').datepicker({ autoclose: true, format: 'dd-mm-yyyy', todayHighlight: true });

function calculate_tax(i){
    set_tax_value(i);
    var tax_type = $("#tr_tax_type_"+i).val();
    var tax_amount = $("#td_data_"+i+"_5").val();
    var qty=$("#td_data_"+i+"_3").val().trim();
    var purchase_price=parseFloat($("#td_data_"+i+"_4").val().trim());
    var discount_amt=$("#td_data_"+i+"_8").val().trim();
    discount_amt = (isNaN(parseFloat(discount_amt))) ? 0 : parseFloat(discount_amt);
    var tax=$("#tr_tax_value_"+i).val().trim();
    var amt=parseFloat(qty) * purchase_price;
    var total_amt=amt-discount_amt;
    total_amt = (tax_type=='Inclusive') ? total_amt : parseFloat(total_amt) + parseFloat(tax_amount);
    var tax_each = (tax_type=='Inclusive') ? 0 : calculate_exclusive((purchase_price-discount_amt/parseFloat(qty)),tax);
    var price_per_unit = (purchase_price - (discount_amt/parseFloat(qty)))+(parseFloat(tax_each));
    $("#td_data_"+i+"_10").val('').val(price_per_unit.toFixed(2));
    $("#td_data_"+i+"_9").val('').val(total_amt.toFixed(2));
    final_total();
}

function final_total(){
    var rowcount=$("#hidden_rowcount").val();
    var subtotal=parseFloat(0);
    var other_charges_per_amt=parseFloat(0);
    var other_charges_total_amt=0;
    var taxable=0;
    if($("#other_charges_input").val()!=null && $("#other_charges_input").val()!=''){
        var other_charges_tax_id =$('option:selected', '#other_charges_tax_id').attr('data-tax');
        var other_charges_input=$("#other_charges_input").val();
        if(other_charges_tax_id>0){ other_charges_per_amt=(other_charges_tax_id * other_charges_input)/100; }
        taxable=parseFloat(other_charges_per_amt)+parseFloat(other_charges_input);
        other_charges_total_amt=parseFloat(other_charges_per_amt)+parseFloat(other_charges_input);
    }
    var total_quantity=0;
    for(i=1;i<=rowcount;i++){
        if(document.getElementById("td_data_"+i+"_3")){
            if($("#td_data_"+i+"_3").val()!=null && $("#td_data_"+i+"_3").val()!=''){
                subtotal=subtotal+ + +parseFloat($("#td_data_"+i+"_9").val()).toFixed(2);
                total_quantity +=parseFloat($("#td_data_"+i+"_3").val().trim());
            }
        }
    }
    $(".total_quantity").html(total_quantity);
    if((subtotal!=null || subtotal!='') && (subtotal!=0)){
        $("#subtotal_amt").html(subtotal.toFixed(2));
        $("#other_charges_amt").html(parseFloat(other_charges_total_amt).toFixed(2));
        taxable=taxable+subtotal;
        var discount_input=parseFloat($("#discount_to_all_input").val());
        discount_input = isNaN(discount_input) ? 0 : discount_input;
        var discount=0;
        if(discount_input>0){
            var discount_type=$("#discount_to_all_type").val();
            if(discount_type=='in_fixed'){ taxable-=discount_input; discount=discount_input; }
            else if(discount_type=='in_percentage'){ discount=(taxable*discount_input)/100; taxable-=discount; }
        }
        discount=parseFloat(discount).toFixed(2);
        $("#discount_to_all_amt").html(discount);
        $("#hidden_discount_to_all_amt").val(discount);
        subtotal_round=round_off(taxable);
        subtotal_diff=subtotal_round-taxable;
        $("#round_off_amt").html(parseFloat(subtotal_diff).toFixed(2));
        $("#total_amt").html(parseFloat(subtotal_round).toFixed(2));
        if (save_operation()) { $("#amount").val(parseFloat(subtotal_round).toFixed(2)); }
        $("#hidden_total_amt").val(parseFloat(subtotal_round).toFixed(2));
    } else {
        $("#subtotal_amt").html('0.00');
        $("#amount").val('0.00');
    }
}

function save_operation() { return {{ $oper !== 'edit_existing_return' ? 'true' : 'false' }}; }

function removerow(id){ $("#row_"+id).remove(); final_total(); failed.currentTime = 0; failed.play(); }

function enable_or_disable_item_discount(){
    var rowcount=$("#hidden_rowcount").val();
    for(k=1;k<=rowcount;k++){
        if(document.getElementById("tr_item_id_"+k)){ calculate_tax(k); }
    }
}

function show_purchase_item_modal(row_id){
    $('#purchase_item').modal('toggle');
    $("#popup_tax_id").select2();
    var item_name = $("#td_data_"+row_id+"_1").html();
    var tax_type = $("#tr_tax_type_"+row_id).val();
    var tax_id = $("#tr_tax_id_"+row_id).val();
    var description = $("#description_"+row_id).val();
    var item_discount_input = $("#item_discount_input_"+row_id).val();
    var item_discount_type = $("#item_discount_type_"+row_id).val();
    $("#item_discount_input").val(item_discount_input);
    $("#item_discount_type").val(item_discount_type).select2();
    $("#popup_description").val(description);
    $("#popup_item_name").html(item_name);
    $("#popup_tax_type").val(tax_type).select2();
    $("#popup_tax_id").val(tax_id).select2();
    $("#popup_row_id").val(row_id);
}

function set_info(){
    var row_id = $("#popup_row_id").val();
    var tax_type = $("#popup_tax_type").val();
    var tax_id = $("#popup_tax_id").val();
    var tax_name = ($('option:selected', "#popup_tax_id").attr('data-tax-value'));
    var tax = parseFloat($('option:selected', "#popup_tax_id").attr('data-tax'));
    var description = $("#popup_description").val();
    var item_discount_input = $("#item_discount_input").val();
    var item_discount_type = $("#item_discount_type").val();
    $("#item_discount_input_"+row_id).val(item_discount_input);
    $("#item_discount_type_"+row_id).val(item_discount_type);
    $("#tr_tax_type_"+row_id).val(tax_type);
    $("#tr_tax_id_"+row_id).val(tax_id);
    $("#tr_tax_value_"+row_id).val(tax);
    $("#td_data_"+row_id+"_15").html(tax_name);
    $("#description_"+row_id).val(description);
    calculate_tax(row_id);
    $('#purchase_item').modal('toggle');
}

function set_tax_value(row_id){
    var tax_type = $("#tr_tax_type_"+row_id).val();
    var tax = $("#tr_tax_value_"+row_id).val();
    var qty=$("#td_data_"+row_id+"_3").val().trim();
    qty = (isNaN(qty)) ? 0 :qty;
    var purchase_price = parseFloat($("#td_data_"+row_id+"_4").val());
    purchase_price = (isNaN(purchase_price)) ? 0 :purchase_price;
    purchase_price = purchase_price * qty;
    var item_discount_type = $("#item_discount_type_"+row_id).val();
    var item_discount_input = parseFloat($("#item_discount_input_"+row_id).val());
    item_discount_input = (isNaN(item_discount_input)) ? 0 :item_discount_input;
    var discount_amt=(item_discount_type=='Percentage') ? ((purchase_price) * item_discount_input)/100 : (item_discount_input * qty);
    purchase_price-=parseFloat(discount_amt);
    var tax_amount = (tax_type=='Inclusive') ? calculate_inclusive(purchase_price,tax) : calculate_exclusive(purchase_price,tax);
    $("#td_data_"+row_id+"_8").val(discount_amt);
    $("#td_data_"+row_id+"_5").val(tax_amount);
}

@if($oper === 'return_against_purchase')
$(document).ready(function(){
    var base_url='{{ url('/').'/' }}';
    var purchase_id='{{ $purchase->id }}';
    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $.post(base_url+"purchase_return/purchase_list/"+purchase_id,{},function(result){
        $('#purchase_table tbody').append(result);
        $("#hidden_rowcount").val(parseInt({{ $itemsCount }})+1);
        success.currentTime = 0;
        success.play();
        enable_or_disable_item_discount();
        $(".overlay").remove();
    });
});
@endif

@if($oper === 'edit_existing_return')
$(document).ready(function(){
    var base_url='{{ url('/').'/' }}';
    var return_id='{{ $returnEntry->id }}';
    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $.post(base_url+"purchase_return/return_purchase_list/"+return_id,{},function(result){
        $('#purchase_table tbody').append(result);
        $("#hidden_rowcount").val(parseInt({{ $itemsCount }})+1);
        success.currentTime = 0;
        success.play();
        enable_or_disable_item_discount();
        $(".overlay").remove();
    });
});
@endif
</script>
@endpush
