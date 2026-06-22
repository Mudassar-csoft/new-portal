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

            @if($activeScope === 'all')
                <div class="follow-tab-bar follow-tab-bar--sub">
                    @foreach ($periods as $periodKey => $periodLabel)
                        <a
                            href="{{ route('admission.status', ['scope' => 'all', 'period' => $periodKey]) }}"
                            class="follow-tab follow-tab--sub {{ $activePeriod === $periodKey ? 'active' : '' }}"
                        >
                            <span class="label-text">{{ $periodLabel }}</span>
                            <span class="badge badge-light">{{ (int) ($periodCounts[$periodKey] ?? 0) }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

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
                                @php
                                    $isApproved = ($row->approval_status ?? \App\Models\Admission::APPROVAL_STATUS_APPROVED) === \App\Models\Admission::APPROVAL_STATUS_APPROVED;
                                @endphp
                                <tr data-entry-row="1">
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td>
                                        @if($isApproved && $row->registration_id && $canStudentView)
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

                        <div class="form-group">
                            <label>CNIC Front Side <span class="text-danger">*</span></label>
                            <input type="file" name="document_cnic_front" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>

                        <div class="form-group">
                            <label>CNIC Back Side <span class="text-danger">*</span></label>
                            <input type="file" name="document_cnic_back" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>

                        <div class="form-group">
                            <label>Admission Form <span class="text-danger">*</span></label>
                            <input type="file" name="document_admission_form" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>

                        <div class="form-group mb-0">
                            <label>Paid Slip With Authorized Stamp <span class="text-danger">*</span></label>
                            <input type="file" name="document_paid_slip" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>
                    </div>
                    <div class="admission-modal__footer">
                        <button type="button" class="btn btn-default" data-admission-modal-close>Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload Documents</button>
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
                            <a href="#" target="_blank" rel="noopener" id="reviewDocCnic" class="admission-doc-link">CNIC Front Side</a>
                            <a href="#" target="_blank" rel="noopener" id="reviewDocCnicBack" class="admission-doc-link">CNIC Back Side</a>
                            <a href="#" target="_blank" rel="noopener" id="reviewDocForm" class="admission-doc-link">Admission Form</a>
                            <a href="#" target="_blank" rel="noopener" id="reviewDocSlip" class="admission-doc-link">Paid Slip With Authorized Stamp</a>
                        </div>

                        <div class="alert alert-info admission-modal__notice" id="reviewAdmissionPreviousRemark" style="display:none;"></div>

                        <div class="form-group mb-0">
                            <label>Remarks <span class="text-danger">*</span></label>
                            <textarea name="approval_remarks" id="reviewAdmissionRemarks" rows="4" class="form-control" required placeholder="Write approval or revert remarks"></textarea>
                        </div>
                    </div>
                    <div class="admission-modal__footer admission-modal__footer--split">
                        <button type="button" class="btn btn-default" data-admission-review-close>Cancel</button>
                        <div class="admission-modal__actions">
                            <button type="submit" name="review_action" value="revert" class="btn btn-warning">Revert</button>
                            <button type="submit" name="review_action" value="approve" class="btn btn-success">Approve</button>
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
            position: absolute !important;
            top: 100% !important;
            left: auto !important;
            right: 0 !important;
            margin-top: 6px !important;
            margin-right: 0 !important;
            transform: none !important;
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
            font-size: 26px;
            line-height: 1;
            cursor: pointer;
            padding: 0;
        }

        .admission-modal__body {
            padding: 18px;
        }

        .admission-modal__student {
            margin: 0 0 16px;
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
        }

        .admission-modal__notice {
            margin-bottom: 16px;
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
            display: block;
            padding: 10px 12px;
            border: 1px solid #dbe3ec;
            border-radius: 6px;
            color: #0f5fa8;
            font-weight: 600;
            text-decoration: none;
            background: #f8fbff;
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
                var uploadBase = @json(url('/admission'));

                if (!modal || !form) {
                    return;
                }

                function openModal(button) {
                    var admissionId = button.getAttribute('data-admission-id');
                    var studentName = button.getAttribute('data-student-name') || 'Admission';
                    var remark = button.getAttribute('data-approval-remarks') || '';

                    form.action = uploadBase + '/' + admissionId + '/documents';
                    form.reset();
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
                }

                function closeModal() {
                    modal.classList.remove('is-open');
                    modal.setAttribute('aria-hidden', 'true');
                }

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
