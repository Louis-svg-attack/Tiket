<?php

namespace App\Http\Requests\Equipment;

use App\Enums\EquipmentStatus;
use App\Models\EquipmentDistribution;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class EquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $equipment = $this->route('equipment');

        return $equipment instanceof EquipmentDistribution
            ? Gate::allows('update', $equipment)
            : Gate::allows('create', EquipmentDistribution::class);
    }

    public function rules(): array
    {
        return [
            'registration_id' => ['required', 'integer', 'exists:registrations,id'],
            'item' => ['required', 'string', 'max:100'],
            'size' => ['nullable', 'string', 'max:50'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'status' => ['required', Rule::in(EquipmentStatus::values())],
            'handed_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
