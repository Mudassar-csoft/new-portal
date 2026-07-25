@php
    $actionId = $actionId ?? ('adm-action-' . uniqid());
    $admission = $admission ?? null;
    $registrationId = $admission->registration_id ?? null;
    $canAdminEdit = auth()->user()?->isAdmin() ?? false;
    $canReviewAdmission = auth()->user()?->hasAnyPermission(['admission.review']) ?? false;
    $canViewStudent = auth()->user()?->hasAnyPermission(['student.view']) ?? false;
    $canAdmissionUpload = auth()->user()?->hasAnyPermission(['admission.create', 'admission.update']) ?? false;
    $showCollectFeeInstallment = $admission && $registrationId && $canViewStudent && (int) ($admission->pending_admission_fee_count ?? 0) > 0;
    $approvalStatus = $admission->approval_status ?? \App\Models\Admission::APPROVAL_STATUS_APPROVED;
    $identityDocumentType = $admission?->resolveIdentityDocumentType() ?? \App\Models\Admission::IDENTITY_DOCUMENT_TYPE_CNIC;
    $docCnicUrl = $admission?->document_cnic_front_path ? route('admission.documents.view', ['admission' => $admission->id, 'document' => 'cnic-front']) : null;
    $docCnicBackUrl = $admission?->document_cnic_back_path ? route('admission.documents.view', ['admission' => $admission->id, 'document' => 'cnic-back']) : null;
    $docFormUrl = $admission?->document_admission_form_path ? route('admission.documents.view', ['admission' => $admission->id, 'document' => 'admission-form']) : null;
    $docSlipUrl = $admission?->document_paid_slip_path ? route('admission.documents.view', ['admission' => $admission->id, 'document' => 'paid-slip']) : null;
    $reviewProgramLabel = $admission?->program?->title ?? $admission?->program?->name ?? '';
    $reviewSessionLabel = $admission?->batch?->code ?? $admission?->batch?->name ?? '';
    $reviewStatusLabel = \App\Models\Admission::STUDENT_STATUS_LABELS[$admission->student_status ?? ''] ?? ($admission->student_status ?? '');
    $reviewDobLabel = optional($admission?->date_of_birth)->format('d-M-Y') ?? '';
    $reviewRegistrationDateLabel = optional($admission?->registration?->registered_at ?? $admission?->registration?->created_at)->format('d-M-Y') ?? '';
    $reviewAdmissionDateLabel = optional($admission?->admission_date)->format('d-M-Y') ?? '';
    $reviewFeePackageLabel = $admission?->fee_package !== null ? number_format((float) $admission->fee_package, 0) : '';
    $reviewDiscountLabel = $admission
        ? trim(number_format((float) ($admission->discount_percent ?? 0), 0) . '% (' . number_format((float) ($admission->discount_amount ?? 0), 0) . ')')
        : '';
    $reviewFeePlanLabel = match ($admission?->fee_type) {
        'full' => 'Full Payment',
        'installments' => 'Installments',
        default => '',
    };
    $reviewPaidInstallmentLabel = number_format((float) ($admission?->paid_admission_fee_total ?? 0), 0);
    $reviewPendingInstallmentLabel = number_format((float) ($admission?->pending_admission_fee_total ?? 0), 0);
@endphp

@once
    @push('styles')
        <style>
        :root {
            --dimension-admission-partials-action-1: 22px;
            --dimension-admission-partials-action-2: 24px;
            --dimension-admission-partials-action-3: 292px;
            --space-admission-partials-action-1: 0 !important;
            --color-admission-partials-action-1: #303740;
            --typo-admission-partials-action-font-size-1: 17px;
            --typo-admission-partials-action-font-weight-2: 500;
        }

               .admission-action-dropdown {
            position: relative;
        }
.dataTables_wrapper{
    overflow: visible !important;
}

div.dataTables_scrollBody{
    overflow: visible !important;
}
        .admission-action-dropdown .dropdown-menu {
            min-width: var(--dimension-admission-partials-action-3);
/*             
            position: absolute !important;
            top: 100% !important;
            left: auto !important;
            right: 0 !important;
            margin-top: 6px !important;
            margin-right: var(--space-admission-partials-action-1);
            transform: none !important; */
            z-index: 9999;
        }
            .admission-action-dropdown .dropdown-menu.lead-action-menu {
                min-width: var(--dimension-admission-partials-action-3);
                padding: 8px 0;
                border: 1px solid #dfe5eb;
                border-radius: 6px;
                background: #fff;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
                text-align: left !important;
            }

            .admission-action-dropdown .dropdown-item.lead-action-item {
                display: flex !important;
                align-items: center;
                justify-content: flex-start;
                gap: 14px;
                padding: 5px 18px !important;
                color: var(--color-admission-partials-action-1) !important;
                font-size: var(--typo-admission-partials-action-font-size-1) !important;
                font-weight: var(--typo-admission-partials-action-font-weight-2);
                line-height: 1.35;
                background: transparent !important;
                border: 0;
                text-align: left !important;
            }

            .admission-action-dropdown .dropdown-item.lead-action-item:hover,
            .admission-action-dropdown .dropdown-item.lead-action-item:focus {
                background: #f7fafc !important;
                color: #222b33 !important;
                text-decoration: none;
            }

            .admission-action-dropdown .lead-action-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: var(--dimension-admission-partials-action-2);
                min-width: var(--dimension-admission-partials-action-2);
                height: var(--dimension-admission-partials-action-2);
                font-size: 1.125rem !important;
                line-height: 1;
                margin-right: var(--space-admission-partials-action-1);
                padding: var(--space-admission-partials-action-1);
            }

            .admission-action-dropdown .lead-action-label {
                display: inline-block;
                font-size: var(--typo-admission-partials-action-font-size-1) !important;
                font-weight: var(--typo-admission-partials-action-font-weight-2);
                letter-spacing: 0.01em;
            }

            .admission-action-dropdown .lead-action-icon svg {
                display: block;
                width: var(--dimension-admission-partials-action-2);
                height: var(--dimension-admission-partials-action-2);
            }

            .admission-action-dropdown .lead-action-icon--whatsapp svg {
                width: var(--dimension-admission-partials-action-1);
                height: var(--dimension-admission-partials-action-1);
            }

            .admission-action-dropdown .lead-icon-blue { color: #1677ff; }
            .admission-action-dropdown .lead-icon-cyan { color: #19b6e6; }
            .admission-action-dropdown .lead-icon-black { color: var(--color-admission-partials-action-1); }
            .admission-action-dropdown .lead-icon-green { color: #2db853; }

            .admission-action-state {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
            }

            .admission-action-note {
                max-width: 250px;
                white-space: normal;
                text-align: right;
                font-size: 0.75rem;
                line-height: 1.4;
                color: #64748b;
            }
        </style>
    @endpush
@endonce

@if($approvalStatus === \App\Models\Admission::APPROVAL_STATUS_PENDING)
        <div class="admission-action-state admission-action-dropdown">
        @if($canAdmissionUpload)
         <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="{{ $actionId }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Actions
        </button>
        
        <div class="dropdown-menu dropdown-menu-right lead-action-menu" aria-labelledby="{{ $actionId }}">
           
           
            <a class="dropdown-item lead-action-item" href="#">
                @include('partials.action-send-sms-content')
            </a>

            <a class="dropdown-item lead-action-item" href="#">
                @include('partials.action-send-email-content')
            </a>

            <a class="dropdown-item lead-action-item" href="#">
                @include('partials.action-whatsapp-content')
            </a>
<button type="button"
    class="dropdown-item lead-action-item js-open-upload-admission"
    data-admission-id="{{ $admission->id }}"
    data-student-name="{{ $admission->student_name }}"
    data-identity-document-type="{{ $identityDocumentType }}"
    data-approval-remarks="{{ $admission->approval_remarks ?? '' }}"
    data-doc-cnic="{{ $docCnicUrl ?? '' }}"
    data-doc-cnic-back="{{ $docCnicBackUrl ?? '' }}"
    data-doc-form="{{ $docFormUrl ?? '' }}"
    data-doc-slip="{{ $docSlipUrl ?? '' }}">

   <span class="lead-action-icon lead-action-icon--whatsapp lead-icon-green" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
            <path d="M5 20h14v-2H5v2zm7-18L5.33 8h3.84v4h6.66V8h3.84L12 2z"/>
        </svg>
    </span>

    <span class="lead-action-label">
        Upload Documents
    </span>
</button>
            
        </div>

       

        @else
            <span class="label label-warning">Pending</span>
        @endif

        @if(filled($admission->approval_remarks))
            <!-- <div class="admission-action-note">Admin Comment: {{ $admission->approval_remarks }}</div> -->
        @endif
    </div>
@elseif($approvalStatus === \App\Models\Admission::APPROVAL_STATUS_REQUESTED)
    <div class="admission-action-state admission-action-dropdown">
        @if($canReviewAdmission)
          <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="{{ $actionId }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Actions
        </button> 
         <div class="dropdown-menu dropdown-menu-right lead-action-menu" aria-labelledby="{{ $actionId }}">
            @if($canAdminEdit)
                @if($showCollectFeeInstallment)
                    <a class="dropdown-item lead-action-item" href="{{ route('student.show', $registrationId) }}">
                        @include('partials.action-collect-fee-content')
                    </a>
                @endif

                <a class="dropdown-item lead-action-item" href="{{ route('admission.create', ['source_admission_id' => $admission?->id, 'source_registration_id' => $registrationId]) }}">
                    @include('partials.action-enroll-course-content')
                </a>

                <a class="dropdown-item lead-action-item" href="#">
                    @include('partials.action-send-sms-content')
                </a>

                <a class="dropdown-item lead-action-item" href="#">
                    @include('partials.action-send-email-content')
                </a>

                <a class="dropdown-item lead-action-item" href="#">
                    @include('partials.action-whatsapp-content')
                </a>
            @endif

            @if($registrationId)
                <a class="dropdown-item lead-action-item" href="{{ route('student.show', $registrationId) }}">
                    <span class="lead-action-icon lead-action-icon--whatsapp lead-icon-cyan" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                            <path d="M12 5C7 5 2.73 8.11 1 12c1.73 3.89 6 7 11 7s9.27-3.11 11-7c-1.73-3.89-6-7-11-7zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-2.5A2.5 2.5 0 1 0 12 9a2.5 2.5 0 0 0 0 5z"/>
                        </svg>
                    </span>
                    <span class="lead-action-label">Verify Documents</span>
                </a>
            @endif

            <button
                type="button"
                class="dropdown-item lead-action-item
                 js-open-review-admission"
                data-admission-id="{{ $admission->id }}"
                data-student-name="{{ $admission->student_name }}"
                data-father-name="{{ $admission->guardian_name }}"
                data-cnic="{{ $admission->cnic }}"
                data-phone="{{ $admission->phone }}"
                data-email="{{ $admission->email }}"
                data-dob="{{ $reviewDobLabel }}"
                data-address="{{ $admission->postal_address }}"
                data-gender="{{ ucfirst((string) $admission->gender) }}"
                data-admission-date="{{ $reviewAdmissionDateLabel }}"
                data-registration-date="{{ $reviewRegistrationDateLabel }}"
                data-fee-package="{{ $reviewFeePackageLabel }}"
                data-discount="{{ $reviewDiscountLabel }}"
                data-fee-plan="{{ $reviewFeePlanLabel }}"
                data-paid-installment="{{ $reviewPaidInstallmentLabel }}"
                data-pending-installment="{{ $reviewPendingInstallmentLabel }}"
                data-program="{{ $reviewProgramLabel }}"
                data-session="{{ $reviewSessionLabel }}"
                data-status="{{ $reviewStatusLabel }}"
                data-doc-cnic="{{ $docCnicUrl ?? '' }}"
                data-doc-cnic-back="{{ $docCnicBackUrl ?? '' }}"
                data-doc-form="{{ $docFormUrl ?? '' }}"
                data-doc-slip="{{ $docSlipUrl ?? '' }}"
                data-identity-document-type="{{ $identityDocumentType }}"
                data-approval-remarks="{{ $admission->approval_remarks ?? '' }}"
            >

            <span class="lead-action-icon lead-action-icon--whatsapp lead-icon-green" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                    <path d="M12 5C7 5 2.73 8.11 1 12c1.73 3.89 6 7 11 7s9.27-3.11 11-7c-1.73-3.89-6-7-11-7zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-2.5A2.5 2.5 0 1 0 12 9a2.5 2.5 0 0 0 0 5z"/>
                </svg>
                </span>
                            <span class="lead-action-label">
                    Review
                </span>
            </button>

            @if($canAdminEdit)
                <a class="dropdown-item lead-action-item" href="#">
                        @include('partials.action-edit-black-content')
                    </a>
            @endif
        </div>
       
        @else
            <span class="label label-info">Waiting Approval</span>
        @endif

        @if(filled($admission->approval_remarks))
            <!-- <div class="admission-action-note">Admin Comment: {{ $admission->approval_remarks }}</div> -->
        @endif
    </div>
@else
    <div class="dropdown admission-action-dropdown">
        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="{{ $actionId }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Actions
        </button>
        <div class="dropdown-menu dropdown-menu-right lead-action-menu" aria-labelledby="{{ $actionId }}">
            @if($showCollectFeeInstallment)
                <a class="dropdown-item lead-action-item" href="{{ route('student.show', $registrationId) }}">
                    @include('partials.action-collect-fee-content')
                </a>
            @endif

            <a class="dropdown-item lead-action-item" href="{{ route('admission.create', ['source_admission_id' => $admission?->id, 'source_registration_id' => $registrationId]) }}">
                @include('partials.action-enroll-course-content')
            </a>

            <a class="dropdown-item lead-action-item" href="#">
                @include('partials.action-send-sms-content')
            </a>

            <a class="dropdown-item lead-action-item" href="#">
                @include('partials.action-send-email-content')
            </a>

            <a class="dropdown-item lead-action-item" href="#">
                @include('partials.action-whatsapp-content')
            </a>

            @if($canAdminEdit)
                <a class="dropdown-item lead-action-item" href="#">
                    @include('partials.action-edit-black-content')
                </a>
            @endif
        </div>
    </div>
@endif
