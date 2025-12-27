<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
});

// Leads routes
require __DIR__ . '/lead.php';

// Registration routes
require __DIR__ . '/registration.php';

// Admission routes
require __DIR__ . '/admission.php';
