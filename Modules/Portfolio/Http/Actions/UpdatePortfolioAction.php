<?php

namespace Modules\Portfolio\Http\Actions;

use Illuminate\Http\JsonResponse;
use Modules\Portfolio\Http\Resources\PortfolioResource;
use Modules\Portfolio\Models\Portfolio;
use Modules\Portfolio\Services\PortfolioService;

class UpdatePortfolioAction
{
    public function __construct(
        private PortfolioService $service
    ) {}

    public function execute(Portfolio $portfolio, array $data): JsonResponse
    {
        $portfolio = $this->service->updatePortfolio($portfolio, $data);

        return response()->json([
            'data' => new PortfolioResource($portfolio),
        ]);
    }
}
