<?php

namespace App\Http\Controllers\API\HomeCMS;

use App\Http\Controllers\Controller;
use App\Http\Resources\Marketplace\ListingCmsCategoryResource;
use App\Models\ListingCategory;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Cache;

class HomeExploreCategoryController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $items = Cache::remember('home_explore_categories', 3600, function () {
            return ListingCategory::active()->with('type')->get();
        });

        return $this->success('Explore categories retrieved successfully', ListingCmsCategoryResource::collection($items));
    }
}
