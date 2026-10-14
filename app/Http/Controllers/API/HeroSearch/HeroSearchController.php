<?php

namespace App\Http\Controllers\API\HeroSearch;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\HeroSearch\HeroSearchRequest;
use App\Http\Resources\HeroSearch\HeroSearchListingResource;
use App\Services\HeroSearch\HeroSearchService;
use App\Traits\ApiResponse;

class HeroSearchController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly HeroSearchService $heroSearchService
    ) {}

    /**
     * Perform a global hero search.
     *
     * GET /api/search?keyword=iphone&location=Texas
     */
    public function __invoke(HeroSearchRequest $request)
    {
        $validated = $request->validated();

        $keyword = $validated['keyword'] ?? null;
        $location = $validated['location'] ?? null;
        $perPage = (int) ($validated['per_page'] ?? 20);

        $results = $this->heroSearchService->search($keyword, $location, $perPage);

        return $this->pagination(
            'Search completed successfully.',
            [
                'filters' => [
                    'keyword' => $keyword,
                    'location' => $location,
                ],
                'listings' => HeroSearchListingResource::collection($results->getCollection()),
            ],
            $results
        );
    }
}
