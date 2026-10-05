<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-settings');
    }

    public function rules(): array
    {
        return [
            'app_name' => ['required', 'string', 'max:100'],
            'app_subtitle' => ['nullable', 'string', 'max:150'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_email' => ['nullable', 'email', 'max:100'],
            'footer_text' => ['nullable', 'string', 'max:255'],
            'primary_color' => ['required', 'string', 'regex:/^#([0-9a-fA-F]{6})$/'],
            'secondary_color' => ['required', 'string', 'regex:/^#([0-9a-fA-F]{6})$/'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,ico,webp', 'max:1024'],
        ];
    }

    public function attributes(): array
    {
        return [
            'app_name' => 'nama aplikasi',
            'primary_color' => 'warna utama',
            'secondary_color' => 'warna sekunder',
        ];
    }
}
