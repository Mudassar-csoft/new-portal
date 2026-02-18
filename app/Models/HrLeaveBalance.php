<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrLeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'opening_balance',
        'accrued',
        'used',
        'encashed',
        'closing_balance',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'accrued' => 'decimal:2',
        'used' => 'decimal:2',
        'encashed' => 'decimal:2',
        'closing_balance' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(HrLeaveType::class, 'leave_type_id');
    }
}

