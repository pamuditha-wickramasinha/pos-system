<div class="modal fade " id="customer-modal" tabindex="-1">
    <form id="customer-form">
    @csrf
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header header-custom">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center">Add Customer</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="customer_name">Customer Name*</label>
                            <span id="customer_name_msg" class="text-danger text-right pull-right"></span>
                            <input type="text" class="form-control" id="customer_name" name="customer_name">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="mobile">Mobile</label>
                            <span id="mobile_msg" class="text-danger text-right pull-right"></span>
                            <input type="tel" class="form-control no_special_char_no_space" id="mobile" name="mobile">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <span id="phone_msg" class="text-danger text-right pull-right"></span>
                            <input type="tel" maxlength="10" class="form-control maxlength no_special_char_no_space" id="phone" name="phone">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <span id="email_msg" class="text-danger text-right pull-right"></span>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="opening_balance">Previous Due</label>
                            <span id="opening_balance_msg" class="text-danger text-right pull-right"></span>
                            <input type="text" class="form-control" id="opening_balance" name="opening_balance">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="gstin">GST Number</label>
                            <span id="gstin_msg" class="text-danger text-right pull-right"></span>
                            <input type="text" class="form-control" id="gstin" name="gstin">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="tax_number">Tax Number</label>
                            <span id="tax_number_msg" class="text-danger text-right pull-right"></span>
                            <input type="text" class="form-control" id="tax_number" name="tax_number">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="country">Country</label>
                            <select class="form-control select2" id="country" name="country" style="width: 100%;">
                                @foreach (\App\Models\Country::where('status', true)->get() as $c)
                                    <option value="{{ $c->id }}">{{ $c->country }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="state">State</label>
                            <select class="form-control" id="state" name="state" style="width: 100%;">
                                <option value="">-Select-</option>
                                @foreach (\App\Models\State::where('status', true)->get() as $s)
                                    <option value="{{ $s->id }}">{{ $s->state }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" class="form-control" id="city" name="city">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="postcode">Postcode</label>
                            <input type="text" class="form-control" id="postcode" name="postcode">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea class="form-control" id="address" name="address"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary add_customer">Save</button>
            </div>
        </div>
    </div>
    </form>
</div>
