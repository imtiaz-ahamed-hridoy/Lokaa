<?php

namespace App\Http\Controllers\API\Marketplace;

use App\Http\Controllers\Controller;
use App\Services\Marketplace\MarketplaceDashboardService;
use App\Traits\ApiResponse;

class MarketplaceDashboardController extends Controller
{
    use ApiResponse;

    public function __invoke(MarketplaceDashboardService $dashboardService)
    {
        return $this->success(
            'Dashboard data retrieved successfully',
            $dashboardService->forUser(auth()->user())
        );
    }
}
