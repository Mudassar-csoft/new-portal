<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadFollowup extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'campus_id',
        'user_id',
        'method',
        'probability',
        'note',
        'next_action_date',
        'stage',
        'lead_status',
        'metadata',
    ];

    protected $casts = [
        'next_action_date' => 'datetime',
        'probability' => 'integer',
        'metadata' => 'array',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
