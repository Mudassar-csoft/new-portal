<?php

use App\Http\Controllers\WebLeadController;
use Illuminate\Support\Facades\Route;

Route::post('/web-leads', [WebLeadController::class, 'storePublic'])->name('api.web-leads.store');
