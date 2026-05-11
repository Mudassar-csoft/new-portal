<?php

use App\Http\Controllers\CertificateController;
use Illuminate\Support\Facades\Route;

Route::prefix('certificate')->name('certificate.')->group(function () {
    Route::get('/', [CertificateController::class, 'index'])->name('index');
    Route::get('/new', [CertificateController::class, 'create'])->name('create');
    Route::post('/', [CertificateController::class, 'store'])->name('store');
    Route::get('/{certificate}/edit', [CertificateController::class, 'edit'])->name('edit');
    Route::put('/{certificate}', [CertificateController::class, 'update'])->name('update');
    Route::delete('/{certificate}', [CertificateController::class, 'destroy'])->name('destroy');

    // Workflow transitions
    Route::patch('/{certificate}/approve', [CertificateController::class, 'approve'])->name('approve');
    Route::patch('/{certificate}/reject', [CertificateController::class, 'reject'])->name('reject');
    Route::patch('/{certificate}/send-to-printing', [CertificateController::class, 'sendToPrinting'])->name('send-to-printing');
    Route::patch('/{certificate}/mark-ready', [CertificateController::class, 'markReady'])->name('mark-ready');
    Route::patch('/{certificate}/mark-delivered', [CertificateController::class, 'markDelivered'])->name('mark-delivered');
});
