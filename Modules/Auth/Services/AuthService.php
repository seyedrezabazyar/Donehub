<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Interfaces\AuthRepositoryInterface;
use Modules\Auth\Models\User;

class AuthService
{
    public function __construct(
        private AuthRepositoryInterface $repository
    ) {}

    public function register(array $data): User
    {
        return $this->repository->createUser([
            'username' => $data['username'],
            'password' => $data['password'],
        ]);
    }

    public function login(array $credentials): array
    {
        $user = $this->repository->findByUsername($credentials['username']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
