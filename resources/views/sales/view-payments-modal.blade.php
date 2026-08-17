@php($dueAmount = number_format($sale->grand_total - $sale->paid_amount, 2, '.', ''))
<div class="modal fade" id="view_payments_modal">
    <div class="modal-dialog modal-lg">
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
                                Customer Information
                                <address>
                                    <strong>{{ $sale->customer->customer_name }}</strong><br>
                                    @if($sale->customer->mobile)Mobile: {{ $sale->customer->mobile }}<br>@endif
                                    @if($sale->customer->email)Email: {{ $sale->customer->email }}<br>@endif
                                </address>
                            </div>
                            <div class="col-sm-4 invoice-col">
                                Sales Information:
                                <address>
                                    <b>Invoice #{{ $sale->sales_code }}</b><br>
                                    <b>Date :{{ show_date($sale->sales_date) }}</b><br>
                                    <b>Grand Total :{{ $sale->grand_total }}</b><br>
                                </address>
                            </div>
                            <div class="col-sm-4 invoice-col">
                                <b>Paid Amount :<span>{{ number_format($sale->paid_amount, 2, '.', '') }}</span></b><br>
                                <b>Due Amount :<span id="due_amount_temp">{{ $dueAmount }}</span></b><br>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr class="bg-primary">
                                        <th>#</th><th>Payment Date</th><th>Payment</th><th>Payment Type</th><th>Payment Note</th><th>Created by</th><th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($sale->payments as $i => $p)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ show_date($p->payment_date) }}</td>
                                            <td>{{ $p->payment }}</td>
                                            <td>{{ $p->payment_type }}</td>
                                            <td>{{ $p->payment_note }}</td>
                                            <td>{{ ucfirst((string) $p->created_by) }}</td>
                                            <td><a onclick="delete_sales_payment({{ $p->id }})" class="pointer btn btn-danger"><i class="fa fa-trash"></i></a></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-danger text-center">No Records Found</td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-lg" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
