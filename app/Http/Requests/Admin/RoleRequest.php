<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('role')?->id;

        return [
            'name' => ['required', 'string', 'max:64', Rule::unique('roles', 'name')->ignore($id)],
            'slug' => ['nullable', 'string', 'max:64', Rule::unique('roles', 'slug')->ignore($id)],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ];
    }
}
