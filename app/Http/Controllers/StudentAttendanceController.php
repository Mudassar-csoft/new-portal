<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Batch;
use App\Models\Campus;
use App\Models\Program;
use App\Models\StudentAttendance;
use App\Models\StudentAttendanceImportLog;
use App\Support\ResolvesCampusScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentAttendanceController extends Controller
{
    use ResolvesCampusScope;

    public function index(Request $request): View
    {
        $attendanceDate = $request->input('attendance_date', now()->toDateString());

        $baseAdmissions = $this->scopeQueryToUserCampus(Admission::query(), $request->user())
            ->with([
                'campus:id,code,name',
                'program:id,code,title,name',
                'batch:id,code,name,session,start_time,end_time',
                'attendances' => fn ($query) => $query
                    ->whereDate('attendance_date', $attendanceDate)
                    ->orderByDesc('check_in_at'),
            ]);

        $this->applyAdmissionFilters($baseAdmissions, $request);

        $summaryAdmissions = clone $baseAdmissions;
        $attendanceSummary = $this->scopeQueryToUserCampus(StudentAttendance::query(), $request->user())
            ->whereDate('attendance_date', $attendanceDate);
        $this->applyAttendanceFilters($attendanceSummary, $request);

        $register = clone $baseAdmissions;
        if ($request->filled('status')) {
            $status = (string) $request->input('status');

            if ($status === 'absent') {
                $register->whereDoesntHave('attendances', function (Builder $query) use ($attendanceDate) {
                    $query
                        ->whereDate('attendance_date', $attendanceDate)
                        ->where('status', '!=', 'absent');
                });
            } else {
                $register->whereHas('attendances', function (Builder $query) use ($attendanceDate, $status) {
                    $query
                        ->whereDate('attendance_date', $attendanceDate)
                        ->where('status', $status);
                });
            }
        }

        $students = $register
            ->orderBy('student_name')
            ->paginate(25)
            ->withQueryString();

        $totalStudents = (clone $summaryAdmissions)->count();
        $presentCount = (clone $attendanceSummary)->where('status', 'present')->count();
        $lateCount = (clone $attendanceSummary)->where('status', 'late')->count();
        $halfDayCount = (clone $attendanceSummary)->where('status', 'half_day')->count();
        $leaveCount = (clone $attendanceSummary)->where('status', 'leave')->count();
        $trackedStudents = (clone $attendanceSummary)
            ->where('status', '!=', 'absent')
            ->distinct('admission_id')
            ->count('admission_id');
        $absentCount = max(0, $totalStudents - $trackedStudents);

        return view('student.attendance.index', [
            'students' => $students,
            'imports' => StudentAttendanceImportLog::query()->latest()->limit(10)->get(),
            'campuses' => $this->campusOptionsForUser($request->user(), ['id', 'code', 'name']),
            'programs' => Program::query()->orderByRaw('COALESCE(title, name)')->get(['id', 'code', 'title', 'name']),
            'batches' => $this->scopeQueryToUserCampus(Batch::query(), $request->user())
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
            'filters' => [
                'attendance_date' => $attendanceDate,
                'campus_id' => $this->effectiveCampusFilter($request->integer('campus_id'), $request->user()),
                'program_id' => $request->integer('program_id') ?: null,
                'batch_id' => $request->integer('batch_id') ?: null,
                'status' => $request->input('status'),
                'search' => $request->input('search'),
            ],
            'summary' => [
                'total' => $totalStudents,
                'present' => $presentCount,
                'late' => $lateCount,
                'half_day' => $halfDayCount,
                'leave' => $leaveCount,
                'absent' => $absentCount,
            ],
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'source_type' => ['required', Rule::in(['zkteco', 'csv'])],
            'source_name' => ['nullable', 'string', 'max:120'],
            'import_file' => ['required', 'file', 'mimes:csv,txt'],
            'remarks' => ['nullable', 'string'],
        ]);

        $rows = $this->parseCsv($request->file('import_file'));
        $processed = 0;
        $failed = 0;

        foreach ($rows as $row) {
            $admission = $this->resolveAdmission($row);
            if (!$admission) {
                $failed++;
                continue;
            }

            $explicitCheckIn = $this->resolveDateTime($row, ['check_in_at', 'check_in', 'in_time']);
            $explicitCheckOut = $this->resolveDateTime($row, ['check_out_at', 'check_out', 'out_time']);
            $punchAt = $this->resolveDateTime($row, ['punch_time', 'timestamp', 'scan_time', 'datetime']);
            $attendanceDate = $this->resolveAttendanceDate($row, $explicitCheckIn, $explicitCheckOut, $punchAt);

            if (!$attendanceDate) {
                $failed++;
                continue;
            }

            $attendance = StudentAttendance::query()->firstOrNew([
                'admission_id' => $admission->id,
                'attendance_date' => $attendanceDate,
            ]);

            $attendance->campus_id = $admission->campus_id;
            $attendance->program_id = $admission->program_id;
            $attendance->batch_id = $admission->batch_id;
            $attendance->source = $validated['source_type'];
            $attendance->device_name = $this->resolveValue($row, ['device_name', 'device', 'terminal_name']) ?: ($validated['source_name'] ?? null);
            $attendance->device_user_id = $this->resolveValue($row, ['device_user_id', 'biometric_id', 'uid', 'user_id', 'employee_id']);
            $attendance->remarks = $this->resolveValue($row, ['remarks', 'note']) ?: ($validated['remarks'] ?? null);

            if ($explicitCheckIn) {
                $attendance->check_in_at = $this->minDateTime($attendance->check_in_at, $explicitCheckIn);
            }

            if ($explicitCheckOut) {
                $attendance->check_out_at = $this->maxDateTime($attendance->check_out_at, $explicitCheckOut);
            }

            if ($punchAt) {
                $this->applyPunch($attendance, $punchAt, $this->resolveDirection($row));
            }

            $this->applyMetrics($attendance, $admission, $this->normalizeStatus($this->resolveValue($row, ['status', 'attendance_status'])));
            $attendance->save();
            $processed++;
        }

        StudentAttendanceImportLog::query()->create([
            'import_date' => now()->toDateString(),
            'source_type' => $validated['source_type'],
            'source_name' => $validated['source_name'] ?? $request->file('import_file')->getClientOriginalName(),
            'total_records' => count($rows),
            'processed_records' => $processed,
            'failed_records' => $failed,
            'remarks' => $validated['remarks'] ?? 'Student attendance import completed.',
            'imported_by' => $request->user()?->id,
        ]);

        return back()->with('status', "Attendance import finished. Processed {$processed} row(s), failed {$failed} row(s).");
    }

    private function applyAdmissionFilters(Builder $query, Request $request): void
    {
        $campusId = $this->effectiveCampusFilter($request->integer('campus_id'), $request->user());

        $query
            ->when($campusId, fn (Builder $q, int $resolvedCampusId) => $q->where('campus_id', $resolvedCampusId))
            ->when($request->integer('program_id'), fn (Builder $q, int $programId) => $q->where('program_id', $programId))
            ->when($request->integer('batch_id'), fn (Builder $q, int $batchId) => $q->where('batch_id', $batchId))
            ->when($request->filled('search'), function (Builder $q) use ($request) {
                $search = trim((string) $request->input('search'));

                $q->where(function (Builder $inner) use ($search) {
                    $inner
                        ->where('student_name', 'like', '%' . $search . '%')
                        ->orWhere('roll_number', 'like', '%' . $search . '%')
                        ->orWhere('registration_number', 'like', '%' . $search . '%');
                });
            });
    }

    private function applyAttendanceFilters(Builder $query, Request $request): void
    {
        $campusId = $this->effectiveCampusFilter($request->integer('campus_id'), $request->user());

        $query
            ->when($campusId, fn (Builder $q, int $resolvedCampusId) => $q->where('campus_id', $resolvedCampusId))
            ->when($request->integer('program_id'), fn (Builder $q, int $programId) => $q->where('program_id', $programId))
            ->when($request->integer('batch_id'), fn (Builder $q, int $batchId) => $q->where('batch_id', $batchId))
            ->when($request->filled('search'), function (Builder $q) use ($request) {
                $search = trim((string) $request->input('search'));

                $q->where(function (Builder $inner) use ($search) {
                    $inner
                        ->where('device_user_id', 'like', '%' . $search . '%')
                        ->orWhereHas('admission', function (Builder $admissions) use ($search) {
                            $admissions
                                ->where('student_name', 'like', '%' . $search . '%')
                                ->orWhere('roll_number', 'like', '%' . $search . '%')
                                ->orWhere('registration_number', 'like', '%' . $search . '%');
                        });
                });
            });
    }

    private function parseCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            return [];
        }

        $rows = [];
        $headers = null;

        while (($data = fgetcsv($handle)) !== false) {
            if ($headers === null) {
                $headers = array_map(fn ($header) => $this->normalizeHeader((string) $header), $data);
                continue;
            }

            if ($this->isEmptyCsvRow($data)) {
                continue;
            }

            $data = array_pad($data, count($headers), null);
            $rows[] = array_combine($headers, $data) ?: [];
        }

        fclose($handle);

        return $rows;
    }

    private function resolveAdmission(array $row): ?Admission
    {
        $rollNumber = $this->resolveValue($row, ['roll_number', 'roll_no', 'student_code', 'student_id']);
        $registrationNumber = $this->resolveValue($row, ['registration_number', 'registration_no', 'reg_no']);
        $deviceUserId = $this->resolveValue($row, ['device_user_id', 'biometric_id', 'uid', 'user_id', 'employee_id']);
        $studentName = $this->resolveValue($row, ['student_name', 'name']);
        $campusCode = $this->resolveValue($row, ['campus_code']);
        $programCode = $this->resolveValue($row, ['program_code']);
        $batchCode = $this->resolveValue($row, ['batch_code']);

        $query = $this->scopeQueryToUserCampus(Admission::query(), auth()->user())
            ->with(['batch:id,code,start_time']);

        if ($rollNumber) {
            return $query->where('roll_number', $rollNumber)->first();
        }

        if ($registrationNumber) {
            return $query->where('registration_number', $registrationNumber)->first();
        }

        if ($deviceUserId) {
            $matchedByDevice = (clone $query)
                ->where(function (Builder $builder) use ($deviceUserId) {
                    $builder
                        ->where('roll_number', $deviceUserId)
                        ->orWhere('registration_number', $deviceUserId);
                })
                ->first();

            if ($matchedByDevice) {
                return $matchedByDevice;
            }
        }

        if (!$studentName) {
            return null;
        }

        return $query
            ->where('student_name', $studentName)
            ->when($campusCode, function (Builder $builder, string $value) {
                $builder->whereHas('campus', fn (Builder $campus) => $campus->where('code', $value));
            })
            ->when($programCode, function (Builder $builder, string $value) {
                $builder->whereHas('program', fn (Builder $program) => $program->where('code', $value));
            })
            ->when($batchCode, function (Builder $builder, string $value) {
                $builder->whereHas('batch', fn (Builder $batch) => $batch->where('code', $value));
            })
            ->first();
    }

    private function resolveAttendanceDate(array $row, ?Carbon $checkInAt, ?Carbon $checkOutAt, ?Carbon $punchAt): ?string
    {
        $explicitDate = $this->resolveValue($row, ['attendance_date', 'date']);
        if ($explicitDate) {
            try {
                return Carbon::parse($explicitDate)->toDateString();
            } catch (\Throwable) {
                return null;
            }
        }

        return $checkInAt?->toDateString()
            ?: $checkOutAt?->toDateString()
            ?: $punchAt?->toDateString();
    }

    private function resolveDateTime(array $row, array $keys): ?Carbon
    {
        $value = $this->resolveValue($row, $keys);
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function resolveValue(array $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $row)) {
                continue;
            }

            $value = trim((string) $row[$key]);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function resolveDirection(array $row): ?string
    {
        $direction = strtolower((string) ($this->resolveValue($row, ['direction', 'punch_state', 'state', 'type']) ?? ''));

        return match ($direction) {
            'in', 'checkin', 'check_in', '0' => 'in',
            'out', 'checkout', 'check_out', '1' => 'out',
            default => null,
        };
    }

    private function applyPunch(StudentAttendance $attendance, Carbon $punchAt, ?string $direction): void
    {
        if ($direction === 'in') {
            $attendance->check_in_at = $this->minDateTime($attendance->check_in_at, $punchAt);
            return;
        }

        if ($direction === 'out') {
            $attendance->check_out_at = $this->maxDateTime($attendance->check_out_at, $punchAt);
            return;
        }

        if (!$attendance->check_in_at || $punchAt->lt($attendance->check_in_at)) {
            $attendance->check_in_at = $punchAt;
            return;
        }

        if (!$attendance->check_out_at || $punchAt->gt($attendance->check_out_at)) {
            $attendance->check_out_at = $punchAt;
        }
    }

    private function applyMetrics(StudentAttendance $attendance, Admission $admission, ?string $importedStatus): void
    {
        $attendance->worked_minutes = 0;
        $attendance->late_minutes = 0;

        if (
            $attendance->check_in_at
            && $attendance->check_out_at
            && $attendance->check_out_at->greaterThan($attendance->check_in_at)
        ) {
            $attendance->worked_minutes = (int) $attendance->check_in_at->diffInMinutes($attendance->check_out_at);
        } else {
            $attendance->check_out_at = null;
        }

        if ($attendance->check_in_at && $admission->batch?->start_time) {
            $batchStart = Carbon::parse($attendance->attendance_date->toDateString() . ' ' . $admission->batch->start_time)->addMinutes(15);
            if ($attendance->check_in_at->greaterThan($batchStart)) {
                $attendance->late_minutes = (int) $batchStart->diffInMinutes($attendance->check_in_at);
            }
        }

        if ($importedStatus) {
            $attendance->status = $importedStatus;
            return;
        }

        if (!$attendance->check_in_at) {
            $attendance->status = 'absent';
            return;
        }

        if ($attendance->worked_minutes > 0 && $attendance->worked_minutes < 240) {
            $attendance->status = 'half_day';
            return;
        }

        if ($attendance->late_minutes > 0) {
            $attendance->status = 'late';
            return;
        }

        $attendance->status = 'present';
    }

    private function normalizeHeader(string $header): string
    {
        $header = ltrim($header, "\xEF\xBB\xBF");
        $header = strtolower(trim($header));
        $header = str_replace([' ', '-', '/', '\\'], '_', $header);
        $header = preg_replace('/[^a-z0-9_]+/', '', $header) ?? '';

        return trim($header, '_');
    }

    private function normalizeStatus(?string $status): ?string
    {
        if (!$status) {
            return null;
        }

        $value = strtolower(str_replace([' ', '-'], '_', trim($status)));

        return match ($value) {
            'present', 'checked_in', 'checkedin' => 'present',
            'late', 'delayed' => 'late',
            'half_day', 'halfday' => 'half_day',
            'leave', 'on_leave' => 'leave',
            'absent', 'missed' => 'absent',
            default => null,
        };
    }

    private function isEmptyCsvRow(array $data): bool
    {
        foreach ($data as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function minDateTime($currentValue, Carbon $candidate): Carbon
    {
        if (!$currentValue) {
            return $candidate;
        }

        $current = $currentValue instanceof Carbon ? $currentValue : Carbon::parse($currentValue);

        return $candidate->lt($current) ? $candidate : $current;
    }

    private function maxDateTime($currentValue, Carbon $candidate): Carbon
    {
        if (!$currentValue) {
            return $candidate;
        }

        $current = $currentValue instanceof Carbon ? $currentValue : Carbon::parse($currentValue);

        return $candidate->gt($current) ? $candidate : $current;
    }
}
