<?php

namespace App\Http\Controllers\API\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Marketplace\MarketplaceIndexRequest;
use App\Http\Resources\Marketplace\ListingCategoryResource;
use App\Http\Resources\Marketplace\ListingTypeResource;
use App\Http\Resources\Marketplace\MarketplaceListingCardResource;
use App\Http\Resources\Marketplace\MarketplaceListingDetailResource;
use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\ListingType;
use App\Services\Marketplace\MarketplaceQueryService;
use App\Traits\ApiResponse;

class MarketplaceController extends Controller
{
    use ApiResponse;

    public function index(MarketplaceIndexRequest $request, MarketplaceQueryService $marketplaceQueryService)
    {
        $listings = $marketplaceQueryService->listings(
            $request->validated(),
            $request->user('sanctum') ?? $request->user()
        );

        $listingTypes = ListingTypeResource::collection(
            ListingType::active()->orderBy('id')->get()
        );

        $categories = ListingCategoryResource::collection(
            ListingCategory::active()->with('type')->orderBy('id')->get()
        );

        return $this->pagination(
            'Marketplace listings retrieved successfully.',
            [
                'listing_types' => $listingTypes,
                'categories' => $categories,
                'listings' => MarketplaceListingCardResource::collection($listings->getCollection()),
            ],
            $listings
        );
    }

    public function show(Listing $listing, MarketplaceQueryService $marketplaceQueryService)
    {
        abort_unless($listing->status === Listing::STATUS_ACTIVE, 404);

        return $this->success(
            'Marketplace listing retrieved successfully.',
            new MarketplaceListingDetailResource(
                $marketplaceQueryService->detail($listing, request()->user('sanctum') ?? request()->user(), request())
            )
        );
    }
}
