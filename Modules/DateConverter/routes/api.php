<?php

use Illuminate\Support\Facades\Route;
use Modules\DateConverter\Http\Controllers\DateConverterController;

Route::get('/convert-date', [DateConverterController::class, 'convert']);
