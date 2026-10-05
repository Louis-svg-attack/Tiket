<?php

namespace App\Http\Requests\Agent;

use App\Models\Agent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $agent = $this->route('agent');

        return $agent instanceof Agent
            ? Gate::allows('update', $agent)
            : Gate::allows('create', Agent::class);
    }

    public function rules(): array
    {
        $agent = $this->route('agent');
        $id = $agent instanceof Agent ? $agent->id : null;

        return [
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('agents', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:1000'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
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
