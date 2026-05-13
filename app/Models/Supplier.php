<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name', 'phone', 'email', 'company', 'address',
        'opening_balance', 'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:4',
        'is_active' => 'boolean',
    ];
}
