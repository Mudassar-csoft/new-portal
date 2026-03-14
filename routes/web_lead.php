<?php

use App\Http\Controllers\WebLeadController;
use Illuminate\Support\Facades\Route;

Route::get('/web-leads', [WebLeadController::class, 'index'])->name('web-leads.index');
Route::get('/web-leads/{webLead}', [WebLeadController::class, 'show'])->name('web-leads.show');
Route::post('/web-leads/{webLead}/not-interested', [WebLeadController::class, 'markNotInterested'])->name('web-leads.not-interested');
