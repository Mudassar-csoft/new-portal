<?php

use App\Http\Controllers\BatchController;
use App\Http\Controllers\BatchTimetableController;
use Illuminate\Support\Facades\Route;

Route::prefix('batch')->name('batch.')->group(function () {
    Route::get('/', [BatchController::class, 'index'])->middleware('permission:batch.view')->name('index');
    Route::get('/new', [BatchController::class, 'create'])->middleware('permission:batch.create')->name('create');
    Route::post('/', [BatchController::class, 'store'])->middleware('permission:batch.create')->name('store');
    Route::get('/timetable', [BatchTimetableController::class, 'index'])->middleware('permission:batch-timetable.view')->name('timetable.index');
    Route::post('/timetable', [BatchTimetableController::class, 'store'])->middleware('permission:batch-timetable.create')->name('timetable.store');
    Route::patch('/timetable/{timetable}', [BatchTimetableController::class, 'update'])->middleware('permission:batch-timetable.update')->name('timetable.update');
    Route::delete('/timetable/{timetable}', [BatchTimetableController::class, 'destroy'])->middleware('permission:batch-timetable.delete')->name('timetable.destroy');
    Route::get('/{batch}/edit', [BatchController::class, 'edit'])->middleware('permission:batch.update')->name('edit');
    Route::put('/{batch}', [BatchController::class, 'update'])->middleware('permission:batch.update')->name('update');
    Route::patch('/{batch}/toggle-status', [BatchController::class, 'toggleStatus'])->middleware('permission:batch.update')->name('toggle-status');
    Route::delete('/{batch}', [BatchController::class, 'destroy'])->middleware('permission:batch.delete')->name('destroy');
});
