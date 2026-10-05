<?php

namespace App\Http\Requests\User;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User
            ? Gate::allows('update', $user)
            : Gate::allows('create', User::class);
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $isEdit = $user instanceof User;

        // Admins may not create/assign the super_admin role.
        $allowedRoles = Role::values();
        if (! $this->user()?->isSuperAdmin()) {
            $allowedRoles = array_values(array_filter($allowedRoles, fn ($r) => $r !== Role::SuperAdmin->value));
        }

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($isEdit ? $user->id : null)],
            'role' => ['required', Rule::in($allowedRoles)],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
            'password' => [$isEdit ? 'nullable' : 'required', 'string', 'min:8'],
        ];
    }

    public function passedValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}
