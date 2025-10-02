<?php

namespace Modules\Portfolio\Interfaces;

use Modules\Portfolio\Models\Portfolio;

interface PortfolioRepositoryInterface
{
    public function findByUser(int $userId): ?Portfolio;
    public function create(array $data): Portfolio;
    public function update(Portfolio $portfolio, array $data): Portfolio;
    public function delete(Portfolio $portfolio): bool;
}
