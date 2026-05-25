<?php

use App\Http\Controllers\CoworkingRegistrationController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/registration/new', [RegistrationController::class, 'create'])
    ->middleware('permission:registration.create')
    ->name('registration.create');
Route::post('/registration', [RegistrationController::class, 'store'])
    ->middleware('permission:registration.create')
    ->name('registration.store');
Route::get('/registration/preview', [RegistrationController::class, 'preview'])
    ->middleware('permission:registration.view')
    ->name('registration.preview');
Route::get('/registration/{registration}/voucher', [RegistrationController::class, 'voucher'])
    ->middleware('permission:registration.view')
    ->name('registration.voucher');

Route::get('/registration/status', [RegistrationController::class, 'status'])
    ->middleware('permission:registration.view')
    ->name('registration.status');

Route::get('/coworking-registrations/new', [CoworkingRegistrationController::class, 'create'])
    ->middleware('permission:registration.create')
    ->name('coworking-registrations.create');
Route::post('/coworking-registrations', [CoworkingRegistrationController::class, 'store'])
    ->middleware('permission:registration.create')
    ->name('coworking-registrations.store');
Route::get('/coworking-registrations/preview', [CoworkingRegistrationController::class, 'preview'])
    ->middleware('permission:registration.view')
    ->name('coworking-registrations.preview');
Route::get('/coworking-registrations/{coworkingRegistration}/edit', [CoworkingRegistrationController::class, 'edit'])
    ->middleware('permission:registration.update')
    ->name('coworking-registrations.edit');
Route::put('/coworking-registrations/{coworkingRegistration}', [CoworkingRegistrationController::class, 'update'])
    ->middleware('permission:registration.update')
    ->name('coworking-registrations.update');
Route::post('/coworking-registrations/{coworkingRegistration}/inactive', [CoworkingRegistrationController::class, 'deactivate'])
    ->middleware('permission:registration.update')
    ->name('coworking-registrations.deactivate');
Route::get('/coworking-registrations/{coworkingRegistration}', [CoworkingRegistrationController::class, 'show'])
    ->middleware('permission:registration.view')
    ->name('coworking-registrations.show');
Route::post('/coworking-registrations/{coworkingRegistration}/charge', [CoworkingRegistrationController::class, 'collectCharge'])
    ->middleware('permission:registration.update')
    ->name('coworking-registrations.collect-charge');
Route::get('/coworking-registrations/{coworkingRegistration}/voucher', [CoworkingRegistrationController::class, 'voucher'])
    ->middleware('permission:registration.view')
    ->name('coworking-registrations.voucher');
Route::get('/coworking-registrations/receipts/{receipt}/voucher', [CoworkingRegistrationController::class, 'receiptVoucher'])
    ->middleware('permission:registration.view')
    ->name('coworking-registrations.receipts.voucher');
