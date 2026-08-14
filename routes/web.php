<?php

declare(strict_types=1);

use App\Http\Controllers\SharedImageController;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::dashboard')
    ->name('dashboard');

Route::get('/shared-images', [SharedImageController::class, 'index'])
    ->name('shared-images.index');
Route::post('/shared-images', [SharedImageController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('shared-images.store');
Route::get('/shared-images/file/{asset}', [SharedImageController::class, 'show'])
    ->middleware('signed')
    ->name('shared-images.show');
