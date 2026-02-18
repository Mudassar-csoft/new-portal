<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrAdvance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'amount',
        'balance_amount',
        'installment_amount',
        'issued_date',
        'status',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'issued_date' => 'date',
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

