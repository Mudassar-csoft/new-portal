<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'resource',
        'action',
        'slug',
        'description',
        'at_deleted',
    ];

    protected $casts = [
        'at_deleted' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('not_deleted', function ($query) {
            $query->whereNull('at_deleted');
        });
    }

    public function scopeWithTrashed($query)
    {
        return $query->withoutGlobalScope('not_deleted');
    }

    public function scopeOnlyTrashed($query)
    {
        return $query->withoutGlobalScope('not_deleted')->whereNotNull('at_deleted');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\User::class)
            ->withTimestamps();
    }
}
