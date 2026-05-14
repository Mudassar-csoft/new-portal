<?php

use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

Route::get('/voucher-preview', [VoucherController::class, 'index'])->name('voucher.preview');
