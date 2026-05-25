<?php

use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->middleware('permission:user.view')->name('index');
    Route::get('/create', [UserController::class, 'create'])->middleware('permission:user.create')->name('create');
    Route::post('/', [UserController::class, 'store'])->middleware('permission:user.create')->name('store');
    Route::get('/{user}/edit', [UserController::class, 'edit'])->middleware('permission:user.update')->name('edit');
    Route::put('/{user}', [UserController::class, 'update'])->middleware('permission:user.update')->name('update');
    Route::delete('/{user}', [UserController::class, 'destroy'])->middleware('permission:user.delete')->name('destroy');
    Route::patch('/{id}/restore', [UserController::class, 'restore'])->middleware('permission:user.update')->name('restore');
});
