<?php

namespace Modules\PasswordGenerator\Providers;

use Illuminate\Support\ServiceProvider;

class PasswordGeneratorServiceProvider extends ServiceProvider
{
    /**
     * مسیر namespace ماژول
     */
    protected string $moduleName = 'PasswordGenerator';
    protected string $moduleNameLower = 'passwordgenerator';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerConfig();
        $this->registerRoutes();
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * ثبت config
     */
    protected function registerConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path($this->moduleNameLower . '.php'),
        ], 'config');

        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php',
            $this->moduleNameLower
        );
    }

    /**
     * ثبت routes
     */
    protected function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }
}