<?php

namespace App\Http\Controllers\API\HomeCMS;

use App\Http\Controllers\Controller;
use App\Http\Resources\HomeCMS\HomeProcessItemResource;
use App\Models\HomeProcessItem;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Cache;

class HomeProcessController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $items = Cache::remember('home_process_items', 3600, function () {
            return HomeProcessItem::active()->get();
        });

        return $this->success('Process items retrieved successfully', HomeProcessItemResource::collection($items));
    }
}
