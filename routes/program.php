<?php

use App\Http\Controllers\ProgramController;
use Illuminate\Support\Facades\Route;

Route::prefix('program')->name('program.')->group(function () {
    Route::get('/', [ProgramController::class, 'index'])->middleware('permission:program.view')->name('index');
    Route::get('/new', [ProgramController::class, 'create'])->middleware('permission:program.create')->name('create');
    Route::get('/{program}/outline', [ProgramController::class, 'outline'])->middleware('permission:program.view')->name('outline');
    Route::get('/{program}/edit', [ProgramController::class, 'edit'])->middleware('permission:program.update')->name('edit');
    Route::post('/', [ProgramController::class, 'store'])->middleware('permission:program.create')->name('store');
    Route::put('/{program}', [ProgramController::class, 'update'])->middleware('permission:program.update')->name('update');
    Route::patch('/{program}/toggle-status', [ProgramController::class, 'toggleStatus'])->middleware('permission:program.update')->name('toggle-status');
    Route::delete('/{program}', [ProgramController::class, 'destroy'])->middleware('permission:program.delete')->name('destroy');
});
