<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'campus_id',
        'department_id',
        'designation_id',
        'reporting_manager_id',
        'employee_code',
        'first_name',
        'last_name',
        'cnic',
        'contact_no',
        'email',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'joining_date',
        'employment_type',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'joining_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(HrDepartment::class, 'department_id');
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(HrDesignation::class, 'designation_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reporting_manager_id');
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(HrAttendance::class, 'employee_id');
    }

    public function attendanceRequests(): HasMany
    {
        return $this->hasMany(HrAttendanceRequest::class, 'employee_id');
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(HrLeaveBalance::class, 'employee_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(HrLeaveRequest::class, 'employee_id');
    }

    public function salaryStructures(): HasMany
    {
        return $this->hasMany(HrSalaryStructure::class, 'employee_id');
    }

    public function payrollItems(): HasMany
    {
        return $this->hasMany(HrPayrollItem::class, 'employee_id');
    }

    public function advances(): HasMany
    {
        return $this->hasMany(HrAdvance::class, 'employee_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(HrDocument::class, 'employee_id');
    }

    public function shiftAssignments(): HasMany
    {
        return $this->hasMany(HrShiftAssignment::class, 'employee_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }
}

