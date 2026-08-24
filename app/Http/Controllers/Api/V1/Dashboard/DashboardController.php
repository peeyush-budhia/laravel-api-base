<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\BaseApiController;
use App\Http\Resources\Api\V1\DashboardResource;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;

final class DashboardController extends BaseApiController
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    /**
     * Get dashboard data.
     */
    public function __invoke(): JsonResponse
    {
        return $this->success(
            new DashboardResource(
                $this->dashboardService->getDashboard(),
            ),
            __('dashboard.retrieved'),
        );
    }
}
