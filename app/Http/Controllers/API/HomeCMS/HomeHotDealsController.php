<?php

namespace App\Http\Controllers\API\HomeCMS;

use App\Http\Controllers\Controller;
use App\Http\Resources\HomeCMS\HomeHotDealsConfigResource;
use App\Models\HomeHotDealsConfig;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Cache;

class HomeHotDealsController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $config = Cache::remember('home_hot_deals', 3600, function () {
            return HomeHotDealsConfig::active()->first();
        });

        if (! $config) {
            return $this->error('Hot deals configuration not found', 404);
        }

        $config->loadListingsRelation();

        return $this->success('Hot deals retrieved successfully', new HomeHotDealsConfigResource($config));
    }
}
