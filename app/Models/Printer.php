<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Printer extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'cut_paper' => 'boolean',
            'open_cash_drawer' => 'boolean',
            'is_default' => 'boolean',
            'status' => 'boolean',
            'port' => 'integer',
            'paper_width' => 'integer',
        ];
    }

    public function printJobs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PrintJob::class);
    }
}
