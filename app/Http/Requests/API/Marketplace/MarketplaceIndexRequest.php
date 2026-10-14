<?php

namespace App\Http\Requests\API\Marketplace;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MarketplaceIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'listing_type' => ['nullable', 'string', 'max:120'],
            'listing_type_id' => ['nullable', 'integer', 'exists:listing_types,id'],
            'category' => ['nullable', 'string', 'max:120'],
            'category_id' => ['nullable', 'integer', 'exists:listing_categories,id'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'country' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'area' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:120'],
            'sort' => ['nullable', Rule::in(['newest', 'oldest', 'lowest_price', 'highest_price', 'most_viewed', 'most_favorited'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
