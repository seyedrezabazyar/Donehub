<?php

namespace Modules\DateConverter\Providers;

use Illuminate\Support\ServiceProvider;

class DateConverterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        // اگر بخوای سرویس‌ها رو bind کنی
        $this->app->singleton('calendar.converter', function ($app) {
            return new \Modules\DateConverter\Services\CalendarConverter();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // بارگذاری روت‌ها
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }
}
