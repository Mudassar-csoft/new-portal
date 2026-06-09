<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceOtherChargePayment extends Model
{
    use HasFactory;

    protected $table = 'finance_other_charge_payments';

    protected $fillable = [
        'finance_other_charge_id',
        'payment_date',
        'amount',
        'payment_method',
        'payment_ref_no',
        'receiver_name',
        'depositor_name',
        'bank_name',
        'account_no',
        'transfer_id',
        'cheque_no',
        'cheque_date',
        'cheque_payee_name',
        'bank_receipt_no',
        'attachment_path',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'cheque_date' => 'date',
    ];

    public function charge(): BelongsTo
    {
        return $this->belongsTo(FinanceOtherCharge::class, 'finance_other_charge_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
