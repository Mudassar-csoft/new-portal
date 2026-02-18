<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrAnnouncement extends Model
{
    use HasFactory;

    protected $fillable = [
        'campus_id',
        'department_id',
        'title',
        'message',
        'audience_scope',
        'publish_at',
        'expire_at',
        'channel_email',
        'channel_sms',
        'channel_whatsapp',
        'channel_in_app',
        'status',
        'created_by',
    ];

    protected $casts = [
        'publish_at' => 'datetime',
        'expire_at' => 'datetime',
        'channel_email' => 'boolean',
        'channel_sms' => 'boolean',
        'channel_whatsapp' => 'boolean',
        'channel_in_app' => 'boolean',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(HrDepartment::class, 'department_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

