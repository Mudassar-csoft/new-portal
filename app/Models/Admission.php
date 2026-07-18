<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Admission extends Model
{
    use HasFactory;

    public const APPROVAL_STATUS_PENDING = 'pending';
    public const APPROVAL_STATUS_REQUESTED = 'approval_requested';
    public const APPROVAL_STATUS_APPROVED = 'approved';
    public const IDENTITY_DOCUMENT_TYPE_CNIC = 'cnic';
    public const IDENTITY_DOCUMENT_TYPE_B_FORM = 'b_form';
    public const CERTIFICATE_REQUESTABLE_STATUS = 'concluded';
    public const CERTIFICATE_REQUESTABLE_STATUSES = [
        'concluded',
        'completed',
    ];
    public const CERTIFICATE_STATUS_REQUESTED = 'requested';
    public const CERTIFICATE_STATUS_APPROVED = 'approved';
    public const CERTIFICATE_STATUS_PRINTING = 'printing';
    public const CERTIFICATE_STATUS_READY = 'ready';
    public const CERTIFICATE_STATUS_DELIVERED = 'delivered';

    public const STUDENT_STATUS_LABELS = [
        'enrolled' => 'Enrolled',
        'concluded' => 'Concluded',
        'completed' => 'Completed',
        'frozen' => 'Frozen',
        'incomplete' => 'Incomplete',
        'suspended' => 'Suspended',
        'admission_cancelled' => 'Cancelled',
        'dropped' => 'Dropped',
        self::CERTIFICATE_STATUS_REQUESTED => 'Requested',
        self::CERTIFICATE_STATUS_APPROVED => 'Approved',
        self::CERTIFICATE_STATUS_PRINTING => 'Printing',
        self::CERTIFICATE_STATUS_READY => 'Ready',
        self::CERTIFICATE_STATUS_DELIVERED => 'Delivered',
    ];

    public const STUDENT_STATUS_BADGE_CLASSES = [
        'enrolled' => 'label-success',
        'concluded' => 'label-primary',
        'completed' => 'label-primary',
        'frozen' => 'label-warning',
        'incomplete' => 'label-default',
        'suspended' => 'label-info',
        'admission_cancelled' => 'label-danger',
        'dropped' => 'label-danger',
        self::CERTIFICATE_STATUS_REQUESTED => 'label-warning',
        self::CERTIFICATE_STATUS_APPROVED => 'label-info',
        self::CERTIFICATE_STATUS_PRINTING => 'label-primary',
        self::CERTIFICATE_STATUS_READY => 'label-success',
        self::CERTIFICATE_STATUS_DELIVERED => 'label-default',
    ];

    public const CERTIFICATE_WORKFLOW_STATUSES = [
        self::CERTIFICATE_STATUS_REQUESTED,
        self::CERTIFICATE_STATUS_APPROVED,
        self::CERTIFICATE_STATUS_PRINTING,
        self::CERTIFICATE_STATUS_READY,
        self::CERTIFICATE_STATUS_DELIVERED,
    ];

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
        'certificate_status',
        'certificate_origin_status',
        'approval_status',
        'identity_document_type',
        'status_updated_at',
        'document_cnic_front_path',
        'document_cnic_back_path',
        'document_admission_form_path',
        'document_paid_slip_path',
        'documents_uploaded_at',
        'documents_uploaded_by',
        'approval_reviewed_at',
        'approval_reviewed_by',
        'approval_remarks',
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
        'documents_uploaded_at' => 'datetime',
        'approval_reviewed_at' => 'datetime',
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

    public function documentsUploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'documents_uploaded_by');
    }

    public function approvalReviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approval_reviewed_by');
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

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('approval_status', self::APPROVAL_STATUS_APPROVED);
    }

    public function scopeCertificateWorkflow(Builder $query): Builder
    {
        return $query->whereIn('certificate_status', self::CERTIFICATE_WORKFLOW_STATUSES);
    }

    public function scopeCurrentStudents(Builder $query): Builder
    {
        return $query
            ->where('student_status', 'enrolled')
            ->where(function (Builder $builder): void {
                $builder
                    ->whereNull('certificate_status')
                    ->orWhere('certificate_status', '');
            })
            ->whereNull('certificate_delivered_at');
    }

    public static function normalizeIdentityDocumentType(?string $type): string
    {
        return in_array((string) $type, [
            self::IDENTITY_DOCUMENT_TYPE_CNIC,
            self::IDENTITY_DOCUMENT_TYPE_B_FORM,
        ], true)
            ? (string) $type
            : self::IDENTITY_DOCUMENT_TYPE_CNIC;
    }

    public static function requiresIdentityDocumentBack(?string $type): bool
    {
        return self::normalizeIdentityDocumentType($type) === self::IDENTITY_DOCUMENT_TYPE_CNIC;
    }

    public static function identityDocumentPrimaryLabelFor(?string $type): string
    {
        return self::normalizeIdentityDocumentType($type) === self::IDENTITY_DOCUMENT_TYPE_B_FORM
            ? 'B-Form Copy'
            : 'CNIC Front Side';
    }

    public function resolveIdentityDocumentType(): string
    {
        return self::normalizeIdentityDocumentType($this->identity_document_type);
    }

    public static function isCertificateRequestableStatus(?string $status): bool
    {
        return in_array((string) $status, self::CERTIFICATE_REQUESTABLE_STATUSES, true);
    }

    public static function isCertificateWorkflowStatus(?string $status): bool
    {
        return in_array((string) $status, self::CERTIFICATE_WORKFLOW_STATUSES, true);
    }

    public function resolveCertificateOriginStatus(): string
    {
        $originStatus = (string) ($this->certificate_origin_status ?? '');

        if (self::isCertificateRequestableStatus($originStatus)) {
            return $originStatus;
        }

        $currentStatus = (string) ($this->student_status ?? '');

        if (self::isCertificateRequestableStatus($currentStatus)) {
            return $currentStatus;
        }

        return self::CERTIFICATE_REQUESTABLE_STATUS;
    }
}
