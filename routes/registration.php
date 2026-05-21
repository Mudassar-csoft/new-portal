<?php

use App\Http\Controllers\CoworkingRegistrationController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/registration/new', [RegistrationController::class, 'create'])->name('registration.create');
Route::post('/registration', [RegistrationController::class, 'store'])->name('registration.store');
Route::get('/registration/preview', [RegistrationController::class, 'preview'])->name('registration.preview');
Route::get('/registration/{registration}/voucher', [RegistrationController::class, 'voucher'])->name('registration.voucher');

Route::get('/registration/status', [RegistrationController::class, 'status'])->name('registration.status');

Route::get('/coworking-registrations/new', [CoworkingRegistrationController::class, 'create'])->name('coworking-registrations.create');
Route::post('/coworking-registrations', [CoworkingRegistrationController::class, 'store'])->name('coworking-registrations.store');
Route::get('/coworking-registrations/preview', [CoworkingRegistrationController::class, 'preview'])->name('coworking-registrations.preview');
Route::get('/coworking-registrations/{coworkingRegistration}/edit', [CoworkingRegistrationController::class, 'edit'])->name('coworking-registrations.edit');
Route::put('/coworking-registrations/{coworkingRegistration}', [CoworkingRegistrationController::class, 'update'])->name('coworking-registrations.update');
Route::post('/coworking-registrations/{coworkingRegistration}/inactive', [CoworkingRegistrationController::class, 'deactivate'])->name('coworking-registrations.deactivate');
Route::get('/coworking-registrations/{coworkingRegistration}', [CoworkingRegistrationController::class, 'show'])->name('coworking-registrations.show');
Route::post('/coworking-registrations/{coworkingRegistration}/charge', [CoworkingRegistrationController::class, 'collectCharge'])->name('coworking-registrations.collect-charge');
Route::get('/coworking-registrations/{coworkingRegistration}/voucher', [CoworkingRegistrationController::class, 'voucher'])->name('coworking-registrations.voucher');
Route::get('/coworking-registrations/receipts/{receipt}/voucher', [CoworkingRegistrationController::class, 'receiptVoucher'])->name('coworking-registrations.receipts.voucher');
