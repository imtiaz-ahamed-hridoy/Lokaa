<?php

namespace App\Http\Resources\HomeCMS;

use App\Http\Resources\Marketplace\MarketplaceListingCardResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeFeaturedServicesConfigResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'listing_source' => $this->listing_source,
            'listings' => MarketplaceListingCardResource::collection($this->whenLoaded('listings')),
        ];
    }
}
