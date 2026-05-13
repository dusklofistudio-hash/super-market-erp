<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('brand')?->id;

        return [
            'name' => ['required', 'string', 'max:191', Rule::unique('brands', 'name')->ignore($id)],
            'slug' => ['nullable', 'string', 'max:191', Rule::unique('brands', 'slug')->ignore($id)],
            'logo' => ['nullable', 'image', 'max:4096'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}
