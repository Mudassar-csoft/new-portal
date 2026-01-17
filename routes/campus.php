<?php

use App\Http\Controllers\CampusController;
use Illuminate\Support\Facades\Route;

Route::prefix('campus')->name('campus.')->group(function () {
    Route::get('/', [CampusController::class, 'index'])->name('index');
    Route::get('/create', [CampusController::class, 'create'])->name('create');
    Route::post('/', [CampusController::class, 'store'])->name('store');
});
