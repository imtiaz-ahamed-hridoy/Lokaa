<?php

namespace App\Http\Controllers\API\HomeCMS;

use App\Http\Controllers\Controller;
use App\Http\Resources\HomeCMS\HomeCtaBannerResource;
use App\Models\HomeCtaBanner;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Cache;

class HomeCtaBannerController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $banner = Cache::remember('home_cta_banner', 3600, function () {
            return HomeCtaBanner::active()->first();
        });

        if (! $banner) {
            return $this->error('CTA banner not found', 404);
        }

        return $this->success('CTA banner retrieved successfully', new HomeCtaBannerResource($banner));
    }
}
