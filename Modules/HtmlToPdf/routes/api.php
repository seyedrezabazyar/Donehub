<?php

use Illuminate\Support\Facades\Route;
use Modules\HtmlToPdf\Http\Controllers\HtmlToPdfController;

Route::post('/convert', [HtmlToPdfController::class, 'convert'])
    ->name('htmltopdf.convert');
