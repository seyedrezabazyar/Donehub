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

    /**
     * ثبت‌نام کاربر جدید و صدور توکن
     *
     * @param array $data
     * @return array
     */
    public function register(array $data): array
    {
        $user = $this->repository->createUser([
            'username' => $data['username'],
            'password' => $data['password'],
        ]);

        // صدور توکن برای کاربر جدید
        $tokenName = config('auth.token_name', 'auth_token');
        $token = $user->createToken($tokenName)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * ورود کاربر و صدور توکن
     *
     * @param array $credentials
     * @return array
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        $user = $this->repository->findByUsername($credentials['username']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        // حذف توکن‌های قبلی (اختیاری - برای امنیت بیشتر)
        // $user->tokens()->delete();

        $tokenName = config('auth.token_name', 'auth_token');
        $token = $user->createToken($tokenName)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * خروج کاربر و حذف توکن
     *
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        // حذف توکن فعلی
        $user->currentAccessToken()->delete();

        // یا حذف همه توکن‌ها:
        // $user->tokens()->delete();
    }
}
