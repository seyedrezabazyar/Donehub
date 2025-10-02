<?php

namespace Modules\Portfolio\Services;

use Modules\Portfolio\Interfaces\ProjectRepositoryInterface;
use Modules\Portfolio\Models\Project;
use Illuminate\Support\Collection;

class ProjectService
{
    public function __construct(
        private ProjectRepositoryInterface $repository
    ) {}

    public function getPortfolioProjects(int $portfolioId): Collection
    {
        return $this->repository->getByPortfolio($portfolioId);
    }

    public function createProject(int $portfolioId, array $data): Project
    {
        $data['portfolio_id'] = $portfolioId;
        return $this->repository->create($data);
    }

    public function updateProject(Project $project, array $data): Project
    {
        return $this->repository->update($project, $data);
    }

    public function deleteProject(Project $project): bool
    {
        return $this->repository->delete($project);
    }
}
