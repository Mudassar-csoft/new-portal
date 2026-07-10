<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'designation',
        'profile_image',
        'review',
        'rating',
        'display_order',
        'featured',
        'status',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'rating' => 'integer',
        'display_order' => 'integer',
    ];
}
