<?php

namespace App\Http\Resources\Marketplace;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserReviewResource extends JsonResource
{
    private function formatReviewerName(?string $name): ?string
    {
        if (! $name) {
            return null;
        }
        $parts = explode(' ', trim($name));
        if (count($parts) > 1) {
            $lastName = array_pop($parts);

            return implode(' ', $parts).' '.mb_substr($lastName, 0, 1).'.';
        }

        return $name;
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'description' => $this->description,
            'review_date' => $this->created_at?->toISOString(),
            'review_date_display' => $this->created_at?->format('M Y'),
            'reviewer' => [
                'id' => $this->reviewer?->id,
                'name' => $this->formatReviewerName($this->reviewer?->name),
                'avatar' => $this->reviewer?->avatar_url,
            ],
            'owner' => [
                'id' => $this->reviewee?->id,
                'name' => $this->reviewee?->name,
                'avatar' => $this->reviewee?->avatar_url,
            ],
            'listing' => $this->when($this->relationLoaded('listing'), fn () => [
                'id' => $this->listing?->id,
                'slug' => $this->listing?->slug,
                'title' => $this->listing?->title,
            ]),
        ];
    }
}
