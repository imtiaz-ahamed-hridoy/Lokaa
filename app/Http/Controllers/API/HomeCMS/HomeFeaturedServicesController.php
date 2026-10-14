<?php

namespace App\Http\Controllers\API\HomeCMS;

use App\Http\Controllers\Controller;
use App\Http\Resources\HomeCMS\HomeFeaturedServicesConfigResource;
use App\Models\HomeFeaturedServicesConfig;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Cache;

class HomeFeaturedServicesController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $config = Cache::remember('home_featured_services', 3600, function () {
            return HomeFeaturedServicesConfig::active()->first();
        });

        if (! $config) {
            return $this->error('Featured services configuration not found', 404);
        }

        $config->loadListingsRelation();

        return $this->success('Featured services retrieved successfully', new HomeFeaturedServicesConfigResource($config));
    }
}
