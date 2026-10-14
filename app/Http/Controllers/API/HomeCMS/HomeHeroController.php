<?php

namespace App\Http\Controllers\API\HomeCMS;

use App\Http\Controllers\Controller;
use App\Http\Resources\HomeCMS\HomeHeroSectionResource;
use App\Models\HomeHeroSection;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Cache;

class HomeHeroController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $hero = Cache::remember('home_hero_section', 3600, function () {
            return HomeHeroSection::active()->first();
        });

        if (! $hero) {
            return $this->error('Hero section not found', 404);
        }

        return $this->success('Hero section retrieved successfully', new HomeHeroSectionResource($hero));
    }
}
