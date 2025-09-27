<?php

namespace Modules\Auth\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;

class AuthServiceProvider extends ServiceProvider
{
    protected string $moduleNamespace = 'Modules\Auth\Http\Controllers';

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/auth.php', 'auth-module');

        $this->app->singleton(\Modules\Auth\Services\TokenService::class);
        $this->app->singleton(\Modules\Auth\Services\PhoneService::class);
        $this->app->singleton(\Modules\Auth\Services\OTPService::class, function ($app) {
            return new \Modules\Auth\Services\OTPService(
                $app->make(\Modules\Auth\Services\PhoneService::class)
            );
        });

        $this->registerFortifyActions();

        // Register Auth Contract
        $this->app->singleton(\Modules\Auth\Contracts\AuthContract::class,
            \Modules\Auth\Services\AuthService::class);
    }

    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerMiddleware();
        $this->configureFortify();
        $this->configureSanctum();
        $this->configureRateLimiting();
        $this->registerValidators();
        $this->registerEventListeners();
    }

    protected function registerEventListeners(): void
    {
    }

    protected function registerRoutes(): void
    {
        if ($this->app->routesAreCached()) return;

        Route::middleware(['api', \Modules\Auth\Http\Middleware\SecurityHeaders::class])
            ->prefix('api/v1')
            ->namespace($this->moduleNamespace)
            ->group(__DIR__ . '/../Routes/api.php');
    }

    protected function registerMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        }
    }

    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];

        $router->aliasMiddleware('ability', \Modules\Auth\Http\Middleware\EnsureTokenHasAbility::class);
        $router->aliasMiddleware('security.headers', \Modules\Auth\Http\Middleware\SecurityHeaders::class);
        $router->aliasMiddleware('role', \Modules\Auth\Http\Middleware\CheckRole::class);

        $router->pushMiddlewareToGroup('api', \Modules\Auth\Http\Middleware\SecurityHeaders::class);
    }

    protected function configureFortify(): void
    {
        config([
            'auth.providers.users.model' => \Modules\Auth\Models\User::class,
            'auth.guards.api.driver' => 'sanctum'
        ]);

        Fortify::ignoreRoutes();
    }

    protected function configureSanctum(): void
    {
        $accessLifetime = config('auth-module.tokens.access_token_lifetime', 7200);
        config(['sanctum.expiration' => $accessLifetime / 60]);
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('identifier') ?: $request->ip());
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perHour(3)->by($request->ip());
        });

        RateLimiter::for('otp-send', function (Request $request) {
            return [
                Limit::perMinute(1)->by($request->input('identifier') ?: $request->ip()),
                Limit::perHour(3)->by($request->input('identifier') ?: $request->ip())
            ];
        });

        RateLimiter::for('otp-verify', function (Request $request) {
            return Limit::perMinute(3)->by($request->input('identifier') ?: $request->ip());
        });

        RateLimiter::for('refresh', function (Request $request) {
            return Limit::perMinute(10)->by(optional($request->user())->id ?: $request->ip());
        });
    }

    protected function registerFortifyActions(): void
    {
        $this->app->singleton(
            \Laravel\Fortify\Contracts\CreatesNewUsers::class,
            \Modules\Auth\Actions\CreateNewUser::class
        );

        $this->app->singleton(
            \Laravel\Fortify\Contracts\ResetsUserPasswords::class,
            \Modules\Auth\Actions\ResetUserPassword::class
        );
    }

    protected function registerValidators(): void
    {
        $this->app['validator']->extend('iranian_phone', function ($attribute, $value) {
            try {
                $phoneService = app(\Modules\Auth\Services\PhoneService::class);
                return $phoneService->isIranian($phoneService->normalize($value));
            } catch (\Exception $e) {
                return false;
            }
        });

        $this->app['validator']->extend('valid_phone', function ($attribute, $value) {
            return app(\Modules\Auth\Services\PhoneService::class)->isValid($value);
        });
    }
}
