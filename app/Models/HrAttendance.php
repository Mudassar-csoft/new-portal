<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'campus_id',
        'shift_id',
        'attendance_date',
        'check_in_at',
        'check_out_at',
        'status',
        'late_minutes',
        'early_exit_minutes',
        'worked_minutes',
        'source',
        'remarks',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(HrShift::class, 'shift_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(HrAttendanceRequest::class, 'attendance_id');
    }
}

