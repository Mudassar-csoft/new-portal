<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'code',
        'program_type',
        'fee',
        'duration_weeks',
        'discount_limit',
        'installments',
        'outline_path',
        'prerequisite',
        'remarks',
        'status',
    ];

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function campusDiscounts(): HasMany
    {
        return $this->hasMany(ProgramCampusDiscount::class);
    }
}
