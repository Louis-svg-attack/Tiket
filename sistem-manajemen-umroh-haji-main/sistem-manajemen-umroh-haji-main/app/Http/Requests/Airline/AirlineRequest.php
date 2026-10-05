<?php

namespace App\Http\Requests\Airline;

use App\Models\Airline;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class AirlineRequest extends FormRequest
{
    public function authorize(): bool
    {
        $airline = $this->route('airline');

        return $airline instanceof Airline
            ? Gate::allows('update', $airline)
            : Gate::allows('create', Airline::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:20'],
            'transit' => ['nullable', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function passedValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}
