<?php

use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/registration/new', [RegistrationController::class, 'create'])->name('registration.create');
Route::post('/registration', [RegistrationController::class, 'store'])->name('registration.store');
Route::get('/registration/preview', [RegistrationController::class, 'preview'])->name('registration.preview');
Route::get('/registration/{registration}/voucher', [RegistrationController::class, 'voucher'])->name('registration.voucher');

Route::get('/registration/status', function () {
    return view('registration.status');
})->name('registration.status');
