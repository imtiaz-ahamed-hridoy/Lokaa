<?php

namespace App\Http\Requests\Admin\HomeCMS;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHotDealsConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'Admin';
    }

    public function rules(): array
    {
        return [
            'section_title' => ['nullable', 'string', 'max:255'],
            'listing_type_id' => ['nullable', 'integer', Rule::exists('listing_types', 'id')],
            'number_of_listings' => ['nullable', 'integer', 'min:1', 'max:100'],
            'listing_source' => ['required', Rule::in(['newest', 'featured', 'trending', 'manual', 'random'])],
            'listing_category_id' => ['nullable', 'integer', Rule::exists('listing_categories', 'id')],
            'manual_listing_ids' => ['nullable', 'array'],
            'manual_listing_ids.*' => ['integer', Rule::exists('listings', 'id')],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ];
    }
}
