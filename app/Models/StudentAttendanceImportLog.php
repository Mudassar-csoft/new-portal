<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAttendanceImportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_date',
        'source_type',
        'source_name',
        'total_records',
        'processed_records',
        'failed_records',
        'remarks',
        'imported_by',
    ];

    protected $casts = [
        'import_date' => 'date',
    ];

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
