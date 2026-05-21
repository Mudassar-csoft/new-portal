<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoworkingRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'campus_id',
        'registration_number',
        'receipt_number',
        'full_name',
        'phone',
        'guardian_name',
        'guardian_phone',
        'cnic',
        'email',
        'education',
        'date_of_birth',
        'nature_of_work',
        'timing',
        'gender',
        'address',
        'registration_date',
        'next_due_date',
        'coworking_charges',
        'security_fee',
        'remarks',
        'status',
        'leave_date',
        'used_days',
        'daily_deduction_amount',
        'usage_deduction_amount',
        'damage_deduction_amount',
        'refund_amount',
        'damage_notes',
        'inactive_reason',
        'inactive_remarks',
        'refund_processed_at',
        'created_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'registration_date' => 'date',
        'next_due_date' => 'date',
        'leave_date' => 'date',
        'coworking_charges' => 'decimal:2',
        'security_fee' => 'decimal:2',
        'daily_deduction_amount' => 'decimal:2',
        'usage_deduction_amount' => 'decimal:2',
        'damage_deduction_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'refund_processed_at' => 'datetime',
    ];

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

    public function receipts(): HasMany
    {
        return $this->hasMany(CoworkingRegistrationReceipt::class);
    }
}
