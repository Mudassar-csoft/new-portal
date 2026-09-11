<?php

use App\Http\Controllers\Api\CampusController;
use App\Http\Controllers\Api\CertificateVerificationController;
use App\Http\Controllers\Api\ProgramController;
use App\Http\Controllers\FakeLeadController;
use App\Http\Controllers\WebLeadController;
use Illuminate\Support\Facades\Route;

Route::get('/programs/{id}', [ProgramController::class, 'show'])
    ->whereNumber('id')
    ->name('api.programs.show');
Route::get('/campuses/{id}', [CampusController::class, 'show'])
    ->whereNumber('id')
    ->name('api.campuses.show');

Route::post('/web-leads', [WebLeadController::class, 'storePublic'])->name('api.web-leads.store');
Route::get('/verify-certificate/{rollNumber}', [CertificateVerificationController::class, 'show'])
    ->where('rollNumber', '.+')
    ->name('api.verify-certificate');

// Fake leads API - public JSON feed of pending web leads + generator for test data
Route::get('/leads/feed', [FakeLeadController::class, 'feed'])->name('api.leads.feed');
Route::post('/leads/generate-fake', [FakeLeadController::class, 'generate'])->name('api.leads.generate');
