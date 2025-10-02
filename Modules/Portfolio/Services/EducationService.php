<?php

namespace Modules\Portfolio\Services;

use Modules\Portfolio\Interfaces\EducationRepositoryInterface;
use Modules\Portfolio\Models\Education;
use Illuminate\Support\Collection;

class EducationService
{
    public function __construct(
        private EducationRepositoryInterface $repository
    ) {}

    public function getPortfolioEducations(int $portfolioId): Collection
    {
        return $this->repository->getByPortfolio($portfolioId);
    }

    public function createEducation(int $portfolioId, array $data): Education
    {
        $data['portfolio_id'] = $portfolioId;
        return $this->repository->create($data);
    }

    public function updateEducation(Education $education, array $data): Education
    {
        return $this->repository->update($education, $data);
    }

    public function deleteEducation(Education $education): bool
    {
        return $this->repository->delete($education);
    }
}
