<?php

namespace Modules\Portfolio\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Portfolio\Interfaces\EducationRepositoryInterface;
use Modules\Portfolio\Interfaces\ExperienceRepositoryInterface;
use Modules\Portfolio\Interfaces\PortfolioRepositoryInterface;
use Modules\Portfolio\Interfaces\ProjectRepositoryInterface;
use Modules\Portfolio\Interfaces\SkillRepositoryInterface;
use Modules\Portfolio\Repositories\EducationRepository;
use Modules\Portfolio\Repositories\ExperienceRepository;
use Modules\Portfolio\Repositories\PortfolioRepository;
use Modules\Portfolio\Repositories\ProjectRepository;
use Modules\Portfolio\Repositories\SkillRepository;

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

        $this->app->bind(PortfolioRepositoryInterface::class, PortfolioRepository::class);
        $this->app->bind(SkillRepositoryInterface::class, SkillRepository::class);
        $this->app->bind(ExperienceRepositoryInterface::class, ExperienceRepository::class);
        $this->app->bind(EducationRepositoryInterface::class, EducationRepository::class);
        $this->app->bind(ProjectRepositoryInterface::class, ProjectRepository::class);
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
