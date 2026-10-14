<?php

namespace App\Http\Requests\API\Chat;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'conversation_id' => 'required|integer|exists:conversations,id',
            'message' => 'required_without:attachments|string|nullable',
            'type' => 'sometimes|string|in:text',
            'attachments' => 'sometimes|array',
            'attachments.*.file' => 'required_with:attachments|string',
            'attachments.*.mime_type' => 'required_with:attachments|string|max:100',
            'attachments.*.size' => 'required_with:attachments|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'message.required_without' => 'Message content or attachment is required.',
            'conversation_id.exists' => 'Conversation does not exist.',
        ];
    }
}
