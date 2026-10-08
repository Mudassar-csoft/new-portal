<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class CoworkingRegistrationReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'coworking_registration_id',
        'lead_id',
        'campus_id',
        'receipt_type',
        'receipt_number',
        'payment_method',
        'amount',
        'paid_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function getDueDateAttribute(): ?Carbon
    {
        if ($this->receipt_type === 'coworking_charge'
            && preg_match('/^Coworking charge collected for due date (\d{4}-\d{2}-\d{2})$/', (string) $this->notes, $matches)) {
            return Carbon::parse($matches[1])->startOfDay();
        }

        return $this->paid_at ?? $this->created_at;
    }

    public function coworkingRegistration(): BelongsTo
    {
        return $this->belongsTo(CoworkingRegistration::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
