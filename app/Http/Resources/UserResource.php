<?php

namespace App\Http\Resources;

use App\Traits\ImagePathTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    use ImagePathTrait;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'agree_to_terms' => $this->agree_to_terms,
            'email_verified_at' => $this->email_verified_at,
            'phone' => $this->phone,
            'location' => $this->location,
            'avatar' => $this->avatar,
            'avatar_full_url' => $this->fullImageUrl($this->avatar),
            'role' => $this->role,
            'status' => $this->status,
            'listings' => $this->listings()->count(),
            'Joined_at' => $this->created_at?->format('Y') ?? '',
        ];
    }
}
