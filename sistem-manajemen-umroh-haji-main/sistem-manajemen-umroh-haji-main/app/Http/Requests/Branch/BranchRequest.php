<?php

namespace App\Http\Requests\Branch;

use App\Models\Branch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class BranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        $branch = $this->route('branch');

        return $branch instanceof Branch
            ? Gate::allows('update', $branch)
            : Gate::allows('create', Branch::class);
    }

    public function rules(): array
    {
        $branch = $this->route('branch');

        return [
            'code' => [
                'required', 'string', 'max:50',
                Rule::unique('branches', 'code')->ignore($branch instanceof Branch ? $branch->id : null),
            ],
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'is_head_office' => ['nullable', 'boolean'],
        ];
    }

    public function passedValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'is_head_office' => $this->boolean('is_head_office'),
        ]);
    }
}
