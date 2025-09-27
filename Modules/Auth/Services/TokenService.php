<?php

namespace Modules\Auth\Services;

use Modules\Auth\Models\User;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TokenService
{
    /**
     * Create access and refresh tokens for user
     */
    public function createTokens(User $user): array
    {
        try {
            Log::info('Starting token creation', ['user_id' => $user->id]);

            // پاکسازی توکن‌های منقضی شده
            $this->cleanupExpiredTokens($user);

            // دریافت تنظیمات lifetime
            $accessLifetimeMinutes = config('auth-module.tokens.access_token_lifetime', 7200) / 60; // تبدیل ثانیه به دقیقه
            $refreshLifetimeMinutes = config('auth-module.tokens.refresh_token_lifetime', 604800) / 60;

            Log::info('Token lifetimes', [
                'access_minutes' => $accessLifetimeMinutes,
                'refresh_minutes' => $refreshLifetimeMinutes
            ]);

            // ایجاد Access Token
            $accessToken = $user->createToken(
                'access-token',
                ['*'], // همه دسترسی‌ها
                now()->addMinutes($accessLifetimeMinutes)
            );

            // ایجاد Refresh Token
            $refreshToken = $user->createToken(
                'refresh-token',
                ['token:refresh'], // فقط دسترسی refresh
                now()->addMinutes($refreshLifetimeMinutes)
            );

            Log::info('Tokens created successfully', [
                'user_id' => $user->id,
                'access_token_id' => $accessToken->accessToken->id,
                'refresh_token_id' => $refreshToken->accessToken->id
            ]);

            return [
                'access_token' => $accessToken->plainTextToken,
                'refresh_token' => $refreshToken->plainTextToken,
                'token_type' => 'Bearer',
                'expires_in' => $accessLifetimeMinutes * 60, // برگرداندن به ثانیه
                'expires_at' => now()->addMinutes($accessLifetimeMinutes)->toISOString(),
                'refresh_expires_in' => $refreshLifetimeMinutes * 60,
                'refresh_expires_at' => now()->addMinutes($refreshLifetimeMinutes)->toISOString()
            ];

        } catch (\Exception $e) {
            Log::error('Token creation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new \Exception('خطا در ایجاد توکن: ' . $e->getMessage());
        }
    }

    /**
     * Refresh tokens using refresh token
     */
    public function refreshTokens(User $user, PersonalAccessToken $currentToken): array
    {
        try {
            // بررسی دسترسی refresh
            if (!in_array('token:refresh', $currentToken->abilities ?? [])) {
                throw new \Exception('این توکن قابلیت refresh ندارد');
            }

            // بررسی انقضای توکن
            if ($currentToken->expires_at && now()->gt($currentToken->expires_at)) {
                throw new \Exception('توکن منقضی شده است');
            }

            // حذف توکن فعلی
            $currentToken->delete();

            // ایجاد توکن‌های جدید
            return $this->createTokens($user);

        } catch (\Exception $e) {
            Log::error('Token refresh failed', [
                'user_id' => $user->id,
                'token_id' => $currentToken->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Revoke all user tokens
     */
    public function revokeAllTokens(User $user): int
    {
        try {
            $count = $user->tokens()->count();
            $user->tokens()->delete();

            Log::info('All tokens revoked', [
                'user_id' => $user->id,
                'count' => $count
            ]);

            return $count;

        } catch (\Exception $e) {
            Log::error('Failed to revoke tokens', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return 0;
        }
    }

    /**
     * Revoke specific token
     */
    public function revokeToken(PersonalAccessToken $token): bool
    {
        try {
            $token->delete();

            Log::info('Token revoked', [
                'token_id' => $token->id
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to revoke token', [
                'token_id' => $token->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Get user tokens
     */
    public function getUserTokens(User $user): \Illuminate\Support\Collection
    {
        try {
            return $user->tokens()
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($token) {
                    return [
                        'id' => $token->id,
                        'name' => $token->name,
                        'abilities' => $token->abilities,
                        'last_used_at' => $token->last_used_at,
                        'expires_at' => $token->expires_at,
                        'created_at' => $token->created_at
                    ];
                });

        } catch (\Exception $e) {
            Log::error('Failed to get user tokens', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return collect([]);
        }
    }

    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens(User $user): int
    {
        try {
            $count = $user->tokens()
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', now())
                ->count();

            if ($count > 0) {
                $user->tokens()
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<', now())
                    ->delete();

                Log::info('Expired tokens cleaned', [
                    'user_id' => $user->id,
                    'count' => $count
                ]);
            }

            return $count;

        } catch (\Exception $e) {
            Log::warning('Failed to cleanup expired tokens', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return 0;
        }
    }

    /**
     * Get token statistics for user
     */
    public function getTokenStats(User $user): array
    {
        try {
            $total = $user->tokens()->count();

            $active = $user->tokens()
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->count();

            $lastUsed = $user->tokens()
                ->whereNotNull('last_used_at')
                ->max('last_used_at');

            return [
                'total' => $total,
                'active' => $active,
                'expired' => $total - $active,
                'last_used' => $lastUsed
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get token stats', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'total' => 0,
                'active' => 0,
                'expired' => 0,
                'last_used' => null
            ];
        }
    }

    /**
     * Validate token
     */
    public function validateToken(string $token): ?User
    {
        try {
            $accessToken = PersonalAccessToken::findToken($token);

            if (!$accessToken) {
                return null;
            }

            // بررسی انقضا
            if ($accessToken->expires_at && now()->gt($accessToken->expires_at)) {
                $accessToken->delete();
                return null;
            }

            // بروزرسانی زمان آخرین استفاده
            $accessToken->forceFill(['last_used_at' => now()])->save();

            return $accessToken->tokenable;

        } catch (\Exception $e) {
            Log::error('Token validation failed', [
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }
}
