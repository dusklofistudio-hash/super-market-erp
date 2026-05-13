<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('category')?->id;

        return [
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'name_en' => ['required', 'string', 'max:191'],
            'name_kh' => ['nullable', 'string', 'max:191'],
            'slug' => ['nullable', 'string', 'max:191', Rule::unique('categories', 'slug')->ignore($id)],
            'image' => ['nullable', 'image', 'max:4096'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}
