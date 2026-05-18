<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('user')?->id;
        $isCreate = $id === null;

        return [
            'name' => ['required', 'string', 'max:191'],
            'username' => ['required', 'string', 'max:64', Rule::unique('users', 'username')->ignore($id)],
            'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($id)],
            'phone' => ['nullable', 'string', 'max:64'],
            'password' => [$isCreate ? 'required' : 'nullable', 'string', 'min:6'],
            'default_branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'is_active' => ['boolean'],
            'is_super_admin' => ['boolean'],
            'locale' => ['nullable', 'string', 'max:8', Rule::in(['en', 'kh'])],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'roles' => ['array'],
            'roles.*' => ['integer', 'exists:roles,id'],
            'branches' => ['array'],
            'branches.*' => ['integer', 'exists:branches,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'is_super_admin' => $this->boolean('is_super_admin'),
        ]);
    }
}
