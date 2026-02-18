<?php

use App\Http\Controllers\Hrm\AnnouncementController;
use App\Http\Controllers\Hrm\AttendanceController;
use App\Http\Controllers\Hrm\DashboardController;
use App\Http\Controllers\Hrm\DocumentController;
use App\Http\Controllers\Hrm\EmployeeController;
use App\Http\Controllers\Hrm\LeaveController;
use App\Http\Controllers\Hrm\MasterController;
use App\Http\Controllers\Hrm\PayrollController;
use App\Http\Controllers\Hrm\ShiftController;
use Illuminate\Support\Facades\Route;

Route::prefix('hrm')->name('hrm.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::post('/employees/{employee}/status', [EmployeeController::class, 'updateStatus'])->name('employees.status');

    Route::get('/masters', [MasterController::class, 'index'])->name('masters.index');
    Route::post('/masters/departments', [MasterController::class, 'storeDepartment'])->name('masters.departments.store');
    Route::post('/masters/designations', [MasterController::class, 'storeDesignation'])->name('masters.designations.store');
    Route::post('/masters/leave-types', [MasterController::class, 'storeLeaveType'])->name('masters.leave-types.store');
    Route::post('/masters/holidays', [MasterController::class, 'storeHoliday'])->name('masters.holidays.store');

    Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
    Route::post('/shifts', [ShiftController::class, 'store'])->name('shifts.store');
    Route::post('/shifts/assignments', [ShiftController::class, 'storeAssignment'])->name('shifts.assignments.store');

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('/attendance/{attendance}/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');
    Route::post('/attendance/requests', [AttendanceController::class, 'storeRequest'])->name('attendance.requests.store');
    Route::post('/attendance/requests/{requestRecord}/approve', [AttendanceController::class, 'approveRequest'])->name('attendance.requests.approve');
    Route::post('/attendance/requests/{requestRecord}/reject', [AttendanceController::class, 'rejectRequest'])->name('attendance.requests.reject');
    Route::post('/attendance/import-device', [AttendanceController::class, 'importDevice'])->name('attendance.import');

    Route::get('/leaves', [LeaveController::class, 'index'])->name('leaves.index');
    Route::post('/leaves/requests', [LeaveController::class, 'storeRequest'])->name('leaves.requests.store');
    Route::post('/leaves/requests/{leaveRequest}/approve', [LeaveController::class, 'approveRequest'])->name('leaves.requests.approve');
    Route::post('/leaves/requests/{leaveRequest}/reject', [LeaveController::class, 'rejectRequest'])->name('leaves.requests.reject');
    Route::post('/leaves/balances', [LeaveController::class, 'storeBalance'])->name('leaves.balances.store');

    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::post('/payroll/structures', [PayrollController::class, 'storeStructure'])->name('payroll.structures.store');
    Route::post('/payroll/advances', [PayrollController::class, 'storeAdvance'])->name('payroll.advances.store');
    Route::post('/payroll/runs', [PayrollController::class, 'storeRun'])->name('payroll.runs.store');
    Route::post('/payroll/runs/{run}/close', [PayrollController::class, 'closeRun'])->name('payroll.runs.close');
    Route::post('/payroll/items/{item}/mark-paid', [PayrollController::class, 'markItemPaid'])->name('payroll.items.paid');

    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::post('/announcements/{announcement}/publish', [AnnouncementController::class, 'publish'])->name('announcements.publish');

    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
});

