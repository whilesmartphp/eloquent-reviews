<?php

use Illuminate\Support\Facades\Route;
use Workbench\App\Http\Controllers\ReviewController;

Route::post('/products/{product}/reviews', [ReviewController::class, 'store']);
