<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrAttendanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'attendance_id',
        'request_type',
        'requested_check_in_at',
        'requested_check_out_at',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'requested_check_in_at' => 'datetime',
        'requested_check_out_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(HrAttendance::class, 'attendance_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

