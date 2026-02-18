<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrSalaryStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'effective_from',
        'effective_to',
        'basic_salary',
        'allowances',
        'deductions',
        'overtime_rate',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'basic_salary' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'allowances' => 'array',
        'deductions' => 'array',
        'is_active' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

