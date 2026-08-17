<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'group_bit' => 'boolean',
            'status' => 'boolean',
            'undelete_bit' => 'boolean',
        ];
    }

    public function subtaxNames(): string
    {
        if (empty($this->subtax_ids)) {
            return '';
        }

        return static::whereIn('id', explode(',', (string) $this->subtax_ids))
            ->pluck('tax_name')
            ->map(fn ($name) => "($name)")
            ->implode('+');
    }
}
