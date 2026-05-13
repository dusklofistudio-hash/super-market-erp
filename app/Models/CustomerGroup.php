<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerGroup extends Model
{
    protected $fillable = ['name', 'discount_percent', 'is_active'];

    protected $casts = [
        'discount_percent' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
