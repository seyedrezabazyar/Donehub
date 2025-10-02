<?php

namespace Modules\Portfolio\Repositories;

use Modules\Portfolio\Interfaces\ProjectRepositoryInterface;
use Modules\Portfolio\Models\Project;
use Illuminate\Support\Collection;

class ProjectRepository implements ProjectRepositoryInterface
{
    public function getByPortfolio(int $portfolioId): Collection
    {
        return Project::where('portfolio_id', $portfolioId)->get();
    }

    public function create(array $data): Project
    {
        return Project::create($data);
    }

    public function update(Project $project, array $data): Project
    {
        $project->update($data);
        return $project->fresh();
    }

    public function delete(Project $project): bool
    {
        return $project->delete();
    }
}
