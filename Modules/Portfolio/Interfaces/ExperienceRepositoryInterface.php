<?php

namespace Modules\Portfolio\Interfaces;

use Modules\Portfolio\Models\Experience;
use Illuminate\Support\Collection;

interface ExperienceRepositoryInterface
{
    public function getByPortfolio(int $portfolioId): Collection;
    public function create(array $data): Experience;
    public function update(Experience $experience, array $data): Experience;
    public function delete(Experience $experience): bool;
}
