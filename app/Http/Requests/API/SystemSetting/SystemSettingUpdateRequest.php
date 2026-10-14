<?php

namespace App\Http\Requests\API\SystemSetting;

use Illuminate\Foundation\Http\FormRequest;

class SystemSettingUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'Admin';
    }

    public function rules(): array
    {
        return [
            'system_name' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:5000'],
            'copyright_text' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'favicon' => ['nullable', 'mimetypes:image/x-icon,image/png,image/vnd.microsoft.icon', 'max:1024'],
        ];
    }

    public function messages(): array
    {
        return [
            'logo.max' => 'Logo must not exceed 2 MB.',
            'favicon.max' => 'Favicon must not exceed 1 MB.',
        ];
    }
}
