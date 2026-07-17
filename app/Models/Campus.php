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

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(Admission::class);
    }

    public function programDiscounts(): HasMany
    {
        return $this->hasMany(ProgramCampusDiscount::class);
    }
}
