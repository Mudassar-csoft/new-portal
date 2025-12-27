<?php

use Illuminate\Support\Facades\Route;

Route::get('/admission/new', function () {
    return view('admission.create');
})->name('admission.create');

Route::get('/admission/status', function () {
    return view('admission.status');
})->name('admission.status');
