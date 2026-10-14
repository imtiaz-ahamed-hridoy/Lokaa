<?php

namespace App\Http\Resources\HomeCMS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeProcessItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'icon_url' => $this->icon_url,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
        ];
    }
}
