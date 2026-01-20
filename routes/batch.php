<?php

use App\Http\Controllers\BatchController;
use Illuminate\Support\Facades\Route;

Route::prefix('batch')->name('batch.')->group(function () {
    Route::get('/', [BatchController::class, 'index'])->name('index');
    Route::get('/new', [BatchController::class, 'create'])->name('create');
    Route::post('/', [BatchController::class, 'store'])->name('store');
});
