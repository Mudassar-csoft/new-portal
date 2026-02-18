<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'campus_id',
        'name',
        'start_time',
        'end_time',
        'grace_check_in_minutes',
        'grace_check_out_minutes',
        'break_minutes',
        'is_night_shift',
        'is_active',
    ];

    protected $casts = [
        'is_night_shift' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(HrShiftAssignment::class, 'shift_id');
    }
}

