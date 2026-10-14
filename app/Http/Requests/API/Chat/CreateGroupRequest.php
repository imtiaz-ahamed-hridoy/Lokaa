<?php

namespace App\Http\Requests\API\Chat;

use Illuminate\Foundation\Http\FormRequest;

class CreateGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'integer|exists:users,id|distinct',
        ];
    }

    public function messages(): array
    {
        return [
            'member_ids.required' => 'Please select at least one member.',
            'member_ids.*.exists' => 'One or more selected members do not exist.',
            'member_ids.*.distinct' => 'Duplicate members are not allowed.',
        ];
    }
}
