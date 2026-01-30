<?php

use App\Http\Controllers\AdmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/admission/new', [AdmissionController::class, 'create'])->name('admission.create');
Route::post('/admission', [AdmissionController::class, 'store'])->name('admission.store');
Route::get('/admission/{admission}/voucher', [AdmissionController::class, 'voucher'])->name('admission.voucher');

Route::get('/admission/status', [AdmissionController::class, 'status'])->name('admission.status');
