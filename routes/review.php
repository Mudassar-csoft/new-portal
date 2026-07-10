<?php

use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('reviews')->name('reviews.')->group(function () {
    Route::get('/', [ReviewController::class, 'index'])->middleware('permission:review.view')->name('index');
    Route::get('/create', [ReviewController::class, 'create'])->middleware('permission:review.create')->name('create');
    Route::get('/{review}/edit', [ReviewController::class, 'edit'])->middleware(['permission:review.update', 'admin'])->name('edit');
    Route::post('/', [ReviewController::class, 'store'])->middleware('permission:review.create')->name('store');
    Route::put('/{review}', [ReviewController::class, 'update'])->middleware(['permission:review.update', 'admin'])->name('update');
    Route::patch('/{review}/toggle-status', [ReviewController::class, 'toggleStatus'])->middleware('permission:review.update')->name('toggle-status');
    Route::patch('/{review}/toggle-featured', [ReviewController::class, 'toggleFeatured'])->middleware('permission:review.update')->name('toggle-featured');
    Route::delete('/{review}', [ReviewController::class, 'destroy'])->middleware('permission:review.delete')->name('destroy');
});
