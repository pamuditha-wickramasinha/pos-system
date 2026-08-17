<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerOpeningBalancePayment extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['payment_date' => 'date', 'status' => 'boolean'];
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
