<?php

use App\Http\Controllers\BatchController;
use App\Http\Controllers\BatchTimetableController;
use Illuminate\Support\Facades\Route;

Route::prefix('batch')->name('batch.')->group(function () {
    Route::get('/', [BatchController::class, 'index'])->name('index');
    Route::get('/new', [BatchController::class, 'create'])->name('create');
    Route::post('/', [BatchController::class, 'store'])->name('store');
    Route::get('/timetable', [BatchTimetableController::class, 'index'])->name('timetable.index');
    Route::post('/timetable', [BatchTimetableController::class, 'store'])->name('timetable.store');
    Route::patch('/timetable/{timetable}', [BatchTimetableController::class, 'update'])->name('timetable.update');
    Route::delete('/timetable/{timetable}', [BatchTimetableController::class, 'destroy'])->name('timetable.destroy');
    Route::get('/{batch}/edit', [BatchController::class, 'edit'])->name('edit');
    Route::put('/{batch}', [BatchController::class, 'update'])->name('update');
});
