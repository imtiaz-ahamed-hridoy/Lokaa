<?php

namespace App\Http\Resources\HomeCMS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeHeroSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'badge' => $this->badge,
            'heading' => $this->heading,
            'description' => $this->description,
            'background_image_url' => $this->background_image_url,
            'status' => $this->status,
        ];
    }
}
