<?php

use App\Http\Controllers\StudentRecordController;
use App\Http\Controllers\StudentAttendanceController;
use Illuminate\Support\Facades\Route;

Route::prefix('student')->name('student.')->group(function () {
    Route::view('/', 'student.portal')->middleware('permission:student.view')->name('portal');
    Route::get('/attendance', [StudentAttendanceController::class, 'index'])->middleware('permission:student.view')->name('attendance.index');
    Route::post('/attendance/import', [StudentAttendanceController::class, 'import'])->middleware('permission:student.update')->name('attendance.import');
    Route::get('/records/{scope?}', [StudentRecordController::class, 'index'])->middleware('permission:student.view')->name('records.index');
    Route::post('/records/{admission}/status', [StudentRecordController::class, 'updateStatus'])->middleware('permission:student.update')->name('records.status');
    Route::post('/records/{admission}/certificate-delivered', [StudentRecordController::class, 'markCertificateDelivered'])->middleware('permission:student.update')->name('records.certificate-delivered');
    Route::get('/registration/{registration}', [StudentRecordController::class, 'show'])->middleware('permission:student.view')->name('show');
    Route::post('/fee/{feeCollection}', [StudentRecordController::class, 'updateFee'])->middleware(['permission:student.update', 'admin'])->name('fee.update');
    Route::post('/fee/{feeCollection}/collect', [StudentRecordController::class, 'collectInstallment'])->middleware('permission:student.update')->name('fee.collect');
});
