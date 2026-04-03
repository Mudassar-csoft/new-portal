<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceBuildingRent extends Model
{
    use HasFactory;

    protected $table = 'finance_building_rents';

    protected $fillable = [
        'campus_id',
        'agreement_date',
        'address',
        'rent_amount',
        'increment_percentage',
        'current_amount',
        'advance_payment',
        'is_active',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'agreement_date' => 'date',
        'rent_amount' => 'decimal:2',
        'increment_percentage' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'advance_payment' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(FinanceExpense::class, 'rent_id');
    }
}
