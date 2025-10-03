<?php

namespace Modules\Auth\Repositories;

use Modules\Auth\Interfaces\AuthRepositoryInterface;
use Modules\Auth\Models\User;

class AuthRepository implements AuthRepositoryInterface
{
    public function createUser(array $data): User
    {
        return User::create($data);
    }

    public function findByUsername(string $username): ?User
    {
        return User::where('username', $username)->first();
    }
}
