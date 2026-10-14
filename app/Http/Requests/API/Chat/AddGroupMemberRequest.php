<?php

namespace App\Http\Requests\API\Chat;

use Illuminate\Foundation\Http\FormRequest;

class AddGroupMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists' => 'User does not exist.',
        ];
    }
}
