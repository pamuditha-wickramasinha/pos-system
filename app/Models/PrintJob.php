<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrintJob extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_test' => 'boolean',
            'claimed_at' => 'datetime',
        ];
    }

    public function sale(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function printer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Printer::class);
    }
}
