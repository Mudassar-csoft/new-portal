<?php

use Illuminate\Support\Facades\Route;

Route::get('/registration/new', function () {
    return view('registration.create');
})->name('registration.create');

Route::get('/registration/status', function () {
    return view('registration.status');
})->name('registration.status');
