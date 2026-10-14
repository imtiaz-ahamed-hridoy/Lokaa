<?php

namespace App\Http\Requests\Admin\HomeCMS;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProcessItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'Admin';
    }

    public function rules(): array
    {
        return [
            'icon' => ['nullable', 'mimes:jpeg,png,jpg,webp,svg', 'max:10120'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ];
    }
}
