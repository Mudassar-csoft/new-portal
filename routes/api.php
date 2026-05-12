<?php

use App\Http\Controllers\FakeLeadController;
use App\Http\Controllers\WebLeadController;
use Illuminate\Support\Facades\Route;

Route::post('/web-leads', [WebLeadController::class, 'storePublic'])->name('api.web-leads.store');

// Fake leads API — public JSON feed of pending web leads + generator for test data
Route::get('/leads/feed', [FakeLeadController::class, 'feed'])->name('api.leads.feed');
Route::post('/leads/generate-fake', [FakeLeadController::class, 'generate'])->name('api.leads.generate');
