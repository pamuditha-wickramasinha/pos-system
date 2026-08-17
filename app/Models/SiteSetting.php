<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'change_return' => 'boolean',
            'round_off' => 'boolean',
            'show_upi_code' => 'boolean',
            'disable_tax' => 'boolean',
        ];
    }

    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
