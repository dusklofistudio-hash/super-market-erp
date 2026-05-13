<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use SoftDeletes;

    protected $fillable = ['name_en', 'name_kh', 'short_name', 'base_unit_id', 'conversion_factor', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'conversion_factor' => 'decimal:4',
    ];

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(self::class, 'base_unit_id');
    }

    public function localized(string $field): ?string
    {
        $locale = app()->getLocale();
        $key = $field.'_'.$locale;

        return $this->{$key} ?? $this->{$field.'_en'};
    }
}
