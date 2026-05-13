<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TaxRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:64'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_inclusive' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_inclusive' => $this->boolean('is_inclusive'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
