<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrDepartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'campus_id',
        'name',
        'status',
        'description',
    ];

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function designations(): HasMany
    {
        return $this->hasMany(HrDesignation::class, 'department_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(HrEmployee::class, 'department_id');
    }
}

