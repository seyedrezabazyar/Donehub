<?php

namespace Modules\Portfolio\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Portfolio\Http\Requests\ExperienceRequest;
use Modules\Portfolio\Http\Resources\ExperienceResource;
use Modules\Portfolio\Models\Portfolio;
use Modules\Portfolio\Models\Experience;
use Modules\Portfolio\Services\ExperienceService;

class ExperienceController
{
    public function __construct(
        private ExperienceService $service
    ) {}

    public function index(Portfolio $portfolio): JsonResponse
    {
        $experiences = $this->service->getPortfolioExperiences($portfolio->id);

        return response()->json([
            'data' => ExperienceResource::collection($experiences),
        ]);
    }

    public function store(ExperienceRequest $request, Portfolio $portfolio): JsonResponse
    {
        $experience = $this->service->createExperience($portfolio->id, $request->validated());

        return response()->json([
            'data' => new ExperienceResource($experience),
        ], 201);
    }

    public function update(ExperienceRequest $request, Portfolio $portfolio, Experience $experience): JsonResponse
    {
        $experience = $this->service->updateExperience($experience, $request->validated());

        return response()->json([
            'data' => new ExperienceResource($experience),
        ]);
    }

    public function destroy(Portfolio $portfolio, Experience $experience): JsonResponse
    {
        $this->service->deleteExperience($experience);

        return response()->json(null, 204);
    }
}
