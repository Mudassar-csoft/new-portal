<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Admission extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'campus_id',
        'program_id',
        'batch_id',
        'student_name',
        'phone',
        'guardian_name',
        'guardian_phone',
        'cnic',
        'passport_number',
        'date_of_birth',
        'email',
        'gender',
        'education',
        'country',
        'city',
        'area',
        'postal_address',
        'registration_number',
        'roll_number',
        'admission_date',
        'fee_package',
        'discount_amount',
        'discount_percent',
        'discounted_fee',
        'fee_type',
        'student_status',
        'status_updated_at',
        'certificate_delivered_at',
        'certificate_delivered_by',
        'certificate_delivery_notes',
        'remarks',
        'receipt_number',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
        'status_updated_at' => 'datetime',
        'certificate_delivered_at' => 'datetime',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function certificateDeliveredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'certificate_delivered_by');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class);
    }

    public function feeCollections(): HasMany
    {
        return $this->hasMany(FeeCollection::class);
    }
}
