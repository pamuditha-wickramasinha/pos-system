<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturnPayment extends Model
{
    protected $table = 'sales_payments_returns';

    protected $guarded = ['id'];

    public function returnEntry(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SalesReturn::class, 'return_id');
    }
}
