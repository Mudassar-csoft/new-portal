<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\UserSetupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserLoginLogController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

// Public password-reset flow (OTP routed to admin email)
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.forgot');
Route::post('/forgot-password', [PasswordResetController::class, 'requestOtp'])
    ->middleware('throttle:5,10')
    ->name('password.request');
Route::get('/reset-password', [PasswordResetController::class, 'showResetForm'])->name('password.reset.form');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
    ->middleware('throttle:5,10')
    ->name('password.reset');

// Signed user-setup link (sent in welcome email)
Route::get('/user/setup/{user}', [UserSetupController::class, 'show'])
    ->middleware('signed')
    ->name('users.setup.show');
Route::post('/user/setup/{user}', [UserSetupController::class, 'store'])
    ->middleware('signed')
    ->name('users.setup.store');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard/live-data', [DashboardController::class, 'liveData'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard.live-data');
    Route::get('/dashboard/pending-recovery', [DashboardController::class, 'pendingRecovery'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard.pending-recovery');
    Route::get('/dashboard/pending-recovery/campus/{campus}', [DashboardController::class, 'pendingRecoveryCampusReport'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard.pending-recovery.campus');
    Route::get('/', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::get('/admin/coming-soon/{module}', function (string $module) {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $modules = [
            'event-management' => 'Event Management',
            'marketing-management' => 'Marketing Management',
            'reports' => 'Reports',
        ];

        abort_unless(array_key_exists($module, $modules), 404);

        return view('admin.coming-soon', [
            'pageTitle' => $modules[$module],
        ]);
    })->name('admin.coming-soon');

    // Student routes
    require __DIR__ . '/student.php';

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

    // Inventory routes
    require __DIR__ . '/inventory.php';

    // Finance routes
    require __DIR__ . '/finance.php';

    // HRM routes
    require __DIR__ . '/hrm.php';

    // Certificate routes
    require __DIR__ . '/certificate.php';

    // Voucher routes
    require __DIR__ . '/voucher.php';

    // Profile routes (current user's own profile + password change)
    require __DIR__ . '/profile.php';

    Route::get('/student-search', [\App\Http\Controllers\StudentSearchController::class, 'index'])
        ->middleware('permission:student.view')
        ->name('student-search.index');

    Route::get('/login-logs', [UserLoginLogController::class, 'index'])
        ->middleware('permission:user.view')
        ->name('login-logs.index');
});
