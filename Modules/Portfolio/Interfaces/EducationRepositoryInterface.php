<?php

namespace Modules\Portfolio\Interfaces;

use Modules\Portfolio\Models\Education;
use Illuminate\Support\Collection;

interface EducationRepositoryInterface
{
    public function getByPortfolio(int $portfolioId): Collection;
    public function create(array $data): Education;
    public function update(Education $education, array $data): Education;
    public function delete(Education $education): bool;
}
