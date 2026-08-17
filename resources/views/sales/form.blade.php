@extends('layouts.app')
@php($activeMenu = 'sales')
@php($salesId = $sale?->id ?? null)
@php($salesDiscountDefault = \App\Models\SiteSetting::query()->value('sales_discount'))

@section('content')
<section class="content-header">
    <h1>{{ $page_title }} <small>Add/Update Sales</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{ route('sales.index') }}">Sales List</a></li>
        <li><a href="{{ route('sales.add') }}">New Sales</a></li>
        <li class="active">{{ $page_title }}</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        @include('partials.flashdata')
        <div class="col-md-12">
            <div class="box box-info">
                <form class="form-horizontal" id="sales-form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="base_url" value="{{ url('/').'/' }}">
                    <input type="hidden" value="1" id="hidden_rowcount" name="hidden_rowcount">
                    <input type="hidden" value="0" id="hidden_update_rowid" name="hidden_update_rowid">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="customer_id" class="col-sm-2 control-label">Customer Name<label class="text-danger">*</label></label>
                            <div class="col-sm-3">
                                <div class="input-group">
                                    <select class="form-control select2" id="customer_id" name="customer_id" style="width: 100%;"></select>
                                    <span class="input-group-addon pointer" data-toggle="modal" data-target="#customer-modal" title="New Customer?"><i class="fa fa-user-plus text-primary fa-lg"></i></span>
                                </div>
                                <span id="customer_id_msg" style="display:none" class="text-danger"></span>
                            </div>
                            <label for="sales_date" class="col-sm-2 control-label">Sales Date <label class="text-danger">*</label></label>
                            <div class="col-sm-3">
                                <div class="input-group date">
                                    <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                    <input type="text" class="form-control pull-right datepicker" id="sales_date" name="sales_date" readonly value="{{ $sale?->sales_date ? show_date($sale?->sales_date) : show_date(date('d-m-Y')) }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="sales_status" class="col-sm-2 control-label">Status <label class="text-danger">*</label></label>
                            <div class="col-sm-3">
                                <select class="form-control select2" id="sales_status" name="sales_status" style="width: 100%;">
                                    <option @selected(($sale?->sales_status ?? 'Final') === 'Final') value="Final">Final</option>
                                    <option @selected(($sale?->sales_status ?? '') === 'Quotation') value="Quotation">Quotation</option>
                                </select>
                            </div>
                            <label for="reference_no" class="col-sm-2 control-label">Reference No</label>
                            <div class="col-sm-3">
                                <input type="text" value="{{ $sale?->reference_no ?? '' }}" class="form-control" id="reference_no" name="reference_no">
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
                                                <table class="table table-hover table-bordered" style="width:100%" id="sales_table">
                                                    <thead class="custom_thead">
                                                    <tr class="bg-primary">
                                                        <th rowspan="2" style="width:15%">Item Name</th>
                                                        <th rowspan="2" style="width:15%;min-width: 180px;">Quantity</th>
                                                        <th rowspan="2" style="width:15%">Sales Price({{ $CURRENCY }})</th>
                                                        <th rowspan="2" style="width:5%">Discount({{ $CURRENCY }})</th>
                                                        <th rowspan="2" class="{{ tax_disable_class() }}" style="width:7.5%">Tax Amount({{ $CURRENCY }})</th>
                                                        <th rowspan="2" class="{{ tax_disable_class() }}" style="width:7.5%">Tax</th>
                                                        <th rowspan="2" style="width:7.5%">Total Amount({{ $CURRENCY }})</th>
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
                                            <input type="text" class="form-control text-right only_currency" id="other_charges_input" name="other_charges_input" onkeyup="final_total();" value="{{ $sale?->other_charges_input ?? '' }}">
                                        </div>
                                        <div class="col-sm-4">
                                            <select class="form-control" id="other_charges_tax_id" name="other_charges_tax_id" onchange="final_total();" style="width: 100%;">
                                                <option>None</option>
                                                @foreach (\App\Models\Tax::where('status', true)->get() as $t)
                                                    <option @selected(($sale?->other_charges_tax_id ?? null) == $t->id) data-tax="{{ $t->tax }}" value="{{ $t->id }}">{{ $t->tax_name }}</option>
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
                                            <input type="text" class="form-control text-right only_currency" id="discount_to_all_input" name="discount_to_all_input" onkeyup="enable_or_disable_item_discount();" value="{{ $sale?->discount_to_all_input ?? ($salesDiscountDefault ?: '') }}">
                                        </div>
                                        <div class="col-sm-4">
                                            <select class="form-control" onchange="final_total();" id="discount_to_all_type" name="discount_to_all_type">
                                                <option value="in_percentage">Per%</option>
                                                <option value="in_fixed">Fixed</option>
                                            </select>
                                        </div>
                                        <script type="text/javascript">
                                        @if(! empty($sale?->discount_to_all_type ?? ''))
                                            document.getElementById('discount_to_all_type').value='{{ $sale?->discount_to_all_type }}';
                                        @endif
                                        </script>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="sales_note" class="col-sm-4 control-label">Note</label>
                                        <div class="col-sm-8">
                                            <textarea class="form-control text-left" id="sales_note" name="sales_note">{{ $sale?->sales_note ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="col-sm-8 col-sm-offset-4">
                                            <input type="checkbox" @checked(! $salesId) class="form-control" id="send_sms" name="send_sms">
                                            <label for="send_sms" class="control-label">Send SMS to Customer</label>
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
                                                <th class="text-right" style="padding-left:10%;font-size: 17px;"><h4>{!! currency('<b id="subtotal_amt" name="subtotal_amt">0.00</b>') !!}</h4></th>
                                            </tr>
                                            <tr>
                                                <th class="text-right" style="font-size: 17px;">Other Charges</th>
                                                <th class="text-right" style="padding-left:10%;font-size: 17px;"><h4>{!! currency('<b id="other_charges_amt" name="other_charges_amt">0.00</b>') !!}</h4></th>
                                            </tr>
                                            <tr>
                                                <th class="text-right" style="font-size: 17px;">Discount on All</th>
                                                <th class="text-right" style="padding-left:10%;font-size: 17px;"><h4>{!! currency('<b id="discount_to_all_amt" name="discount_to_all_amt">0.00</b>') !!}</h4></th>
                                            </tr>
                                            <tr style="{{ ! is_enabled_round_off() ? 'display: none;' : '' }}">
                                                <th class="text-right" style="font-size: 17px;">Round Off</th>
                                                <th class="text-right" style="padding-left:10%;font-size: 17px;"><h4>{!! currency('<b id="round_off_amt" name="tot_round_off_amt">0.00</b>') !!}</h4></th>
                                            </tr>
                                            <tr>
                                                <th class="text-right" style="font-size: 17px;">Grand Total</th>
                                                <th class="text-right" style="padding-left:10%;font-size: 17px;"><h4>{!! currency('<b id="total_amt" name="total_amt">0.00</b>') !!}</h4></th>
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
                                            @if($salesId)
                                                @forelse ($sale->payments as $i => $p)
                                                    <tr class="text-center text-bold" id="payment_row_{{ $p->id }}">
                                                        <td>{{ $i + 1 }}</td>
                                                        <td>{{ show_date($p->payment_date) }}</td>
                                                        <td>{{ $p->payment_type }}</td>
                                                        <td>{{ $p->payment_note }}</td>
                                                        <td class="text-right" id="paid_amt_{{ $i + 1 }}">{{ currency($p->payment) }}</td>
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
                                        <h4 class="box-title text-info">Make Payment :</h4>
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
                            @if($salesId)
                                <input type="hidden" name="sales_id" id="sales_id" value="{{ $salesId }}">
                                @php($btn_id = 'update') @php($btn_name = 'Update')
                            @else
                                @php($btn_id = 'save') @php($btn_name = 'Save')
                            @endif
                            <div class="col-md-3 col-md-offset-3">
                                <button type="button" id="{{ $btn_id }}" class="btn bg-maroon btn-block btn-flat btn-lg payments_modal">{{ $btn_name }}</button>
                            </div>
                            <div class="col-sm-3"><a href="{{ url('dashboard') }}"><button type="button" class="btn bg-gray btn-block btn-flat btn-lg">Close</button></a></div>
                        </center>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

@include('modals.customer')
@include('modals.sales-item')
@endsection

@push('scripts')
<script src="{{ $theme_link }}js/modals.js"></script>
<script src="{{ $theme_link }}js/sales.js"></script>
<script src="{{ $theme_link }}js/ajaxselect/customer_select_ajax.js"></script>
<script>
function getCustomerSelectionId() { return '#customer_id'; }

$(document).ready(function () {
    var customer_id = "{{ $sale?->customer_id ?? '' }}";
    if (customer_id != '') { autoLoadFirstCustomer(customer_id); }
});

$(".close_btn").on("click",function(){
    if (confirm('Are you sure you want to navigate away from this page?')) { window.location='{{ url('/').'/' }}dashboard'; }
});
$(".select2").select2();
$('.datepicker').datepicker({ autoclose: true, format: 'dd-mm-yyyy', todayHighlight: true });

function calculate_tax(i){
    set_tax_value(i);
    var tax_type = $("#tr_tax_type_"+i).val();
    var qty=$("#td_data_"+i+"_3").val().trim();
    var sales_price=parseFloat($("#td_data_"+i+"_10").val().trim());
    var discount_amt=$("#td_data_"+i+"_8").val().trim();
    discount_amt = (isNaN(parseFloat(discount_amt))) ? 0 : parseFloat(discount_amt);
    var tax=$("#tr_tax_value_"+i).val().trim();
    var tax_amount = (tax_type=='Inclusive') ? calculate_inclusive((sales_price*qty)-discount_amt,tax) : calculate_exclusive((sales_price*qty)-discount_amt,tax);
    $("#td_data_"+i+"_11").val(tax_amount);
    $("#td_data_"+i+"_5").val(tax_amount);
    $("#td_data_"+i+"_4").val(sales_price);
    var total_amt = (parseFloat(sales_price)*parseFloat(qty)) - discount_amt + parseFloat(tax_amount);
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
        $("#hidden_total_amt").val(parseFloat(subtotal_round).toFixed(2));
    } else {
        $("#subtotal_amt").html('0.00');
    }
}

function removerow(id){ $("#row_"+id).remove(); final_total(); failed.currentTime = 0; failed.play(); }

function enable_or_disable_item_discount(){
    var rowcount=$("#hidden_rowcount").val();
    for(k=1;k<=rowcount;k++){
        if(document.getElementById("tr_item_id_"+k)){ calculate_tax(k); }
    }
}

function item_qty_input(row_id){
    var available = parseFloat($("#tr_available_qty_"+row_id+"_13").val());
    var qty = parseFloat($("#td_data_"+row_id+"_3").val());
    if (qty > available) {
        toastr["warning"]("Only "+available+" in stock!");
        $("#td_data_"+row_id+"_3").val(available);
    }
    calculate_tax(row_id);
}

function show_sales_item_modal(row_id){
    $('#sales_item').modal('toggle');
    var item_name = $("#td_data_"+row_id+"_1").html();
    var tax_type = $("#tr_tax_type_"+row_id).val();
    var tax_id = $("#tr_tax_id_"+row_id).val();
    var description = $("#description_"+row_id).val();
    var item_discount_input = $("#item_discount_input_"+row_id).val();
    var item_discount_type = $("#item_discount_type_"+row_id).val();
    $("#item_discount_input").val(item_discount_input);
    $("#item_discount_type").val(item_discount_type);
    $("#popup_description").val(description);
    $("#popup_item_name").html(item_name);
    $("#popup_tax_type").val(tax_type);
    $("#popup_tax_id").val(tax_id);
    $("#popup_row_id").val(row_id);
}

function set_info(){
    var row_id = $("#popup_row_id").val();
    var description = $("#popup_description").val();
    var item_discount_input = $("#item_discount_input").val();
    var item_discount_type = $("#item_discount_type").val();
    $("#item_discount_input_"+row_id).val(item_discount_input);
    $("#item_discount_type_"+row_id).val(item_discount_type);
    $("#description_"+row_id).val(description);
    calculate_tax(row_id);
    $('#sales_item').modal('toggle');
}

function set_tax_value(row_id){}

@if($salesId)
$(document).ready(function(){
    var base_url='{{ url('/').'/' }}';
    var sales_id='{{ $salesId }}';
    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $.post(base_url+"sales/return_sales_list/"+sales_id,{},function(result){
        $('#sales_table tbody').append(result);
        $("#hidden_rowcount").val(parseFloat({{ $itemsCount }})+1);
        success.currentTime = 0;
        success.play();
        $(".overlay").remove();
    });
});
@endif
</script>
@endpush
