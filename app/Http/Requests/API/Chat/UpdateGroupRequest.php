<?php

namespace App\Http\Requests\API\Chat;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'image' => 'nullable|image|max:2048',
        ];
    }
}
