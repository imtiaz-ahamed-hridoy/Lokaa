<?php

namespace App\Http\Controllers\API\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Marketplace\StoreReviewRequest;
use App\Http\Requests\API\Marketplace\UpdateReviewRequest;
use App\Http\Resources\Marketplace\ReviewSummaryResource;
use App\Http\Resources\Marketplace\UserReviewResource;
use App\Models\User;
use App\Models\UserReview;
use App\Services\Marketplace\ReviewService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ApiResponse;

    public function index(Request $request, User $user)
    {
        $reviews = UserReview::query()
            ->where('reviewee_id', $user->id)
            ->with(['reviewer:id,name,avatar', 'listing:id,slug,title'])
            ->latest('id')
            ->paginate($request->integer('per_page', 10))
            ->withQueryString();

        return $this->pagination(
            'Reviews retrieved successfully.',
            UserReviewResource::collection($reviews->getCollection()),
            $reviews
        );
    }

    public function store(StoreReviewRequest $request)
    {
        $review = UserReview::create([
            'reviewer_id' => $request->user()->id,
            'reviewee_id' => $request->ownerId(),
            'listing_id' => $request->integer('listing_id') ?: null,
            'rating' => $request->integer('rating'),
            'description' => (string) $request->string('description'),
        ])->load(['reviewer:id,name,avatar', 'reviewee:id,name,avatar', 'listing:id,slug,title']);

        return $this->success('Review submitted successfully.', new UserReviewResource($review), 201);
    }

    public function update(UpdateReviewRequest $request, UserReview $review)
    {
        $review->update($request->validated());

        return $this->success(
            'Review updated successfully.',
            new UserReviewResource($review->load(['reviewer:id,name,avatar', 'reviewee:id,name,avatar', 'listing:id,slug,title']))
        );
    }

    public function destroy(Request $request, UserReview $review)
    {
        abort_unless($request->user()?->role === 'Admin' || $review->reviewer_id === $request->user()?->id, 403);

        $review->delete();

        return $this->success('Review deleted successfully.');
    }

    public function summary(User $user, ReviewService $reviewService)
    {
        return $this->success(
            'Review summary retrieved successfully.',
            new ReviewSummaryResource($reviewService->summary($user))
        );
    }
}
