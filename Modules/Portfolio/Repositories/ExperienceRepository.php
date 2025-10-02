<?php

namespace Modules\Portfolio\Repositories;

use Modules\Portfolio\Interfaces\ExperienceRepositoryInterface;
use Modules\Portfolio\Models\Experience;
use Illuminate\Support\Collection;

class ExperienceRepository implements ExperienceRepositoryInterface
{
    public function getByPortfolio(int $portfolioId): Collection
    {
        return Experience::where('portfolio_id', $portfolioId)->get();
    }

    public function create(array $data): Experience
    {
        return Experience::create($data);
    }

    public function update(Experience $experience, array $data): Experience
    {
        $experience->update($data);
        return $experience->fresh();
    }

    public function delete(Experience $experience): bool
    {
        return $experience->delete();
    }
}
