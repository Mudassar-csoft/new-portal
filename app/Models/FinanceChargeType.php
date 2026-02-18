<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceChargeType extends Model
{
    use HasFactory;

    protected $table = 'finance_charge_types';

    protected $fillable = [
        'name',
        'category',
        'default_amount',
        'is_active',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function charges(): HasMany
    {
        return $this->hasMany(FinanceOtherCharge::class, 'charge_type_id');
    }
}
