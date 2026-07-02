<?php

use App\Http\Controllers\CertificateController;
use Illuminate\Support\Facades\Route;

Route::prefix('certificate')->name('certificate.')->group(function () {
    Route::get('/', [CertificateController::class, 'index'])->middleware('permission:certificate.view')->name('index');
    Route::get('/new', [CertificateController::class, 'create'])->middleware('permission:certificate.create')->name('create');
    Route::post('/', [CertificateController::class, 'store'])->middleware('permission:certificate.create')->name('store');
    Route::get('/{admission}/edit', [CertificateController::class, 'edit'])->middleware(['permission:certificate.update', 'admin'])->name('edit');
    Route::put('/{admission}', [CertificateController::class, 'update'])->middleware(['permission:certificate.update', 'admin'])->name('update');
    Route::delete('/{admission}', [CertificateController::class, 'destroy'])->middleware('permission:certificate.delete')->name('destroy');

    // Workflow transitions
    Route::patch('/{admission}/approve', [CertificateController::class, 'approve'])->middleware('permission:certificate.approve')->name('approve');
    Route::patch('/{admission}/reject', [CertificateController::class, 'reject'])->middleware('permission:certificate.reject')->name('reject');
    Route::patch('/{admission}/send-to-printing', [CertificateController::class, 'sendToPrinting'])->middleware('permission:certificate.send-to-printing')->name('send-to-printing');
    Route::patch('/{admission}/mark-ready', [CertificateController::class, 'markReady'])->middleware('permission:certificate.mark-ready')->name('mark-ready');
    Route::patch('/{admission}/mark-delivered', [CertificateController::class, 'markDelivered'])->middleware('permission:certificate.mark-delivered')->name('mark-delivered');
});
