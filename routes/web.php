<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
});

Route::view('/login', 'auth.login')->name('login');

// Leads routes
require __DIR__ . '/lead.php';

// Users routes
require __DIR__ . '/user.php';

// Roles routes
require __DIR__ . '/role.php';

// Permissions routes
require __DIR__ . '/permission.php';

// Registration routes
require __DIR__ . '/registration.php';

// Admission routes
require __DIR__ . '/admission.php';

// Program routes
require __DIR__ . '/program.php';

// Batch routes
require __DIR__ . '/batch.php';

// Campus routes
require __DIR__ . '/campus.php';
