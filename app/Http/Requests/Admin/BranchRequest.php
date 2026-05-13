<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $branchId = $this->route('branch')?->id;

        return [
            'code' => ['required', 'string', 'max:64', Rule::unique('branches', 'code')->ignore($branchId)],
            'name_en' => ['required', 'string', 'max:191'],
            'name_kh' => ['nullable', 'string', 'max:191'],
            'phone' => ['nullable', 'string', 'max:64'],
            'email' => ['nullable', 'email', 'max:191'],
            'address' => ['nullable', 'string'],
            'manager_id' => ['nullable', 'integer', 'exists:users,id'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
