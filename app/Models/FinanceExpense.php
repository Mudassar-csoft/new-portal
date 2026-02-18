<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceExpense extends Model
{
    use HasFactory;

    protected $table = 'finance_expenses';

    protected $fillable = [
        'campus_id',
        'payee_id',
        'expense_type_id',
        'bill_id',
        'category',
        'payment_date',
        'amount',
        'payment_method',
        'bank_name',
        'cheque_no',
        'bank_receipt_no',
        'payment_ref_no',
        'voucher_no',
        'receipt_no',
        'attachment_path',
        'status',
        'remarks',
        'created_by',
        'requested_by',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'is_reversal',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'is_reversal' => 'boolean',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function payee(): BelongsTo
    {
        return $this->belongsTo(FinancePayee::class, 'payee_id');
    }

    public function expenseType(): BelongsTo
    {
        return $this->belongsTo(FinanceExpenseType::class, 'expense_type_id');
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(FinanceBill::class, 'bill_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
