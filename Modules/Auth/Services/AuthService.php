<?php

namespace Modules\Auth\Services;

use Modules\Auth\Contracts\AuthContract;
use Modules\Auth\Models\User;

class AuthService implements AuthContract
{
    public function validateUser(User $user, array $abilities = []): bool
    {
        if ($user->isLocked()) {
            return false;
        }

        if (!empty($abilities)) {
            return $this->checkAbilities($user, $abilities);
        }

        return true;
    }

    public function checkAbilities(User $user, array $abilities): bool
    {
        $token = $user->currentAccessToken();

        if (!$token) {
            return false;
        }

        foreach ($abilities as $ability) {
            if (!$token->can($ability)) {
                return false;
            }
        }

        return true;
    }

    public function getUserPermissions(User $user): array
    {
        $token = $user->currentAccessToken();
        return $token ? $token->abilities : [];
    }
}
