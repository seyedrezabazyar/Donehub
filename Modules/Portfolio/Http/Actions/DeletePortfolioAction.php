<?php

namespace Modules\Portfolio\Http\Actions;

use Illuminate\Http\JsonResponse;
use Modules\Portfolio\Models\Portfolio;
use Modules\Portfolio\Services\PortfolioService;

class DeletePortfolioAction
{
    public function __construct(
        private PortfolioService $service
    ) {}

    public function execute(Portfolio $portfolio): JsonResponse
    {
        $this->service->deletePortfolio($portfolio);

        return response()->json(null, 204);
    }
}
