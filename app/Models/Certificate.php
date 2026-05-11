<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    use HasFactory;

    public const STATUS_REQUESTED = 'requested';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PRINTING = 'printing';
    public const STATUS_READY = 'ready';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_REQUESTED,
        self::STATUS_APPROVED,
        self::STATUS_PRINTING,
        self::STATUS_READY,
        self::STATUS_DELIVERED,
        self::STATUS_REJECTED,
    ];

    protected $fillable = [
        'admission_id',
        'campus_id',
        'program_id',
        'certificate_number',
        'status',
        'requested_by',
        'requested_at',
        'approved_by',
        'approved_at',
        'printing_at',
        'ready_at',
        'delivered_at',
        'delivered_to',
        'delivered_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
        'remarks',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'printing_at' => 'datetime',
        'ready_at' => 'datetime',
        'delivered_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function deliverer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
