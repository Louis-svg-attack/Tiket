<?php

namespace App\Http\Requests\Pilgrim;

use App\Enums\Gender;
use App\Models\Pilgrim;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class PilgrimRequest extends FormRequest
{
    public function authorize(): bool
    {
        $pilgrim = $this->route('pilgrim');

        return $pilgrim instanceof Pilgrim
            ? Gate::allows('update', $pilgrim)
            : Gate::allows('create', Pilgrim::class);
    }

    public function rules(): array
    {
        $pilgrim = $this->route('pilgrim');
        $id = $pilgrim instanceof Pilgrim ? $pilgrim->id : null;

        return [
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'agent_id' => ['nullable', 'integer', 'exists:agents,id'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('pilgrims', 'code')->ignore($id)],
            // Data Pribadi
            'name' => ['required', 'string', 'max:150'],
            'gender' => ['required', new Enum(Gender::class)],
            'nik' => ['nullable', 'string', 'max:32'],
            'birth_place' => ['nullable', 'string', 'max:150'],
            'birth_date' => ['nullable', 'date'],
            'blood_type' => ['nullable', 'string', 'max:5'],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            // Paspor
            'passport_number' => ['nullable', 'string', 'max:50'],
            'passport_issued_at' => ['nullable', 'date'],
            'passport_expired_at' => ['nullable', 'date'],
            'passport_issued_place' => ['nullable', 'string', 'max:150'],
            // Mahram & Darurat
            'mahram_name' => ['nullable', 'string', 'max:150'],
            'mahram_relation' => ['nullable', 'string', 'max:100'],
            'emergency_contact_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            // Kesehatan
            'health_notes' => ['nullable', 'string', 'max:1000'],
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
