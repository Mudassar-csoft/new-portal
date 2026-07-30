<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebLead extends Model
{
    use HasFactory;

    public const SOURCE_QUICK_LEAD = 'quick_lead';
    public const SOURCE_WEBSITE_ENROLLMENT = 'website_enrollment';
    public const SOURCE_WEBSITE_ADMISSION = 'website_admission';
    public const SOURCE_BROCHURE_DOWNLOAD = 'brochure_download';
    public const SOURCE_FEE_ALERT = 'Fee_Alert';

    public const STATUS_NEW = 'new';
    public const STATUS_NOT_INTERESTED = 'not_interested';
    public const STATUS_LEAD_CREATED = 'lead_created';

    protected $fillable = [
        'source_type',
        'source_site',
        'full_name',
        'email',
        'phone',
        'country',
        'city',
        'area',
        'interested_program',
        'preferred_campus',
        'teaching_method',
        'gender',
        'message',
        'payload',
        'status',
        'submitted_at',
        'converted_to_lead_id',
        'handled_by',
        'handled_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'submitted_at' => 'datetime',
        'handled_at' => 'datetime',
    ];

    public static function sourceLabels(): array
    {
        return [
            self::SOURCE_QUICK_LEAD => 'Quick Leads',
            self::SOURCE_WEBSITE_ENROLLMENT => 'Website Enrollments',
            self::SOURCE_WEBSITE_ADMISSION => 'Website Admissions',
            self::SOURCE_BROCHURE_DOWNLOAD => 'Brochure Downloads',
            self::SOURCE_FEE_ALERT => 'Fee Alert',
        ];
    }

    public static function leadManagementSourceLabels(): array
    {
        return [
            self::SOURCE_QUICK_LEAD => 'Quick Leads',
            self::SOURCE_WEBSITE_ENROLLMENT => 'Website Enrollments',
            self::SOURCE_WEBSITE_ADMISSION => 'Website Admissions',
            self::SOURCE_BROCHURE_DOWNLOAD => 'Brochure Downloads',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_NOT_INTERESTED => 'Not Interested',
            self::STATUS_LEAD_CREATED => 'Lead Created',
        ];
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_NEW);
    }

    public function scopeOfSource(Builder $query, string $sourceType): Builder
    {
        return $query->where('source_type', $sourceType);
    }

    public function convertedLead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'converted_to_lead_id');
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function getSourceLabelAttribute(): string
    {
        return self::sourceLabels()[$this->source_type] ?? ucfirst(str_replace('_', ' ', (string) $this->source_type));
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function getDisplaySubmittedAtAttribute(): ?\Illuminate\Support\Carbon
    {
        $submittedAt = $this->submitted_at ?? $this->created_at;

        return $submittedAt?->copy()->setTimezone('Asia/Karachi');
    }

    public function isActionable(): bool
    {
        return $this->status === self::STATUS_NEW;
    }
}
