<?php

namespace Modules\Portfolio\Http\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Portfolio\Http\Resources\PortfolioResource;
use Modules\Portfolio\Services\PortfolioService;

class ShowPortfolioAction
{
    public function __construct(
        private PortfolioService $service
    ) {}

    public function execute(): JsonResponse
    {
        $userId = Auth::id();

        $portfolio = $this->service->getUserPortfolio($userId);

        if (!$portfolio) {
            return response()->json([
                'message' => 'Portfolio not found',
            ], 404);
        }

        $portfolio->load(['skills', 'experiences', 'educations', 'projects']);

        return response()->json([
            'data' => new PortfolioResource($portfolio),
        ]);
    }
}
