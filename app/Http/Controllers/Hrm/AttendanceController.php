<?php

namespace App\Http\Controllers\Hrm;

use App\Models\Campus;
use App\Models\HrAttendance;
use App\Models\HrAttendanceRequest;
use App\Models\HrDeviceImportLog;
use App\Models\HrEmployee;
use App\Models\HrShift;
use App\Models\HrShiftAssignment;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceController extends BaseController
{
    public function index(Request $request): View
    {
        $this->authorizeHrm($request, ['hrm_attendance.view']);

        $date = $request->input('date', now()->toDateString());

        $logs = HrAttendance::query()
            ->with(['employee:id,employee_code,first_name,last_name', 'campus:id,code,name', 'shift:id,name'])
            ->whereDate('attendance_date', $date)
            ->when($request->integer('campus_id'), fn ($q, $campusId) => $q->where('campus_id', $campusId))
            ->orderBy('employee_id')
            ->paginate(25, ['*'], 'logs_page')
            ->withQueryString();

        $requests = HrAttendanceRequest::query()
            ->with(['employee:id,employee_code,first_name,last_name', 'attendance:id,attendance_date'])
            ->when($request->filled('request_status'), fn ($q) => $q->where('status', $request->input('request_status')))
            ->orderByDesc('id')
            ->paginate(20, ['*'], 'requests_page')
            ->withQueryString();

        return view('hrm.attendance.index', [
            'logs' => $logs,
            'requests' => $requests,
            'employees' => HrEmployee::query()->where('status', 'active')->orderBy('first_name')->limit(400)->get(['id', 'employee_code', 'first_name', 'last_name', 'campus_id']),
            'shifts' => HrShift::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'code', 'name']),
            'filters' => [
                'date' => $date,
                'campus_id' => $request->integer('campus_id') ?: null,
                'request_status' => $request->input('request_status'),
            ],
        ]);
    }

    public function checkIn(Request $request): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_attendance.checkin']);

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:hr_employees,id'],
            'check_in_at' => ['nullable', 'date'],
            'shift_id' => ['nullable', 'exists:hr_shifts,id'],
            'remarks' => ['nullable', 'string'],
        ]);

        $employee = HrEmployee::query()->findOrFail($validated['employee_id']);
        $checkInAt = isset($validated['check_in_at']) ? Carbon::parse($validated['check_in_at']) : now();
        $attendanceDate = $checkInAt->toDateString();

        $attendance = HrAttendance::query()->firstOrNew([
            'employee_id' => $employee->id,
            'attendance_date' => $attendanceDate,
        ]);

        $resolvedShiftId = $validated['shift_id'] ?? $this->resolveShiftId($employee->id, $attendanceDate);

        if (!$attendance->exists) {
            $attendance->campus_id = $employee->campus_id;
            $attendance->shift_id = $resolvedShiftId;
            $attendance->status = 'present';
            $attendance->source = 'manual';
        } elseif (!empty($validated['shift_id'])) {
            $attendance->shift_id = $validated['shift_id'];
        }

        $attendance->check_in_at = $checkInAt;
        $attendance->late_minutes = $this->calculateLateMinutes($attendanceDate, $checkInAt, $attendance->shift_id);
        $attendance->remarks = $validated['remarks'] ?? $attendance->remarks;
        $attendance->save();

        return back()->with('status', 'Check-in recorded.');
    }

    public function checkOut(Request $request, HrAttendance $attendance): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_attendance.checkout']);

        $validated = $request->validate([
            'check_out_at' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
        ]);

        if (!$attendance->check_in_at) {
            return back()->withErrors(['check_out_at' => 'Check-in is missing for this attendance record.']);
        }

        $checkOutAt = isset($validated['check_out_at']) ? Carbon::parse($validated['check_out_at']) : now();
        if ($checkOutAt->lessThanOrEqualTo($attendance->check_in_at)) {
            return back()->withErrors(['check_out_at' => 'Check-out time must be after check-in time.']);
        }

        $workedMinutes = (int) $attendance->check_in_at->diffInMinutes($checkOutAt);

        $attendance->check_out_at = $checkOutAt;
        $attendance->worked_minutes = $workedMinutes;
        $attendance->early_exit_minutes = $this->calculateEarlyExitMinutes($attendance, $checkOutAt);
        $attendance->status = $workedMinutes < 240 ? 'half_day' : 'present';
        $attendance->remarks = $validated['remarks'] ?? $attendance->remarks;
        $attendance->save();

        return back()->with('status', 'Check-out recorded.');
    }

    public function storeRequest(Request $request): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_attendance.request']);

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:hr_employees,id'],
            'attendance_id' => ['nullable', 'exists:hr_attendances,id'],
            'request_type' => ['nullable', Rule::in(['checkin_correction', 'checkout_correction', 'full_day_correction'])],
            'requested_check_in_at' => ['nullable', 'date'],
            'requested_check_out_at' => ['nullable', 'date', 'after:requested_check_in_at'],
            'reason' => ['nullable', 'string'],
        ]);

        HrAttendanceRequest::query()->create([
            'employee_id' => $validated['employee_id'],
            'attendance_id' => $validated['attendance_id'] ?? null,
            'request_type' => $validated['request_type'] ?? 'full_day_correction',
            'requested_check_in_at' => $validated['requested_check_in_at'] ?? null,
            'requested_check_out_at' => $validated['requested_check_out_at'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('status', 'Attendance correction request submitted.');
    }

    public function approveRequest(Request $request, HrAttendanceRequest $requestRecord): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_attendance.approve']);

        $requestRecord->update([
            'status' => 'approved',
            'approved_by' => $request->user()?->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        $requestedIn = $requestRecord->requested_check_in_at;
        $requestedOut = $requestRecord->requested_check_out_at;
        $effectiveDate = $requestedIn?->toDateString() ?: $requestedOut?->toDateString();

        if ($effectiveDate) {
            $attendance = $requestRecord->attendance ?: HrAttendance::query()->firstOrNew([
                'employee_id' => $requestRecord->employee_id,
                'attendance_date' => $effectiveDate,
            ]);

            if (!$attendance->exists) {
                $employee = $requestRecord->employee;
                $attendance->campus_id = $employee?->campus_id;
                $attendance->status = 'present';
                $attendance->source = 'manual';
            }

            if ($requestedIn) {
                $attendance->check_in_at = $requestedIn;
            }
            if ($requestedOut) {
                $attendance->check_out_at = $requestedOut;
            }

            if ($attendance->check_in_at && $attendance->check_out_at && $attendance->check_out_at->greaterThan($attendance->check_in_at)) {
                $attendance->worked_minutes = (int) $attendance->check_in_at->diffInMinutes($attendance->check_out_at);
                $attendance->status = $attendance->worked_minutes < 240 ? 'half_day' : 'present';
            }

            $attendance->save();

            if (!$requestRecord->attendance_id) {
                $requestRecord->attendance_id = $attendance->id;
                $requestRecord->save();
            }
        }

        return back()->with('status', 'Attendance request approved.');
    }

    public function rejectRequest(Request $request, HrAttendanceRequest $requestRecord): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_attendance.approve']);

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string'],
        ]);

        $requestRecord->update([
            'status' => 'rejected',
            'approved_by' => $request->user()?->id,
            'approved_at' => now(),
            'rejection_reason' => $validated['rejection_reason'] ?? 'Rejected by approver',
        ]);

        return back()->with('status', 'Attendance request rejected.');
    }

    public function importDevice(Request $request): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_attendance.import']);

        $validated = $request->validate([
            'source_name' => ['nullable', 'string', 'max:120'],
            'import_file' => ['nullable', 'file', 'mimes:csv,txt'],
            'remarks' => ['nullable', 'string'],
        ]);

        $file = $request->file('import_file');
        $totalRows = $this->countRows($file);

        HrDeviceImportLog::query()->create([
            'import_date' => now()->toDateString(),
            'source_name' => $validated['source_name'] ?? ($file ? $file->getClientOriginalName() : 'manual-import'),
            'total_records' => $totalRows,
            'processed_records' => $totalRows,
            'failed_records' => 0,
            'remarks' => $validated['remarks'] ?? 'Device import log created. Map and parser can be extended per device format.',
            'imported_by' => $request->user()?->id,
        ]);

        return back()->with('status', 'Device import logged. Parser hook is ready for biometric integration.');
    }

    private function countRows(?UploadedFile $file): int
    {
        if (!$file) {
            return 0;
        }

        $lines = @file($file->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) {
            return 0;
        }

        return max(0, count($lines) - 1);
    }

    private function resolveShiftId(int $employeeId, string $attendanceDate): ?int
    {
        $assignment = HrShiftAssignment::query()
            ->where('employee_id', $employeeId)
            ->whereDate('effective_from', '<=', $attendanceDate)
            ->where(function ($q) use ($attendanceDate) {
                $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $attendanceDate);
            })
            ->orderByDesc('effective_from')
            ->first();

        return $assignment?->shift_id;
    }

    private function calculateLateMinutes(string $attendanceDate, Carbon $checkInAt, ?int $shiftId): int
    {
        if (!$shiftId) {
            return 0;
        }

        $shift = HrShift::query()->find($shiftId);
        if (!$shift) {
            return 0;
        }

        $shiftStartWithGrace = Carbon::parse($attendanceDate . ' ' . $shift->start_time)
            ->addMinutes((int) $shift->grace_check_in_minutes);

        if ($checkInAt->lessThanOrEqualTo($shiftStartWithGrace)) {
            return 0;
        }

        return (int) $shiftStartWithGrace->diffInMinutes($checkInAt);
    }

    private function calculateEarlyExitMinutes(HrAttendance $attendance, Carbon $checkOutAt): int
    {
        if (!$attendance->shift_id || !$attendance->attendance_date) {
            return 0;
        }

        $shift = HrShift::query()->find($attendance->shift_id);
        if (!$shift) {
            return 0;
        }

        $shiftEndWithGrace = Carbon::parse($attendance->attendance_date->toDateString() . ' ' . $shift->end_time)
            ->subMinutes((int) $shift->grace_check_out_minutes);

        if ($checkOutAt->greaterThanOrEqualTo($shiftEndWithGrace)) {
            return 0;
        }

        return (int) $checkOutAt->diffInMinutes($shiftEndWithGrace);
    }
}
