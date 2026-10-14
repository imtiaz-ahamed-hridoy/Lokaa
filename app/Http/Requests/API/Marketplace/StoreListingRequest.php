<?php

namespace App\Http\Requests\API\Marketplace;

use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\ListingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Listing::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'listing_type_id' => ['required', 'integer', Rule::exists('listing_types', 'id')->where('status', ListingType::STATUS_ACTIVE)],
            'listing_category_id' => ['required', 'integer', Rule::exists('listing_categories', 'id')->where('status', ListingCategory::STATUS_ACTIVE)],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:10000'],
            'pricing_mode' => ['required', Rule::in([Listing::PRICING_FIXED, Listing::PRICING_HOURLY, Listing::PRICING_DAILY])],
            'price' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'country' => ['nullable', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],
            'area' => ['nullable', 'string', 'max:120'],
            'street_address' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([Listing::STATUS_DRAFT, Listing::STATUS_PENDING])],
            'media' => ['required', 'array', 'min:4'],
            'media.*' => ['required', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:10120'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $type = ListingType::find($this->integer('listing_type_id'));
                $category = ListingCategory::find($this->integer('listing_category_id'));

                if ($type && $category && $category->listing_type_id !== $type->id) {
                    $validator->errors()->add('listing_category_id', 'The selected category does not belong to the selected listing type.');
                }

                if ($type?->slug === 'product' && $this->input('pricing_mode') !== Listing::PRICING_FIXED) {
                    $validator->errors()->add('pricing_mode', 'Product listings must use fixed pricing.');
                }

                if ($type?->slug === 'service' && ! in_array($this->input('pricing_mode'), [Listing::PRICING_HOURLY, Listing::PRICING_DAILY], true)) {
                    $validator->errors()->add('pricing_mode', 'Service listings must use hourly or daily pricing.');
                }

                $totalSize = collect($this->file('media', []))->sum(fn ($file) => $file?->getSize() ?? 0);
                if ($totalSize > 50 * 1024 * 1024) {
                    $validator->errors()->add('media', 'The combined upload size may not exceed 50 MB.');
                }

                $hashes = collect($this->file('media', []))
                    ->filter()
                    ->map(fn ($file) => hash_file('sha256', $file->getRealPath()))
                    ->all();

                if (count($hashes) !== count(array_unique($hashes))) {
                    $validator->errors()->add('media', 'Duplicate images are not allowed.');
                }
            },
        ];
    }
}
