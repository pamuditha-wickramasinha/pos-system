@php($dueAmount = number_format($purchase->grand_total - $purchase->paid_amount, 2, '.', ''))
<div class="modal fade" id="pay_now">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header header-custom">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center">Payments</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row invoice-info">
                            <div class="col-sm-4 invoice-col">
                                Supplier Information
                                <address>
                                    <strong>{{ $purchase->supplier->supplier_name }}</strong><br>
                                    @if($purchase->supplier->mobile)Mobile: {{ $purchase->supplier->mobile }}<br>@endif
                                    @if($purchase->supplier->phone)Phone: {{ $purchase->supplier->phone }}<br>@endif
                                    @if($purchase->supplier->email)Email: {{ $purchase->supplier->email }}<br>@endif
                                </address>
                            </div>
                            <div class="col-sm-4 invoice-col">
                                Purchase Information:
                                <address>
                                    <b>Invoice #{{ $purchase->purchase_code }}</b><br>
                                    <b>Date :{{ show_date($purchase->purchase_date) }}</b><br>
                                    <b>Grand Total :{{ $purchase->grand_total }}</b><br>
                                </address>
                            </div>
                            <div class="col-sm-4 invoice-col">
                                <b>Paid Amount :<span>{{ number_format($purchase->paid_amount, 2, '.', '') }}</span></b><br>
                                <b>Due Amount :<span id="due_amount_temp">{{ $dueAmount }}</span></b><br>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <input type="hidden" name="payment_row_count" id="payment_row_count" value="1">
                        <div class="col-md-12 payments_div">
                            <div class="box box-solid bg-gray">
                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label for="payment_date">Date</label>
                                            <div class="input-group date">
                                                <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                                <input type="text" class="form-control pull-right datepicker" value="{{ show_date(date('d-m-Y')) }}" id="payment_date" name="payment_date" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="amount">Amount</label>
                                            <input type="text" class="form-control text-right paid_amt" id="amount" name="amount" value="{{ $dueAmount }}" onkeyup="calculate_payments()">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="payment_type">Payment Type</label>
                                            <select class="form-control" id="payment_type" name="payment_type">
                                                @forelse (\App\Models\PaymentType::where('status', true)->get() as $pt)
                                                    <option value="{{ $pt->payment_type }}">{{ $pt->payment_type }}</option>
                                                @empty
                                                    <option>No Records Found</option>
                                                @endforelse
                                            </select>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="payment_note">Payment Note</label>
                                            <textarea class="form-control" id="payment_note" name="payment_note"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-lg" data-dismiss="modal">Close</button>
                <button type="button" onclick="save_payment({{ $purchase->id }})" class="btn bg-green btn-lg place_order btn-lg payment_save">Save<i class="fa  fa-check "></i></button>
            </div>
        </div>
    </div>
</div>
