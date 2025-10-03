<?php

use Illuminate\Support\Facades\Route;
use Modules\Portfolio\Http\Controllers\PortfolioController;
use Modules\Portfolio\Http\Controllers\SkillController;
use Modules\Portfolio\Http\Controllers\ExperienceController;
use Modules\Portfolio\Http\Controllers\EducationController;
use Modules\Portfolio\Http\Controllers\ProjectController;

Route::prefix('portfolio')->group(function () {
    Route::get('/', [PortfolioController::class, 'show']);
    Route::post('/', [PortfolioController::class, 'store']);
    Route::put('/{portfolio}', [PortfolioController::class, 'update']);
    Route::delete('/{portfolio}', [PortfolioController::class, 'destroy']);

    Route::prefix('{portfolio}')->group(function () {
        Route::apiResource('skills', SkillController::class);
        Route::apiResource('experiences', ExperienceController::class);
        Route::apiResource('educations', EducationController::class);
        Route::apiResource('projects', ProjectController::class);
    });
});
