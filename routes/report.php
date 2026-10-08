<?php

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/dbr', [ReportController::class, 'dbr'])
        ->middleware('permission:report.leads,report.admissions,report.finance')
        ->name('dbr');
});
