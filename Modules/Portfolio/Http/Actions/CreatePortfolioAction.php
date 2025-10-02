<?php

namespace Modules\Portfolio\Http\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Portfolio\Http\Resources\PortfolioResource;
use Modules\Portfolio\Services\PortfolioService;

class CreatePortfolioAction
{
    public function __construct(
        private PortfolioService $service
    ) {}

    public function execute(array $data): JsonResponse
    {
        $userId = Auth::id();

        $portfolio = $this->service->createPortfolio($userId, $data);

        return response()->json([
            'data' => new PortfolioResource($portfolio),
        ], 201);
    }
}
