<div class="modal fade" id="pay_now">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header header-custom">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center">Pay Due Payments</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row invoice-info">
                            <div class="col-sm-12 invoice-col">
                                <i>Supplier Details</i>
                                <address>
                                    <strong>{{ $supplier->supplier_name }}</strong><br>
                                    @if($supplier->mobile)Mobile: {{ $supplier->mobile }}<br>@endif
                                    @if($supplier->phone)Phone: {{ $supplier->phone }}<br>@endif
                                    @if($supplier->email)Email: {{ $supplier->email }}<br>@endif
                                </address>
                            </div>
                            <div class="col-sm-12 invoice-col">
                                <table class="table table-sm table-bordered bg-info" width="100%">
                                    <tr><td class="text-right">Total Purchase Amount</td><td class="text-right">{{ currency($totalPurchaseAmount) }}</td></tr>
                                    <tr><td class="text-right">Paid Amount</td><td class="text-right">{{ currency($totalPaidAmount) }}</td></tr>
                                    <tr><td class="text-right">Opening Balance Due</td><td class="text-right">{{ currency($openingBalanceDue) }}</td></tr>
                                    <tr><td class="text-right">Purchase Due</td><td class="text-right">{{ currency($supplier->purchase_due) }}</td></tr>
                                </table>
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
                                            <span id="payment_date_msg" style="display:none" class="text-danger"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="amount">Amount</label>
                                            <input type="text" class="form-control text-right paid_amt" data-due-amt="{{ $dueAmount }}" id="amount" name="amount" value="{{ $dueAmount }}">
                                            <span id="amount_msg" style="display:none" class="text-danger"></span>
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
                                            <span id="payment_type_msg" style="display:none" class="text-danger"></span>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label for="payment_note">Payment Note</label>
                                            <textarea class="form-control" id="payment_note" name="payment_note"></textarea>
                                            <span id="payment_note_msg" style="display:none" class="text-danger"></span>
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
                <button type="button" onclick="save_payment({{ $supplier->id }})" class="btn bg-green btn-lg place_order btn-lg payment_save">Save<i class="fa  fa-check "></i></button>
            </div>
        </div>
    </div>
</div>
