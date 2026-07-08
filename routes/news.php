<?php

use App\Http\Controllers\NewsController;
use Illuminate\Support\Facades\Route;

Route::prefix('news')->name('news.')->group(function () {
    Route::get('/', [NewsController::class, 'index'])->middleware('permission:news.view')->name('index');
    Route::get('/create', [NewsController::class, 'create'])->middleware('permission:news.create')->name('create');
    Route::get('/{news}/edit', [NewsController::class, 'edit'])->middleware(['permission:news.update', 'admin'])->name('edit');
    Route::post('/', [NewsController::class, 'store'])->middleware('permission:news.create')->name('store');
    Route::put('/{news}', [NewsController::class, 'update'])->middleware(['permission:news.update', 'admin'])->name('update');
    Route::patch('/{news}/toggle-status', [NewsController::class, 'toggleStatus'])->middleware('permission:news.update')->name('toggle-status');
    Route::delete('/{news}', [NewsController::class, 'destroy'])->middleware('permission:news.delete')->name('destroy');
});
