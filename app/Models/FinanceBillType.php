<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceBillType extends Model
{
    use HasFactory;

    protected $table = 'finance_bill_types';

    protected $fillable = [
        'name',
        'company_name',
        'service_name',
        'payee_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function payee(): BelongsTo
    {
        return $this->belongsTo(FinancePayee::class, 'payee_id');
    }

    public function bills(): HasMany
    {
        return $this->hasMany(FinanceBill::class, 'bill_type_id');
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->company_name || $this->service_name) {
            return trim(implode(' - ', array_filter([$this->company_name, $this->service_name])));
        }

        return (string) $this->name;
    }
}
