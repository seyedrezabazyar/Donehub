<?php

namespace Modules\HtmlToPdf\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class HtmlToPdfServiceProvider extends ServiceProvider
{
    protected $moduleName = 'HtmlToPdf';
    protected $moduleNameLower = 'htmltopdf';

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerConfig();
        $this->registerRoutes();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Singleton service for HTML to PDF conversion
        $this->app->singleton(
            \Modules\HtmlToPdf\Services\HtmlToPdfService::class
        );
    }

    /**
     * Register module routes.
     */
    protected function registerRoutes(): void
    {
        if (file_exists($routes = module_path($this->moduleName, 'routes/api.php'))) {
            Route::prefix('api/pdf')   // مسیر اصلی API
                ->middleware('api')    // middleware API
                ->group($routes);
        }
    }

    /**
     * Register module configuration.
     */
    protected function registerConfig(): void
    {
        $configPath = module_path($this->moduleName, 'Config/config.php');
        if (file_exists($configPath)) {
            $this->publishes([
                $configPath => config_path($this->moduleNameLower . '.php'),
            ], 'htmltopdf-config');

            $this->mergeConfigFrom($configPath, $this->moduleNameLower);
        }
    }
}
