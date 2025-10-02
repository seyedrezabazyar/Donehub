<?php

namespace Modules\Portfolio\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Portfolio\Http\Actions\CreatePortfolioAction;
use Modules\Portfolio\Http\Actions\ShowPortfolioAction;
use Modules\Portfolio\Http\Actions\UpdatePortfolioAction;
use Modules\Portfolio\Http\Actions\DeletePortfolioAction;
use Modules\Portfolio\Http\Requests\PortfolioRequest;
use Modules\Portfolio\Models\Portfolio;

class PortfolioController
{
    public function show(ShowPortfolioAction $action): JsonResponse
    {
        return $action->execute();
    }

    public function store(PortfolioRequest $request, CreatePortfolioAction $action): JsonResponse
    {
        return $action->execute($request->validated());
    }

    public function update(PortfolioRequest $request, Portfolio $portfolio, UpdatePortfolioAction $action): JsonResponse
    {
        return $action->execute($portfolio, $request->validated());
    }

    public function destroy(Portfolio $portfolio, DeletePortfolioAction $action): JsonResponse
    {
        return $action->execute($portfolio);
    }
}
