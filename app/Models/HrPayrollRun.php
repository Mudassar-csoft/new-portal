<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrPayrollRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'campus_id',
        'payroll_month',
        'from_date',
        'to_date',
        'status',
        'processed_by',
        'processed_at',
        'closed_by',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'processed_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(HrPayrollItem::class, 'payroll_run_id');
    }
}

