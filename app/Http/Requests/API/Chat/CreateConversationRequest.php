<?php

namespace App\Http\Requests\API\Chat;

use Illuminate\Foundation\Http\FormRequest;

class CreateConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|string|in:private,group',
            'name' => 'required_if:type,group|string|max:255|nullable',
            'image' => 'nullable|image|max:2048',
            'member_ids' => 'required_if:type,group|array',
            'member_ids.*' => 'integer|exists:users,id|distinct',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required_if' => 'Group name is required.',
            'member_ids.required_if' => 'Please select at least one member for the group.',
            'member_ids.*.exists' => 'One or more selected members do not exist.',
            'member_ids.*.distinct' => 'Duplicate members are not allowed.',
        ];
    }
}
