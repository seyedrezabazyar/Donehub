<?php

namespace Modules\Portfolio\Repositories;

use Modules\Portfolio\Interfaces\SkillRepositoryInterface;
use Modules\Portfolio\Models\Skill;
use Illuminate\Support\Collection;

class SkillRepository implements SkillRepositoryInterface
{
    public function getByPortfolio(int $portfolioId): Collection
    {
        return Skill::where('portfolio_id', $portfolioId)->get();
    }

    public function create(array $data): Skill
    {
        return Skill::create($data);
    }

    public function update(Skill $skill, array $data): Skill
    {
        $skill->update($data);
        return $skill->fresh();
    }

    public function delete(Skill $skill): bool
    {
        return $skill->delete();
    }
}
