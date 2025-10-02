<?php

namespace Modules\Portfolio\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Portfolio\Http\Requests\ProjectRequest;
use Modules\Portfolio\Http\Resources\ProjectResource;
use Modules\Portfolio\Models\Portfolio;
use Modules\Portfolio\Models\Project;
use Modules\Portfolio\Services\ProjectService;

class ProjectController
{
    public function __construct(
        private ProjectService $service
    ) {}

    public function index(Portfolio $portfolio): JsonResponse
    {
        $projects = $this->service->getPortfolioProjects($portfolio->id);

        return response()->json([
            'data' => ProjectResource::collection($projects),
        ]);
    }

    public function store(ProjectRequest $request, Portfolio $portfolio): JsonResponse
    {
        $project = $this->service->createProject($portfolio->id, $request->validated());

        return response()->json([
            'data' => new ProjectResource($project),
        ], 201);
    }

    public function update(ProjectRequest $request, Portfolio $portfolio, Project $project): JsonResponse
    {
        $project = $this->service->updateProject($project, $request->validated());

        return response()->json([
            'data' => new ProjectResource($project),
        ]);
    }

    public function destroy(Portfolio $portfolio, Project $project): JsonResponse
    {
        $this->service->deleteProject($project);

        return response()->json(null, 204);
    }
}
