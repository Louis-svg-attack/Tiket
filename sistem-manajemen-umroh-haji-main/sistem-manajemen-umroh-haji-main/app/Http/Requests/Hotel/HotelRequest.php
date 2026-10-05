<?php

namespace App\Http\Requests\Hotel;

use App\Models\Hotel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class HotelRequest extends FormRequest
{
    public function authorize(): bool
    {
        $hotel = $this->route('hotel');

        return $hotel instanceof Hotel
            ? Gate::allows('update', $hotel)
            : Gate::allows('create', Hotel::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'city' => ['required', 'string', 'max:100'],
            'star' => ['required', 'integer', 'min:1', 'max:7'],
            'distance_to_masjid' => ['nullable', 'integer', 'min:0', 'max:50000'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function passedValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}
