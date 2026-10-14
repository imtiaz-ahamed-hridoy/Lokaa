<?php

namespace App\Http\Resources\Marketplace;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketplaceListingCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'thumbnail' => $this->coverMedia?->url,
            // 'listing_type' => [
            //     'id' => $this->type?->id,
            //     'name' => $this->type?->name,
            //     'slug' => $this->type?->slug,
            // ],
            // 'category' => [
            //     'id' => $this->category?->id,
            //     'name' => $this->category?->name,
            //     'slug' => $this->category?->slug,
            // ],
            'formatted_price' => $this->price_display,
            'pricing_type' => $this->pricing_mode,
            'location' => [
                'country' => $this->country,
                'city' => $this->city,
                'area' => $this->area,
                'display' => $this->location_display,
            ],
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'is_verified' => $this->is_verified,
            'wishlist_count' => $this->favorites_count ?? 0,
            'is_wishlisted' => (bool) ($this->is_wishlisted ?? false),
        ];
    }
}
