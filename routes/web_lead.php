<?php

use App\Http\Controllers\WebLeadController;
use Illuminate\Support\Facades\Route;

Route::get('/web-leads', [WebLeadController::class, 'index'])
    ->middleware('permission:web-lead.view')
    ->name('web-leads.index');
Route::get('/web-leads/{webLead}', [WebLeadController::class, 'show'])
    ->middleware('permission:web-lead.view')
    ->name('web-leads.show');
Route::post('/web-leads/{webLead}/not-interested', [WebLeadController::class, 'markNotInterested'])
    ->middleware('permission:web-lead.view,web-lead.update')
    ->name('web-leads.not-interested');
