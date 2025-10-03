<?php

use Illuminate\Support\Facades\Route;
use Modules\SlugGenerator\Http\Controllers\SlugGeneratorController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('sluggenerators', SlugGeneratorController::class)->names('sluggenerator');
});
