<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrLeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'is_paid',
        'annual_quota',
        'accrual_frequency',
        'accrual_rate',
        'carry_forward_limit',
        'is_active',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'is_active' => 'boolean',
        'annual_quota' => 'decimal:2',
        'accrual_rate' => 'decimal:2',
        'carry_forward_limit' => 'decimal:2',
    ];

    public function balances(): HasMany
    {
        return $this->hasMany(HrLeaveBalance::class, 'leave_type_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(HrLeaveRequest::class, 'leave_type_id');
    }
}

