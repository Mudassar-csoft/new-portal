<?php

use App\Http\Controllers\Role\RoleController;
use Illuminate\Support\Facades\Route;

Route::prefix('roles')->name('roles.')->group(function () {
    Route::get('/', [RoleController::class, 'index'])->middleware('permission:role.view,role.manage')->name('index');
    Route::get('/create', [RoleController::class, 'create'])->middleware('permission:role.create,role.manage')->name('create');
    Route::post('/', [RoleController::class, 'store'])->middleware('permission:role.create,role.manage')->name('store');
    Route::get('/{role}/edit', [RoleController::class, 'edit'])->middleware(['permission:role.update,role.manage', 'admin'])->name('edit');
    Route::put('/{role}', [RoleController::class, 'update'])->middleware(['permission:role.update,role.manage', 'admin'])->name('update');
    Route::delete('/{role}', [RoleController::class, 'destroy'])->middleware('permission:role.delete,role.manage')->name('destroy');
    Route::patch('/{id}/restore', [RoleController::class, 'restore'])->middleware('permission:role.update,role.manage')->name('restore');
});
