<?php

namespace App\Http\Requests\API\HeroSearch;

use Illuminate\Foundation\Http\FormRequest;

class HeroSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (! $this->filled('keyword') && ! $this->filled('location')) {
                $validator->errors()->add('keyword', 'At least one search parameter (keyword or location) is required.');
                $validator->errors()->add('location', 'At least one search parameter (keyword or location) is required.');
            }
        });
    }
}
