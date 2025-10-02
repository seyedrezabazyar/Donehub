<?php

namespace Modules\Portfolio\Services;

use Modules\Portfolio\Interfaces\PortfolioRepositoryInterface;
use Modules\Portfolio\Models\Portfolio;

class PortfolioService
{
    public function __construct(
        private PortfolioRepositoryInterface $repository
    ) {}

    public function getUserPortfolio(int $userId): ?Portfolio
    {
        return $this->repository->findByUser($userId);
    }

    public function createPortfolio(int $userId, array $data): Portfolio
    {
        $data['user_id'] = $userId;
        return $this->repository->create($data);
    }

    public function updatePortfolio(Portfolio $portfolio, array $data): Portfolio
    {
        return $this->repository->update($portfolio, $data);
    }

    public function deletePortfolio(Portfolio $portfolio): bool
    {
        return $this->repository->delete($portfolio);
    }
}
