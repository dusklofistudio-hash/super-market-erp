<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosSession extends Model
{
    protected $fillable = [
        'register_id', 'user_id', 'opened_at', 'closed_at',
        'opening_cash', 'expected_cash', 'closing_cash', 'difference', 'note',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_cash' => 'decimal:4',
        'expected_cash' => 'decimal:4',
        'closing_cash' => 'decimal:4',
        'difference' => 'decimal:4',
    ];

    public function register(): BelongsTo
    {
        return $this->belongsTo(PosRegister::class, 'register_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'pos_session_id');
    }
}
