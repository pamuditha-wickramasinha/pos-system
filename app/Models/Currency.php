<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }
}
