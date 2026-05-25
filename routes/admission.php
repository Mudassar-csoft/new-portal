<?php

use App\Http\Controllers\AdmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/admission/new', [AdmissionController::class, 'create'])->middleware('permission:admission.create')->name('admission.create');
Route::get('/admission/preview-numbers', [AdmissionController::class, 'previewNumbersAjax'])->middleware('permission:admission.view,admission.create')->name('admission.preview-numbers');
Route::post('/admission', [AdmissionController::class, 'store'])->middleware('permission:admission.create')->name('admission.store');
Route::get('/admission/{admission}/voucher', [AdmissionController::class, 'voucher'])->middleware('permission:admission.view')->name('admission.voucher');

Route::get('/admission/status', [AdmissionController::class, 'status'])->middleware('permission:admission.view')->name('admission.status');
