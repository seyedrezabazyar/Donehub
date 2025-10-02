<?php

namespace Modules\Portfolio\Repositories;

use Modules\Portfolio\Interfaces\PortfolioRepositoryInterface;
use Modules\Portfolio\Models\Portfolio;

class PortfolioRepository implements PortfolioRepositoryInterface
{
    public function findByUser(int $userId): ?Portfolio
    {
        return Portfolio::where('user_id', $userId)->first();
    }

    public function create(array $data): Portfolio
    {
        return Portfolio::create($data);
    }

    public function update(Portfolio $portfolio, array $data): Portfolio
    {
        $portfolio->update($data);
        return $portfolio->fresh();
    }

    public function delete(Portfolio $portfolio): bool
    {
        return $portfolio->delete();
    }
}
