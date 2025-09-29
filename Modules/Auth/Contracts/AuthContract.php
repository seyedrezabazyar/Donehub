<?php

namespace Modules\Auth\Contracts;

use Modules\Auth\Models\User;

interface AuthContract
{
    public function validateUser(User $user, array $abilities = []): bool;
    public function checkAbilities(User $user, array $abilities): bool;
    public function getUserPermissions(User $user): array;
}
