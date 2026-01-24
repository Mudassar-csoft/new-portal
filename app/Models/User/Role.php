<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
        'at_deleted',
    ];

    protected $casts = [
        'is_system' => 'boolean',
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

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\User::class)
            ->withPivot(['assigned_by'])
            ->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)
            ->withTimestamps();
    }
}
