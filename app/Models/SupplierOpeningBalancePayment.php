<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierOpeningBalancePayment extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['payment_date' => 'date', 'status' => 'boolean'];
    }

    public function supplier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
