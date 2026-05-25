<?php

use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', [InventoryController::class, 'index'])->middleware('permission:inventory.view')->name('index');
    Route::get('/create', [InventoryController::class, 'create'])->middleware('permission:inventory.create')->name('create');
    Route::post('/', [InventoryController::class, 'store'])->middleware('permission:inventory.create')->name('store');
    Route::get('/{inventoryItem}/edit', [InventoryController::class, 'edit'])->middleware('permission:inventory.update')->name('edit');
    Route::put('/{inventoryItem}', [InventoryController::class, 'update'])->middleware('permission:inventory.update')->name('update');
});
