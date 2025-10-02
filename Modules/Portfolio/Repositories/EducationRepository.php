<?php

namespace Modules\Portfolio\Repositories;

use Modules\Portfolio\Interfaces\EducationRepositoryInterface;
use Modules\Portfolio\Models\Education;
use Illuminate\Support\Collection;

class EducationRepository implements EducationRepositoryInterface
{
    public function getByPortfolio(int $portfolioId): Collection
    {
        return Education::where('portfolio_id', $portfolioId)->get();
    }

    public function create(array $data): Education
    {
        return Education::create($data);
    }

    public function update(Education $education, array $data): Education
    {
        $education->update($data);
        return $education->fresh();
    }

    public function delete(Education $education): bool
    {
        return $education->delete();
    }
}
