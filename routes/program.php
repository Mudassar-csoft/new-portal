<?php

use App\Http\Controllers\ProgramController;
use Illuminate\Support\Facades\Route;

Route::prefix('program')->name('program.')->group(function () {
    Route::get('/', [ProgramController::class, 'index'])->name('index');
    Route::get('/new', [ProgramController::class, 'create'])->name('create');
    Route::post('/', [ProgramController::class, 'store'])->name('store');
});
