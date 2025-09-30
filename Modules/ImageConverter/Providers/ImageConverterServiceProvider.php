<?php

namespace Modules\ImageConverter\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ImageConverterServiceProvider extends ServiceProvider
{
    protected $moduleName = 'ImageConverter';
    protected $moduleNameLower = 'imageconverter';

    public function boot()
    {
        $this->registerConfig();
        $this->registerRoutes();
        $this->registerCommands();
    }

    public function register()
    {
        $this->app->singleton(
            \Modules\ImageConverter\Services\ImageConverterService::class
        );
    }

    protected function registerRoutes()
    {
        Route::prefix('api/image')
            ->middleware('api')
            ->group(module_path($this->moduleName, '/routes/api.php'));
    }

    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'imageconverter-config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'config/config.php'),
            $this->moduleNameLower
        );
    }

    public function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\ImageConverter\Console\CleanupOldImages::class,
            ]);
        }
    }
}