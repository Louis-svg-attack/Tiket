<?php

namespace App\Http\Requests\Payment;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class GenerateBillsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', Payment::class);
    }

    public function rules(): array
    {
        return [
            'installments' => ['required', 'integer', 'min:1', 'max:36'],
            'dp_amount' => ['nullable', 'numeric', 'min:0'],
            'first_due' => ['nullable', 'date'],
        ];
    }
}
