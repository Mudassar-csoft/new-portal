<?php

use App\Http\Controllers\AdmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/admission/new', [AdmissionController::class, 'create'])->middleware('permission:admission.create')->name('admission.create');
Route::get('/admission/preview-numbers', [AdmissionController::class, 'previewNumbersAjax'])->middleware('permission:admission.view,admission.create')->name('admission.preview-numbers');
Route::post('/admission', [AdmissionController::class, 'store'])->middleware('permission:admission.create')->name('admission.store');
Route::get('/admission/{admission}/voucher', [AdmissionController::class, 'voucher'])->middleware('permission:admission.view')->name('admission.voucher');

Route::get('/admission/status', [AdmissionController::class, 'status'])->middleware('permission:admission.view,admission.review')->name('admission.status');
Route::post('/admission/{admission}/documents', [AdmissionController::class, 'uploadDocuments'])->middleware('permission:admission.create,admission.update')->name('admission.documents.upload');
Route::post('/admission/{admission}/review', [AdmissionController::class, 'reviewApproval'])->middleware('permission:admission.review')->name('admission.review');
Route::get('/admission/{admission}/documents/{document}', [AdmissionController::class, 'viewDocument'])->middleware('permission:admission.view,admission.review')->name('admission.documents.view');
