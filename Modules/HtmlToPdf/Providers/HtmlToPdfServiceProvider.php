<?php

namespace Modules\HtmlToPdf\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class HtmlToPdfServiceProvider extends ServiceProvider
{
    protected $moduleName = 'HtmlToPdf';
    protected $moduleNameLower = 'htmltopdf';

    public function boot()
    {
        $this->registerConfig();
        $this->registerRoutes();
    }

    public function register()
    {
        $this->app->singleton(
            \Modules\HtmlToPdf\Services\HtmlToPdfService::class
        );
    }

    protected function registerRoutes()
    {
        Route::prefix('api/pdf')
            ->middleware('api')
            ->group(module_path($this->moduleName, '/routes/api.php'));
    }

    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'htmltopdf-config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'config/config.php'),
            $this->moduleNameLower
        );
    }
}