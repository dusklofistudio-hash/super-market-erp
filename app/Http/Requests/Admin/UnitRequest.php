<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_en' => ['required', 'string', 'max:191'],
            'name_kh' => ['nullable', 'string', 'max:191'],
            'short_name' => ['required', 'string', 'max:16'],
            'base_unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'conversion_factor' => ['required', 'numeric', 'min:0.0001'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}
