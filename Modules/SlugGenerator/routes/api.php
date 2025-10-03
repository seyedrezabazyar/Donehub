<?php

use Illuminate\Support\Facades\Route;
use Modules\SlugGenerator\Http\Controllers\SlugGeneratorController;

Route::prefix('v1')->group(function () {
    Route::post('/sluggenerators', [SlugGeneratorController::class, 'store']);
});

