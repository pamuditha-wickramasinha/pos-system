<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function isWalkIn(): bool
    {
        return $this->id === 1;
    }

    public function openingBalancePayments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CustomerOpeningBalancePayment::class);
    }
}
