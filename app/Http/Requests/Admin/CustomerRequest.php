<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('customer')?->id;

        return [
            'code' => ['required', 'string', 'max:64', Rule::unique('customers', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:191'],
            'phone' => ['nullable', 'string', 'max:64'],
            'email' => ['nullable', 'email', 'max:191'],
            'address' => ['nullable', 'string'],
            'customer_group_id' => ['nullable', 'integer', 'exists:customer_groups,id'],
            'opening_balance' => ['nullable', 'numeric'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}
