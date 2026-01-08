<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'campus_id',
        'name',
        'code',
        'start_date',
        'end_date',
        'session',
        'start_time',
        'end_time',
        'instructor',
        'lab',
        'status',
        'remarks',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }
}
