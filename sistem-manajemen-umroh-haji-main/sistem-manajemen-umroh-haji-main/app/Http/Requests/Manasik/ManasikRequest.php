<?php

namespace App\Http\Requests\Manasik;

use App\Models\ManasikSchedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ManasikRequest extends FormRequest
{
    public function authorize(): bool
    {
        $manasik = $this->route('manasik');

        return $manasik instanceof ManasikSchedule
            ? Gate::allows('update', $manasik)
            : Gate::allows('create', ManasikSchedule::class);
    }

    public function rules(): array
    {
        return [
            'package_id' => ['nullable', 'integer', 'exists:packages,id'],
            'guide_id' => ['nullable', 'integer', 'exists:guides,id'],
            'title' => ['required', 'string', 'max:150'],
            'date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
