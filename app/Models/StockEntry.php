<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockEntry extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['entry_date' => 'date', 'status' => 'boolean'];
    }

    public function item(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
