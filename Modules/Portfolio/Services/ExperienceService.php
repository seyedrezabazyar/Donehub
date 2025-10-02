<?php

namespace Modules\Portfolio\Services;

use Modules\Portfolio\Interfaces\ExperienceRepositoryInterface;
use Modules\Portfolio\Models\Experience;
use Illuminate\Support\Collection;

class ExperienceService
{
    public function __construct(
        private ExperienceRepositoryInterface $repository
    ) {}

    public function getPortfolioExperiences(int $portfolioId): Collection
    {
        return $this->repository->getByPortfolio($portfolioId);
    }

    public function createExperience(int $portfolioId, array $data): Experience
    {
        $data['portfolio_id'] = $portfolioId;
        return $this->repository->create($data);
    }

    public function updateExperience(Experience $experience, array $data): Experience
    {
        return $this->repository->update($experience, $data);
    }

    public function deleteExperience(Experience $experience): bool
    {
        return $this->repository->delete($experience);
    }
}
