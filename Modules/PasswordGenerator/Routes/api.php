<?php

use Illuminate\Support\Facades\Route;
use Modules\PasswordGenerator\Http\Controllers\PasswordGeneratorController;

Route::prefix('api/password-generator')->group(function () {
    Route::post('/generate', [PasswordGeneratorController::class, 'generate'])
        ->name('password-generator.generate');
});