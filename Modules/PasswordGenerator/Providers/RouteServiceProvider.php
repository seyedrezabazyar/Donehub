<?php

namespace Modules\PasswordGenerator\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * مسیر namespace برای کنترلرها
     */
    protected $namespace = 'Modules\PasswordGenerator\Http\Controllers';

    /**
     * Define the routes for the application.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Define the "api" routes for the module.
     */
    public function map(): void
    {
        $this->mapApiRoutes();
    }

    /**
     * Define the "api" routes for the module.
     */
    protected function mapApiRoutes(): void
    {
        Route::middleware('api')
            ->namespace($this->namespace)
            ->group(__DIR__ . '/../Routes/api.php');
    }
}