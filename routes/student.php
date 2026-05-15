<?php

use App\Http\Controllers\StudentRecordController;
use App\Http\Controllers\StudentAttendanceController;
use Illuminate\Support\Facades\Route;

Route::prefix('student')->name('student.')->group(function () {
    Route::view('/', 'student.portal')->name('portal');
    Route::get('/attendance', [StudentAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/import', [StudentAttendanceController::class, 'import'])->name('attendance.import');
    Route::get('/records/{scope?}', [StudentRecordController::class, 'index'])->name('records.index');
    Route::post('/records/{admission}/status', [StudentRecordController::class, 'updateStatus'])->name('records.status');
    Route::post('/records/{admission}/certificate-delivered', [StudentRecordController::class, 'markCertificateDelivered'])->name('records.certificate-delivered');
    Route::get('/registration/{registration}', [StudentRecordController::class, 'show'])->name('show');
    Route::post('/fee/{feeCollection}', [StudentRecordController::class, 'updateFee'])->name('fee.update');
    Route::post('/fee/{feeCollection}/collect', [StudentRecordController::class, 'collectInstallment'])->name('fee.collect');
});
