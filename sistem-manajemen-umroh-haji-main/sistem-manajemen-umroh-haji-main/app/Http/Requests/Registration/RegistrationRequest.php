<?php

namespace App\Http\Requests\Registration;

use App\Models\Registration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class RegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $registration = $this->route('registration');

        return $registration instanceof Registration
            ? Gate::allows('update', $registration)
            : Gate::allows('create', Registration::class);
    }

    public function rules(): array
    {
        return [
            'pilgrim_id' => ['required', 'integer', 'exists:pilgrims,id'],
            'package_id' => ['required', 'integer', 'exists:packages,id'],
            'guide_id' => ['nullable', 'integer', 'exists:guides,id'],
            'registered_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
