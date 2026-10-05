<?php

namespace App\Http\Requests\BankAccount;

use App\Models\BankAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class BankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $account = $this->route('bank_account');

        return $account instanceof BankAccount
            ? Gate::allows('update', $account)
            : Gate::allows('create', BankAccount::class);
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'bank_name' => ['required', 'string', 'max:150'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_holder' => ['required', 'string', 'max:150'],
            'branch_office' => ['nullable', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function passedValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}
