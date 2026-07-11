@extends('layouts.theme')

@section('title', 'Student — ' . ($registration->student_name ?? 'Detail'))

@section('content')
    @php
        $totalCourses = $admissions->count();
        $studentPhone = preg_replace('/\D+/', '', (string) ($registration->phone ?? ''));
        $studentEmail = trim((string) ($registration->email ?? ''));
        $studentLeadId = $registration->lead_id ?? optional($latestAdmission)->lead_id;
        $studentSmsUrl = $studentPhone !== '' ? 'sms:' . $studentPhone : null;
        $studentEmailUrl = $studentEmail !== '' ? 'mailto:' . $studentEmail : null;
        $studentWhatsappUrl = $studentPhone !== '' ? 'https://wa.me/' . $studentPhone : null;
        $canAdminEdit = auth()->user()?->isAdmin();
        $canAdmissionReview = $canAdmissionReview ?? (auth()->user()?->hasAnyPermission(['admission.review']) ?? false);
    @endphp

    <div class="student-detail-shell">
        @include('partials.student-session-status-flash')
        @if(session('error'))
            <div class="student-flash student-flash--error">{{ session('error') }}</div>
        @endif

        <!-- <div class="box-typical box-typical-dashboard panel panel-default student-detail-header">
            <div class="panel-body">
                <h3 class="panel-title mb-0">Student Detail
                    <small class="text-muted ci-muted-meta">
                        — {{ $registration->student_name ?? '' }}
                    </small>
                </h3>
            </div>
        </div> -->

        <div class="student-detail-grid">
            {{-- ============ LEFT: PROFILE CARD ============ --}}
            <aside class="student-profile-card box-typical box-typical-dashboard panel panel-default">
                <div class="profile-banner"></div>
                <div class="profile-avatar-wrap">
                    <div class="profile-avatar">
                        <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <circle cx="50" cy="50" r="48" fill="#3a8fcf"/>
                            <circle cx="50" cy="38" r="16" fill="#fff8e1"/>
                            <path d="M22 86c0-15 12-26 28-26s28 11 28 26z" fill="#fff8e1"/>
                        </svg>
                    </div>
                </div>

                <div class="profile-body">
                    <h3 class="profile-name">{{ $registration->student_name }}</h3>
                    <div class="profile-phone">{{ $registration->phone ?? '—' }}</div>

                    <div class="profile-action">
                        <div class="dropdown student-action-wrap">
                            <button class="btn  btn-primary-outline dropdown-toggle student-action-btn" type="button" id="student-action-{{ $registration->id }}" aria-haspopup="true" aria-expanded="false">
                                Action <span class="caret"></span>
                            </button>
                            <div class="dropdown-menu student-action-menu" aria-labelledby="student-action-{{ $registration->id }}">
                                <a class="dropdown-item lead-action-item" href="{{ route('admission.create', ['source_registration_id' => $registration->id, 'source_admission_id' => optional($latestAdmission)->id]) }}">
                                    <span class="lead-action-icon lead-icon-black" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M8 3.75h5.5L18.25 8.5V19a1.25 1.25 0 0 1-1.25 1.25h-9.5A1.25 1.25 0 0 1 6.25 19V5A1.25 1.25 0 0 1 7.5 3.75Z"/>
                                            <path d="M13.5 3.75V8.5h4.75"/>
                                            <path d="m9 12 2 2 4-4"/>
                                        </svg>
                                    </span>
                                    <span class="lead-action-label">Enroll To Another Course</span>
                                </a>
                                <a class="dropdown-item lead-action-item {{ $studentSmsUrl ? '' : 'is-disabled' }}" href="{{ $studentSmsUrl ?: '#' }}" @if(!$studentSmsUrl) aria-disabled="true" tabindex="-1" @endif>
                                    <span class="lead-action-icon lead-icon-yellow" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4.75 5.75h14.5A1.75 1.75 0 0 1 21 7.5v9A1.75 1.75 0 0 1 19.25 18.25H4.75A1.75 1.75 0 0 1 3 16.5v-9a1.75 1.75 0 0 1 1.75-1.75Z"/>
                                            <path d="M7 9.5h10"/>
                                            <path d="M7 13h7"/>
                                            <path d="m8 18.25-2.75 2.25"/>
                                        </svg>
                                    </span>
                                    <span class="lead-action-label">Send SMS</span>
                                </a>
                                <a class="dropdown-item lead-action-item {{ $studentEmailUrl ? '' : 'is-disabled' }}" href="{{ $studentEmailUrl ?: '#' }}" @if(!$studentEmailUrl) aria-disabled="true" tabindex="-1" @endif>
                                    @include('partials.action-send-email-content')
                                </a>
                                <a class="dropdown-item lead-action-item {{ $studentWhatsappUrl ? '' : 'is-disabled' }}" href="{{ $studentWhatsappUrl ?: '#' }}" @if($studentWhatsappUrl) target="_blank" rel="noopener" @else aria-disabled="true" tabindex="-1" @endif>
                                    @include('partials.action-whatsapp-content')
                                </a>
                                @if($canAdminEdit)
                                    <a class="dropdown-item lead-action-item {{ $studentLeadId ? '' : 'is-disabled' }}" href="{{ $studentLeadId ? route('leads.show', $studentLeadId) : '#' }}" @if(!$studentLeadId) aria-disabled="true" tabindex="-1" @endif>
                                        <span class="lead-action-icon lead-icon-blue" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3.75 20.25h4.5l11-11a1.6 1.6 0 0 0 0-2.25l-2.25-2.25a1.6 1.6 0 0 0-2.25 0l-11 11v4.5Z"/>
                                                <path d="m13.5 6.5 4 4"/>
                                            </svg>
                                        </span>
                                        <span class="lead-action-label">Edit</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="profile-stats">
                        <div class="stat-tile">
                            <div class="stat-label">Total Fee</div>
                            <div class="stat-value stat-value--blue">{{ number_format((float) $totalFee, 0) }}</div>
                        </div>
                        <div class="stat-tile">
                            <div class="stat-label">Pending Fee</div>
                            <div class="stat-value stat-value--orange">{{ number_format((float) $pendingFee, 0) }}</div>
                        </div>
                        <div class="stat-tile">
                            <div class="stat-label">Total Course</div>
                            <div class="stat-value">{{ $totalCourses }}</div>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- ============ RIGHT: TAB CONTENT ============ --}}
            <section class="student-detail-content box-typical box-typical-dashboard panel panel-default">
                <ul class="student-tab-bar" role="tablist">
                    <li class="student-tab" data-target="#pane-admission" role="tab">
                        Admission History
                    </li>
                    @if($canAdmissionReview)
                        <li class="student-tab" data-target="#pane-verification" role="tab">
                            Document Verification
                        </li>
                    @endif
                    <li class="student-tab active" data-target="#pane-account" role="tab">
                        Account History
                    </li>
                    <li class="student-tab" data-target="#pane-personal" role="tab">
                        Personal Information
                    </li>
                </ul>

                {{-- ===== ADMISSION HISTORY ===== --}}
                <div class="student-pane" id="pane-admission">
                    <div class="pane-card table-responsive">
                        <table class="table table-bordered follow-table">
                            <thead>
                                <tr>
                                    <th>Course Title</th>
                                    <th>Roll Number</th>
                                    <th>Fee Status</th>
                                    <th>Total Fee</th>
                                    <th>Batch History</th>
                                    <th>Campus History</th>
                                    <th>Certificate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($admissions->isNotEmpty())
                                    @foreach($admissions as $admission)
                                        @php
                                        $admissionFees = $feeCollections->where('admission_id', $admission->id);
                                        $admissionTotal = $admissionFees->sum('net_amount');
                                        $admissionPaid = $admissionFees->where('status', 'paid')->sum('net_amount');
                                        $feeStatus = $admissionPaid >= $admissionTotal && $admissionTotal > 0 ? 'Paid' : ($admissionTotal > 0 ? 'Pending' : '—');
                                        $certificateStatus = (string) ($admission->student_status ?? '');
                                        $canRequestCertificate = auth()->user()?->hasAnyPermission('certificate.create') ?? false;
                                        $canOpenCertificateRequest = $certificateStatus === 'enrolled'
                                            && $canRequestCertificate;
                                    @endphp
                                    <tr>
                                        <td>{{ $admission->program?->title ?? $admission->program?->name ?? '—' }}</td>
                                        <td>{{ $admission->roll_number ?? '—' }}</td>
                                        <td>
                                            @if($feeStatus === 'Paid')
                                                <span class="label label-primary">Paid</span>
                                            @elseif($feeStatus === 'Pending')
                                                <span class="label label-warning">Pending</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ number_format((float) ($admission->fee_package ?? 0), 0) }}</td>
                                        <td>{{ $admission->batch?->code ?? $admission->batch?->name ?? '—' }}</td>
                                        <td>{{ $admission->campus?->code ?? $admission->campus?->name ?? '—' }}</td>
                                        <td>
                                            @if(isset($certificateStatusLabels[$certificateStatus]))
                                                <span class="label {{ $certificateStatusClasses[$certificateStatus] ?? 'label-default' }}">
                                                    {{ $certificateStatusLabels[$certificateStatus] ?? ucfirst($certificateStatus) }}
                                                </span>
                                            @elseif($canOpenCertificateRequest)
                                                <a href="{{ route('certificate.create', ['admission_id' => $admission->id]) }}"
                                                   class="label label-default student-cert-request-link"
                                                   title="Request certificate approval">
                                                    Pending
                                                </a>
                                            @elseif($admission->certificate_delivered_at)
                                                <span class="label label-default">Delivered</span>
                                            @else
                                                <span class="label label-default">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="7" class="empty-state">
                                            Student is registered only — not yet enrolled in any admission.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($canAdmissionReview)
                    <div class="student-pane" id="pane-verification">
                        <div class="pane-card table-responsive">
                            <table class="table table-bordered follow-table">
                                <thead>
                                    <tr>
                                        <th>Course Title</th>
                                        <th>Campus</th>
                                        <th>Approval Status</th>
                                        <th>Documents</th>
                                        <th>Uploaded / Reviewed</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($admissions as $admission)
                                        @php
                                            $approvalStatus = (string) ($admission->approval_status ?? \App\Models\Admission::APPROVAL_STATUS_APPROVED);
                                            $approvalLabel = match ($approvalStatus) {
                                                \App\Models\Admission::APPROVAL_STATUS_PENDING => 'Pending',
                                                \App\Models\Admission::APPROVAL_STATUS_REQUESTED => 'Requested',
                                                \App\Models\Admission::APPROVAL_STATUS_APPROVED => 'Approved',
                                                default => ucfirst(str_replace('_', ' ', $approvalStatus)),
                                            };
                                            $approvalClass = match ($approvalStatus) {
                                                \App\Models\Admission::APPROVAL_STATUS_PENDING => 'label-warning',
                                                \App\Models\Admission::APPROVAL_STATUS_REQUESTED => 'label-info',
                                                \App\Models\Admission::APPROVAL_STATUS_APPROVED => 'label-success',
                                                default => 'label-default',
                                            };
                                            $documentLinks = [
                                                'CNIC Front Side' => $admission->document_cnic_front_path
                                                    ? route('admission.documents.view', ['admission' => $admission->id, 'document' => 'cnic-front'])
                                                    : null,
                                                'CNIC Back Side' => $admission->document_cnic_back_path
                                                    ? route('admission.documents.view', ['admission' => $admission->id, 'document' => 'cnic-back'])
                                                    : null,
                                                'Admission Form' => $admission->document_admission_form_path
                                                    ? route('admission.documents.view', ['admission' => $admission->id, 'document' => 'admission-form'])
                                                    : null,
                                                'Paid Slip' => $admission->document_paid_slip_path
                                                    ? route('admission.documents.view', ['admission' => $admission->id, 'document' => 'paid-slip'])
                                                    : null,
                                            ];
                                        @endphp
                                        <tr>
                                            <td>{{ $admission->program?->title ?? $admission->program?->name ?? '—' }}</td>
                                            <td>{{ $admission->campus?->code ?? $admission->campus?->name ?? '—' }}</td>
                                            <td>
                                                <span class="label {{ $approvalClass }}">{{ $approvalLabel }}</span>
                                            </td>
                                            <td>
                                                <div class="student-doc-links">
                                                    @foreach($documentLinks as $label => $url)
                                                        @if($url)
                                                            <a class="student-doc-link" href="{{ $url }}" target="_blank" rel="noopener">{{ $label }}</a>
                                                        @else
                                                            <span class="student-doc-link is-disabled">{{ $label }}</span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td>
                                                <div class="student-verification-meta">
                                                    <div><strong>Uploaded:</strong> {{ optional($admission->documents_uploaded_at)->format('d-M-Y h:i A') ?? '—' }}</div>
                                                    <div><strong>By:</strong> {{ $admission->documentsUploadedBy?->name ?? '—' }}</div>
                                                    <div><strong>Reviewed:</strong> {{ optional($admission->approval_reviewed_at)->format('d-M-Y h:i A') ?? '—' }}</div>
                                                    <div><strong>Reviewer:</strong> {{ $admission->approvalReviewedBy?->name ?? '—' }}</div>
                                                </div>
                                            </td>
                                            <td>{{ $admission->approval_remarks ?: '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="empty-state">No admissions available for document verification.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- ===== ACCOUNT HISTORY ===== --}}
                <div class="student-pane is-active" id="pane-account">
                    <div class="pane-card table-responsive">
                        <table class="table table-bordered follow-table">
                            <thead>
                                <tr>
                                    <th>Course Title</th>
                                    <th>Amount</th>
                                    <th>Installment</th>
                                    <th>Due Date</th>
                                    <th>Collected At</th>
                                    <th>Fee Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($feeCollections as $fee)
                                    @php
                                        $feeAdmission = $fee->admission ?? $admissions->firstWhere('id', $fee->admission_id);
                                        $feeProgramTitle = $fee->fee_type === 'registration'
                                            ? 'Registration Fee'
                                            : ($feeAdmission?->program?->title
                                                ?? $feeAdmission?->program?->name
                                                ?? $fee->program?->title
                                                ?? $fee->program?->name
                                                ?? 'â€”');
                                        $admission = $feeAdmission
                                            ?? ($fee->program ? (object) ['program' => $fee->program] : null)
                                            ?? $admission
                                            ?? null;
                                        $voucherUrl = $fee->fee_type === 'registration'
                                            ? route('registration.voucher', $registration)
                                            : ($feeAdmission ? route('admission.voucher', ['admission' => $feeAdmission, 'fee_collection' => $fee->id]) : null);
                                        $nextPendingInstallment = $fee->fee_type === 'admission'
                                            ? $feeCollections
                                                ->filter(function ($candidate) use ($fee) {
                                                    return (int) $candidate->id !== (int) $fee->id
                                                        && $candidate->fee_type === 'admission'
                                                        && (int) ($candidate->admission_id ?? 0) === (int) ($fee->admission_id ?? 0)
                                                        && $candidate->status === 'pending'
                                                        && (int) ($candidate->installment_no ?? 0) > (int) ($fee->installment_no ?? 0);
                                                })
                                                ->sortBy(function ($candidate) {
                                                    return sprintf('%06d-%06d', (int) ($candidate->installment_no ?? 999999), (int) $candidate->id);
                                                })
                                                ->first()
                                            : null;
                                    @endphp
                                    <tr>
                                        <td>{{ $fee->fee_type === 'registration' ? 'Registration Fee' : ($admission?->program?->title ?? $registration->program?->title ?? '—') }}</td>
                                        <td>{{ number_format((float) ($fee->net_amount ?? $fee->amount ?? 0), 0) }}</td>
                                        <td>{{ $fee->installment_no ?? 0 }}</td>

                                    <td>{{ optional($fee->due_at ?? null)->format('Y-m-d') ?? '' }}</td>
                                    <td>{{ optional($fee->paid_at)->format('Y-m-d') ?? '—' }}</td>
                                   <td class="fee-status-cell">
                                            @if($fee->status === 'paid')
                                            <span class="label label-primary p-2 fee-status-control fee-status-control--text">Paid</span>
                                            @elseif($fee->status === 'pending')
                                            <button type="button"
                                            class="label label-warning js-fee-collect fee-status-control fee-status-control--text"
                                            title="Collect installment"
                                            data-fee-id="{{ $fee->id }}"
                                            data-fee-amount="{{ (float) ($fee->net_amount ?? $fee->amount ?? 0) }}"
                                            data-fee-receipt="{{ $fee->receipt_number ?? '' }}"
                                            data-fee-installment-no="{{ $fee->installment_no ?? '' }}"
                                            style="border:0;cursor:pointer;">
                                            Pending
                                             </button>
                                             @else
                                             <span class="label label-default">{{ ucfirst($fee->status ?? '—') }}</span>
                                             @endif

                                        </td>
                                    <td> @if($voucherUrl)
                                        <a class="btn btn-xs btn-warning fee-action-btn fee-status-control fee-status-control--icon p-2"
                                        title="View Voucher" href="{{ $voucherUrl }}" target="_blank" rel="noopener">
                                        <i class="bi bi-menu-button-wide"></i>
                                    </a>
                                    @else
                                    <span class="btn btn-xs btn-default fee-action-btn disabled" title="Voucher not available"><i class="fa fa-file-text-o"></i></span>
                                    @endif
                                    @if($canAdminEdit)
                                    <button class="btn btn-xs btn-default fee-action-btn js-fee-edit fee-status-control fee-status-control--text p-2"
                                    type="button"
                                    data-fee-id="{{ $fee->id }}"
                                    data-fee-net="{{ (float) ($fee->net_amount ?? $fee->amount ?? 0) }}"
                                    data-fee-paid-at="{{ optional($fee->paid_at)->format('Y-m-d') }}"
                                    data-fee-label="{{ $fee->fee_type === 'registration' ? 'Registration Fee' : (ucfirst($fee->fee_type ?? '') . ' Fee') }}">
                                    Edit
                                </button>
                                @endif</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="empty-state">No fee history yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @php($admission = $latestAdmission)
                {{-- ===== PERSONAL INFORMATION ===== --}}
                <div class="student-pane" id="pane-personal">
                    <div class="pane-card pane-card--info">
                        <h3 class="pane-section-title">Personal Details</h3>
                        <table class="info-table">
                            <tbody>
                                <tr>
                                    <th>Contact:</th>
                                    <td>{{ $registration->phone ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Date of Birth:</th>
                                    <td>{{ optional($registration->date_of_birth)->format('d-M-Y') ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>CNIC:</th>
                                    <td>{{ $registration->cnic ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Registration No:</th>
                                    <td>{{ $registration->registration_number ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Guardian Name:</th>
                                    <td>{{ $registration->guardian_name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Postal Address:</th>
                                    <td>{{ $registration->address ?? ($admission?->postal_address ?? '—') }}</td>
                                </tr>
                                <tr>
                                    <th>Gender:</th>
                                    <td>{{ ucfirst($registration->gender ?? '—') }}</td>
                                </tr>
                                <tr>
                                    <th>Qualification:</th>
                                    <td>{{ $registration->education ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Email Address:</th>
                                    <td>{{ $registration->email ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Registration Date:</th>
                                    <td>{{ optional($registration->registered_at ?? $registration->created_at)->format('d-M-Y') ?? '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

    {{-- ============ FEE EDIT MODAL ============ --}}
    <div class="fee-edit-modal" id="feeEditModal" aria-hidden="true">
        <div class="fee-edit-backdrop" data-close></div>
        <div class="fee-edit-dialog" role="dialog" aria-modal="true" aria-labelledby="feeEditTitle">
            <div class="fee-edit-header">
                <h4 id="feeEditTitle">Edit Fee</h4>
                <button type="button" class="fee-edit-close" data-close aria-label="Close">&times;</button>
            </div>
            <form id="feeEditForm" method="POST" action="">
                @csrf
                <div class="fee-edit-body">
                    <div class="fee-edit-label-row">
                        <span class="fee-edit-sublabel">Editing:</span>
                        <span class="fee-edit-name" id="feeEditName">—</span>
                    </div>
                    <div class="fee-edit-field">
                        <label for="fee_net_amount">Net Amount (after discount)</label>
                        <input type="number" step="0.01" min="0" id="fee_net_amount" name="net_amount" required>
                    </div>
                    <div class="fee-edit-field">
                        <label for="fee_paid_at">Paid Date</label>
                        <input type="date" id="fee_paid_at" name="paid_at" required>
                    </div>
                </div>
                <div class="fee-edit-footer">
                    <button type="button" class="btn-fee-cancel" data-close>Cancel</button>
                    <button type="submit" class="btn-fee-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============ COLLECT INSTALLMENT MODAL ============ --}}
    <div class="fee-edit-modal" id="feeCollectModal" aria-hidden="true">
        <div class="fee-edit-backdrop" data-collect-close></div>
        <div class="fee-edit-dialog" role="dialog" aria-modal="true" aria-labelledby="feeCollectTitle">
            <div class="fee-edit-header">
                <h4 id="feeCollectTitle">Collect Installment <small style="font-weight:400;opacity:.85;">(All fields are required)</small></h4>
                <button type="button" class="fee-edit-close" data-collect-close aria-label="Close">&times;</button>
            </div>
            <form id="feeCollectForm" method="POST" action="">
                @csrf
                <div class="fee-edit-body">
                    <div class="fee-edit-field">
                        <label id="feeCollectAmountLabel" for="fee_collect_amount">Installment Amount</label>
                        <input type="number" step="0.01" id="fee_collect_amount" readonly>
                    </div>
                    <div class="fee-edit-field">
                        <label for="fee_collect_paid">Paid Amount</label>
                        <input type="number" step="0.01" min="0.01" id="fee_collect_paid" name="paid_amount" required autofocus>
                    </div>
                    <div class="fee-edit-field">
                        <label for="fee_collect_remaining">Remaining Amount</label>
                        <input type="text" id="fee_collect_remaining" readonly>
                        <small class="text-muted" id="fee_collect_remaining_hint" style="font-size: var(--typo-student-show-font-size-1);display:block;margin-top:4px;"></small>
                    </div>
                    <div class="fee-edit-field">
                        <label for="fee_collect_receipt">Receipt Number <span style="color:#dc3545;">*</span></label>
                        <input type="text" id="fee_collect_receipt" readonly>
                    </div>
                </div>
                <div class="fee-edit-footer">
                    <button type="submit" class="btn-fee-save">Update</button>
                    <button type="button" class="btn-fee-cancel" data-collect-close>Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --dimension-student-show-1: 100%;
            --dimension-student-show-2: 100px;
            --dimension-student-show-3: 22px;
            --dimension-student-show-4: 24px;
            --dimension-student-show-5: 26px;
            --space-student-show-1: 12px;
            --space-student-show-2: 14px;
            --space-student-show-3: 14px 18px;
            --space-student-show-4: 16px;
            --space-student-show-5: 4px;
            --space-student-show-6: 6px;
            --space-student-show-7: 8px;
            --color-student-show-1: #00a8ff;
            --color-student-show-2: #0a96cc;
            --color-student-show-3: #1f2d3d;
            --color-student-show-4: #303740;
            --color-student-show-5: #4c5a6a;
            --color-student-show-6: #cbd5e1;
            --color-student-show-7: #e2e8f0;
            --color-student-show-8: #fff;
            --color-student-show-9: rgba(15,23,42,0.55);
            --color-student-show-10: rgba(255,255,255,0.04);
        }

        :root {
            --dimension-student-show-1: 100%;
            --dimension-student-show-2: 100px;
            --dimension-student-show-3: 22px;
            --dimension-student-show-4: 24px;
            --dimension-student-show-5: 26px;
            --space-student-show-1: 12px;
            --space-student-show-2: 14px;
            --space-student-show-3: 14px 18px;
            --space-student-show-4: 16px;
            --space-student-show-5: 4px;
            --space-student-show-6: 6px;
            --space-student-show-7: 8px;
            --typo-student-show-font-size-1: 11px;
            --typo-student-show-font-size-2: 18px;
            --typo-student-show-font-weight-3: 600;
            --typo-student-show-font-size-4: 14px;
            --typo-student-show-font-weight-5: 500;
            --typo-student-show-line-height-6: 1;
            --typo-student-show-font-size-7: 13px;
            --typo-student-show-font-size-8: 16px;
            --typo-student-show-font-size-9: 12px;
        }0___

        .ci-muted-meta {
            font-size: 0.875rem !important;
            font-weight: 400 !important;
        }

        .student-detail-shell {
            padding: 0;
        }

        .student-detail-header {
            margin-bottom: var(--space-student-show-2);
        }
        .student-detail-header .panel-body { padding: var(--space-student-show-3); }
        .student-detail-header .panel-title { margin: 0; font-size: 1.125rem !important; font-weight: 600 !important; }

        .student-detail-grid {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: var(--space-student-show-4);
            align-items: start;
        }
        @media (max-width: 991px) {
            .student-detail-grid { grid-template-columns: 1fr; }
        }

        .student-profile-card,
        .student-detail-content {
            padding: 0;
        }

        /* ==================== LEFT PROFILE CARD ==================== */
        .student-profile-card {
            overflow: hidden;
        }
        .profile-banner {
            height: 160px;
            background:
                linear-gradient(135deg, var(--color-student-show-9), var(--color-student-show-9)),
                linear-gradient(135deg, #2c3e50 0%, #4a5568 60%, #1e293b 100%);
            position: relative;
            overflow: hidden;
        }
        .profile-banner::after {
            content: '';
            position: absolute;
            inset: 0;
            background:
                repeating-linear-gradient(135deg, var(--color-student-show-10) 0 12px, transparent 12px 24px),
                repeating-linear-gradient(45deg, var(--color-student-show-10) 0 12px, transparent 12px 24px);
            pointer-events: none;
        }
        .profile-avatar-wrap {
            margin-top: -50px;
            display: flex;
            justify-content: center;
            position: relative;
            z-index: 2;
        }
        span {
    font-size: 1rem !important;
    line-height: 1.5;
}
        .student-cert-request-link {
            display: inline-block;
            cursor: pointer;
            text-decoration: none !important;
        }
        .student-cert-request-link:hover,
        .student-cert-request-link:focus {
            background: #9ca3af !important;
            color: #fff !important;
        }
        .profile-avatar {
            width: var(--dimension-student-show-2);
            height: var(--dimension-student-show-2);
            border-radius: 50%;
            border: 4px solid var(--color-student-show-8);
            background: var(--color-student-show-8);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .profile-avatar svg { display: block; width: var(--dimension-student-show-1); height: var(--dimension-student-show-1); }
        .profile-body { padding: 12px 20px 20px; text-align: center; }
        .profile-name {
            margin: 8px 0 4px;
            font-size: var(--typo-student-show-font-size-2);
            font-weight: var(--typo-student-show-font-weight-3);
            color: var(--color-student-show-3);
        }
        .profile-phone {
            color: var(--color-student-show-5);
            font-size: var(--typo-student-show-font-size-4);
            margin-bottom: var(--space-student-show-4);
        }
        .profile-action { margin-bottom: 20px; }
        .student-action-wrap { display: inline-block; position: relative; }
        .student-action-btn {
            background: transparent !important;
            color: var(--color-student-show-1) !important;
            border: 1px solid var(--color-student-show-1) !important;
            padding: 6px 18px !important;
            border-radius: 4px;
            font-weight: var(--typo-student-show-font-weight-5);
        }
        .student-action-btn:hover {
            background: var(--color-student-show-1) !important;
            color: var(--color-student-show-8) !important;
            border-color: var(--color-student-show-1) !important;
        }
        .student-action-wrap .dropdown-menu {
            display: none;

            width:237px;
            min-width: 150px !important;
            background: var(--color-student-show-8);
            border: 1px solid #dfe5eb;
            border-radius: 6px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
            padding: 10px 0;
            margin-top: 0px;
            position: absolute;
            left: 50%;
            transform: translateX(-33%);
            top: -190px;
            z-index: 50;
            text-align: left;
        }
        .student-action-wrap.is-open .dropdown-menu { display: block; }
        .student-action-wrap .dropdown-item.lead-action-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 22px;
            color: var(--color-student-show-4);
            font-size: 1.0625rem;
            font-weight: var(--typo-student-show-font-weight-5);
            line-height: 1.4;
            text-decoration: none;
        }
        .student-action-wrap .dropdown-item.lead-action-item:hover,
        .student-action-wrap .dropdown-item.lead-action-item:focus {
            background: #f7fafc;
            color: #222b33;
        }
        .student-action-wrap .dropdown-item.is-disabled {
            opacity: 0.45;
            pointer-events: none;
        }
        .student-action-wrap .lead-action-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: var(--dimension-student-show-5);
            min-width: var(--dimension-student-show-5);
            height: var(--dimension-student-show-5);
            line-height: var(--typo-student-show-line-height-6);
            flex-shrink: 0;
        }
        .student-action-wrap .lead-action-icon svg {
            display: block;
            width: var(--dimension-student-show-4);
            height: var(--dimension-student-show-4);
        }
        .student-action-wrap .lead-action-icon--whatsapp svg {
            width: var(--dimension-student-show-3);
            height: var(--dimension-student-show-3);
        }
        .student-action-wrap .lead-action-label {
            display: inline-block;
            letter-spacing: 0.01em;
        }
        .student-action-wrap .lead-icon-blue { color: #1b95ff; }
        .student-action-wrap .lead-icon-yellow { color: #f5b400; }
        .student-action-wrap .lead-icon-black { color: var(--color-student-show-4); }
        .student-action-wrap .lead-icon-green { color: #2db853; }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--space-student-show-7);
        }
        .stat-tile {
            border: 1px solid var(--color-student-show-7);
            border-radius: 4px;
            padding: 10px 6px;
            text-align: center;
        }
        .stat-label { font-size: var(--typo-student-show-font-size-7); color: var(--color-student-show-3); font-weight: var(--typo-student-show-font-weight-3); }
        .stat-value { font-size: var(--typo-student-show-font-size-8); font-weight: var(--typo-student-show-font-weight-3); margin-top: var(--space-student-show-5); color: var(--color-student-show-3); }
        .stat-value--blue { color: var(--color-student-show-2); }
        .stat-value--orange { color: #f5a623; }

        /* ==================== RIGHT CONTENT ==================== */
        .student-detail-content { min-width: 0; }

        .student-tab-bar {
            display: flex;
            margin: 0;
            padding: 0;
            list-style: none;
            background: var(--color-student-show-8);
            border-bottom: 1px solid var(--color-student-show-7);
            overflow: hidden;
        }
        .student-tab {
            flex: 1;
            padding: 14px 12px;
            text-align: center;
            cursor: pointer;
            color: var(--color-student-show-5);
            font-size: var(--typo-student-show-font-size-8);
            font-weight: var(--typo-student-show-font-weight-5);
            border-bottom: 3px solid transparent;
            transition: color 0.15s ease, border-color 0.15s ease, background 0.15s ease;
        }
        .student-tab:hover { background: #f8fbff; }
        .student-tab.active {
            color: var(--color-student-show-2);
            font-weight: var(--typo-student-show-font-weight-3);
            border-bottom-color: var(--color-student-show-2);
        }

        .student-pane { display: none; }
        .student-pane.is-active { display: block; }

        .pane-card {
            padding: var(--space-student-show-2);
        }
        .pane-card--info { padding: 18px 20px; }

        .student-pane .follow-table { margin-bottom: 0; }
        .student-pane .follow-table tbody td { vertical-align: middle; }
        .empty-state {
            text-align: center;
            color: #94a3b8;
            font-style: italic;
            padding: 22px 12px !important;
        }

        .fee-status-cell { white-space: nowrap; }
        .fee-status-cell .label { padding: 4px 10px; font-size: var(--typo-student-show-font-size-1); font-weight: var(--typo-student-show-font-weight-3); }
        .fee-status-control {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            height: 30px !important;
            line-height: 1 !important;
            vertical-align: middle;
        }
        .fee-status-control--text { min-width: 45px; }
        .fee-status-control--icon { width: 30px; }
        .fee-action-btn { margin-left: var(--space-student-show-5); padding: 3px 8px; }
        .fee-action-btn .fa { font-size: var(--typo-student-show-font-size-9); }
        .fee-action-btn.disabled { opacity: 0.5; cursor: not-allowed; }
        .js-fee-collect:hover { background: #d99020 !important; }

        .pane-section-title {
            margin: 0 0 14px;
            font-size: var(--typo-student-show-font-size-2);
            font-weight: var(--typo-student-show-font-weight-3);
            color: var(--color-student-show-3);
            padding-bottom: var(--space-student-show-1);
            border-bottom: 1px solid var(--color-student-show-7);
        }
        .info-table {
            width: var(--dimension-student-show-1);
            border-collapse: collapse;
        }
        .info-table th,
        .info-table td {
            padding: 5px 0;
            border-bottom: 1px solid #eef2f7;
            font-size: var(--typo-student-show-font-size-4);
            vertical-align: middle;
        }
        .info-table th {
            width: 28%;
            text-align: left;
            color: var(--color-student-show-3);
            font-weight: 700;
            text-transform: uppercase;
            font-size: var(--typo-student-show-font-size-9);
            letter-spacing: 0.5px;
        }
        .info-table td { color: #334155; }
        .info-table tr:last-child th,
        .info-table tr:last-child td { border-bottom: 0; }

        .student-doc-links {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .student-doc-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #0f5ca8;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.3;
            text-decoration: none;
        }
        .student-doc-link:hover,
        .student-doc-link:focus {
            background: #e0f2fe;
            color: #0c4a6e;
            text-decoration: none;
        }
        .student-doc-link.is-disabled {
            color: #94a3b8;
            background: #f8fafc;
            border-color: #e2e8f0;
            cursor: default;
        }
        .student-verification-meta {
            display: grid;
            gap: 4px;
            font-size: 12px;
            color: #475569;
        }

        .student-flash {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: var(--space-student-show-1);
            font-size: var(--typo-student-show-font-size-7);
        }
        .student-flash--error {
            background: #fef2f2;
            color: #991b1b;
            border-color: #fecaca;
        }

        .badge-pill-icon { text-decoration: none; }
        .badge-pill-icon:hover { background: #e0931a; color: var(--color-student-show-8); }
        .badge-pill-icon--muted { background: var(--color-student-show-6); cursor: not-allowed; }

        /* ============ FEE EDIT MODAL ============ */
        .fee-edit-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1050;
            align-items: center;
            justify-content: center;
        }
        .fee-edit-modal.is-open { display: flex; }
        .fee-edit-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
        }
        .fee-edit-dialog {
            position: relative;
            background: var(--color-student-show-8);
            border-radius: 8px;
            width: var(--dimension-student-show-1);
            max-width: 440px;
            margin: var(--space-student-show-4);
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            animation: feeEditIn 0.15s ease-out;
        }
        @keyframes feeEditIn {
            from { transform: translateY(-12px); opacity: 0; }
            to   { transform: translateY(0);     opacity: 1; }
        }
        .fee-edit-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--space-student-show-3);
            background: #1fb2ff;
            color: var(--color-student-show-8);
        }
        .fee-edit-header h4 { margin: 0; font-size: var(--typo-student-show-font-size-8); font-weight: var(--typo-student-show-font-weight-3); }
        .fee-edit-close {
            background: transparent;
            border: 0;
            color: var(--color-student-show-8);
            font-size: 1.5rem;
            line-height: var(--typo-student-show-line-height-6);
            cursor: pointer;
            padding: 0;
        }
        .fee-edit-body { padding: 18px; }
        .fee-edit-label-row {
            display: flex;
            gap: var(--space-student-show-6);
            font-size: var(--typo-student-show-font-size-7);
            margin-bottom: var(--space-student-show-2);
            padding-bottom: var(--space-student-show-1);
            border-bottom: 1px solid var(--color-student-show-7);
        }
        .fee-edit-sublabel { color: #64748b; }
        .fee-edit-name { font-weight: var(--typo-student-show-font-weight-3); color: var(--color-student-show-3); }
        .fee-edit-field { margin-bottom: var(--space-student-show-2); }
        .fee-edit-field label {
            display: block;
            font-size: var(--typo-student-show-font-size-7);
            font-weight: var(--typo-student-show-font-weight-3);
            color: var(--color-student-show-3);
            margin-bottom: var(--space-student-show-6);
        }
        .fee-edit-field input {
            width: var(--dimension-student-show-1);
            padding: 8px 10px;
            border: 1px solid var(--color-student-show-6);
            border-radius: 4px;
            font-size: var(--typo-student-show-font-size-4);
            color: var(--color-student-show-3);
        }
        .fee-edit-field input:focus {
            outline: 0;
            border-color: var(--color-student-show-2);
            box-shadow: 0 0 0 2px rgba(10, 150, 204, 0.15);
        }
        .fee-edit-footer {
            display: flex;
            justify-content: flex-end;
            gap: var(--space-student-show-7);
            padding: 12px 18px;
            border-top: 1px solid var(--color-student-show-7);
            background: #f8fafc;
        }
        .btn-fee-cancel,
        .btn-fee-save {
            padding: 7px 16px;
            border-radius: 4px;
            font-size: var(--typo-student-show-font-size-7);
            font-weight: var(--typo-student-show-font-weight-3);
            cursor: pointer;
            border: 0;
        }
        .btn-fee-cancel { background: var(--color-student-show-7); color: var(--color-student-show-3); }
        .btn-fee-cancel:hover { background: var(--color-student-show-6); }
        .btn-fee-save { background: #20b2aa; color: var(--color-student-show-8); }
        .btn-fee-save:hover { background: #199994; }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            // Tab switching
            const tabs = document.querySelectorAll('.student-tab');
            const panes = document.querySelectorAll('.student-pane');
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    const target = this.dataset.target;
                    if (!target) return;
                    tabs.forEach(function (t) { t.classList.remove('active'); });
                    panes.forEach(function (p) { p.classList.remove('is-active'); });
                    this.classList.add('active');
                    const pane = document.querySelector(target);
                    if (pane) pane.classList.add('is-active');
                });
            });

            // Action dropdown
            document.querySelectorAll('.student-action-btn').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const wrap = this.closest('.student-action-wrap');
                    if (!wrap) return;
                    document.querySelectorAll('.student-action-wrap.is-open').forEach(function (w) {
                        if (w !== wrap) w.classList.remove('is-open');
                    });
                    wrap.classList.toggle('is-open');
                });
            });
            document.addEventListener('click', function (e) {
                if (!e.target.closest('.student-action-wrap')) {
                    document.querySelectorAll('.student-action-wrap.is-open').forEach(function (w) {
                        w.classList.remove('is-open');
                    });
                }
            });

            // Fee edit modal
            const modal = document.getElementById('feeEditModal');
            const form = document.getElementById('feeEditForm');
            const nameEl = document.getElementById('feeEditName');
            const netInput = document.getElementById('fee_net_amount');
            const paidInput = document.getElementById('fee_paid_at');
            const feeUpdateBase = @json(url('/student/fee'));

            function openFeeModal(btn) {
                const id = btn.getAttribute('data-fee-id');
                const net = btn.getAttribute('data-fee-net') || '';
                const paid = btn.getAttribute('data-fee-paid-at') || '';
                const label = btn.getAttribute('data-fee-label') || 'Fee';
                form.action = feeUpdateBase + '/' + id;
                nameEl.textContent = label;
                netInput.value = net;
                paidInput.value = paid;
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
            }
            function closeFeeModal() {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
            }
            document.querySelectorAll('.js-fee-edit').forEach(function (btn) {
                btn.addEventListener('click', function () { openFeeModal(this); });
            });
            modal.querySelectorAll('[data-close]').forEach(function (el) {
                el.addEventListener('click', closeFeeModal);
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && modal.classList.contains('is-open')) {
                    closeFeeModal();
                }
            });

            // Collect installment modal
            const collectModal = document.getElementById('feeCollectModal');
            const collectForm = document.getElementById('feeCollectForm');
            const collectAmount = document.getElementById('fee_collect_amount');
            const collectAmountLabel = document.getElementById('feeCollectAmountLabel');
            const collectPaid = document.getElementById('fee_collect_paid');
            const collectRemaining = document.getElementById('fee_collect_remaining');
            const collectRemainingHint = document.getElementById('fee_collect_remaining_hint');
            const collectReceipt = document.getElementById('fee_collect_receipt');
            const feeCollectBase = @json(url('/student/fee'));
            let collectCanRedistribute = false;
            let collectNextInstallmentNo = '';

            function ordinal(n) {
                const s = ['th', 'st', 'nd', 'rd'];
                const v = n % 100;
                return n + (s[(v - 20) % 10] || s[v] || s[0]);
            }

            function openCollectModal(btn) {
                const id = btn.getAttribute('data-fee-id');
                const amount = parseFloat(btn.getAttribute('data-fee-amount')) || 0;
                const receipt = btn.getAttribute('data-fee-receipt') || '';
                const installmentNo = btn.getAttribute('data-fee-installment-no');
                collectCanRedistribute = btn.getAttribute('data-fee-can-redistribute') === '1';
                collectNextInstallmentNo = btn.getAttribute('data-fee-next-installment-no') || '';
                collectForm.action = feeCollectBase + '/' + id + '/collect';
                collectAmount.value = amount.toFixed(2);
                collectAmountLabel.textContent = installmentNo
                    ? ordinal(parseInt(installmentNo, 10)) + ' Installment Amount'
                    : 'Installment Amount';
                collectPaid.value = amount.toFixed(2);
                collectReceipt.value = receipt;
                updateCollectRemaining();
                collectModal.classList.add('is-open');
                collectModal.setAttribute('aria-hidden', 'false');
                setTimeout(function () { collectPaid.focus(); collectPaid.select(); }, 50);
            }
            function closeCollectModal() {
                collectModal.classList.remove('is-open');
                collectModal.setAttribute('aria-hidden', 'true');
            }
            function updateCollectRemaining() {
                const amt = parseFloat(collectAmount.value) || 0;
                const paid = parseFloat(collectPaid.value) || 0;
                const diff = Math.round((amt - paid) * 100) / 100;
                collectRemaining.value = diff.toFixed(2);
                if (diff > 0) {
                    if (collectCanRedistribute && collectNextInstallmentNo) {
                        collectRemainingHint.textContent = 'Shortfall of ' + diff.toFixed(2) + ' will be added to the ' + ordinal(parseInt(collectNextInstallmentNo, 10)) + ' installment.';
                        collectRemainingHint.style.color = '#d97706';
                    } else {
                        collectRemainingHint.textContent = 'Exact installment amount is required because no next pending installment is available.';
                        collectRemainingHint.style.color = '#dc2626';
                    }
                } else if (diff < 0) {
                    if (collectCanRedistribute) {
                        collectRemainingHint.textContent = 'Excess of ' + Math.abs(diff).toFixed(2) + ' will be deducted from the next pending installment(s).';
                        collectRemainingHint.style.color = '#0a96cc';
                    } else {
                        collectRemainingHint.textContent = 'Exact installment amount is required because no next pending installment is available.';
                        collectRemainingHint.style.color = '#dc2626';
                    }
                } else {
                    collectRemainingHint.textContent = 'Exact installment amount.';
                    collectRemainingHint.style.color = '#16a34a';
                }
            }
            document.querySelectorAll('.js-fee-collect').forEach(function (btn) {
                btn.addEventListener('click', function () { openCollectModal(this); });
            });
            collectModal.querySelectorAll('[data-collect-close]').forEach(function (el) {
                el.addEventListener('click', closeCollectModal);
            });
            collectPaid.addEventListener('input', updateCollectRemaining);
            collectForm.addEventListener('submit', function (event) {
                const amt = parseFloat(collectAmount.value) || 0;
                const paid = parseFloat(collectPaid.value) || 0;
                const diff = Math.round((amt - paid) * 100) / 100;

                if (diff !== 0 && !collectCanRedistribute) {
                    event.preventDefault();
                    const message = 'Exact installment amount is required because no next pending installment is available.';

                    if (window.swal) {
                        swal({
                            title: 'Error',
                            text: message,
                            type: 'error'
                        });
                    } else {
                        window.alert(message);
                    }
                }
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && collectModal.classList.contains('is-open')) {
                    closeCollectModal();
                }
            });
        })();
    </script>
@endpush
