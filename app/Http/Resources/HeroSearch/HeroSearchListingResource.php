<?php

namespace App\Http\Resources\HeroSearch;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HeroSearchListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'thumbnail' => $this->coverMedia?->url,
            'listing_type' => $this->whenLoaded('type', fn () => [
                'id' => $this->type->id,
                'name' => $this->type->name,
            ]),
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ]),
            'business_name' => null,
            'city' => $this->city,
            'country' => $this->country,
            'price' => (float) $this->price,
            'currency' => '$',
            'rating' => 0,
            'review_count' => 0,
            'is_featured' => (bool) $this->is_featured,
            'is_verified' => (bool) $this->is_verified,
        ];
    }
}
