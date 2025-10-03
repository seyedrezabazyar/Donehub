<?php

namespace Modules\Auth\Interfaces;

use Modules\Auth\Models\User;

interface AuthRepositoryInterface
{
    public function createUser(array $data): User;

    public function findByUsername(string $username): ?User;
}
