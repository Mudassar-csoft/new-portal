<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrPayrollItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'payslip_no',
        'basic_salary',
        'allowance_total',
        'deduction_total',
        'overtime_amount',
        'advance_deduction',
        'loan_deduction',
        'net_payable',
        'payment_mode',
        'bank_account_no',
        'status',
        'paid_at',
        'allowance_breakdown',
        'deduction_breakdown',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'allowance_total' => 'decimal:2',
        'deduction_total' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'loan_deduction' => 'decimal:2',
        'net_payable' => 'decimal:2',
        'paid_at' => 'datetime',
        'allowance_breakdown' => 'array',
        'deduction_breakdown' => 'array',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(HrPayrollRun::class, 'payroll_run_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }
}

