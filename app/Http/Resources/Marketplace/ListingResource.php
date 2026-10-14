<?php

namespace App\Http\Resources\Marketplace;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'listing_type_id' => $this->listing_type_id,
            'listing_category_id' => $this->listing_category_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'pricing_mode' => $this->pricing_mode,
            'price' => $this->price,
            'formatted_price' => $this->price_display,
            'country' => $this->country,
            'city' => $this->city,
            'area' => $this->area,
            'street_address' => $this->street_address,
            'location_display' => $this->location_display,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'is_verified' => $this->is_verified,
            'published_at' => $this->published_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'created_at_display' => $this->created_at?->format('M j, Y'),
            'type' => new ListingTypeResource($this->whenLoaded('type')),
            'category' => new ListingCategoryResource($this->whenLoaded('category')),
            'cover_media' => new ListingMediaResource($this->whenLoaded('coverMedia')),
            'media' => ListingMediaResource::collection($this->whenLoaded('media')),
            'total_images' => $this->whenCounted('media'),
            'views_count' => $this->whenCounted('views'),
            'favorites_count' => $this->whenCounted('favorites'),
            'wishlist_count' => $this->whenCounted('favorites'),
            'is_wishlisted' => (bool) ($this->is_wishlisted ?? false),
        ];
    }
}
