<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    protected $fillable = ['name', 'rate', 'is_inclusive', 'is_active'];

    protected $casts = [
        'rate' => 'decimal:4',
        'is_inclusive' => 'boolean',
        'is_active' => 'boolean',
    ];
}
