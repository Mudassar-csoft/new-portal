<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceBill extends Model
{
    use HasFactory;

    protected $table = 'finance_bills';

    protected $fillable = [
        'campus_id',
        'bill_type_id',
        'reference_number',
        'bill_month',
        'issue_date',
        'due_date',
        'amount_within_due_date',
        'fine',
        'amount',
        'paid_amount',
        'status',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'bill_month' => 'date',
        'issue_date' => 'date',
        'due_date' => 'date',
        'amount_within_due_date' => 'decimal:2',
        'fine' => 'decimal:2',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function billType(): BelongsTo
    {
        return $this->belongsTo(FinanceBillType::class, 'bill_type_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FinanceBillPayment::class, 'bill_id');
    }
}
