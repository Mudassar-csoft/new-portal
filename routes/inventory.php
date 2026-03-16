<?php

use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', [InventoryController::class, 'index'])->name('index');
    Route::get('/create', [InventoryController::class, 'create'])->name('create');
    Route::post('/', [InventoryController::class, 'store'])->name('store');
    Route::get('/{inventoryItem}/edit', [InventoryController::class, 'edit'])->name('edit');
    Route::put('/{inventoryItem}', [InventoryController::class, 'update'])->name('update');
});
