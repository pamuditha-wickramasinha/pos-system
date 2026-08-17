<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'show_signature' => 'boolean',
            'status' => 'boolean',
            'sms_status' => 'boolean',
        ];
    }
}
