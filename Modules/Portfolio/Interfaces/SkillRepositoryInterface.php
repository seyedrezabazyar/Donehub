<?php

namespace Modules\Portfolio\Interfaces;

use Modules\Portfolio\Models\Skill;
use Illuminate\Support\Collection;

interface SkillRepositoryInterface
{
    public function getByPortfolio(int $portfolioId): Collection;
    public function create(array $data): Skill;
    public function update(Skill $skill, array $data): Skill;
    public function delete(Skill $skill): bool;
}
