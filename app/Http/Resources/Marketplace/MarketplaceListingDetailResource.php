<?php

namespace App\Http\Resources\Marketplace;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketplaceListingDetailResource extends JsonResource
{
    private function formatShortNumber($number): string
    {
        if ($number < 1000) {
            return (string) $number;
        }
        if ($number < 1000000) {
            return round($number / 1000, 1).'k';
        }

        return round($number / 1000000, 1).'m';
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'listing_type' => [
                'id' => $this->type?->id,
                'name' => $this->type?->name,
                'slug' => $this->type?->slug,
            ],
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
                'slug' => $this->category?->slug,
            ],
            'location' => [
                'country' => $this->country,
                'city' => $this->city,
                'area' => $this->area,
                'street_address' => $this->street_address,
                'display' => $this->location_display,
            ],
            'pricing_type' => $this->pricing_mode,
            'price' => $this->price,
            'formatted_price' => $this->price_display,
            'featured' => $this->is_featured,
            'verified' => $this->is_verified,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'primary_image' => $this->coverMedia?->url,
            'images' => ListingMediaResource::collection($this->whenLoaded('media')),
            'total_images' => $this->media_count ?? $this->media->count(),
            'owner' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'avatar' => $this->user?->avatar_url,
                'joined_at' => $this->user?->created_at?->toISOString(),
                'joined_at_display' => $this->user?->created_at?->format('Y'),
                'total_listings' => $this->user?->listings_count ?? 0,
                'average_rating' => $this->user?->received_reviews_avg_rating ? round((float) $this->user->received_reviews_avg_rating, 2) : 0,
                'total_reviews' => $this->user?->received_reviews_count ?? 0,
                'total_reviews_display' => $this->formatShortNumber($this->user?->received_reviews_count ?? 0),
                'reviews' => UserReviewResource::collection($this->whenLoaded('ownerReviews')),
            ],
            'statistics' => [
                'wishlist_count' => $this->favorites_count ?? 0,
                'is_wishlisted' => (bool) ($this->is_wishlisted ?? false),
            ],

        ];

    }
}
