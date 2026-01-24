<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserLoginLogController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    });

    Route::view('/student', 'student.portal')->name('student.portal');

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

    Route::get('/login-logs', [UserLoginLogController::class, 'index'])->name('login-logs.index');
});
