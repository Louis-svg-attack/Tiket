<?php

namespace App\Http\Requests\Guide;

use App\Enums\Gender;
use App\Models\Guide;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class GuideRequest extends FormRequest
{
    public function authorize(): bool
    {
        $guide = $this->route('guide');

        return $guide instanceof Guide
            ? Gate::allows('update', $guide)
            : Gate::allows('create', Guide::class);
    }

    public function rules(): array
    {
        $guide = $this->route('guide');
        $id = $guide instanceof Guide ? $guide->id : null;

        return [
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('guides', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:150'],
            'gender' => ['required', new Enum(Gender::class)],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'specialization' => ['nullable', 'string', 'max:150'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'create_account' => ['nullable', 'boolean'],
            'account_email' => ['nullable', 'email', 'max:150'],
        ];
    }

    public function passedValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'create_account' => $this->boolean('create_account'),
        ]);
    }
}
