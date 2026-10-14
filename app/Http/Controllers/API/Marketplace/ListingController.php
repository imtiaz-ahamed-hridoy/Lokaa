<?php

namespace App\Http\Controllers\API\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Marketplace\StoreListingRequest;
use App\Http\Requests\API\Marketplace\UpdateListingRequest;
use App\Http\Resources\Marketplace\ListingResource;
use App\Http\Resources\Marketplace\MarketplaceDashboardResource;
use App\Models\Listing;
use App\Services\Marketplace\ListingService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ListingController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in([Listing::STATUS_DRAFT, Listing::STATUS_PENDING, Listing::STATUS_ACTIVE, Listing::STATUS_REJECTED, Listing::STATUS_ARCHIVED])],
            'listing_type_id' => ['nullable', 'integer', 'exists:listing_types,id'],
            'listing_category_id' => ['nullable', 'integer', 'exists:listing_categories,id'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'city' => ['nullable', 'string', 'max:120'],
            'sort' => ['nullable', Rule::in(['latest', 'oldest', 'price_asc', 'price_desc', 'title_asc', 'title_desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Listing::query()
            ->ownedBy($request->user())
            ->with(['type:id,name,slug', 'category:id,name,slug,listing_type_id', 'coverMedia'])
            ->withCount(['media', 'views', 'favorites'])
            ->withWishlistStatus($request->user())
            ->search(['title', 'description', 'city', 'area'])
            ->status($request->status)
            ->when($request->listing_type_id, fn ($query) => $query->where('listing_type_id', $request->listing_type_id))
            ->when($request->listing_category_id, fn ($query) => $query->where('listing_category_id', $request->listing_category_id))
            ->when($request->city, fn ($query) => $query->where('city', 'like', '%'.$request->city.'%'))
            ->when($request->min_price, fn ($query) => $query->where('price', '>=', $request->min_price))
            ->when($request->max_price, fn ($query) => $query->where('price', '<=', $request->max_price))
            ->when($request->date_from, fn ($query) => $query->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($query) => $query->whereDate('created_at', '<=', $request->date_to));

        match ($request->sort) {
            'oldest' => $query->orderBy('id'),
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'title_asc' => $query->orderBy('title'),
            'title_desc' => $query->orderByDesc('title'),
            default => $query->latest('id'),
        };

        $listings = $query->paginate($request->integer('per_page', 10))->withQueryString();

        return $this->pagination('Listings retrieved successfully', MarketplaceDashboardResource::collection($listings->getCollection()), $listings);
    }

    public function store(StoreListingRequest $request, ListingService $listingService)
    {
        $listing = $listingService->create(
            $request->validated(),
            $request->file('media', []),
            $request->user()
        )->loadCount(['media', 'views', 'favorites']);

        return $this->success('Listing created successfully', new ListingResource($listing), 201);
    }

    public function show(Listing $listing)
    {
        $this->authorize('view', $listing);

        $listing->load(['type', 'category', 'media', 'coverMedia'])->loadCount(['media', 'views', 'favorites']);

        $listing->loadWishlistStatus(auth()->user());

        return $this->success('Listing retrieved successfully', new ListingResource($listing));
    }

    public function update(UpdateListingRequest $request, Listing $listing, ListingService $listingService)
    {
        $listing = $listingService->update(
            $listing,
            $request->validated(),
            $request->hasFile('media') ? $request->file('media', []) : null
        )->loadCount(['media', 'views', 'favorites']);

        return $this->success('Listing updated successfully', new ListingResource($listing));
    }

    public function updateStatus(Request $request, Listing $listing, ListingService $listingService)
    {
        $this->authorize('update', $listing);

        $request->validate([
            'status' => ['required', Rule::in([Listing::STATUS_DRAFT, Listing::STATUS_PENDING, Listing::STATUS_ACTIVE, Listing::STATUS_ARCHIVED])],
        ]);

        return $this->success(
            'Listing status updated successfully',
            new ListingResource($listingService->updateStatus($listing, $request->status))
        );
    }

    public function destroy(Listing $listing)
    {
        $this->authorize('delete', $listing);

        $listing->delete();

        return $this->success('Listing deleted successfully');
    }
}
