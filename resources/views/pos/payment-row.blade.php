<div class="col-md-12 payments_div payments_div_{{ $rowNumber }}">
    <div class="box box-solid bg-gray">
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <label for="amount_{{ $rowNumber }}">Amount</label>
                    <input type="text" class="form-control text-right payment only_currency" id="amount_{{ $rowNumber }}" name="amount_{{ $rowNumber }}" onkeyup="calculate_payments()">
                    <span id="amount_{{ $rowNumber }}_msg" style="display:none" class="text-danger"></span>
                </div>
                <div class="col-md-6">
                    <label for="payment_type_{{ $rowNumber }}">Payment Type</label>
                    <select class="form-control" id="payment_type_{{ $rowNumber }}" name="payment_type_{{ $rowNumber }}">
                        @forelse ($paymentTypes as $pt)
                            <option value="{{ $pt->payment_type }}">{{ $pt->payment_type }}</option>
                        @empty
                            <option>No Records Found</option>
                        @endforelse
                    </select>
                    <span id="payment_type_{{ $rowNumber }}_msg" style="display:none" class="text-danger"></span>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <label for="payment_note_{{ $rowNumber }}">Payment Note</label>
                    <textarea class="form-control" id="payment_note_{{ $rowNumber }}" name="payment_note_{{ $rowNumber }}"></textarea>
                    <span id="payment_note_{{ $rowNumber }}_msg" style="display:none" class="text-danger"></span>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>
