<?php

namespace App\Services\Marketplace;

use App\Models\Listing;
use App\Models\ListingView;
use App\Models\User;
use App\Models\UserReview;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarketplaceQueryService
{
    public function listings(array $filters, ?User $user = null): LengthAwarePaginator
    {
        $query = Listing::query()
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
            ->with([
                'type:id,name,slug',
                'category:id,listing_type_id,name,slug',
                'coverMedia:id,listing_id,path',
            ])
            ->withCount(['media', 'views', 'favorites'])
            ->marketplaceSearch($filters['search'] ?? null)
            ->when($filters['listing_type_id'] ?? null, fn ($query, $id) => $query->where('listing_type_id', $id))
            ->when($filters['listing_type'] ?? null, fn ($query, $type) => $query->whereHas('type', fn ($query) => $query->where('slug', $type)->orWhere('name', $type)))
            ->when($filters['category_id'] ?? null, fn ($query, $id) => $query->where('listing_category_id', $id))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->whereHas('category', fn ($query) => $query->where('slug', $category)->orWhere('name', $category)))
            ->when($filters['min_price'] ?? null, fn ($query, $price) => $query->where('price', '>=', $price))
            ->when($filters['max_price'] ?? null, fn ($query, $price) => $query->where('price', '<=', $price))
            ->when($filters['country'] ?? null, fn ($query, $country) => $query->where('country', 'like', "%{$country}%"))
            ->when($filters['city'] ?? null, fn ($query, $city) => $query->where('city', 'like', "%{$city}%"))
            ->when($filters['area'] ?? null, fn ($query, $area) => $query->where('area', 'like', "%{$area}%"))
            ->when($filters['location'] ?? null, function ($query, $location) {
                $query->where(function ($query) use ($location) {
                    $query->where('country', 'like', "%{$location}%")
                        ->orWhere('city', 'like', "%{$location}%")
                        ->orWhere('area', 'like', "%{$location}%");
                });
            });

        $query->withWishlistStatus($user);

        match ($filters['sort'] ?? 'newest') {
            'oldest' => $query->orderBy('id'),
            'lowest_price' => $query->orderBy('price'),
            'highest_price' => $query->orderByDesc('price'),
            'most_viewed' => $query->orderByDesc('views_count')->latest('id'),
            'most_favorited' => $query->orderByDesc('favorites_count')->latest('id'),
            default => $query->latest('id'),
        };

        return $query->paginate((int) ($filters['per_page'] ?? 12))->withQueryString();
    }

    public function detail(Listing $listing, ?User $user = null, ?Request $request = null): Listing
    {
        $listing->load([
            'type:id,name,slug',
            'category:id,listing_type_id,name,slug',
            'media:id,listing_id,path,created_at',
            'coverMedia:id,listing_id,path',
            'user:id,name,avatar,created_at',
            'ownerReviews' => fn ($query) => $query
                ->with('reviewer:id,name,avatar')
                ->latest('id')
                ->limit(5),
        ])->loadCount(['media', 'views', 'favorites']);

        $listing->user?->loadCount(['listings', 'receivedReviews'])
            ->loadAvg('receivedReviews', 'rating');

        $reviewBreakdown = UserReview::query()
            ->where('reviewee_id', $listing->user_id)
            ->select('rating', DB::raw('count(*) as total'))
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $listing->setAttribute('review_summary', [
            'average_rating' => $listing->user?->received_reviews_avg_rating ? round((float) $listing->user->received_reviews_avg_rating, 2) : 0,
            'total_reviews' => (int) ($listing->user?->received_reviews_count ?? 0),
            'rating_breakdown' => collect(range(5, 1))->mapWithKeys(
                fn (int $rating) => [(string) $rating => (int) ($reviewBreakdown[$rating] ?? 0)]
            )->all(),
        ]);

        $listing->loadWishlistStatus($user);

        $related = Listing::query()
            ->active()
            ->whereKeyNot($listing->id)
            ->where(function ($query) use ($listing): void {
                $query->where('listing_category_id', $listing->listing_category_id)
                    ->orWhere('listing_type_id', $listing->listing_type_id);
            })
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
            ->with([
                'type:id,name,slug',
                'category:id,listing_type_id,name,slug',
                'coverMedia:id,listing_id,path',
            ])
            ->withCount(['media', 'views', 'favorites'])
            ->latest('id')
            ->limit(4);

        $related->withWishlistStatus($user);

        $listing->setRelation('relatedListings', $related->get());

        if ($request) {
            ListingView::create([
                'listing_id' => $listing->id,
                'user_id' => $user?->id,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ]);
        }

        return $listing;
    }
}
