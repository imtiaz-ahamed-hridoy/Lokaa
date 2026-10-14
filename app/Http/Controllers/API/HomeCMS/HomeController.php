<?php

namespace App\Http\Controllers\API\HomeCMS;

use App\Http\Controllers\Controller;
use App\Http\Resources\HomeCMS\HomeCtaBannerResource;
use App\Http\Resources\HomeCMS\HomeFeaturedServicesConfigResource;
use App\Http\Resources\HomeCMS\HomeHeroSectionResource;
use App\Http\Resources\HomeCMS\HomeHotDealsConfigResource;
use App\Http\Resources\HomeCMS\HomeProcessItemResource;
use App\Http\Resources\Marketplace\ListingCategoryResource;
use App\Models\HomeCtaBanner;
use App\Models\HomeFeaturedServicesConfig;
use App\Models\HomeHeroSection;
use App\Models\HomeHotDealsConfig;
use App\Models\HomeProcessItem;
use App\Models\ListingCategory;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $data = Cache::remember('home_page_all', 3600, function () {
            $hero = HomeHeroSection::active()->first();
            $processItems = HomeProcessItem::active()->ordered()->get();
            $exploreCategories = ListingCategory::active()->with('type')->get();
            $hotDealsConfig = HomeHotDealsConfig::active()->first();
            $featuredServicesConfig = HomeFeaturedServicesConfig::active()->first();
            $ctaBanner = HomeCtaBanner::active()->first();

            // Load dynamic listings for config sections
            if ($hotDealsConfig) {
                $hotDealsConfig->loadListingsRelation();
            }
            if ($featuredServicesConfig) {
                $featuredServicesConfig->loadListingsRelation();
            }

            return [
                'hero' => $hero ? new HomeHeroSectionResource($hero) : null,
                'process' => HomeProcessItemResource::collection($processItems),
                'explore_categories' => ListingCategoryResource::collection($exploreCategories),
                'hot_deals' => $hotDealsConfig ? new HomeHotDealsConfigResource($hotDealsConfig) : null,
                'featured_services' => $featuredServicesConfig ? new HomeFeaturedServicesConfigResource($featuredServicesConfig) : null,
                'cta_banner' => $ctaBanner ? new HomeCtaBannerResource($ctaBanner) : null,
            ];
        });

        return $this->success('Home page data retrieved successfully', $data);
    }
}
