<?php

use App\Http\Controllers\Permission\PermissionController;
use Illuminate\Support\Facades\Route;

Route::prefix('permissions')->name('permissions.')->group(function () {
    Route::get('/', [PermissionController::class, 'index'])->middleware('permission:permission.view,permission.manage')->name('index');
    Route::get('/create', [PermissionController::class, 'create'])->middleware('permission:permission.create,permission.manage')->name('create');
    Route::post('/', [PermissionController::class, 'store'])->middleware('permission:permission.create,permission.manage')->name('store');
    Route::get('/{permission}/edit', [PermissionController::class, 'edit'])->middleware(['permission:permission.update,permission.manage', 'admin'])->name('edit');
    Route::put('/{permission}', [PermissionController::class, 'update'])->middleware(['permission:permission.update,permission.manage', 'admin'])->name('update');
    Route::delete('/{permission}', [PermissionController::class, 'destroy'])->middleware('permission:permission.delete,permission.manage')->name('destroy');
    Route::patch('/{id}/restore', [PermissionController::class, 'restore'])->middleware('permission:permission.update,permission.manage')->name('restore');
});
