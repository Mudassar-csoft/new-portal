<?php

use App\Http\Controllers\CampusController;
use Illuminate\Support\Facades\Route;

Route::prefix('campus')->name('campus.')->group(function () {
    Route::get('/', [CampusController::class, 'index'])->middleware('permission:campus.view')->name('index');
    Route::get('/count/{abbr}', [CampusController::class, 'countPreview'])->middleware('permission:campus.view')->name('count');
    Route::get('/create', [CampusController::class, 'create'])->middleware('permission:campus.create')->name('create');
    Route::get('/{campus}/edit', [CampusController::class, 'edit'])->middleware(['permission:campus.update', 'admin'])->name('edit');
    Route::post('/', [CampusController::class, 'store'])->middleware('permission:campus.create')->name('store');
    Route::put('/{campus}', [CampusController::class, 'update'])->middleware(['permission:campus.update', 'admin'])->name('update');
    Route::patch('/{campus}/toggle-status', [CampusController::class, 'toggleStatus'])->middleware('permission:campus.update')->name('toggle-status');
    Route::delete('/{campus}', [CampusController::class, 'destroy'])->middleware('permission:campus.delete')->name('destroy');
});
