<?php

namespace Modules\Portfolio\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Portfolio\Http\Requests\SkillRequest;
use Modules\Portfolio\Http\Resources\SkillResource;
use Modules\Portfolio\Models\Portfolio;
use Modules\Portfolio\Models\Skill;
use Modules\Portfolio\Services\SkillService;

class SkillController
{
    public function __construct(
        private SkillService $service
    ) {}

    public function index(Portfolio $portfolio): JsonResponse
    {
        $skills = $this->service->getPortfolioSkills($portfolio->id);

        return response()->json([
            'data' => SkillResource::collection($skills),
        ]);
    }

    public function store(SkillRequest $request, Portfolio $portfolio): JsonResponse
    {
        $skill = $this->service->createSkill($portfolio->id, $request->validated());

        return response()->json([
            'data' => new SkillResource($skill),
        ], 201);
    }

    public function update(SkillRequest $request, Portfolio $portfolio, Skill $skill): JsonResponse
    {
        $skill = $this->service->updateSkill($skill, $request->validated());

        return response()->json([
            'data' => new SkillResource($skill),
        ]);
    }

    public function destroy(Portfolio $portfolio, Skill $skill): JsonResponse
    {
        $this->service->deleteSkill($skill);

        return response()->json(null, 204);
    }
}
