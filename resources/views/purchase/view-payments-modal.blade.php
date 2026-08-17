@php($dueAmount = number_format($purchase->grand_total - $purchase->paid_amount, 2, '.', ''))
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
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr class="bg-primary">
                                        <th>#</th><th>Payment Date</th><th>Payment</th><th>Payment Type</th><th>Payment Note</th><th>Created by</th><th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($purchase->payments as $i => $p)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ show_date($p->payment_date) }}</td>
                                            <td>{{ $p->payment }}</td>
                                            <td>{{ $p->payment_type }}</td>
                                            <td>{{ $p->payment_note }}</td>
                                            <td>{{ ucfirst((string) $p->created_by) }}</td>
                                            <td><a onclick="delete_purchase_payment({{ $p->id }})" class="pointer btn btn-danger"><i class="fa fa-trash"></i></a></td>
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
