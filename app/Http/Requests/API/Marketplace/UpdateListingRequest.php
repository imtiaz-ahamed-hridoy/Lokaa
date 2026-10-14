<?php

namespace App\Http\Requests\API\Marketplace;

use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\ListingType;
use Illuminate\Validation\Rule;

class UpdateListingRequest extends StoreListingRequest
{
    public function authorize(): bool
    {
        $listing = $this->route('listing');

        return $listing && ($this->user()?->can('update', $listing) ?? false);
    }

    public function rules(): array
    {
        return [
            'listing_type_id' => ['sometimes', 'integer', Rule::exists('listing_types', 'id')->where('status', 'Active')],
            'listing_category_id' => ['sometimes', 'integer', Rule::exists('listing_categories', 'id')->where('status', 'Active')],
            'title' => ['sometimes', 'string', 'max:160'],
            'description' => ['sometimes', 'string', 'max:10000'],
            'pricing_mode' => ['sometimes', Rule::in([Listing::PRICING_FIXED, Listing::PRICING_HOURLY, Listing::PRICING_DAILY])],
            'price' => ['sometimes', 'numeric', 'min:0.01', 'max:99999999.99'],
            'country' => ['nullable', 'string', 'max:120'],
            'city' => ['sometimes', 'string', 'max:120'],
            'area' => ['nullable', 'string', 'max:120'],
            'street_address' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([Listing::STATUS_DRAFT, Listing::STATUS_PENDING])],
            'media' => ['nullable', 'array', 'min:4'],
            'media.*' => ['required', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:10120'],

        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $req = request();

                $typeId = $req->input('listing_type_id') ? (int) $req->input('listing_type_id') : null;
                $catId = $req->input('listing_category_id') ? (int) $req->input('listing_category_id') : null;

                // Validate category belongs to type (only if both provided)
                if ($typeId && $catId) {
                    $category = ListingCategory::find($catId);
                    if ($category && $category->listing_type_id !== $typeId) {
                        $validator->errors()->add('listing_category_id', 'The selected category does not belong to the selected listing type.');
                    }
                }

                // Validate pricing mode against type (only if pricing_mode and type_id provided)
                $pricingMode = $req->input('pricing_mode');
                if ($typeId && $pricingMode) {
                    $type = ListingType::find($typeId);
                    if ($type?->slug === 'product' && $pricingMode !== Listing::PRICING_FIXED) {
                        $validator->errors()->add('pricing_mode', 'Product listings must use fixed pricing.');
                    }
                    if ($type?->slug === 'service' && ! in_array($pricingMode, [Listing::PRICING_HOURLY, Listing::PRICING_DAILY], true)) {
                        $validator->errors()->add('pricing_mode', 'Service listings must use hourly or daily pricing.');
                    }
                }

                // File size and duplicate image check (only if media is uploaded)
                if ($req->hasFile('media')) {
                    $totalSize = collect($req->file('media', []))->sum(fn ($file) => $file?->getSize() ?? 0);
                    if ($totalSize > 50 * 1024 * 1024) {
                        $validator->errors()->add('media', 'The combined upload size may not exceed 50 MB.');
                    }

                    $hashes = collect($req->file('media', []))
                        ->filter()
                        ->map(fn ($file) => hash_file('sha256', $file->getRealPath()))
                        ->all();

                    if (count($hashes) !== count(array_unique($hashes))) {
                        $validator->errors()->add('media', 'Duplicate images are not allowed.');
                    }
                }
            },
        ];
    }
}
