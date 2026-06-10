<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'campus_id',
        'program_id',
        'assigned_user_id',
        'created_by',
        'type',
        'name',
        'email',
        'phone',
        'city',
        'origin',
        'marketing_source',
        'status',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function followups(): HasMany
    {
        return $this->hasMany(LeadFollowup::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(LeadTransfer::class);
    }

    public function coworkingRegistration(): HasOne
    {
        return $this->hasOne(CoworkingRegistration::class);
    }

    public function scopeTraining(Builder $query): Builder
    {
        return $query->where('type', 'training');
    }

    public function scopeCoworking(Builder $query): Builder
    {
        return $query->where('type', 'coworking');
    }
}
