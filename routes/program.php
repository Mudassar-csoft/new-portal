<?php

use App\Http\Controllers\ProgramController;
use Illuminate\Support\Facades\Route;

Route::prefix('program')->name('program.')->group(function () {
    Route::get('/', [ProgramController::class, 'index'])->name('index');
    Route::get('/new', [ProgramController::class, 'create'])->name('create');
    Route::get('/{program}/outline', [ProgramController::class, 'outline'])->name('outline');
    Route::get('/{program}/edit', [ProgramController::class, 'edit'])->name('edit');
    Route::post('/', [ProgramController::class, 'store'])->name('store');
    Route::put('/{program}', [ProgramController::class, 'update'])->name('update');
    Route::patch('/{program}/toggle-status', [ProgramController::class, 'toggleStatus'])->name('toggle-status');
    Route::delete('/{program}', [ProgramController::class, 'destroy'])->name('destroy');
});
