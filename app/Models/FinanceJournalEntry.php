<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceJournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'campus_id',
        'journal_no',
        'entry_date',
        'entry_type',
        'source_type',
        'source_id',
        'event_key',
        'reference_number',
        'description',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(FinanceJournalLine::class)
            ->orderBy('line_no')
            ->orderBy('id');
    }
}
