<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'barcode', 'sku', 'name_en', 'name_kh', 'description',
        'category_id', 'brand_id', 'unit_id', 'tax_rate_id',
        'cost_price', 'sale_price', 'alert_qty', 'image', 'is_active',
    ];

    protected $casts = [
        'cost_price' => 'decimal:4',
        'sale_price' => 'decimal:4',
        'alert_qty' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id');
    }

    public function branchStock(): HasMany
    {
        return $this->hasMany(ProductBranchStock::class);
    }

    public function localized(string $field): ?string
    {
        $locale = app()->getLocale();
        $key = $field.'_'.$locale;

        return $this->{$key} ?? $this->{$field.'_en'};
    }
}
