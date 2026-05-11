<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [ProfileController::class, 'show'])->name('show');
    Route::put('/', [ProfileController::class, 'update'])->name('update');
    Route::get('/change-password', [ProfileController::class, 'showChangePassword'])->name('change-password');
    Route::put('/change-password', [ProfileController::class, 'updatePassword'])->name('update-password');
});
