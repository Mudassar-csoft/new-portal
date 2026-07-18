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
<form method="GET" action="{{ route('admission.status') }}" class="follow-controls">
					<input type="hidden" name="scope" value="{{ $activeScope }}">
					<input type="hidden" name="period" value="{{ $activeScope === 'all' && $activePeriod !== 'all' ? $activePeriod : '' }}">
					<div class="d-flex" style="gap:0.5rem;align-items: baseline;">
						<label class="mr-2 mb-0">Show</label>
						<select name="per_page" class="form-control form-control-sm" onchange="this.form.submit()">
							@foreach ([10, 25, 50, 100] as $option)
								<option value="{{ $option }}" @selected((int) $perPage === $option)>{{ $option }}</option>
							@endforeach
						</select>
						<label class="ml-2 mb-0">Entries</label>
					</div>
					<div class="follow-search">
						<input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search...">
						<!-- <button type="submit" class="reg-search-submit" aria-label="Search">
							<i class="fa fa-search"></i>
						</button> -->
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
            <div class="admission-modal__dialog">
                <div class="admission-modal__header">
                    <h4 class="admission-modal__title">Review Admission Request</h4>
                    <button type="button" class="admission-modal__close" data-admission-review-close>&times;</button>
                </div>
                <form id="reviewAdmissionForm" method="POST">
                    @csrf
                    <div class="admission-modal__body">
                        <p class="admission-modal__student" id="reviewAdmissionStudent"></p>
                        <div class="alert alert-secondary admission-modal__notice" id="reviewAdmissionIdentityType">
                            Identity Document Type: <strong id="reviewAdmissionIdentityTypeValue">CNIC</strong>
                        </div>

                        <div class="admission-doc-list">
                            <div class="admission-doc-link" id="reviewIdentityPrimaryRow">
                                <span id="reviewIdentityPrimaryLabel">CNIC Front Side</span>
                                <span class="text-right eye-span">
                                    <a href="#" id="reviewDocCnic" class="js-review-doc-preview" data-doc-title="CNIC Front Side"><i class="fa fa-eye"></i></a>
                                </span>
                            </div>

                            <div class="admission-doc-link" id="reviewIdentityBackRow">
                                <span id="reviewIdentityBackLabel">CNIC Back Side</span>
                                <span class="text-right eye-span">
                                    <a href="#" id="reviewDocCnicBack" class="js-review-doc-preview" data-doc-title="CNIC Back Side"><i class="fa fa-eye"></i></a>
                                </span>
                            </div>

                            <div class="admission-doc-link">
                                Admission Form
                                <span class="text-right eye-span">
                                    <a href="#" id="reviewDocForm" class="js-review-doc-preview" data-doc-title="Admission Form">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </span>
                            </div>

                            <div class="admission-doc-link">
                                Paid Slip With Authorized Stamp
                                <span class="text-right eye-span-4">
                                    <a href="#" id="reviewDocSlip" class="js-review-doc-preview" data-doc-title="Paid Slip With Authorized Stamp">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </span>
                            </div>
                        </div>

                        <div class="alert alert-info admission-modal__notice" id="reviewAdmissionPreviousRemark" style="display:none;"></div>

                        <div class="form-group mb-0">
                            <label>Remarks <span class="text-danger">*</span></label>
                            <textarea name="approval_remarks" id="reviewAdmissionRemarks" rows="4" class="form-control" required placeholder="Write approval or revert remarks"></textarea>
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

        <div class="admission-modal admission-document-modal" id="reviewDocumentPreviewModal" aria-hidden="true">
            <div class="admission-modal__backdrop" data-admission-document-close></div>
            <div class="admission-modal__dialog admission-document-modal__dialog">
                <div class="admission-modal__header">
                    <h4 class="admission-modal__title" id="reviewDocumentPreviewTitle">Document Preview</h4>
                    <button type="button" class="admission-modal__close" data-admission-document-close>&times;</button>
                </div>
                <div class="admission-document-modal__body">
                    <iframe id="reviewDocumentPreviewFrame" class="admission-document-modal__frame" title="Admission document preview"></iframe>
                </div>
                <div class="admission-modal__footer">
                    <button type="button" class="btn btn-danger-outline" data-admission-document-close>Close</button>
                </div>
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

        .eye-span{
                height: var(--dimension-admission-status-2);
                text-align: right !important;
                /* margin-left: 341px; */
                padding: var(--space-admission-status-6);
                background: var(--color-admission-status-7);
                border-radius: 20px;
        }
         .eye-span-4{
             height: var(--dimension-admission-status-2);
         text-align: right !important;
                /* margin-left: 241px; */
                padding: var(--space-admission-status-6);
                background: var(--color-admission-status-7);
                border-radius: 20px;
        }
        .follow-card,
        .follow-body,
        .table-responsive {
            overflow: visible !important;
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
            justify-content: space-between;
            align-items: center;
            gap: var(--space-admission-status-2);
            margin-bottom: var(--space-admission-status-4);
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
            margin-left: auto;
        }

        .follow-search .form-control {
            width: min(280px, 100%);
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

        .admission-doc-list {
            display: grid;
            gap: var(--space-admission-status-1);
            margin-bottom: var(--space-admission-status-4);
        }

        .admission-doc-link {
            display: flex;
            padding: 10px 12px;
            border: 1px solid var(--color-admission-status-6);
            border-radius: 6px;
            color: #0f5fa8;
            font-weight: var(--typo-admission-status-font-weight-1);
            text-decoration: none;
            background: var(--color-admission-status-9);
             justify-content: space-between;
    align-items: center;
        }

        .admission-doc-link:hover {
            text-decoration: none;
            background: #eef6ff;
        }

        .admission-doc-link.is-disabled {
            color: var(--color-admission-status-5);
            pointer-events: none;
            background: var(--color-admission-status-8);
        }

        .admission-doc-link a.is-disabled {
            color: var(--color-admission-status-5);
            pointer-events: none;
        }

        .admission-document-modal {
            z-index: 1200;
        }

        .admission-document-modal__dialog {
            max-width: 980px;
            height: min(86vh, 760px);
            display: flex;
            flex-direction: column;
        }

        .admission-document-modal__body {
            flex: 1;
            min-height: 0;
            padding: var(--space-admission-status-2);
            background: var(--color-admission-status-8);
            overflow: hidden;
        }

        .admission-document-modal__frame {
            width: var(--dimension-admission-status-1);
            height: var(--dimension-admission-status-1);
            border: 1px solid var(--color-admission-status-6);
            border-radius: 6px;
            background: white;
            display: block;
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
                var documentModal = document.getElementById('reviewDocumentPreviewModal');
                var documentFrame = document.getElementById('reviewDocumentPreviewFrame');
                var documentTitle = document.getElementById('reviewDocumentPreviewTitle');
                var reviewIdentityTypeValue = document.getElementById('reviewAdmissionIdentityTypeValue');
                var reviewIdentityPrimaryLabel = document.getElementById('reviewIdentityPrimaryLabel');
                var reviewIdentityBackRow = document.getElementById('reviewIdentityBackRow');
                var reviewBase = @json(url('/admission'));

                if (!modal || !form) {
                    return;
                }

                function bindDocLink(elementId, url) {
                    var element = document.getElementById(elementId);
                    if (!element) {
                        return;
                    }

                    if (url) {
                        element.href = url;
                        element.classList.remove('is-disabled');
                        var activeRow = element.closest('.admission-doc-link');
                        if (activeRow) {
                            activeRow.classList.remove('is-disabled');
                        }
                    } else {
                        element.href = '#';
                        element.classList.add('is-disabled');
                        var disabledRow = element.closest('.admission-doc-link');
                        if (disabledRow) {
                            disabledRow.classList.add('is-disabled');
                        }
                    }
                }

                function openDocumentPreview(link) {
                    var url = link.getAttribute('href');
                    if (!documentModal || !documentFrame || !url || url === '#' || link.classList.contains('is-disabled')) {
                        return;
                    }

                    documentTitle.textContent = link.getAttribute('data-doc-title') || 'Document Preview';
                    documentFrame.src = url;
                    documentModal.classList.add('is-open');
                    documentModal.setAttribute('aria-hidden', 'false');
                }

                function fitImageInsidePreview() {
                    if (!documentFrame) {
                        return;
                    }

                    try {
                        var frameDocument = documentFrame.contentDocument || documentFrame.contentWindow.document;
                        if (!frameDocument) {
                            return;
                        }

                        var image = frameDocument.querySelector('img');
                        if (!image) {
                            return;
                        }

                        frameDocument.documentElement.style.height = '100%';
                        frameDocument.body.style.height = '100%';
                        frameDocument.body.style.margin = '0';
                        frameDocument.body.style.display = 'flex';
                        frameDocument.body.style.alignItems = 'center';
                        frameDocument.body.style.justifyContent = 'center';
                        frameDocument.body.style.overflow = 'hidden';
                        frameDocument.body.style.background = '#111827';

                        image.style.width = '100%';
                        image.style.height = '100%';
                        image.style.maxWidth = '100%';
                        image.style.maxHeight = '100%';
                        image.style.objectFit = 'contain';
                        image.style.display = 'block';
                    } catch (error) {
                        // Browser PDF viewers may block frame styling; images remain handled when accessible.
                    }
                }

                function closeDocumentPreview() {
                    if (!documentModal || !documentFrame) {
                        return;
                    }

                    documentModal.classList.remove('is-open');
                    documentModal.setAttribute('aria-hidden', 'true');
                    documentFrame.src = 'about:blank';
                }

                function applyReviewIdentityDocumentType(type) {
                    var normalizedType = type === 'b_form' ? 'b_form' : 'cnic';
                    var primaryLabel = normalizedType === 'b_form' ? 'B-Form Copy' : 'CNIC Front Side';
                    var primaryLink = document.getElementById('reviewDocCnic');
                    var identityTypeLabel = normalizedType === 'b_form' ? 'B-Form' : 'CNIC';

                    if (reviewIdentityTypeValue) {
                        reviewIdentityTypeValue.textContent = identityTypeLabel;
                    }

                    if (reviewIdentityPrimaryLabel) {
                        reviewIdentityPrimaryLabel.textContent = primaryLabel;
                    }

                    if (primaryLink) {
                        primaryLink.setAttribute('data-doc-title', primaryLabel);
                    }

                    if (reviewIdentityBackRow) {
                        reviewIdentityBackRow.style.display = normalizedType === 'b_form' ? 'none' : '';
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

                    bindDocLink('reviewDocCnic', button.getAttribute('data-doc-cnic'));
                    bindDocLink('reviewDocCnicBack', button.getAttribute('data-doc-cnic-back'));
                    bindDocLink('reviewDocForm', button.getAttribute('data-doc-form'));
                    bindDocLink('reviewDocSlip', button.getAttribute('data-doc-slip'));

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
                    closeDocumentPreview();
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

                document.querySelectorAll('.js-review-doc-preview').forEach(function (link) {
                    link.addEventListener('click', function (event) {
                        event.preventDefault();
                        openDocumentPreview(this);
                    });
                });

                if (documentModal) {
                    documentModal.querySelectorAll('[data-admission-document-close]').forEach(function (element) {
                        element.addEventListener('click', closeDocumentPreview);
                    });
                }

                if (documentFrame) {
                    documentFrame.addEventListener('load', fitImageInsidePreview);
                }

                document.addEventListener('keydown', function (event) {
                    if (event.key !== 'Escape') {
                        return;
                    }

                    if (documentModal && documentModal.classList.contains('is-open')) {
                        closeDocumentPreview();
                        return;
                    }

                    if (modal.classList.contains('is-open')) {
                        closeModal();
                    }
                });
            })();
        </script>
    @endif
@endpush
