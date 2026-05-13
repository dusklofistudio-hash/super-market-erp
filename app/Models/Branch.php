<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name_en', 'name_kh', 'phone', 'email', 'address',
        'manager_id', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_branch');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function localized(string $field): ?string
    {
        $locale = app()->getLocale();
        $key = $field.'_'.$locale;

        return $this->{$key} ?? $this->{$field.'_en'};
    }
}
