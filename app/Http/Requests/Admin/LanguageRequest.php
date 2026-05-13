<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('language')?->id;

        return [
            'code' => ['required', 'string', 'max:8', Rule::unique('languages', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:64'],
            'native_name' => ['nullable', 'string', 'max:64'],
            'direction' => ['required', 'in:ltr,rtl'],
            'is_default' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_default' => $this->boolean('is_default'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
