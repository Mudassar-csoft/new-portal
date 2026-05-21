<?php

use App\Http\Controllers\LeadController;
use Illuminate\Support\Facades\Route;

Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
Route::post('/leads/{lead}/followups', [LeadController::class, 'addFollowup'])->name('leads.followups.store');

Route::get('/leads/follow-ups', [LeadController::class, 'followups'])->name('leads.followups');
Route::get('/leads/coworking-space/follow-ups', [LeadController::class, 'coworkingFollowups'])->name('leads.coworking.followups');

Route::get('/leads/{lead}/transfer', [LeadController::class, 'transferForm'])->name('leads.transfer.form');
Route::post('/leads/{lead}/transfer', [LeadController::class, 'transferStore'])->name('leads.transfer.store');
Route::post('/lead-transfers/{transfer}/approve', [LeadController::class, 'approveTransfer'])->name('lead_transfers.approve');

Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');

Route::get('/leads/transfers', [LeadController::class, 'transfers'])->name('leads.transfer');

Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
