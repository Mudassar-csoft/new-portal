@extends('layouts.theme')

@section('title', 'Admission Status')

@section('content')
    @php
        $admissions = $admissions ?? collect();
        $activeScope = $activeScope ?? 'pending';
        $activePeriod = $activePeriod ?? 'all';
        $scopeCounts = $scopeCounts ?? [];
        $periodCounts = $periodCounts ?? [];
        $search = $search ?? '';
        $perPage = $perPage ?? 25;
        $campuses = $campuses ?? collect();
        $programs = $programs ?? collect();
        $campusId = $campusId ?? 0;
        $programId = $programId ?? 0;
        $fromDate = $fromDate ?? '';
        $toDate = $toDate ?? '';
        $currentUser = auth()->user();
        $canStudentView = $currentUser?->hasAnyPermission(['student.view']) ?? false;
        $canAdmissionView = $currentUser?->hasAnyPermission(['admission.view']) ?? false;
        $canAdmissionUpload = $currentUser?->hasAnyPermission(['admission.create', 'admission.update']) ?? false;
        $canReviewApproval = $currentUser?->hasAnyPermission(['admission.review']) ?? false;
        $reviewerOnly = $canReviewApproval && ! $canAdmissionView;

        $scopes = $reviewerOnly
            ? [
                'requested' => 'Request for Approval',
            ]
            : [
                'all' => 'All Admissions',
                'pending' => 'Pending',
                'requested' => 'Request for Approval',
                'approved' => 'Approved Admission',
            ];

        $scopeBadgeColors = [
            'all' => 'badge-secondary',
            'pending' => 'badge-warning',
            'requested' => 'badge-info',
            'approved' => 'badge-success',
        ];

        $periods = [
            'all' => 'All Admissions',
            'today' => 'Today Admission',
            'month' => 'Current Month',
            'year' => 'Current Year',
        ];
    @endphp

    <div class="adm-status-shell">
        @include('partials.session-status-alert')

        @include('partials.session-error-alert')

        @if($errors->any())
            <div class="alert alert-danger">
                {{ collect($errors->all())->first() }}
            </div>
        @endif

        <div class="follow-card box-typical box-typical-dashboard panel panel-default">
            <div class="follow-tab-bar">
                @foreach ($scopes as $scopeKey => $scopeLabel)
                    <a
                        href="{{ route('admission.status', ['scope' => $scopeKey]) }}"
                        class="follow-tab {{ $activeScope === $scopeKey ? 'active' : '' }}"
                    >
                        <span class="label-text">{{ $scopeLabel }}</span>
                        <span class="badge {{ $scopeBadgeColors[$scopeKey] ?? 'badge-secondary' }}">{{ (int) ($scopeCounts[$scopeKey] ?? 0) }}</span>
                    </a>
                @endforeach
            </div>

            <div class="box-typical-body panel-body follow-body">
<form method="GET" action="{{ route('admission.status') }}" class="follow-controls adm-filter-form">
					<input type="hidden" name="scope" value="{{ $activeScope }}">
					<input type="hidden" name="period" value="{{ $activeScope === 'all' && $activePeriod !== 'all' ? $activePeriod : '' }}">
					<div class="d-flex control-flow-show-bar ci-inline-gap-05-center">
						<label class="mb-0">Show</label>
						<select name="per_page" class="form-control form-control-sm" onchange="this.form.submit()">
							@foreach ([10, 25, 50, 100] as $option)
								<option value="{{ $option }}" @selected((int) $perPage === $option)>{{ $option }}</option>
							@endforeach
						</select>
						<label class="mb-0">Entries</label>
					</div>

					<div class="adm-filter-row">
						<div class="adm-filter-field">
							<label class="form-label">Campus</label>
							<select name="campus_id" class="form-control form-control-sm" onchange="this.form.submit()">
								<option value="0">All Campuses</option>
								@foreach ($campuses as $campus)
									<option value="{{ $campus->id }}" @selected((int) $campusId === (int) $campus->id)>{{ $campus->code ? $campus->code . ' - ' . $campus->name : $campus->name }}</option>
								@endforeach
							</select>
						</div>

						<div class="adm-filter-field">
							<label class="form-label">Programme</label>
							<select name="program_id" class="form-control form-control-sm" onchange="this.form.submit()">
								<option value="0">All Programs</option>
								@foreach ($programs as $program)
									<option value="{{ $program->id }}" @selected((int) $programId === (int) $program->id)>{{ $program->title ?? $program->name }}</option>
								@endforeach
							</select>
						</div>

						<div class="adm-filter-field adm-date-range-field">
							<label class="form-label">Created Date Range</label>
							<div class="adm-date-range-inputs">
								<input type="date" name="from" value="{{ $fromDate }}" class="form-control form-control-sm" onchange="this.form.submit()">
								<span class="adm-date-range-separator">to</span>
								<input type="date" name="to" value="{{ $toDate }}" class="form-control form-control-sm" onchange="this.form.submit()">
							</div>
						</div>

						<div class="adm-filter-field">
							<label class="form-label">Search</label>
							<div class="follow-search">
								<input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search...">
							</div>
						</div>

						<div class="adm-filter-actions">
							<a href="{{ route('admission.status', array_filter(['scope' => $activeScope, 'period' => $activeScope === 'all' ? $activePeriod : null, 'per_page' => $perPage])) }}" class="btn btn-primary btn-sm">Reset</a>
						</div>
					</div>
				</form>

                <!-- <form method="GET" action="{{ route('admission.status') }}" class="follow-controls">
                    <input type="hidden" name="scope" value="{{ $activeScope }}">
                    @if($activeScope === 'all')
                        <input type="hidden" name="period" value="{{ $activePeriod }}">
                    @endif
                    <input type="hidden" name="per_page" value="{{ $perPage }}">

                    <div class="follow-status-copy">
                        {{ $activeScope === 'all' ? ($periods[$activePeriod] ?? 'All Admissions') : ($scopes[$activeScope] ?? 'Admission Status') }}
                    </div>
                    <div class="follow-search">
                        <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search...">
                        <button type="submit" class="adm-search-submit" aria-label="Search">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </form> -->

                <div class="table-responsive">
                    <table class="table table-bordered follow-table" id="adm-table">
                        <thead>
                            <tr>
                                <th>Sr</th>
                                <th>Name</th>
                                <th>Course Title</th>
                                <th>Primary Contact</th>
                                <th>Campus Code</th>
                                <th>Date</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($admissions as $idx => $row)
                                <tr data-entry-row="1">
                                    <td class="text-center">{{ ($admissions->firstItem() ?? 1) + $idx }}</td>
                                    <td>
                                        @if($row->registration_id && ($canStudentView || $canReviewApproval))
                                            <a href="{{ route('student.show', $row->registration_id) }}" class="adm-name-link" title="View student detail">
                                                {{ $row->student_name }}
                                            </a>
                                        @else
                                            {{ $row->student_name }}
                                        @endif
                                    </td>
                                    <td>{{ $row->program->title ?? $row->program->name ?? 'N/A' }}</td>
                                    <td>{{ $row->phone ?? 'N/A' }}</td>
                                    <td>{{ $row->campus->code ?? 'N/A' }}</td>
                                    <td>{{ optional($row->admission_date ?? $row->created_at)->format('d-M-Y') ?? 'N/A' }}</td>
                                    <td class="action-cell">
                                        @include('admission.partials.action', [
                                            'actionId' => 'adm-action-' . $idx,
                                            'admission' => $row,
                                            'leadId' => $row->lead_id ?? null,
                                        ])
                                    </td>
                                </tr>
                            @empty
                                <tr data-empty-row="1">
                                    <td colspan="7" class="text-center text-muted">No admissions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="follow-footer">
                    @include('partials.follow-pagination', ['paginator' => $admissions, 'countId' => 'adm-count'])
                </div>
            </div>
        </div>
    </div>

    @if($canAdmissionUpload)
        <div class="admission-modal" id="uploadDocumentsModal" aria-hidden="true">
            <div class="admission-modal__backdrop" data-admission-modal-close></div>
            <div class="admission-modal__dialog">
                <div class="admission-modal__header">
                    <h4 class="admission-modal__title">Upload Admission Documents</h4>
                    <button type="button" class="admission-modal__close" data-admission-modal-close>&times;</button>
                </div>
                <form id="uploadDocumentsForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="admission-modal__body">
                        <p class="admission-modal__student" id="uploadDocumentsStudent"></p>
                        <div class="alert alert-warning admission-modal__notice" id="uploadDocumentsRemark" style="display:none;"></div>

                        <div class="admission-modal__previous-docs" id="uploadDocumentsPreviousDocs" style="display:none;">
                            <p class="admission-modal__previous-docs-title">Previously Submitted Documents</p>
                            <div class="admission-doc-tabs">
                                <a href="#" target="_blank" rel="noopener" class="admission-doc-tab" id="uploadPrevDocCnic" style="display:none;">CNIC Front</a>
                                <a href="#" target="_blank" rel="noopener" class="admission-doc-tab" id="uploadPrevDocCnicBack" style="display:none;">CNIC Back</a>
                                <a href="#" target="_blank" rel="noopener" class="admission-doc-tab" id="uploadPrevDocForm" style="display:none;">Form</a>
                                <a href="#" target="_blank" rel="noopener" class="admission-doc-tab" id="uploadPrevDocSlip" style="display:none;">Fee Slip</a>
                            </div>
                        </div>

                        <div class="alert alert-info admission-modal__notice" id="uploadDocumentsScannerNotice" style="display:none;"></div>

                        <div class="form-group">
                            <label>Identity Document Type <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap" style="gap: 1rem;">
                                <label class="mb-0">
                                    <input type="radio" name="identity_document_type" value="cnic" checked>
                                    CNIC
                                </label>
                                <label class="mb-0">
                                    <input type="radio" name="identity_document_type" value="b_form">
                                    B-Form
                                </label>
                            </div>
                        </div>

                      <!-- Primary Identity Document -->
<div class="form-group">
    <label><span id="identityDocumentPrimaryLabelText">CNIC Front Side</span> <span class="text-danger">*</span></label>

    <div class="admission-doc-upload-row">
        <div class="admission-preview-card is-empty" id="preview-cnic-front">
            <div class="admission-upload-tools">
                <button type="button"
                        id="identityDocumentPrimaryScanButton"
                        class="btn btn-default btn-sm js-admission-scan mt-0"
                        data-input-name="document_cnic_front"
                        data-document-label="CNIC Front Side">
                    Scanner
                </button>
            </div>

            <img class="admission-preview-card__image" alt="CNIC front side preview">
            <iframe class="admission-preview-card__frame" title="CNIC front side preview"></iframe>
            <div class="admission-preview-card__meta"></div>
        </div>

        <input type="file"
               name="document_cnic_front"
               class="form-control js-admission-doc-input admission-doc-file-input"
               accept=".jpg,.jpeg,.png,.pdf"
               data-preview-target="preview-cnic-front"
               required>
    </div>

    <span class="admission-upload-tools__hint">
        Use scanner if helper is installed. Otherwise file picker will open.
    </span>
</div>

<!-- CNIC Back Side -->
<div class="form-group" id="identityDocumentBackGroup">
    <label>CNIC Back Side <span class="text-danger">*</span></label>

    <div class="admission-doc-upload-row">
        <div class="admission-preview-card is-empty" id="preview-cnic-back">
            <div class="admission-upload-tools">
                <button type="button"
                        id="identityDocumentBackScanButton"
                        class="btn btn-default btn-sm js-admission-scan mt-0"
                        data-input-name="document_cnic_back"
                        data-document-label="CNIC Back Side">
                    Scanner
                </button>
            </div>

            <img class="admission-preview-card__image" alt="CNIC back side preview">
            <iframe class="admission-preview-card__frame" title="CNIC back side preview"></iframe>
            <div class="admission-preview-card__meta"></div>
        </div>

        <input type="file"
               name="document_cnic_back"
               class="form-control js-admission-doc-input admission-doc-file-input"
               accept=".jpg,.jpeg,.png,.pdf"
               data-preview-target="preview-cnic-back"
               required>
    </div>

    <span class="admission-upload-tools__hint">
        Use scanner if helper is installed. Otherwise file picker will open.
    </span>
</div>

<!-- Admission Form -->
<div class="form-group">
    <label>Admission Form <span class="text-danger">*</span></label>

    <div class="admission-doc-upload-row">
        <div class="admission-preview-card is-empty" id="preview-admission-form">
            <div class="admission-upload-tools">
                <button type="button"
                        class="btn btn-default btn-sm js-admission-scan mt-0"
                        data-input-name="document_admission_form"
                        data-document-label="Admission Form">
                    Scanner
                </button>
            </div>

            <img class="admission-preview-card__image" alt="Admission form preview">
            <iframe class="admission-preview-card__frame" title="Admission form preview"></iframe>
            <div class="admission-preview-card__meta"></div>
        </div>

        <input type="file"
               name="document_admission_form"
               class="form-control js-admission-doc-input admission-doc-file-input"
               accept=".jpg,.jpeg,.png,.pdf"
               data-preview-target="preview-admission-form"
               required>
    </div>

    <span class="admission-upload-tools__hint">
        Use scanner if helper is installed. Otherwise file picker will open.
    </span>
</div>

<!-- Paid Slip -->
<div class="form-group mb-0">
    <label>Paid Slip With Authorized Stamp <span class="text-danger">*</span></label>

        <div class="admission-doc-upload-row">
            <div class="admission-preview-card is-empty" id="preview-paid-slip">
                <div class="admission-upload-tools">
                    <button type="button"
                            class="btn btn-default btn-sm js-admission-scan mt-0"
                            data-input-name="document_paid_slip"
                            data-document-label="Paid Slip With Authorized Stamp">
                        Scanner
                    </button>
                    </div>

                    <img class="admission-preview-card__image" alt="Paid slip preview">
                    <iframe class="admission-preview-card__frame" title="Paid slip preview"></iframe>
                    <div class="admission-preview-card__meta"></div>
                    </div>

                    <input type="file"
                        name="document_paid_slip"
                        class="form-control js-admission-doc-input admission-doc-file-input"
                        accept=".jpg,.jpeg,.png,.pdf"
                        data-preview-target="preview-paid-slip"
                        required>
                    </div>

                    <span class="admission-upload-tools__hint">
                        Use scanner if helper is installed. Otherwise file picker will open.
                    </span>
                    </div>
                    </div>
                    <div class="admission-modal__footer">
                        <button type="submit" class="btn btn-primary-outline">Upload Documents</button>
                        <button type="button" class="btn btn-danger-outline" data-admission-modal-close>Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($canReviewApproval)
        <div class="admission-modal" id="reviewAdmissionModal" aria-hidden="true">
            <div class="admission-modal__backdrop" data-admission-review-close></div>
            <div class="admission-modal__dialog admission-review-modal__dialog">
                <div class="admission-modal__header">
                    <h4 class="admission-modal__title">Review Admission Request</h4>
                    <button type="button" class="admission-modal__close" data-admission-review-close>&times;</button>
                </div>
                <form id="reviewAdmissionForm" method="POST" class="admission-review-modal__form">
                    @csrf
                    <div class="admission-review-modal__body">
                        <div class="admission-review-modal__pane admission-review-modal__pane--info">
                            <p class="admission-modal__student" id="reviewAdmissionStudent"></p>

                            <table class="admission-review-details">
                                <tbody>
                                    <tr>
                                        <th>Guardian Name</th>
                                        <td id="reviewAdmissionFather">—</td>
                                    </tr>
                                    <tr>
                                        <th>Contact</th>
                                        <td id="reviewAdmissionPhone">—</td>
                                    </tr>
                                    <tr>
                                        <th>Date of Birth</th>
                                        <td id="reviewAdmissionDob">—</td>
                                    </tr>
                                    <tr>
                                        <th>CNIC</th>
                                        <td id="reviewAdmissionCnic">—</td>
                                    </tr>
                                    <tr>
                                        <th>Address</th>
                                        <td id="reviewAdmissionAddress">—</td>
                                    </tr>
                                    <tr>
                                        <th>Gender</th>
                                        <td id="reviewAdmissionGender">—</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td id="reviewAdmissionEmail">—</td>
                                    </tr>
                                    <tr>
                                        <th>Program</th>
                                        <td id="reviewAdmissionProgram">—</td>
                                    </tr>
                                    <tr>
                                        <th>Session</th>
                                        <td id="reviewAdmissionSession">—</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td id="reviewAdmissionStatus">—</td>
                                    </tr>
                                    <tr>
                                        <th>Admission Date</th>
                                        <td id="reviewAdmissionAdmissionDate">—</td>
                                    </tr>
                                    <tr>
                                        <th>Registration Date</th>
                                        <td id="reviewAdmissionRegistrationDate">—</td>
                                    </tr>
                                    <tr>
                                        <th>Fee Package</th>
                                        <td id="reviewAdmissionFeePackage">—</td>
                                    </tr>
                                    <tr>
                                        <th>Discount %/Amount</th>
                                        <td id="reviewAdmissionDiscount">—</td>
                                    </tr>
                                    <tr>
                                        <th>Payment Plan</th>
                                        <td id="reviewAdmissionFeePlan">—</td>
                                    </tr>
                                    <tr>
                                        <th>Paid Installment</th>
                                        <td id="reviewAdmissionPaidInstallment">—</td>
                                    </tr>
                                    <tr>
                                        <th>Pending Installment</th>
                                        <td id="reviewAdmissionPendingInstallment">—</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="alert alert-secondary admission-modal__notice" id="reviewAdmissionIdentityType">
                                Identity Document Type: <strong id="reviewAdmissionIdentityTypeValue">CNIC</strong>
                            </div>

                            <div class="alert alert-info admission-modal__notice" id="reviewAdmissionPreviousRemark" style="display:none;"></div>

                            <div class="form-group mb-0">
                                <label>Remarks <span class="text-danger">*</span></label>
                                <textarea name="approval_remarks" id="reviewAdmissionRemarks" rows="3" class="form-control" required placeholder="Write approval or revert remarks"></textarea>
                            </div>
                        </div>
                        <div class="admission-review-modal__pane admission-review-modal__pane--preview">
                            <div class="admission-review-modal__preview-frame-wrap">
                                <iframe id="reviewDocumentPreviewFrame" class="admission-review-modal__preview-frame" title="Admission document preview"></iframe>
                                <div class="admission-review-modal__preview-empty" id="reviewDocumentPreviewEmpty">Select a document below to preview it here.</div>
                                <div class="admission-review-modal__zoom-controls" id="reviewZoomControls" style="display:none;">
                                    <button type="button" class="admission-review-modal__zoom-btn" id="reviewZoomOut" title="Zoom out">&minus;</button>
                                    <span class="admission-review-modal__zoom-level" id="reviewZoomLevel">100%</span>
                                    <button type="button" class="admission-review-modal__zoom-btn" id="reviewZoomIn" title="Zoom in">+</button>
                                    <button type="button" class="admission-review-modal__zoom-btn" id="reviewZoomReset" title="Reset zoom">Reset</button>
                                </div>
                            </div>

                            <div class="admission-doc-tabs" id="reviewDocTabs">
                                <button type="button" class="admission-doc-tab js-review-doc-preview" id="reviewDocCnic" data-doc-title="CNIC Front Side">
                                    <span id="reviewIdentityPrimaryLabel">CNIC Front</span>
                                </button>
                                <button type="button" class="admission-doc-tab js-review-doc-preview" id="reviewDocCnicBack" data-doc-title="CNIC Back Side">
                                    <span id="reviewIdentityBackLabel">CNIC Back</span>
                                </button>
                                <button type="button" class="admission-doc-tab js-review-doc-preview" id="reviewDocForm" data-doc-title="Admission Form">
                                    Form
                                </button>
                                <button type="button" class="admission-doc-tab js-review-doc-preview" id="reviewDocSlip" data-doc-title="Paid Slip With Authorized Stamp">
                                    Fee Slip
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="admission-modal__footer admission-modal__footer--split">
                        <button type="button" class="btn btn-danger-outline" data-admission-review-close>Cancel</button>
                        <div class="admission-modal__actions">
                            <button type="submit" name="review_action" value="revert" class="btn btn-danger-outline">Revert</button>
                            <button type="submit" name="review_action" value="approve" class="btn btn-primary-outline">Approve</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('styles')
    <style>
        :root {
            --dimension-admission-status-1: 100%;
            --dimension-admission-status-2: 35px;
            --space-admission-status-1: 10px;
            --space-admission-status-2: 12px;
            --space-admission-status-3: 14px 18px;
            --space-admission-status-4: 16px;
            --space-admission-status-5: 8px;
            --space-admission-status-6: 9px;
            --color-admission-status-1: #0a6fd1;
            --color-admission-status-2: #1593ff;
            --color-admission-status-3: #334155;
            --color-admission-status-4: #64748b;
            --color-admission-status-5: #94a3b8;
            --color-admission-status-6: #dbe3ec;
            --color-admission-status-7: #e1efff;
            --color-admission-status-8: #f8fafc;
            --color-admission-status-9: #f8fbff;
            --color-admission-status-10: #fff;
        }

        :root {
            --dimension-admission-status-1: 100%;
            --dimension-admission-status-2: 35px;
            --space-admission-status-1: 10px;
            --space-admission-status-2: 12px;
            --space-admission-status-3: 14px 18px;
            --space-admission-status-4: 16px;
            --space-admission-status-5: 8px;
            --space-admission-status-6: 9px;
            --typo-admission-status-font-weight-1: 600;
            --typo-admission-status-line-height-2: 1;
            --typo-admission-status-font-size-3: 12px;
        }0___

        .action-cell {
            min-width: 180px;
            white-space: nowrap;
            position: relative;
        }

        .adm-name-link {
            color: var(--color-admission-status-1);
            font-weight: var(--typo-admission-status-font-weight-1);
            text-decoration: none;
            border-bottom: 1px dashed transparent;
            transition: color 0.15s ease, border-color 0.15s ease;
        }

        .adm-name-link:hover {
            color: #0958a8;
            border-bottom-color: var(--color-admission-status-1);
            text-decoration: none;
        }

        .admission-doc-upload-row {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admission-doc-file-input {
            width: 350px;
        }

        .follow-card,
        .follow-body,
        .table-responsive {
            overflow: visible !important;
        }

        .adm-status-shell .select2-hidden-accessible {
            width: 1px !important;
        }

        .follow-tab-bar .follow-tab {
            text-decoration: none;
        }

        .follow-tab-bar--sub {
            border-top: 1px solid #eef2f7;
            background: var(--color-admission-status-9);
        }

        .follow-tab-bar--sub .follow-tab {
            padding-top: var(--space-admission-status-1);
            padding-bottom: var(--space-admission-status-1);
        }

        .follow-tab-bar--sub .follow-tab.active {
            background: #eaf4ff;
        }

        .follow-controls {
            display: flex;
            align-items: center;
            gap: var(--space-admission-status-2);
            margin-bottom: var(--space-admission-status-4);
            flex-wrap: wrap;
        }

        .ci-inline-gap-05-center {
            gap: 0.5rem;
            align-items: center;
        }

        .follow-status-copy {
            font-size: 0.9375rem;
            font-weight: var(--typo-admission-status-font-weight-1);
            color: var(--color-admission-status-3);
        }

        .follow-search {
            display: flex;
            align-items: center;
            gap: var(--space-admission-status-5);
        }

        .follow-search .form-control {
            width: min(280px, 100%);
        }

        .adm-filter-row {
            display: flex;
            flex: 1 1 100%;
            flex-wrap: wrap;
            align-items: end;
            gap: 14px;
        }

        .adm-filter-field {
            flex: 1 1 220px;
            min-width: 210px;
        }

        .adm-filter-field .form-label {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #54667a;
            margin-bottom: 4px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .adm-date-range-field {
            flex: 1.4 1 320px;
        }

        .adm-date-range-inputs {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .adm-date-range-inputs .form-control {
            min-width: 0;
        }

        .adm-date-range-separator {
            color: #6b7b8f;
            font-size: 0.8125rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .adm-filter-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .adm-search-submit {
            border: 0;
            background: transparent;
            color: #8a97a8;
            line-height: var(--typo-admission-status-line-height-2);
            padding: 0;
        }

        .adm-search-submit:hover,
        .adm-search-submit:focus {
            color: var(--color-admission-status-2);
        }

        .admission-action-dropdown {
            position: relative;
        }

        .admission-action-dropdown .dropdown-menu {
            min-width: 292px;
            /* position: absolute !important;
            top: 100% !important;
            left: auto !important;
            right: 0 !important;
            margin-top: 6px !important;
            margin-right: 0 !important;
            transform: none !important; */
            z-index: 9999;
        }

        .admission-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1100;
            align-items: center;
            justify-content: center;
            padding: var(--space-admission-status-4);
        }

        .admission-modal.is-open {
            display: flex;
        }

        .admission-modal__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.58);
        }

        .admission-modal__dialog {
            position: relative;
            width: var(--dimension-admission-status-1);
            max-width: 560px;
            background: var(--color-admission-status-10);
            border-radius: 8px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.24);
            overflow: hidden;
        }

        .admission-modal__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: var(--space-admission-status-3);
            background: var(--color-admission-status-2);
            color: var(--color-admission-status-10);
        }

        .admission-modal__title {
            margin: 0;
            font-size: 1.0625rem;
            font-weight: var(--typo-admission-status-font-weight-1);
        }

        .admission-modal__close {
            border: 0;
            background: transparent;
            color: var(--color-admission-status-10);
            font-size: 1.875rem !important;
            line-height: var(--typo-admission-status-line-height-2);
            cursor: pointer;
            padding: 0;
        }

        .admission-modal__body {
            padding: 18px;
            overflow-y: auto;
            max-height: 80vh !important;
        }

        .admission-modal__student {
            margin: 0 0 16px;
            font-size: 0.875rem;
            font-weight: var(--typo-admission-status-font-weight-1);
            color: #1f2937;
        }

        .admission-modal__notice {
            margin-bottom: 5px;
        }
    .alert{
        font-size:0.75rem !important;
    }
        .admission-upload-tools {
            display: flex;
            align-items: center;
            gap: var(--space-admission-status-1);
            margin-bottom: 0px;
            flex-wrap: wrap;
        }

        .admission-upload-tools__hint {
            color: var(--color-admission-status-4);
            font-size: var(--typo-admission-status-font-size-3);
            line-height: 1.4;
        }

        .admission-preview-card {
            margin-top: 2px;
            padding: 6px;
            border: 1px solid var(--color-admission-status-6);
            border-radius: 6px;
            background: var(--color-admission-status-9);
        }

        .admission-preview-card__empty {
            color: var(--color-admission-status-4);
            font-size: var(--typo-admission-status-font-size-3);
        }

        .admission-preview-card__image,
        .admission-preview-card__frame {
            display: none;
            width: var(--dimension-admission-status-1);
            border: 0;
            border-radius: 4px;
            background: var(--color-admission-status-10);
        }

        .admission-preview-card__image {
            max-height: 72px;
            max-width: 100px;

            object-fit: contain;
        }

        .admission-preview-card__frame {
            height: 220px;
        }

        .admission-preview-card__meta {
            display: none;
            margin-top: var(--space-admission-status-5);
            font-size: var(--typo-admission-status-font-size-3);
            color: var(--color-admission-status-3);
            word-break: break-word;
        }

        .admission-modal__footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: var(--space-admission-status-1);
            padding: var(--space-admission-status-3);
            background: var(--color-admission-status-8);
            border-top: 1px solid #e2e8f0;
        }

        .admission-modal__footer--split {
            justify-content: space-between;
        }

        .admission-modal__actions {
            display: flex;
            gap: var(--space-admission-status-1);
        }

        .admission-review-modal__dialog {
            max-width: 1080px;
            height: min(88vh, 760px);
            display: flex;
            flex-direction: column;
        }

        .admission-review-modal__form {
            display: flex;
            flex-direction: column;
            min-height: 0;
            flex: 1;
        }

        .admission-review-modal__body {
            display: flex;
            flex: 1;
            min-height: 0;
        }

        .admission-review-modal__pane {
            padding: 18px;
            overflow-y: auto;
        }

        .admission-review-modal__pane--info {
            flex: 0 0 35%;
            max-width: 35%;
            border-right: 1px solid #e2e8f0;
        }

        .admission-review-modal__pane--preview {
            flex: 0 0 65%;
            max-width: 65%;
            display: flex;
            flex-direction: column;
            background: var(--color-admission-status-8);
        }

        .admission-review-details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 0.8125rem;
        }

        .admission-review-details th {
            width: 38%;
            text-align: left;
            padding: 5px 8px 5px 0;
            color: var(--color-admission-status-4);
            font-weight: var(--typo-admission-status-font-weight-1);
            vertical-align: top;
        }

        .admission-review-details td {
            padding: 5px 0;
            color: #1f2937;
            word-break: break-word;
        }

        .admission-review-modal__preview-frame-wrap {
            position: relative;
            flex: 1;
            min-height: 260px;
            margin-bottom: var(--space-admission-status-2);
        }

        .admission-review-modal__preview-frame {
            width: 100%;
            height: 100%;
            border: 1px solid var(--color-admission-status-6);
            border-radius: 6px;
            background: white;
            display: none;
            position: absolute;
            inset: 0;
        }

        .admission-review-modal__preview-frame.is-active {
            display: block;
        }

        .admission-review-modal__preview-empty {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 20px;
            color: var(--color-admission-status-5);
            font-size: 0.875rem;
            border: 1px dashed var(--color-admission-status-6);
            border-radius: 6px;
            background: var(--color-admission-status-9);
        }

        .admission-review-modal__zoom-controls {
            position: absolute;
            top: 8px;
            right: 8px;
            z-index: 5;
            display: flex;
            align-items: center;
            gap: 4px;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid var(--color-admission-status-6);
            border-radius: 6px;
            padding: 4px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.18);
        }

        .admission-review-modal__zoom-btn {
            width: 26px;
            height: 26px;
            padding: 0;
            border: 1px solid var(--color-admission-status-6);
            border-radius: 4px;
            background: var(--color-admission-status-10);
            color: var(--color-admission-status-3);
            font-weight: var(--typo-admission-status-font-weight-1);
            font-size: 14px;
            line-height: 1;
            cursor: pointer;
        }

        #reviewZoomReset {
            width: auto;
            padding: 0 8px;
            font-size: var(--typo-admission-status-font-size-3);
        }

        .admission-review-modal__zoom-btn:hover:not(:disabled) {
            background: #eef6ff;
        }

        .admission-review-modal__zoom-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .admission-review-modal__zoom-level {
            min-width: 40px;
            text-align: center;
            font-size: var(--typo-admission-status-font-size-3);
            font-weight: var(--typo-admission-status-font-weight-1);
            color: var(--color-admission-status-4);
        }

        .admission-doc-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: var(--space-admission-status-1);
        }

        .admission-doc-tab {
            flex: 1 1 auto;
            padding: 9px 10px;
            border: 1px solid var(--color-admission-status-6);
            border-radius: 6px;
            color: #0f5fa8;
            font-weight: var(--typo-admission-status-font-weight-1);
            font-size: var(--typo-admission-status-font-size-3);
            background: var(--color-admission-status-9);
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }

        .admission-doc-tab:hover {
            background: #eef6ff;
            color: #0f5fa8;
            text-decoration: none;
        }

        .admission-modal__previous-docs {
            margin-bottom: var(--space-admission-status-4);
        }

        .admission-modal__previous-docs-title {
            margin: 0 0 var(--space-admission-status-5);
            font-size: var(--typo-admission-status-font-size-3);
            font-weight: var(--typo-admission-status-font-weight-1);
            color: var(--color-admission-status-4);
        }

        .admission-doc-tab.is-active {
            background: var(--color-admission-status-2);
            border-color: var(--color-admission-status-2);
            color: var(--color-admission-status-10);
        }

        .admission-doc-tab:disabled {
            color: var(--color-admission-status-5);
            cursor: not-allowed;
            background: var(--color-admission-status-8);
        }

        @media (max-width: 768px) {
            .admission-review-modal__body {
                flex-direction: column;
            }

            .admission-review-modal__pane--info,
            .admission-review-modal__pane--preview {
                flex: 1 1 auto;
                max-width: 100%;
            }

            .admission-review-modal__pane--info {
                border-right: 0;
                border-bottom: 1px solid #e2e8f0;
            }

            .admission-review-modal__preview-frame-wrap {
                min-height: 220px;
            }
        }

        @media (max-width: 768px) {
            .follow-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .adm-status-shell .table-responsive {
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch;
            }

            .adm-status-shell .follow-table {
                width: max-content !important;
                min-width: 100% !important;
            }

            .adm-status-shell .follow-table th,
            .adm-status-shell .follow-table td {
                white-space: nowrap;
            }

            .admission-modal__footer,
            .admission-modal__footer--split {
                flex-direction: column;
                align-items: stretch;
            }

            .admission-modal__actions {
                width: var(--dimension-admission-status-1);
            }

            .admission-modal__actions .btn {
                flex: 1 1 0;
            }
        }
    </style>
@endpush

@push('scripts')
    @if($canAdmissionUpload)
        <script>
            (function () {
                var modal = document.getElementById('uploadDocumentsModal');
                var form = document.getElementById('uploadDocumentsForm');
                var studentLabel = document.getElementById('uploadDocumentsStudent');
                var remarkBox = document.getElementById('uploadDocumentsRemark');
                var previousDocsPanel = document.getElementById('uploadDocumentsPreviousDocs');
                var scannerNotice = document.getElementById('uploadDocumentsScannerNotice');
                var uploadBase = @json(url('/admission'));
                var scannerHelperBases = ['http://127.0.0.1:18777', 'http://localhost:18777'];
                var identityDocumentTypeRadios = Array.prototype.slice.call(form.querySelectorAll('input[name="identity_document_type"]'));
                var identityDocumentPrimaryLabel = document.getElementById('identityDocumentPrimaryLabelText');
                var identityDocumentPrimaryScanButton = document.getElementById('identityDocumentPrimaryScanButton');
                var identityDocumentBackGroup = document.getElementById('identityDocumentBackGroup');
                var identityDocumentBackInput = form.querySelector('[name="document_cnic_back"]');
                var identityDocumentBackScanButton = document.getElementById('identityDocumentBackScanButton');
                var docInputs = [];
                var scanButtons = [];
                var activeHttpScannerBase = null;

                if (!modal || !form) {
                    return;
                }

                docInputs = Array.prototype.slice.call(form.querySelectorAll('.js-admission-doc-input'));
                scanButtons = Array.prototype.slice.call(form.querySelectorAll('.js-admission-scan'));

                function formatFileSize(bytes) {
                    if (!bytes) {
                        return '0 KB';
                    }

                    if (bytes >= 1024 * 1024) {
                        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
                    }

                    return Math.max(1, Math.round(bytes / 1024)) + ' KB';
                }

                function showScannerNotice(message, tone) {
                    if (!scannerNotice) {
                        return;
                    }

                    scannerNotice.classList.remove('alert-info', 'alert-success', 'alert-warning', 'alert-danger');
                    scannerNotice.classList.add('alert-' + (tone || 'info'));
                    scannerNotice.textContent = message;
                    scannerNotice.style.display = 'block';
                }

                function hideScannerNotice() {
                    if (!scannerNotice) {
                        return;
                    }

                    scannerNotice.style.display = 'none';
                    scannerNotice.textContent = '';
                    scannerNotice.classList.remove('alert-success', 'alert-warning', 'alert-danger');
                    scannerNotice.classList.add('alert-info');
                }

                function findInputByName(inputName) {
                    return form.querySelector('[name="' + inputName + '"]');
                }

                function applyIdentityDocumentType(type) {
                    var normalizedType = type === 'b_form' ? 'b_form' : 'cnic';
                    var primaryLabel = normalizedType === 'b_form' ? 'B-Form Copy' : 'CNIC Front Side';
                    var requiresBackDocument = normalizedType === 'cnic';

                    identityDocumentTypeRadios.forEach(function (radio) {
                        radio.checked = radio.value === normalizedType;
                    });

                    if (identityDocumentPrimaryLabel) {
                        identityDocumentPrimaryLabel.textContent = primaryLabel;
                    }

                    if (identityDocumentPrimaryScanButton) {
                        identityDocumentPrimaryScanButton.setAttribute('data-document-label', primaryLabel);
                    }

                    if (identityDocumentBackGroup) {
                        identityDocumentBackGroup.style.display = requiresBackDocument ? '' : 'none';
                    }

                    if (identityDocumentBackInput) {
                        if (!requiresBackDocument) {
                            identityDocumentBackInput.value = '';
                            updatePreview(identityDocumentBackInput);
                        }

                        identityDocumentBackInput.disabled = !requiresBackDocument;
                        identityDocumentBackInput.required = requiresBackDocument;
                    }

                    if (identityDocumentBackScanButton) {
                        identityDocumentBackScanButton.disabled = !requiresBackDocument;
                    }
                }

                function guessExtension(type) {
                    switch ((type || '').toLowerCase()) {
                        case 'image/jpeg':
                            return '.jpg';
                        case 'image/png':
                            return '.png';
                        case 'application/pdf':
                            return '.pdf';
                        default:
                            return '';
                    }
                }

                function dataUrlToBlob(dataUrl) {
                    var parts = (dataUrl || '').split(',');
                    if (parts.length !== 2) {
                        throw new Error('Invalid scanner data URL.');
                    }

                    var match = parts[0].match(/data:(.*?);base64/i);
                    var mimeType = match && match[1] ? match[1] : 'application/octet-stream';
                    var binary = atob(parts[1]);
                    var length = binary.length;
                    var bytes = new Uint8Array(length);

                    for (var index = 0; index < length; index++) {
                        bytes[index] = binary.charCodeAt(index);
                    }

                    return new Blob([bytes], { type: mimeType });
                }

                function assignFileToInput(input, file) {
                    var transfer = new DataTransfer();
                    transfer.items.add(file);
                    input.files = transfer.files;
                }

                function normalizeScannerResult(result, input) {
                    if (result instanceof File) {
                        return result;
                    }

                    if (result && result.file instanceof File) {
                        return result.file;
                    }

                    var blob = null;
                    var type = '';
                    var name = '';

                    if (result instanceof Blob) {
                        blob = result;
                        type = result.type || '';
                    } else if (result && result.blob instanceof Blob) {
                        blob = result.blob;
                        type = result.type || result.blob.type || '';
                    } else if (result && typeof result.dataUrl === 'string') {
                        blob = dataUrlToBlob(result.dataUrl);
                        type = result.type || blob.type || '';
                    }

                    if (!blob) {
                        throw new Error('Scanner did not return a usable file.');
                    }

                    name = (result && result.name) ? result.name : (input.name + '-scan' + guessExtension(type || blob.type));

                    return new File([blob], name, {
                        type: type || blob.type || 'application/octet-stream',
                        lastModified: Date.now(),
                    });
                }

                function resolveScannerBridge() {
                    if (window.CRMAdmissionScanner && typeof window.CRMAdmissionScanner.scan === 'function') {
                        return window.CRMAdmissionScanner;
                    }

                    if (window.CRMScanner && typeof window.CRMScanner.scan === 'function') {
                        return window.CRMScanner;
                    }

                    return null;
                }

                async function fetchFromHttpScannerHelper(path, options) {
                    var bases = activeHttpScannerBase
                        ? [activeHttpScannerBase].concat(scannerHelperBases.filter(function (base) { return base !== activeHttpScannerBase; }))
                        : scannerHelperBases.slice();
                    var lastError = null;

                    for (var index = 0; index < bases.length; index++) {
                        var base = bases[index];

                        try {
                            var response = await fetch(base + path, Object.assign({
                                mode: 'cors',
                                credentials: 'omit',
                                cache: 'no-store',
                            }, options || {}));

                            activeHttpScannerBase = base;

                            return {
                                base: base,
                                response: response,
                            };
                        } catch (error) {
                            lastError = error;
                        }
                    }

                    throw lastError || new Error('Scanner helper not detected.');
                }

                async function readHelperError(response) {
                    try {
                        var payload = await response.json();
                        if (payload && payload.message) {
                            return payload.message;
                        }
                    } catch (error) {
                        // Ignore JSON parse failures and use status text fallback.
                    }

                    return response.statusText || 'Scanner helper request failed.';
                }

                async function getHttpScannerHelperHealth() {
                    var result = await fetchFromHttpScannerHelper('/health', {
                        method: 'GET',
                    });

                    if (!result.response.ok) {
                        throw new Error(await readHelperError(result.response));
                    }

                    return result.response.json();
                }

                async function scanWithHttpScannerHelper(input, documentLabel) {
                    var result = await fetchFromHttpScannerHelper('/scan', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            field: input.name,
                            label: documentLabel,
                        }),
                    });

                    if (!result.response.ok) {
                        throw new Error(await readHelperError(result.response));
                    }

                    var blob = await result.response.blob();
                    var fileName = result.response.headers.get('X-Scan-File-Name') || (input.name + '-scan.jpg');
                    var contentType = blob.type || 'image/jpeg';

                    return new File([blob], fileName, {
                        type: contentType,
                        lastModified: Date.now(),
                    });
                }

                function resetPreview(card) {
                    if (!card) {
                        return;
                    }

                    var objectUrl = card.getAttribute('data-object-url');
                    var empty = card.querySelector('.admission-preview-card__empty');
                    var image = card.querySelector('.admission-preview-card__image');
                    var frame = card.querySelector('.admission-preview-card__frame');
                    var meta = card.querySelector('.admission-preview-card__meta');

                    if (objectUrl) {
                        URL.revokeObjectURL(objectUrl);
                        card.removeAttribute('data-object-url');
                    }

                    if (image) {
                        image.removeAttribute('src');
                        image.style.display = 'none';
                    }

                    if (frame) {
                        frame.removeAttribute('src');
                        frame.style.display = 'none';
                    }

                    if (meta) {
                        meta.textContent = '';
                        meta.style.display = 'none';
                    }

                    if (empty) {
                        empty.textContent = 'No file selected yet.';
                        empty.style.display = 'block';
                    }

                    card.classList.add('is-empty');
                }

                function resetAllPreviews() {
                    docInputs.forEach(function (input) {
                        var previewId = input.getAttribute('data-preview-target');
                        if (!previewId) {
                            return;
                        }

                        resetPreview(document.getElementById(previewId));
                    });
                }

                function updatePreview(input) {
                    var previewId = input.getAttribute('data-preview-target');
                    var card = previewId ? document.getElementById(previewId) : null;
                    var file = input.files && input.files[0] ? input.files[0] : null;

                    if (!card) {
                        return;
                    }

                    resetPreview(card);

                    if (!file) {
                        return;
                    }

                    var objectUrl = URL.createObjectURL(file);
                    var empty = card.querySelector('.admission-preview-card__empty');
                    var image = card.querySelector('.admission-preview-card__image');
                    var frame = card.querySelector('.admission-preview-card__frame');
                    var meta = card.querySelector('.admission-preview-card__meta');
                    var isPdf = (file.type || '').toLowerCase() === 'application/pdf' || /\.pdf$/i.test(file.name);
                    var isImage = (file.type || '').toLowerCase().indexOf('image/') === 0;

                    card.setAttribute('data-object-url', objectUrl);
                    card.classList.remove('is-empty');

                    if (meta) {
                        meta.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
                        meta.style.display = 'block';
                    }

                    if (empty) {
                        empty.style.display = 'none';
                    }

                    if (isImage && image) {
                        image.src = objectUrl;
                        image.style.display = 'block';
                        return;
                    }

                    if (isPdf && frame) {
                        frame.src = objectUrl;
                        frame.style.display = 'block';
                        return;
                    }

                    if (empty) {
                        empty.textContent = 'Preview is not available for this file type.';
                        empty.style.display = 'block';
                    }
                }

                async function handleScannerClick(button) {
                    var inputName = button.getAttribute('data-input-name') || '';
                    var documentLabel = button.getAttribute('data-document-label') || 'Document';
                    var input = findInputByName(inputName);
                    var scannerBridge = resolveScannerBridge();

                    if (!input) {
                        return;
                    }

                    try {
                        button.disabled = true;

                        if (scannerBridge) {
                            showScannerNotice('Starting scanner for ' + documentLabel + '...', 'info');

                            var browserResult = await scannerBridge.scan({
                                field: input.name,
                                label: documentLabel,
                                accept: input.getAttribute('accept') || '',
                            });

                            if (!browserResult) {
                                showScannerNotice('Scanner was closed before a file was captured for ' + documentLabel + '.', 'warning');
                                return;
                            }

                            assignFileToInput(input, normalizeScannerResult(browserResult, input));
                            updatePreview(input);
                            showScannerNotice(documentLabel + ' scanned successfully. Review the preview, then save.', 'success');
                            return;
                        }

                        showScannerNotice('Connecting to local scanner helper for ' + documentLabel + '...', 'info');

                        var scannedFile = await scanWithHttpScannerHelper(input, documentLabel);
                        assignFileToInput(input, scannedFile);
                        updatePreview(input);
                        showScannerNotice(documentLabel + ' scanned successfully. Review the preview, then save.', 'success');
                    } catch (error) {
                        var message = error && error.message ? error.message : 'Scanner helper not detected.';

                        if (/not detected/i.test(message) || /failed to fetch/i.test(message)) {
                            showScannerNotice('Scanner helper not detected for ' + documentLabel + '. File picker opened instead.', 'warning');
                            input.click();
                        } else {
                            showScannerNotice('Scanner could not complete for ' + documentLabel + ': ' + message, 'danger');
                        }
                    } finally {
                        button.disabled = false;
                    }
                }

                async function refreshScannerStatusNotice() {
                    var scannerBridge = resolveScannerBridge();

                    if (scannerBridge) {
                        showScannerNotice('Browser scanner bridge detected. You can scan directly from this screen.', 'success');
                        return;
                    }

                    try {
                        var health = await getHttpScannerHelperHealth();
                        var deviceName = health && health.devices && health.devices.length ? health.devices[0].name : 'WIA scanner';

                        showScannerNotice('Scanner helper connected: ' + deviceName + '.', 'success');
                    } catch (error) {
                        showScannerNotice('Scanner helper not detected. Start the local scanner helper, or file picker will open.', 'warning');
                    }
                }

                function bindPreviousDoc(elementId, url) {
                    var element = document.getElementById(elementId);
                    if (!element) {
                        return false;
                    }

                    if (url) {
                        element.href = url;
                        element.style.display = '';
                        return true;
                    }

                    element.href = '#';
                    element.style.display = 'none';
                    return false;
                }

                function openModal(button) {
                    var admissionId = button.getAttribute('data-admission-id');
                    var studentName = button.getAttribute('data-student-name') || 'Admission';
                    var identityDocumentType = button.getAttribute('data-identity-document-type') || 'cnic';
                    var remark = button.getAttribute('data-approval-remarks') || '';

                    form.action = uploadBase + '/' + admissionId + '/documents';
                    form.reset();
                    resetAllPreviews();
                    applyIdentityDocumentType(identityDocumentType);
                    hideScannerNotice();
                    studentLabel.textContent = studentName;

                    if (remark) {
                        remarkBox.style.display = 'block';
                        remarkBox.textContent = 'Admin remark: ' + remark;
                    } else {
                        remarkBox.style.display = 'none';
                        remarkBox.textContent = '';
                    }

                    var hasPreviousDocs = false;
                    hasPreviousDocs = bindPreviousDoc('uploadPrevDocCnic', button.getAttribute('data-doc-cnic')) || hasPreviousDocs;
                    hasPreviousDocs = bindPreviousDoc('uploadPrevDocCnicBack', button.getAttribute('data-doc-cnic-back')) || hasPreviousDocs;
                    hasPreviousDocs = bindPreviousDoc('uploadPrevDocForm', button.getAttribute('data-doc-form')) || hasPreviousDocs;
                    hasPreviousDocs = bindPreviousDoc('uploadPrevDocSlip', button.getAttribute('data-doc-slip')) || hasPreviousDocs;

                    if (previousDocsPanel) {
                        previousDocsPanel.style.display = hasPreviousDocs ? 'block' : 'none';
                    }

                    modal.classList.add('is-open');
                    modal.setAttribute('aria-hidden', 'false');
                    refreshScannerStatusNotice();
                }

                function closeModal() {
                    form.reset();
                    resetAllPreviews();
                    applyIdentityDocumentType('cnic');
                    hideScannerNotice();
                    modal.classList.remove('is-open');
                    modal.setAttribute('aria-hidden', 'true');
                }

                identityDocumentTypeRadios.forEach(function (radio) {
                    radio.addEventListener('change', function () {
                        applyIdentityDocumentType(this.value);
                        hideScannerNotice();
                    });
                });

                docInputs.forEach(function (input) {
                    input.addEventListener('change', function () {
                        updatePreview(this);
                        hideScannerNotice();
                    });
                });

                scanButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        handleScannerClick(this);
                    });
                });

                document.querySelectorAll('.js-open-upload-admission').forEach(function (button) {
                    button.addEventListener('click', function () {
                        openModal(this);
                    });
                });

                modal.querySelectorAll('[data-admission-modal-close]').forEach(function (element) {
                    element.addEventListener('click', closeModal);
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                        closeModal();
                    }
                });
            })();
        </script>
    @endif

    @if($canReviewApproval)
        <script>
            (function () {
                var modal = document.getElementById('reviewAdmissionModal');
                var form = document.getElementById('reviewAdmissionForm');
                var studentLabel = document.getElementById('reviewAdmissionStudent');
                var remarksInput = document.getElementById('reviewAdmissionRemarks');
                var previousRemark = document.getElementById('reviewAdmissionPreviousRemark');
                var documentFrame = document.getElementById('reviewDocumentPreviewFrame');
                var documentEmpty = document.getElementById('reviewDocumentPreviewEmpty');
                var docTabs = document.getElementById('reviewDocTabs');
                var reviewIdentityTypeValue = document.getElementById('reviewAdmissionIdentityTypeValue');
                var reviewIdentityPrimaryLabel = document.getElementById('reviewIdentityPrimaryLabel');
                var reviewIdentityBackRow = document.getElementById('reviewDocCnicBack');
                var reviewBase = @json(url('/admission'));
                var zoomLevels = [1, 1.5, 2, 2.5, 3];
                var zoomIndex = 0;
                var zoomControls = document.getElementById('reviewZoomControls');
                var zoomInBtn = document.getElementById('reviewZoomIn');
                var zoomOutBtn = document.getElementById('reviewZoomOut');
                var zoomResetBtn = document.getElementById('reviewZoomReset');
                var zoomLevelLabel = document.getElementById('reviewZoomLevel');

                if (!modal || !form) {
                    return;
                }

                function fitImageInsidePreview() {
                    if (documentFrame) {
                        try {
                            var frameDocument = documentFrame.contentDocument || documentFrame.contentWindow.document;
                            var image = frameDocument ? frameDocument.querySelector('img') : null;

                            if (frameDocument && image) {
                                frameDocument.documentElement.style.height = '100%';
                                frameDocument.body.style.height = '100%';
                                frameDocument.body.style.margin = '0';
                                frameDocument.body.style.display = 'flex';
                                frameDocument.body.style.alignItems = 'center';
                                frameDocument.body.style.justifyContent = 'center';
                                frameDocument.body.style.background = '#111827';
                                image.style.display = 'block';
                            }
                        } catch (error) {
                            // Browser PDF viewers may block frame styling; images remain handled when accessible.
                        }
                    }

                    zoomIndex = 0;
                    applyZoom();
                }

                function getPreviewImage() {
                    if (!documentFrame) {
                        return null;
                    }

                    try {
                        var frameDocument = documentFrame.contentDocument || documentFrame.contentWindow.document;
                        return frameDocument ? frameDocument.querySelector('img') : null;
                    } catch (error) {
                        return null;
                    }
                }

                function applyZoom() {
                    var image = getPreviewImage();
                    var zoom = zoomLevels[zoomIndex];

                    if (zoomLevelLabel) {
                        zoomLevelLabel.textContent = Math.round(zoom * 100) + '%';
                    }

                    if (zoomOutBtn) {
                        zoomOutBtn.disabled = !image || zoomIndex === 0;
                    }

                    if (zoomInBtn) {
                        zoomInBtn.disabled = !image || zoomIndex === zoomLevels.length - 1;
                    }

                    if (zoomResetBtn) {
                        zoomResetBtn.disabled = !image;
                    }

                    if (zoomControls) {
                        zoomControls.style.display = image ? 'flex' : 'none';
                    }

                    if (!image) {
                        return;
                    }

                    var frameDocument = image.ownerDocument;

                    if (zoom > 1) {
                        image.style.width = (zoom * 100) + '%';
                        image.style.height = 'auto';
                        image.style.maxWidth = 'none';
                        image.style.maxHeight = 'none';
                        image.style.objectFit = '';
                        frameDocument.body.style.overflow = 'auto';
                    } else {
                        image.style.width = '100%';
                        image.style.height = '100%';
                        image.style.maxWidth = '100%';
                        image.style.maxHeight = '100%';
                        image.style.objectFit = 'contain';
                        frameDocument.body.style.overflow = 'hidden';
                    }
                }

                function zoomIn() {
                    zoomIndex = Math.min(zoomIndex + 1, zoomLevels.length - 1);
                    applyZoom();
                }

                function zoomOut() {
                    zoomIndex = Math.max(zoomIndex - 1, 0);
                    applyZoom();
                }

                function zoomReset() {
                    zoomIndex = 0;
                    applyZoom();
                }

                function bindDocTab(elementId, url) {
                    var element = document.getElementById(elementId);
                    if (!element) {
                        return;
                    }

                    element.setAttribute('data-doc-url', url || '');
                    element.disabled = !url;
                    element.classList.remove('is-active');
                }

                function clearDocumentPreview() {
                    docTabs.querySelectorAll('.admission-doc-tab').forEach(function (el) {
                        el.classList.remove('is-active');
                    });

                    if (documentFrame) {
                        documentFrame.classList.remove('is-active');
                        documentFrame.src = 'about:blank';
                    }

                    if (documentEmpty) {
                        documentEmpty.style.display = 'flex';
                    }
                }

                function showDocument(tab) {
                    var url = tab.getAttribute('data-doc-url');

                    if (!url || tab.disabled) {
                        clearDocumentPreview();
                        return;
                    }

                    docTabs.querySelectorAll('.admission-doc-tab').forEach(function (el) {
                        el.classList.remove('is-active');
                    });
                    tab.classList.add('is-active');

                    if (documentEmpty) {
                        documentEmpty.style.display = 'none';
                    }

                    if (documentFrame) {
                        documentFrame.classList.add('is-active');
                        documentFrame.src = url;
                    }
                }

                function showFirstAvailableDocument() {
                    var firstTab = docTabs.querySelector('.admission-doc-tab:not(:disabled)');

                    if (firstTab) {
                        showDocument(firstTab);
                    } else {
                        clearDocumentPreview();
                    }
                }

                function applyReviewIdentityDocumentType(type) {
                    var normalizedType = type === 'b_form' ? 'b_form' : 'cnic';
                    var primaryLabel = normalizedType === 'b_form' ? 'B-Form Copy' : 'CNIC Front Side';
                    var primaryTab = document.getElementById('reviewDocCnic');
                    var identityTypeLabel = normalizedType === 'b_form' ? 'B-Form' : 'CNIC';

                    if (reviewIdentityTypeValue) {
                        reviewIdentityTypeValue.textContent = identityTypeLabel;
                    }

                    if (reviewIdentityPrimaryLabel) {
                        reviewIdentityPrimaryLabel.textContent = primaryLabel;
                    }

                    if (primaryTab) {
                        primaryTab.setAttribute('data-doc-title', primaryLabel);
                    }

                    if (reviewIdentityBackRow) {
                        reviewIdentityBackRow.style.display = normalizedType === 'b_form' ? 'none' : '';
                    }
                }

                function setDetail(elementId, value) {
                    var element = document.getElementById(elementId);
                    if (element) {
                        element.textContent = value && value.trim() !== '' ? value : '—';
                    }
                }

                function openModal(button) {
                    var admissionId = button.getAttribute('data-admission-id');
                    var studentName = button.getAttribute('data-student-name') || 'Admission';
                    var identityDocumentType = button.getAttribute('data-identity-document-type') || 'cnic';
                    var remark = button.getAttribute('data-approval-remarks') || '';

                    form.action = reviewBase + '/' + admissionId + '/review';
                    studentLabel.textContent = studentName;
                    remarksInput.value = '';
                    applyReviewIdentityDocumentType(identityDocumentType);

                    setDetail('reviewAdmissionFather', button.getAttribute('data-father-name'));
                    setDetail('reviewAdmissionCnic', button.getAttribute('data-cnic'));
                    setDetail('reviewAdmissionPhone', button.getAttribute('data-phone'));
                    setDetail('reviewAdmissionEmail', button.getAttribute('data-email'));
                    setDetail('reviewAdmissionDob', button.getAttribute('data-dob'));
                    setDetail('reviewAdmissionAddress', button.getAttribute('data-address'));
                    setDetail('reviewAdmissionGender', button.getAttribute('data-gender'));
                    setDetail('reviewAdmissionProgram', button.getAttribute('data-program'));
                    setDetail('reviewAdmissionSession', button.getAttribute('data-session'));
                    setDetail('reviewAdmissionStatus', button.getAttribute('data-status'));
                    setDetail('reviewAdmissionAdmissionDate', button.getAttribute('data-admission-date'));
                    setDetail('reviewAdmissionRegistrationDate', button.getAttribute('data-registration-date'));
                    setDetail('reviewAdmissionFeePackage', button.getAttribute('data-fee-package'));
                    setDetail('reviewAdmissionDiscount', button.getAttribute('data-discount'));
                    setDetail('reviewAdmissionFeePlan', button.getAttribute('data-fee-plan'));
                    setDetail('reviewAdmissionPaidInstallment', button.getAttribute('data-paid-installment'));
                    setDetail('reviewAdmissionPendingInstallment', button.getAttribute('data-pending-installment'));

                    bindDocTab('reviewDocCnic', button.getAttribute('data-doc-cnic'));
                    bindDocTab('reviewDocCnicBack', button.getAttribute('data-doc-cnic-back'));
                    bindDocTab('reviewDocForm', button.getAttribute('data-doc-form'));
                    bindDocTab('reviewDocSlip', button.getAttribute('data-doc-slip'));
                    showFirstAvailableDocument();

                    if (remark) {
                        previousRemark.style.display = 'block';
                        previousRemark.textContent = 'Latest remark: ' + remark;
                    } else {
                        previousRemark.style.display = 'none';
                        previousRemark.textContent = '';
                    }

                    modal.classList.add('is-open');
                    modal.setAttribute('aria-hidden', 'false');
                }

                function closeModal() {
                    if (documentFrame) {
                        documentFrame.src = 'about:blank';
                    }
                    modal.classList.remove('is-open');
                    modal.setAttribute('aria-hidden', 'true');
                }

                document.querySelectorAll('.js-open-review-admission').forEach(function (button) {
                    button.addEventListener('click', function () {
                        openModal(this);
                    });
                });

                modal.querySelectorAll('[data-admission-review-close]').forEach(function (element) {
                    element.addEventListener('click', closeModal);
                });

                docTabs.querySelectorAll('.js-review-doc-preview').forEach(function (tab) {
                    tab.addEventListener('click', function () {
                        showDocument(this);
                    });
                });

                if (documentFrame) {
                    documentFrame.addEventListener('load', fitImageInsidePreview);
                }

                if (zoomInBtn) {
                    zoomInBtn.addEventListener('click', zoomIn);
                }

                if (zoomOutBtn) {
                    zoomOutBtn.addEventListener('click', zoomOut);
                }

                if (zoomResetBtn) {
                    zoomResetBtn.addEventListener('click', zoomReset);
                }

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                        closeModal();
                    }
                });
            })();
        </script>
    @endif
@endpush
