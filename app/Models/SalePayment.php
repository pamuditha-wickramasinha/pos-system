<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    protected $table = 'sales_payments';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['payment_date' => 'date', 'status' => 'boolean'];
    }

    public function sale(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sales_id');
    }
}
