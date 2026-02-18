<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrHoliday extends Model
{
    use HasFactory;

    protected $fillable = [
        'campus_id',
        'name',
        'holiday_date',
        'holiday_type',
        'is_optional',
        'description',
    ];

    protected $casts = [
        'holiday_date' => 'date',
        'is_optional' => 'boolean',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }
}

