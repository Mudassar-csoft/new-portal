<?php

use App\Http\Controllers\LeadController;
use Illuminate\Support\Facades\Route;

Route::get('/leads/create', [LeadController::class, 'create'])
    ->middleware('permission:lead.create')
    ->name('leads.create');
Route::post('/leads', [LeadController::class, 'store'])
    ->middleware('permission:lead.create')
    ->name('leads.store');
Route::get('/leads/{lead}/edit', [LeadController::class, 'edit'])
    ->middleware('permission:lead.update')
    ->name('leads.edit');
Route::put('/leads/{lead}', [LeadController::class, 'update'])
    ->middleware('permission:lead.update')
    ->name('leads.update');
Route::post('/leads/{lead}/not-interested', [LeadController::class, 'markNotInterested'])
    ->middleware('permission:lead.followup.update')
    ->name('leads.not-interested');
Route::post('/leads/{lead}/followups', [LeadController::class, 'addFollowup'])
    ->middleware('permission:lead.followup.update')
    ->name('leads.followups.store');

Route::get('/leads/follow-ups', [LeadController::class, 'followups'])
    ->middleware('permission:lead.followup.view')
    ->name('leads.followups');
Route::get('/leads/follow-up-schedule', [LeadController::class, 'followupSchedule'])
    ->middleware('permission:lead.followup.view')
    ->name('leads.followup-schedule');
Route::get('/leads/certification-exam/follow-ups', [LeadController::class, 'certificationFollowups'])
    ->middleware('permission:lead.followup.view')
    ->name('leads.certification.followups');
Route::get('/leads/certification-exam/follow-up-schedule', [LeadController::class, 'certificationFollowupSchedule'])
    ->middleware('permission:lead.followup.view')
    ->name('leads.certification.followup-schedule');
Route::get('/leads/study-abroad/follow-ups', [LeadController::class, 'studyAbroadFollowups'])
    ->middleware('permission:lead.followup.view')
    ->name('leads.study-abroad.followups');
Route::get('/leads/study-abroad/follow-up-schedule', [LeadController::class, 'studyAbroadFollowupSchedule'])
    ->middleware('permission:lead.followup.view')
    ->name('leads.study-abroad.followup-schedule');
Route::get('/leads/coworking-space/follow-ups', [LeadController::class, 'coworkingFollowups'])
    ->middleware('permission:lead.coworking.view')
    ->name('leads.coworking.followups');
Route::get('/leads/coworking-space/follow-up-schedule', [LeadController::class, 'coworkingFollowupSchedule'])
    ->middleware('permission:lead.coworking.view')
    ->name('leads.coworking.followup-schedule');

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
Route::get('/leads/certification-exam', [LeadController::class, 'certificationIndex'])
    ->middleware('permission:lead.view')
    ->name('leads.certification.index');
Route::get('/leads/study-abroad', [LeadController::class, 'studyAbroadIndex'])
    ->middleware('permission:lead.view')
    ->name('leads.study-abroad.index');
Route::get('/leads/coworking-space', [LeadController::class, 'coworkingIndex'])
    ->middleware('permission:lead.coworking.view')
    ->name('leads.coworking.index');

Route::get('/leads/transfers', [LeadController::class, 'transfers'])
    ->middleware('permission:lead.view,lead.transfer.approve')
    ->name('leads.transfer');

Route::get('/leads/{lead}', [LeadController::class, 'show'])
    ->middleware('permission:lead.view')
    ->name('leads.show');
