<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrShiftAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'shift_id',
        'effective_from',
        'effective_to',
        'off_days',
        'is_rotational',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'off_days' => 'array',
        'is_rotational' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(HrShift::class, 'shift_id');
    }
}

