<?php

namespace App\Http\Requests\Announcement;

use App\Enums\AnnouncementAudience;
use App\Models\Announcement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $announcement = $this->route('announcement');

        return $announcement instanceof Announcement
            ? Gate::allows('update', $announcement)
            : Gate::allows('create', Announcement::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:5000'],
            'audience' => ['required', Rule::in(AnnouncementAudience::values())],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'package_id' => ['nullable', 'integer', 'exists:packages,id'],
            'is_published' => ['nullable', 'boolean'],
            'publish_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:publish_at'],
        ];
    }

    public function passedValidation(): void
    {
        $this->merge(['is_published' => $this->boolean('is_published')]);
    }
}
