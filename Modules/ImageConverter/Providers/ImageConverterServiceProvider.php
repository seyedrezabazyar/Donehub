<?php

namespace Modules\ImageConverter\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ImageConverterServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'ImageConverter';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'imageconverter';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRoutes();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Register the module's routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        Route::prefix('api/' . $this->moduleNameLower)
            ->middleware('api')
            ->group(module_path($this->moduleName, '/routes/api.php'));
    }
}