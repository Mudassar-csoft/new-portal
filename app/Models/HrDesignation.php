<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrDesignation extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'name',
        'status',
        'description',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(HrDepartment::class, 'department_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(HrEmployee::class, 'designation_id');
    }
}

