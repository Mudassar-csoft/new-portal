<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceBillPayment extends Model
{
    use HasFactory;

    protected $table = 'finance_bill_payments';

    protected $fillable = [
        'bill_id',
        'payment_date',
        'paid_amount',
        'payment_method',
        'bank_name',
        'cheque_no',
        'bank_receipt_no',
        'payment_ref_no',
        'receipt_no',
        'attachment_path',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'paid_amount' => 'decimal:2',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(FinanceBill::class, 'bill_id');
    }
}
