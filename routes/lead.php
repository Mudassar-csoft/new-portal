<?php

use Illuminate\Support\Facades\Route;

Route::get('/leads/create', function () {
    return view('lead.create');
})->name('leads.create');

Route::get('/leads/follow-ups', function () {
    return view('lead.followups');
})->name('leads.followups');

Route::get('/leads', function () {
    return view('lead.all');
})->name('leads.index');

Route::get('/leads/transfers', function () {
    return view('lead.transfer');
})->name('leads.transfer');

Route::get('/leads/{id}', function () {
    return view('lead.show');
})->name('leads.show');
