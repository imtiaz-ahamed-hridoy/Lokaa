<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListingCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'Admin';
    }

    public function rules(): array
    {
        $category = $this->route('listing_category') ?? $this->route('listingCategory');
        $categoryId = $category?->id;
        $typeId = (int) $this->input('listing_type_id', $category?->listing_type_id);

        return [
            'listing_type_id' => ['required', 'integer', Rule::exists('listing_types', 'id')],
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'nullable',
                'string',
                'max:140',
                Rule::unique('listing_categories', 'slug')->where('listing_type_id', $typeId)->ignore($categoryId),
            ],
            'image' => ['nullable', 'mimes:jpeg,png,jpg,webp,svg', 'max:10120'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ];
    }
}
