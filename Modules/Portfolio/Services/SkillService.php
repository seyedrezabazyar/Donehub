<?php

namespace Modules\Portfolio\Services;

use Modules\Portfolio\Interfaces\SkillRepositoryInterface;
use Modules\Portfolio\Models\Skill;
use Illuminate\Support\Collection;

class SkillService
{
    public function __construct(
        private SkillRepositoryInterface $repository
    ) {}

    public function getPortfolioSkills(int $portfolioId): Collection
    {
        return $this->repository->getByPortfolio($portfolioId);
    }

    public function createSkill(int $portfolioId, array $data): Skill
    {
        $data['portfolio_id'] = $portfolioId;
        return $this->repository->create($data);
    }

    public function updateSkill(Skill $skill, array $data): Skill
    {
        return $this->repository->update($skill, $data);
    }

    public function deleteSkill(Skill $skill): bool
    {
        return $this->repository->delete($skill);
    }
}
