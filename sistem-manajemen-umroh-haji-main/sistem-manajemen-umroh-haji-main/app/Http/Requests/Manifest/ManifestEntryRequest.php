<?php

namespace App\Http\Requests\Manifest;

use App\Models\Package;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ManifestEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $package = $this->route('package');

        return $package instanceof Package && Gate::allows('view', $package);
    }

    public function rules(): array
    {
        return [
            'rows' => ['nullable', 'array'],
            'rows.*.room_number' => ['nullable', 'string', 'max:50'],
            'rows.*.room_type' => ['nullable', 'in:quad,triple,double'],
            'rows.*.bus_number' => ['nullable', 'string', 'max:50'],
            'rows.*.seat_number' => ['nullable', 'string', 'max:50'],
            'rows.*.mahram_group' => ['nullable', 'string', 'max:50'],
        ];
    }
}
