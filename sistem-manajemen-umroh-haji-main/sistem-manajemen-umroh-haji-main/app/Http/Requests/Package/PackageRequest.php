<?php

namespace App\Http\Requests\Package;

use App\Enums\PackageStatus;
use App\Enums\PackageType;
use App\Models\Package;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class PackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $package = $this->route('package');

        return $package instanceof Package
            ? Gate::allows('update', $package)
            : Gate::allows('create', Package::class);
    }

    public function rules(): array
    {
        $package = $this->route('package');
        $id = $package instanceof Package ? $package->id : null;

        return [
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('packages', 'code')->ignore($id)],
            'type' => ['required', new Enum(PackageType::class)],
            'price' => ['required', 'numeric', 'min:0'],
            'down_payment' => ['nullable', 'numeric', 'min:0'],
            'quota' => ['required', 'integer', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:365'],
            'departure_date' => ['nullable', 'date'],
            'departure_city' => ['nullable', 'string', 'max:100'],
            'hotel_makkah_id' => ['nullable', 'integer', 'exists:hotels,id'],
            'hotel_madinah_id' => ['nullable', 'integer', 'exists:hotels,id'],
            'airline_id' => ['nullable', 'integer', 'exists:airlines,id'],
            'facilities_included' => ['nullable', 'string'],
            'facilities_excluded' => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', new Enum(PackageStatus::class)],
        ];
    }
}
