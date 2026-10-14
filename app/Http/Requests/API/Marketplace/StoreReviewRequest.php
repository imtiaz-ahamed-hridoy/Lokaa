<?php

namespace App\Http\Requests\API\Marketplace;

use App\Models\Listing;
use App\Models\UserReview;
use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'reviewee_id' => ['required_without:listing_id', 'integer', 'exists:users,id'],
            'listing_id' => ['nullable', 'integer', 'exists:listings,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'description' => ['required', 'string', 'min:3', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $listing = $this->filled('listing_id')
                    ? Listing::select(['id', 'user_id'])->find($this->integer('listing_id'))
                    : null;

                $revieweeId = $listing?->user_id ?: $this->integer('reviewee_id');

                if ($revieweeId === $this->user()->id) {
                    $validator->errors()->add('reviewee_id', 'You cannot review yourself.');
                }

                if (UserReview::where('reviewer_id', $this->user()->id)->where('reviewee_id', $revieweeId)->exists()) {
                    $validator->errors()->add('reviewee_id', 'You have already reviewed this owner.');
                }
            },
        ];
    }

    public function ownerId(): int
    {
        if ($this->filled('listing_id')) {
            return (int) Listing::whereKey($this->integer('listing_id'))->value('user_id');
        }

        return $this->integer('reviewee_id');
    }
}
