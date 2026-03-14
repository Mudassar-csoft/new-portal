<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserLoginLogController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::view('/student', 'student.portal')->name('student.portal');

    // Leads routes
    require __DIR__ . '/lead.php';
    require __DIR__ . '/web_lead.php';

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

    // Finance routes
    require __DIR__ . '/finance.php';

    // HRM routes
    require __DIR__ . '/hrm.php';

    Route::get('/login-logs', [UserLoginLogController::class, 'index'])->name('login-logs.index');
});
