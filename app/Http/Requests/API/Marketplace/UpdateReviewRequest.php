<?php

namespace App\Http\Requests\API\Marketplace;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $review = $this->route('review');

        return $this->user()?->role === 'Admin' || $review?->reviewer_id === $this->user()?->id;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'description' => ['required', 'string', 'min:3', 'max:2000'],
        ];
    }
}
