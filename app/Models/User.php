<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'username',
        'password',
        'legacy_password',
        'firstname',
        'lastname',
        'mobile',
        'email',
        'gender',
        'dob',
        'country',
        'state',
        'city',
        'address',
        'postcode',
        'profile_picture',
        'company_id',
        'status',
        'created_by',
        'system_ip',
        'system_name',
    ];

    protected $hidden = [
        'password',
        'legacy_password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'status' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function fullName(): string
    {
        return trim($this->firstname.' '.$this->lastname) ?: $this->username;
    }

    public function isSuperAdmin(): bool
    {
        return $this->id === 1;
    }
}
