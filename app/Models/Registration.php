<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'campus_id',
        'program_id',
        'registration_number',
        'receipt_number',
        'student_name',
        'phone',
        'guardian_name',
        'guardian_phone',
        'cnic',
        'passport_number',
        'email',
        'education',
        'date_of_birth',
        'gender',
        'address',
        'remarks',
        'fee',
        'discount',
        'net_payable',
        'status',
        'registered_at',
        'personal_info_updated_by',
        'personal_info_updated_at',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'date_of_birth' => 'date',
        'personal_info_updated_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function personalInfoUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'personal_info_updated_by');
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function admission(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Admission::class);
    }
}
