<?php

namespace App\Http\Requests\Document;

use App\Enums\DocumentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

class VerifyDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('verify', $this->route('document'));
    }

    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(DocumentStatus::class)],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
