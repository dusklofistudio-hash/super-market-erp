<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name', 'phone', 'email', 'address',
        'customer_group_id', 'opening_balance', 'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }
}
