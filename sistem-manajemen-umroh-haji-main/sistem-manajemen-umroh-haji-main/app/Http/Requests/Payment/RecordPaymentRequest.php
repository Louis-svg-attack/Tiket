<?php

namespace App\Http\Requests\Payment;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class RecordPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $payment = $this->route('payment');

        return $payment instanceof Payment && Gate::allows('record', $payment);
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['nullable', 'string', 'in:transfer,cash,edc'],
            'bank_account_id' => ['nullable', 'integer', 'exists:bank_accounts,id'],
            'paid_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
