<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListingTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'Admin';
    }

    public function rules(): array
    {
        $id = $this->route('listing_type')?->id ?? $this->route('listingType')?->id;

        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:140', Rule::unique('listing_types', 'slug')->ignore($id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'mimes:jpeg,png,jpg,webp,svg', 'max:10120'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ];
    }
}
