<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchTimetable extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'day_of_week',
        'start_time',
        'end_time',
        'lab',
        'instructor',
        'topic',
        'status',
        'remarks',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
