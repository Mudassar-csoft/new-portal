<?php

use App\Http\Controllers\CampusController;
use Illuminate\Support\Facades\Route;

Route::prefix('campus')->name('campus.')->group(function () {
    Route::get('/', [CampusController::class, 'index'])->name('index');
    Route::get('/count/{abbr}', [CampusController::class, 'countPreview'])->name('count');
    Route::get('/create', [CampusController::class, 'create'])->name('create');
    Route::get('/{campus}/edit', [CampusController::class, 'edit'])->name('edit');
    Route::post('/', [CampusController::class, 'store'])->name('store');
    Route::put('/{campus}', [CampusController::class, 'update'])->name('update');
    Route::patch('/{campus}/toggle-status', [CampusController::class, 'toggleStatus'])->name('toggle-status');
    Route::delete('/{campus}', [CampusController::class, 'destroy'])->name('destroy');
});
