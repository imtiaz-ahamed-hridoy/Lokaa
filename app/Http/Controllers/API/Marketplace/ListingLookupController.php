<?php

namespace App\Http\Controllers\API\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Resources\Marketplace\ListingCategoryResource;
use App\Http\Resources\Marketplace\ListingTypeResource;
use App\Models\ListingCategory;
use App\Models\ListingType;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ListingLookupController extends Controller
{
    use ApiResponse;

    public function types()
    {
        $types = ListingType::active()
            ->orderBy('id')
            ->get();

        return $this->success('Listing types retrieved successfully', ListingTypeResource::collection($types));
    }

    public function categories(Request $request)
    {
        $request->validate([
            'listing_type_id' => ['nullable', 'integer', 'exists:listing_types,id'],
        ]);

        $categories = ListingCategory::active()
            ->when($request->listing_type_id, fn ($query) => $query->where('listing_type_id', $request->listing_type_id))
            ->orderBy('id')
            ->get();

        return $this->success('Listing categories retrieved successfully', ListingCategoryResource::collection($categories));
    }
}
