<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('customer_group')?->id;

        return [
            'name' => ['required', 'string', 'max:191', Rule::unique('customer_groups', 'name')->ignore($id)],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}
