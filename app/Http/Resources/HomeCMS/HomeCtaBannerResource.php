<?php

namespace App\Http\Resources\HomeCMS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeCtaBannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'background_image' => $this->background_image_url,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
        ];
    }
}
