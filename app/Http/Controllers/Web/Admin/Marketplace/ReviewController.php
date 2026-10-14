<?php

namespace App\Http\Controllers\Web\Admin\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\UserReview;
use App\Services\Marketplace\ReviewService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function index(Request $request, ReviewService $reviewService): Response
    {
        $reviews = UserReview::query()
            ->with(['reviewer:id,name,email', 'reviewee:id,name,email', 'listing:id,title,slug'])
            ->search(['description'])
            ->when($request->rating, fn ($query) => $query->where('rating', $request->rating))
            ->latest('id')
            ->paginateData();

        return Inertia::render('marketplace/reviews/index', [
            'reviews' => $reviews,
            'analytics' => [
                'total_reviews' => UserReview::count(),
                'average_rating' => round((float) UserReview::avg('rating'), 2),
                'rating_distribution' => collect(range(5, 1))->mapWithKeys(
                    fn (int $rating) => [(string) $rating => UserReview::where('rating', $rating)->count()]
                ),
            ],
        ]);
    }

    public function show(UserReview $review): Response
    {
        return Inertia::render('marketplace/reviews/show', [
            'review' => $review->load(['reviewer:id,name,email,avatar', 'reviewee:id,name,email,avatar', 'listing:id,title,slug']),
        ]);
    }

    public function destroy(UserReview $review)
    {
        $review->delete();

        return redirect()->route('review.index')->with('success', 'Review deleted successfully!');
    }
}
