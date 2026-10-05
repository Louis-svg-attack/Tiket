<?php

namespace App\Http\Requests\Commission;

use App\Enums\CommissionStatus;
use App\Models\Commission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateCommissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $commission = $this->route('commission');

        return $commission instanceof Commission
            ? Gate::allows('manage', $commission)
            : false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                CommissionStatus::Approved->value,
                CommissionStatus::Paid->value,
                CommissionStatus::Cancelled->value,
            ])],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
