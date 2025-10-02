<?php

namespace Modules\Portfolio\Providers;

use Illuminate\Support\ServiceProvider;

class PortfolioServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/config.php', 'portfolio'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');

        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'portfolio');
    }
}
