<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceOtherCharge extends Model
{
    use HasFactory;

    protected $table = 'finance_other_charges';

    protected $fillable = [
        'campus_id',
        'registration_id',
        'admission_id',
        'student_name',
        'charge_type_id',
        'amount',
        'discount_amount',
        'net_amount',
        'voucher_number',
        'status',
        'paid_at',
        'payment_method',
        'bank_name',
        'cheque_no',
        'bank_receipt_no',
        'payment_ref_no',
        'attachment_path',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function chargeType(): BelongsTo
    {
        return $this->belongsTo(FinanceChargeType::class, 'charge_type_id');
    }
}
