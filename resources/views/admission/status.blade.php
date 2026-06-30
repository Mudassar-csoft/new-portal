@extends('layouts.theme')

@section('title', 'Admission Status')

@section('content')
    @php
        $admissions = $admissions ?? collect();
        $activeScope = $activeScope ?? 'pending';
        $activePeriod = $activePeriod ?? 'all';
        $scopeCounts = $scopeCounts ?? [];
        $periodCounts = $periodCounts ?? [];
        $canStudentView = auth()->user()?->hasAnyPermission(['student.view']) ?? false;
        $canAdmissionUpload = auth()->user()?->hasAnyPermission(['admission.create', 'admission.update']) ?? false;
        $canReviewApproval = auth()->user()?->isAdmin() ?? false;

        $scopes = [
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
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

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
                <div class="follow-controls">
                    <div class="follow-status-copy">
                        {{ $activeScope === 'all' ? ($periods[$activePeriod] ?? 'All Admissions') : ($scopes[$activeScope] ?? 'Admission Status') }}
                    </div>
                    <div class="follow-search">
                        <input type="text" id="adm-search" class="form-control form-control-sm" placeholder="Search...">
                        <i class="fa fa-search"></i>
                    </div>
                </div>

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
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td>
                                        @if($row->registration_id && $canStudentView)
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
                    <div id="adm-count">Showing {{ $admissions->count() ? 1 : 0 }} to {{ $admissions->count() }} of {{ $admissions->count() }} entries</div>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled"><span class="page-link">1</span></li>
                    </ul>
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

                      <!-- CNIC Front Side -->
<div class="form-group">
    <label>CNIC Front Side <span class="text-danger">*</span></label>

    <div style="display:flex; align-items:center; gap:15px;">
        <div class="admission-preview-card is-empty" id="preview-cnic-front">
            <div class="admission-upload-tools">
                <button type="button"
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
               class="form-control js-admission-doc-input"
               accept=".jpg,.jpeg,.png,.pdf"
               data-preview-target="preview-cnic-front"
               style="width:350px;"
               required>
    </div>

    <span class="admission-upload-tools__hint">
        Use scanner if helper is installed. Otherwise file picker will open.
    </span>
</div>

<!-- CNIC Back Side -->
<div class="form-group">
    <label>CNIC Back Side <span class="text-danger">*</span></label>

    <div style="display:flex; align-items:center; gap:15px;">
        <div class="admission-preview-card is-empty" id="preview-cnic-back">
            <div class="admission-upload-tools">
                <button type="button"
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
               class="form-control js-admission-doc-input"
               accept=".jpg,.jpeg,.png,.pdf"
               data-preview-target="preview-cnic-back"
               style="width:350px;"
               required>
    </div>

    <span class="admission-upload-tools__hint">
        Use scanner if helper is installed. Otherwise file picker will open.
    </span>
</div>

<!-- Admission Form -->
<div class="form-group">
    <label>Admission Form <span class="text-danger">*</span></label>

    <div style="display:flex; align-items:center; gap:15px;">
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
               class="form-control js-admission-doc-input"
               accept=".jpg,.jpeg,.png,.pdf"
               data-preview-target="preview-admission-form"
               style="width:350px;"
               required>
    </div>

    <span class="admission-upload-tools__hint">
        Use scanner if helper is installed. Otherwise file picker will open.
    </span>
</div>

<!-- Paid Slip -->
<div class="form-group mb-0">
    <label>Paid Slip With Authorized Stamp <span class="text-danger">*</span></label>

        <div style="display:flex; align-items:center; gap:15px;">
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
                        class="form-control js-admission-doc-input"
                        accept=".jpg,.jpeg,.png,.pdf"
                        data-preview-target="preview-paid-slip"
                        style="width:350px;"
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

                        <div class="admission-doc-list">
                        <span class="admission-doc-link">CNIC Front Side<a href="#" target="_blank" rel="noopener" id="reviewDocCnic" class="eye-span"><i class="fa fa-eye"></i></a></span>
                            <div class="admission-doc-link"> CNIC Back Side<span class="text-right eye-span"><a href="#" target="_blank" rel="noopener" id="reviewDocCnicBack"><i class="fa fa-eye"></i></a></span></div>

                            <div class="admission-doc-link">
                                Admission Form
                                <span class="text-right eye-span">
                                    <a href="#" target="_blank" rel="noopener" id="reviewDocForm">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </span>
                            </div>

                            <div class="admission-doc-link">
                                Paid Slip With Authorized Stamp
                                <span class="text-right eye-span-4">
                                    <a href="#" target="_blank" rel="noopener" id="reviewDocSlip">
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
    @endif
@endsection

@push('styles')
    <style>
        .action-cell {
            min-width: 180px;
            white-space: nowrap;
            position: relative;
        }

        .adm-name-link {
            color: #0a6fd1;
            font-weight: 600;
            text-decoration: none;
            border-bottom: 1px dashed transparent;
            transition: color 0.15s ease, border-color 0.15s ease;
        }

        .adm-name-link:hover {
            color: #0958a8;
            border-bottom-color: #0a6fd1;
            text-decoration: none;
        }
        .eye-span{
                height: 35px;
                text-align: right !important;
                /* margin-left: 341px; */
                padding: 9px;
                background: #e1efff;
                border-radius: 20px;
        }
         .eye-span-4{
             height: 35px;      
         text-align: right !important;
                /* margin-left: 241px; */
                padding: 9px;
                background: #e1efff;
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
            background: #f8fbff;
        }

        .follow-tab-bar--sub .follow-tab {
            padding-top: 10px;
            padding-bottom: 10px;
        }

        .follow-tab-bar--sub .follow-tab.active {
            background: #eaf4ff;
        }

        .follow-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .follow-status-copy {
            font-size: 15px;
            font-weight: 600;
            color: #334155;
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
            padding: 16px;
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
            width: 100%;
            max-width: 560px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.24);
            overflow: hidden;
        }

        .admission-modal__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            background: #1593ff;
            color: #fff;
        }

        .admission-modal__title {
            margin: 0;
            font-size: 17px;
            font-weight: 600;
        }

        .admission-modal__close {
            border: 0;
            background: transparent;
            color: #fff;
            font-size: 30px !important;
            line-height: 1;
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
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
        }

        .admission-modal__notice {
            margin-bottom: 5px;
        }
    .alert{
        font-size:12px !important;
    }
        .admission-upload-tools {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 0px;
            flex-wrap: wrap;
        }

        .admission-upload-tools__hint {
            color: #64748b;
            font-size: 12px;
            line-height: 1.4;
        }

        .admission-preview-card {
            margin-top: 2px;
            padding: 6px;
            border: 1px solid #dbe3ec;
            border-radius: 6px;
            background: #f8fbff;
        }

        .admission-preview-card__empty {
            color: #64748b;
            font-size: 12px;
        }

        .admission-preview-card__image,
        .admission-preview-card__frame {
            display: none;
            width: 100%;
            border: 0;
            border-radius: 4px;
            background: #fff;
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
            margin-top: 8px;
            font-size: 12px;
            color: #334155;
            word-break: break-word;
        }

        .admission-modal__footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            padding: 14px 18px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }

        .admission-modal__footer--split {
            justify-content: space-between;
        }

        .admission-modal__actions {
            display: flex;
            gap: 10px;
        }

        .admission-doc-list {
            display: grid;
            gap: 10px;
            margin-bottom: 16px;
        }

        .admission-doc-link {
            display: flex;
            padding: 10px 12px;
            border: 1px solid #dbe3ec;
            border-radius: 6px;
            color: #0f5fa8;
            font-weight: 600;
            text-decoration: none;
            background: #f8fbff;
             justify-content: space-between;
    align-items: center;
        }

        .admission-doc-link:hover {
            text-decoration: none;
            background: #eef6ff;
        }

        .admission-doc-link.is-disabled {
            color: #94a3b8;
            pointer-events: none;
            background: #f8fafc;
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
                width: 100%;
            }

            .admission-modal__actions .btn {
                flex: 1 1 0;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
            (function () {
            function updateVisibleCount() {
                var rows = Array.prototype.slice.call(document.querySelectorAll('#adm-table tbody tr[data-entry-row="1"]'));
                var visibleRows = rows.filter(function (row) {
                    return row.style.display !== 'none';
                }).length;

                var countEl = document.getElementById('adm-count');
                if (countEl) {
                    countEl.textContent = 'Showing ' + (visibleRows ? 1 : 0) + ' to ' + visibleRows + ' of ' + visibleRows + ' entries';
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                var searchInput = document.getElementById('adm-search');
                if (searchInput) {
                    searchInput.addEventListener('input', function () {
                        var searchVal = (this.value || '').toLowerCase();
                        document.querySelectorAll('#adm-table tbody tr[data-entry-row="1"]').forEach(function (row) {
                            var show = row.innerText.toLowerCase().indexOf(searchVal) !== -1;
                            row.style.display = show ? '' : 'none';
                        });
                        updateVisibleCount();
                    });
                }

                updateVisibleCount();
            });
        })();
    </script>

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
                    var remark = button.getAttribute('data-approval-remarks') || '';

                    form.action = uploadBase + '/' + admissionId + '/documents';
                    form.reset();
                    resetAllPreviews();
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
                    hideScannerNotice();
                    modal.classList.remove('is-open');
                    modal.setAttribute('aria-hidden', 'true');
                }

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
                    } else {
                        element.href = '#';
                        element.classList.add('is-disabled');
                    }
                }

                function openModal(button) {
                    var admissionId = button.getAttribute('data-admission-id');
                    var studentName = button.getAttribute('data-student-name') || 'Admission';
                    var remark = button.getAttribute('data-approval-remarks') || '';

                    form.action = reviewBase + '/' + admissionId + '/review';
                    studentLabel.textContent = studentName;
                    remarksInput.value = '';

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

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                        closeModal();
                    }
                });
            })();
        </script>
    @endif
@endpush
