<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'slug',
        'code',
        'country',
        'city',
        'city_abbr',
        'campus_type',
        'campus_email',
        'landline',
        'mobile',
        'address',
        'labs_count',
        'royalty_rate',
        'status',
        'remarks',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
