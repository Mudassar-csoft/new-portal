<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceJournalLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'finance_journal_entry_id',
        'line_no',
        'account_code',
        'account_name',
        'debit_amount',
        'credit_amount',
        'memo',
    ];

    protected $casts = [
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(FinanceJournalEntry::class, 'finance_journal_entry_id');
    }
}
