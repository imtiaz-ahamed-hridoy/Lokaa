<?php

namespace App\Http\Resources\Marketplace;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->resource['user_id'],
            'average_rating' => $this->resource['average_rating'],
            'total_reviews' => $this->resource['total_reviews'],
            'rating_distribution' => $this->resource['rating_distribution'],
        ];
    }
}
