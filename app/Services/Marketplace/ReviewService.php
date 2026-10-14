<?php

namespace App\Services\Marketplace;

use App\Models\User;
use App\Models\UserReview;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    public function summary(User $user): array
    {
        $reviews = UserReview::query()
            ->where('reviewee_id', $user->id)
            ->select('rating', DB::raw('count(*) as total'))
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $total = (int) $reviews->sum();
        $average = $total > 0
            ? round((float) UserReview::where('reviewee_id', $user->id)->avg('rating'), 2)
            : 0.0;

        return [
            'user_id' => $user->id,
            'average_rating' => $average,
            'total_reviews' => $total,
            'rating_distribution' => collect(range(5, 1))->mapWithKeys(
                fn (int $rating) => [(string) $rating => (int) ($reviews[$rating] ?? 0)]
            )->all(),
        ];
    }
}
