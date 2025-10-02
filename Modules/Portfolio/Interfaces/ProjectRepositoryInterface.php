<?php

namespace Modules\Portfolio\Interfaces;

use Modules\Portfolio\Models\Project;
use Illuminate\Support\Collection;

interface ProjectRepositoryInterface
{
    public function getByPortfolio(int $portfolioId): Collection;
    public function create(array $data): Project;
    public function update(Project $project, array $data): Project;
    public function delete(Project $project): bool;
}
