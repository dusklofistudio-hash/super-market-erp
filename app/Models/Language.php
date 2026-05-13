<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
    protected $fillable = ['code', 'name', 'native_name', 'direction', 'is_default', 'is_active'];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class, 'language_code', 'code');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public static function default(): ?self
    {
        return static::query()->where('is_default', true)->where('is_active', true)->first()
            ?? static::query()->where('is_active', true)->first();
    }
}
