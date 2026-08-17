<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturnPayment extends Model
{
    protected $table = 'purchase_payments_returns';

    protected $guarded = ['id'];

    public function returnEntry(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class, 'return_id');
    }
}
