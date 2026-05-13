<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('product')?->id;

        return [
            'barcode' => ['required', 'string', 'max:64', Rule::unique('products', 'barcode')->ignore($id)],
            'sku' => ['required', 'string', 'max:64', Rule::unique('products', 'sku')->ignore($id)],
            'name_en' => ['required', 'string', 'max:191'],
            'name_kh' => ['nullable', 'string', 'max:191'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'tax_rate_id' => ['nullable', 'integer', 'exists:tax_rates,id'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'alert_qty' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:4096'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}
