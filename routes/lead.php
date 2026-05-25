<?php

use App\Http\Controllers\LeadController;
use Illuminate\Support\Facades\Route;

Route::get('/leads/create', [LeadController::class, 'create'])
    ->middleware('permission:lead.create')
    ->name('leads.create');
Route::post('/leads', [LeadController::class, 'store'])
    ->middleware('permission:lead.create')
    ->name('leads.store');
Route::post('/leads/{lead}/followups', [LeadController::class, 'addFollowup'])
    ->middleware('permission:lead.followup.update')
    ->name('leads.followups.store');

Route::get('/leads/follow-ups', [LeadController::class, 'followups'])
    ->middleware('permission:lead.followup.view')
    ->name('leads.followups');
Route::get('/leads/coworking-space/follow-ups', [LeadController::class, 'coworkingFollowups'])
    ->middleware('permission:lead.coworking.view')
    ->name('leads.coworking.followups');

Route::get('/leads/{lead}/transfer', [LeadController::class, 'transferForm'])
    ->middleware('permission:lead.update')
    ->name('leads.transfer.form');
Route::post('/leads/{lead}/transfer', [LeadController::class, 'transferStore'])
    ->middleware('permission:lead.update')
    ->name('leads.transfer.store');
Route::post('/lead-transfers/{transfer}/approve', [LeadController::class, 'approveTransfer'])
    ->middleware('permission:lead.transfer.approve')
    ->name('lead_transfers.approve');

Route::get('/leads', [LeadController::class, 'index'])
    ->middleware('permission:lead.view')
    ->name('leads.index');

Route::get('/leads/transfers', [LeadController::class, 'transfers'])
    ->middleware('permission:lead.view,lead.transfer.approve')
    ->name('leads.transfer');

Route::get('/leads/{lead}', [LeadController::class, 'show'])
    ->middleware('permission:lead.view')
    ->name('leads.show');
