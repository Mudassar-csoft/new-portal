<?php

namespace App\Http\Controllers\Hrm;

use App\Models\HrAnnouncement;
use App\Models\HrAttendance;
use App\Models\HrEmployee;
use App\Models\HrLeaveRequest;
use App\Models\HrPayrollRun;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends BaseController
{
    public function index(Request $request): View
    {
        $this->authorizeHrm($request, ['hrm_dashboard.view']);

        $today = now()->toDateString();

        return view('hrm.dashboard', [
            'stats' => [
                'employees_total' => HrEmployee::query()->count(),
                'employees_active' => HrEmployee::query()->where('status', 'active')->count(),
                'employees_inactive' => HrEmployee::query()->where('status', 'inactive')->count(),
                'today_attendance' => HrAttendance::query()->whereDate('attendance_date', $today)->count(),
                'pending_attendance_requests' => \App\Models\HrAttendanceRequest::query()->where('status', 'pending')->count(),
                'pending_leave_requests' => HrLeaveRequest::query()->where('status', 'pending')->count(),
                'draft_payroll_runs' => HrPayrollRun::query()->where('status', 'draft')->count(),
                'published_announcements' => HrAnnouncement::query()->where('status', 'published')->count(),
                'expiring_documents' => \App\Models\HrDocument::query()
                    ->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '<=', now()->addDays(30)->toDateString())
                    ->count(),
            ],
        ]);
    }
}

