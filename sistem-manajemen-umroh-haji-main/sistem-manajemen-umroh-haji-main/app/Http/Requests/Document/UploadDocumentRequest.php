<?php

namespace App\Http\Requests\Document;

use App\Services\FileVault;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UploadDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('upload', $this->route('document'));
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required', 'file', 'max:5120',
                'mimes:'.implode(',', FileVault::ALLOWED_EXTENSIONS),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.mimes' => 'Hanya berkas JPG, PNG, WEBP, atau PDF yang diizinkan.',
            'file.max' => 'Ukuran berkas maksimal 5 MB.',
        ];
    }
}
