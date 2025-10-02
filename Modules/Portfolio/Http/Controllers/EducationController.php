<?php

namespace Modules\Portfolio\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Portfolio\Http\Requests\EducationRequest;
use Modules\Portfolio\Http\Resources\EducationResource;
use Modules\Portfolio\Models\Portfolio;
use Modules\Portfolio\Models\Education;
use Modules\Portfolio\Services\EducationService;

class EducationController
{
    public function __construct(
        private EducationService $service
    ) {}

    public function index(Portfolio $portfolio): JsonResponse
    {
        $educations = $this->service->getPortfolioEducations($portfolio->id);

        return response()->json([
            'data' => EducationResource::collection($educations),
        ]);
    }

    public function store(EducationRequest $request, Portfolio $portfolio): JsonResponse
    {
        $education = $this->service->createEducation($portfolio->id, $request->validated());

        return response()->json([
            'data' => new EducationResource($education),
        ], 201);
    }

    public function update(EducationRequest $request, Portfolio $portfolio, Education $education): JsonResponse
    {
        $education = $this->service->updateEducation($education, $request->validated());

        return response()->json([
            'data' => new EducationResource($education),
        ]);
    }

    public function destroy(Portfolio $portfolio, Education $education): JsonResponse
    {
        $this->service->deleteEducation($education);

        return response()->json(null, 204);
    }
}
