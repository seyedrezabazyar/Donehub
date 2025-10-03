<?php

use Illuminate\Support\Facades\Route;
use Modules\SlugGenerator\Http\Controllers\SlugGeneratorController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('sluggenerators', SlugGeneratorController::class)->names('sluggenerator');
});
