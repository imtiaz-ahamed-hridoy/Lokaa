<?php

namespace App\Http\Controllers\API\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Resources\Marketplace\MarketplaceListingCardResource;
use App\Models\Listing;
use App\Models\ListingFavorite;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $listings = Listing::query()
            ->active()
            ->select([
                'id',
                'user_id',
                'listing_type_id',
                'listing_category_id',
                'title',
                'slug',
                'description',
                'pricing_mode',
                'price',
                'country',
                'city',
                'area',
                'status',
                'is_featured',
                'is_verified',
                'created_at',
            ])
            ->whereHas('favorites', fn ($query) => $query->where('user_id', $request->user()->id))
            ->with([
                'type:id,name,slug',
                'category:id,listing_type_id,name,slug',
                'coverMedia:id,listing_id,path',
            ])
            ->withCount(['media', 'views', 'favorites'])
            ->withWishlistStatus($request->user())
            ->latest('id')
            ->paginate($request->integer('per_page', 12))
            ->withQueryString();

        return $this->pagination(
            'Wishlist retrieved successfully.',
            MarketplaceListingCardResource::collection($listings->getCollection()),
            $listings
        );
    }

    public function toggle(Request $request, Listing $listing)
    {
        $favorite = ListingFavorite::where('user_id', $request->user()->id)
            ->where('listing_id', $listing->id)
            ->first();

        if ($favorite) {
            $favorite->delete();

            $wishlistCount = ListingFavorite::where('listing_id', $listing->id)->count();

            return $this->success('Listing removed from wishlist.', [
                'listing_id' => $listing->id,
                'is_wishlisted' => false,
                'wishlist_count' => $wishlistCount,
            ]);
        }

        // Only validate active status when adding to wishlist, not when removing
        if ($listing->status !== Listing::STATUS_ACTIVE) {
            return $this->error('Only active listings can be added to wishlist.', 422);
        }

        ListingFavorite::create([
            'user_id' => $request->user()->id,
            'listing_id' => $listing->id,
        ]);

        $wishlistCount = ListingFavorite::where('listing_id', $listing->id)->count();

        return $this->success('Listing added to wishlist.', [
            'listing_id' => $listing->id,
            'is_wishlisted' => true,
            'wishlist_count' => $wishlistCount,
        ]);
    }

    public function count(Request $request)
    {
        return $this->success('Wishlist count retrieved successfully.', [
            'count' => ListingFavorite::where('user_id', $request->user()->id)
                ->whereHas('listing', fn ($query) => $query->active())
                ->count(),
        ]);
    }
}
