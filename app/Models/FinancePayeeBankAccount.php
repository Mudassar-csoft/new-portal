<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancePayeeBankAccount extends Model
{
    use HasFactory;

    protected $table = 'finance_payee_bank_accounts';

    protected $fillable = [
        'payee_id',
        'bank_name',
        'account_title',
        'account_number',
        'iban',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function payee(): BelongsTo
    {
        return $this->belongsTo(FinancePayee::class, 'payee_id');
    }
}
