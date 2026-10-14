<?php

namespace App\Http\Resources\Marketplace;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketplaceDashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'listing_type_id' => $this->listing_type_id,
            'listing_category_id' => $this->listing_category_id,
            'title' => $this->title,
            'thumbnail' => $this->coverMedia?->url,
            'formatted_price' => $this->price_display,
            'location_display' => $this->location_display,
            'status' => $this->status,
            'is_verified' => $this->is_verified,
            'created_at_display' => $this->created_at?->format('M j, Y'),

        ];
    }
}
