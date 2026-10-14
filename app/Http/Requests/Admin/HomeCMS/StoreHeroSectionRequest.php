<?php

namespace App\Http\Requests\Admin\HomeCMS;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHeroSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'Admin';
    }

    public function rules(): array
    {
        return [
            'badge' => ['nullable', 'string', 'max:255'],
            'heading' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:2000'],
            'background_image' => ['nullable', 'mimes:jpeg,png,jpg,webp,svg', 'max:10120'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ];
    }
}
