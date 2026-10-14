<?php

namespace App\Http\Resources\Marketplace;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingCmsCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'listing_type_id' => $this->listing_type_id,
            'name' => $this->name,
            'image_url' => $this->image_url,
            'status' => $this->status,
        ];
    }
}
