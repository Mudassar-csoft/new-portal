<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceRoyalty extends Model
{
    use HasFactory;

    protected $table = 'finance_royalties';

    protected $fillable = [
        'campus_id',
        'admission_id',
        'royalty_rate',
        'base_amount',
        'amount',
        'due_date',
        'paid_at',
        'status',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'royalty_rate' => 'decimal:2',
        'base_amount' => 'decimal:2',
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }
}
