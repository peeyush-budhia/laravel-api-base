<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\BaseApiController;
use App\Http\Resources\Api\V1\DashboardResource;
use App\Models\User;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DashboardController extends BaseApiController
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    /**
     * Get dashboard data.
     */
    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->success(
            new DashboardResource(
                $this->dashboardService->getDashboard($user),
            ),
            __('dashboard.retrieved'),
        );
    }
}
