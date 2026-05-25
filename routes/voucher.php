<?php

use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

Route::get('/voucher-preview', [VoucherController::class, 'index'])
    ->middleware('permission:registration.view')
    ->name('voucher.preview');
