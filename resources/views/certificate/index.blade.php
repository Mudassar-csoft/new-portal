@extends('layouts.theme')

@section('title', $pageTitle)

@section('content')
    @php
        $activeScope = $activeScope ?? 'all';
        $scopeCards = $scopeCards ?? [];
        $filters = $filters ?? ['scope' => 'all', 'campus_id' => null, 'program_id' => null, 'search' => null];
        $currentUser = auth()->user();
        $campusOptionCount = $campuses->count();
        $canBulkApprove = $activeScope === 'requested'
            && ($currentUser?->isAdmin() ?? false)
            && ($currentUser?->hasAnyPermission(['certificate.approve']) ?? false);
        $showActionColumn = match ($activeScope) {
            'requested' => $currentUser?->hasAnyPermission(['certificate.approve', 'certificate.reject']) ?? false,
            'approved' => $currentUser?->hasAnyPermission(['certificate.send-to-printing']) ?? false,
            'printing' => $currentUser?->hasAnyPermission(['certificate.mark-ready', 'certificate.view']) ?? false,
            'ready' => $currentUser?->hasAnyPermission(['certificate.mark-delivered', 'certificate.view']) ?? false,
            default => $activeScope !== 'requested'
                || ($currentUser?->hasAnyPermission(['certificate.approve', 'certificate.reject']) ?? false),
        };

        $scopeBadgeColors = [
            'all' => 'badge-secondary',
            'requested' => 'badge-warning',
            'approved' => 'badge-info',
            'printing' => 'badge-primary',
            'ready' => 'badge-success',
            'delivered' => 'badge-default',
        ];

        $statusLabelClasses = [
            'requested' => 'label-warning',
            'approved' => 'label-info',
            'printing' => 'label-primary',
            'ready' => 'label-success',
            'delivered' => 'label-default',
        ];
    @endphp

    <div class="lead-status-shell">
        @include('partials.status-loader', ['id' => 'certificate-status-loader', 'message' => 'Loading certificates...'])

        <div id="certificate-status-content" class="follow-content">
            @include('partials.session-status-alert-spaced')
            @include('partials.session-error-alert-spaced')

            <div class="follow-card box-typical box-typical-dashboard panel panel-default">
                <div class="user-mgmt-header">
                    <div class="follow-tab-bar">
                        @foreach ($scopeCards as $card)
                            @php
                                $isActive = $activeScope === $card['scope'];
                                $url = route('certificate.index', array_filter(array_merge(
                                    request()->except('page', 'scope'),
                                    ['scope' => $card['scope'] !== 'all' ? $card['scope'] : null]
                                )));
                            @endphp
                            <a href="{{ $url }}" class="follow-tab {{ $isActive ? 'active' : '' }}" data-scope="{{ $card['scope'] }}">
                                <span class="label-text">{{ $card['label'] }}</span>
                                <span class="badge {{ $scopeBadgeColors[$card['scope']] ?? 'badge-secondary' }}">{{ number_format((int) $card['count']) }}</span>
                            </a>
                        @endforeach
                    </div>
                    <!-- <a href="{{ route('certificate.create') }}" class="btn btn-inline btn-primary-outline create-action-btn">
                        <i class="fa fa-plus mr-1"></i> Request Certificate
                    </a> -->
                </div>

                <div class="box-typical-body panel-body follow-body">
                    <form method="GET" action="{{ route('certificate.index') }}" id="certificate-filter-form">
                        <input type="hidden" name="scope" value="{{ $activeScope }}">

                        <div class="follow-controls">
                            <div class="d-flex ci-inline-gap-05-center">
                                <label>Show</label>
                                <select class="form-select form-select-sm">
                                    <option>10</option>
                                    <option>25</option>
                                    <option>50</option>
                                </select>
                                <label>Entries</label>
                            </div>
                            <div class="follow-search">
                                <input type="text" name="search" id="certificate-status-search" class="form-control form-control-sm"
                                       placeholder="Search..." value="{{ $filters['search'] ?? '' }}">
                                <i class="fa fa-search"></i>
                            </div>
                        </div>

                        <div class="program-filter-row">
                            <div class="program-filter-field">
                                <label class="form-label">Campus</label>
                                <select class="form-control form-control-sm" name="campus_id">
                                    @if($campusOptionCount > 1)
                                        <option value="">All Campuses</option>
                                    @endif
                                    @foreach($campuses as $campus)
                                        <option value="{{ $campus->id }}" @selected(($filters['campus_id'] ?? null) == $campus->id)>
                                            {{ $campus->code }} - {{ $campus->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @include('partials.filter-program-select')
                            <div class="program-filter-actions">
                                <button type="submit" class="btn btn-primary-outline">Filter</button>
                                <a href="{{ route('certificate.index', array_filter(['scope' => $activeScope !== 'all' ? $activeScope : null])) }}" class="btn btn-danger-outline">Reset</a>
                            </div>
                        </div>
                    </form>

                    @if($canBulkApprove)
                        <form method="POST" action="{{ route('certificate.bulk-approve') }}" id="certificate-bulk-approve-form" class="certificate-bulk-toolbar">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="remarks" id="certificate-bulk-remarks" value="">
                            <!-- <div class="certificate-bulk-toolbar__left">
                                <span class="certificate-bulk-count">
                                    Selected: <strong id="certificate-selected-count">0</strong>
                                </span>
                            </div> -->
                            <!-- <div class="certificate-bulk-toolbar__right">
                                <button type="submit" class="btn btn-primary-outline" id="certificate-bulk-approve-button" disabled>
                                    Approve Selected
                                </button>
                            </div> -->
                        </form>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered follow-table">
                            <thead>
                                <tr>
                                    @if($canBulkApprove)
                                        <th class="text-center certificate-select-col">
                                            <label class="certificate-select-all" for="certificate-select-all">
                                                <input type="checkbox" id="certificate-select-all">
                                            </label>
                                        </th>
                                    @endif
                                    <th>Sr</th>
                                    <th>Student</th>
                                    <th>Reg No</th>
                                    <th>Programme</th>
                                    <th>Campus</th>
                                    @if($showActionColumn)
                                        <th class="text-left">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($certificates as $idx => $cert)
                                    @php
                                        $rowIndex = ($certificates->firstItem() ?? 0) + $idx;
                                        $statusKey = $cert->student_status ?? 'requested';
                                    @endphp
                                    <tr>
                                        @if($canBulkApprove)
                                            <td class="text-center certificate-select-col">
                                                <input
                                                    type="checkbox"
                                                    class="certificate-bulk-checkbox"
                                                    name="admission_ids[]"
                                                    value="{{ $cert->id }}"
                                                    form="certificate-bulk-approve-form"
                                                >
                                            </td>
                                        @endif
                                        <td class="text-center">{{ $rowIndex }}</td>
                                        <td>
                                            @if((int) ($cert->registration_id ?? 0) > 0)
                                                <a href="{{ route('student.show', $cert->registration_id) }}">{{ $cert->student_name ?? 'N/A' }}</a>
                                            @else
                                                {{ $cert->student_name ?? 'N/A' }}
                                            @endif
                                        </td>
                                        <td>{{ $cert->registration_number ?? 'N/A' }}</td>
                                        <td>{{ $cert->program?->title ?? $cert->program?->name ?? 'N/A' }}</td>
                                        <td>{{ $cert->campus?->code ?? $cert->campus?->name ?? 'N/A' }}</td>
                                        @if($showActionColumn)
                                            <td class="action-cell">
                                                @include('certificate.partials.action', ['actionId' => 'cert-action-' . $cert->id, 'activeScope' => $activeScope])
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ ($canBulkApprove ? 1 : 0) + ($showActionColumn ? 6 : 5) }}" class="text-center text-muted">No certificates found for the selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="follow-footer">
                        @include('partials.follow-pagination', ['paginator' => $certificates])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --dimension-certificate-index-1: 100%;
            --dimension-certificate-index-2: 100vh;
            --dimension-certificate-index-3: 12px;
            --space-certificate-index-1: 12px;
            --space-certificate-index-2: 14px;
            --space-certificate-index-3: 8px;
            --color-certificate-index-1: #54667a;
            --typo-certificate-index-font-weight-1: 600;
        }

        .ci-inline-gap-05-center {
            gap: 0.5rem;
            align-items: center;
        }

        .lead-status-shell { position: relative; min-height: var(--dimension-certificate-index-2); width: var(--dimension-certificate-index-1); overflow: hidden; }

        .follow-loader {
            position: absolute; top: 0; left: 0; right: 0; height: var(--dimension-certificate-index-2);
            background: rgba(245, 247, 251, 0.95);
            display: flex; align-items: center; justify-content: center; flex-direction: column;
            z-index: 10; gap: var(--space-certificate-index-1);
        }
        .follow-spinner { display: inline-flex; align-items: center; gap: var(--space-certificate-index-3); }
        .follow-spinner .dot { width: var(--dimension-certificate-index-3); height: var(--dimension-certificate-index-3); border-radius: 50%; background: #12a0ff; animation: bounce 0.9s ease-in-out infinite; }
        .follow-spinner .dot:nth-child(2) { animation-delay: 0.15s; background: #1f8ef1; }
        .follow-spinner .dot:nth-child(3) { animation-delay: 0.3s; background: #36b1ff; }
        .follow-loader p { margin: 0; color: var(--color-certificate-index-1); font-weight: var(--typo-certificate-index-font-weight-1); }
        @keyframes bounce { 0%, 80%, 100% { transform: translateY(0); opacity: 0.6; } 40% { transform: translateY(-12px); opacity: 1; } }

        .follow-content { opacity: 0; visibility: hidden; transition: opacity 0.4s ease; position: relative; min-height: 400px; }
        body.certificates-ready .follow-content { opacity: 1; visibility: visible; }
        body.certificates-ready #certificate-status-loader { display: none; }

        .follow-table .action-cell { min-width: 110px; white-space: nowrap; }
        .table-responsive { overflow: visible !important; }
        .table td { padding: 4px 8px; height: 40px; }

        .follow-action-dropdown { position: relative; }
        .follow-action-dropdown .dropdown-menu {
            min-width: 200px; position: absolute !important;
            top: 0 !important; right: 100% !important;
            margin-right: 0 !important; left: auto !important;
            transform: none !important; z-index: 9999;
        }
        .follow-action-dropdown .dropdown-menu.dropdown-menu-upward {
            top: 0 !important; left: auto !important; right: 100% !important; transform: none !important;
        }

        .program-filter-row { display: flex; gap: var(--space-certificate-index-2); flex-wrap: wrap; align-items: end; margin-bottom: var(--space-certificate-index-2); }
        .program-filter-field { flex: 1 1 200px; min-width: 180px; }
        .program-filter-field .form-label { font-size: 13px; font-weight: var(--typo-certificate-index-font-weight-1); color: var(--color-certificate-index-1); margin-bottom: 4px; }
        .program-filter-actions { display: flex; gap: var(--space-certificate-index-3); margin-left: auto; align-items: center; }

        .user-mgmt-header { display: flex; align-items: stretch; justify-content: space-between; gap: var(--space-certificate-index-1); flex-wrap: wrap; }
        .user-mgmt-header .follow-tab-bar { flex: 1 1 auto; }
        .create-action-btn { align-self: center; padding: 0.5rem 1rem !important; white-space: nowrap; margin: 8px 12px 8px 0; }

        @media (max-width: 767px) {
            .program-filter-actions { width: var(--dimension-certificate-index-1); margin-left: 0; }
            .create-action-btn { margin: 0 12px 8px; width: calc(100% - 24px); text-align: center; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            function reveal() {
                setTimeout(function () { document.body.classList.add('certificates-ready'); }, 150);
            }

            document.addEventListener('DOMContentLoaded', function () {
                reveal();

                var search = document.getElementById('certificate-status-search');
                var form = document.getElementById('certificate-filter-form');
                if (search && form) {
                    search.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter') { e.preventDefault(); form.submit(); }
                    });
                }

                document.querySelectorAll('.follow-action-dropdown .dropdown-toggle').forEach(function (button) {
                    button.addEventListener('click', function () {
                        var wrapper = this.closest('.follow-action-dropdown');
                        if (!wrapper) return;
                        var menu = wrapper.querySelector('.dropdown-menu');
                        if (!menu) return;
                        menu.classList.remove('dropdown-menu-upward');
                        var rect = wrapper.getBoundingClientRect();
                        if ((window.innerHeight - rect.bottom) < 240) {
                            menu.classList.add('dropdown-menu-upward');
                        }
                    });
                });

                var bulkForm = document.getElementById('certificate-bulk-approve-form');
                var selectAll = document.getElementById('certificate-select-all');
                var selectedCount = document.getElementById('certificate-selected-count');
                var bulkButton = document.getElementById('certificate-bulk-approve-button');
                var bulkRemarks = document.getElementById('certificate-bulk-remarks');
                var bulkCheckboxes = Array.prototype.slice.call(document.querySelectorAll('.certificate-bulk-checkbox'));

                function syncBulkSelectionState() {
                    if (!bulkCheckboxes.length) return;

                    var checkedCount = bulkCheckboxes.filter(function (checkbox) {
                        return checkbox.checked;
                    }).length;

                    if (selectedCount) {
                        selectedCount.textContent = String(checkedCount);
                    }

                    if (bulkButton) {
                        bulkButton.disabled = checkedCount === 0;
                    }

                    if (selectAll) {
                        selectAll.checked = checkedCount > 0 && checkedCount === bulkCheckboxes.length;
                        selectAll.indeterminate = checkedCount > 0 && checkedCount < bulkCheckboxes.length;
                    }
                }

                if (selectAll) {
                    selectAll.addEventListener('change', function () {
                        bulkCheckboxes.forEach(function (checkbox) {
                            checkbox.checked = selectAll.checked;
                        });

                        syncBulkSelectionState();
                    });
                }

                bulkCheckboxes.forEach(function (checkbox) {
                    checkbox.addEventListener('change', syncBulkSelectionState);
                });

                if (bulkForm) {
                    bulkForm.addEventListener('submit', function (event) {
                        var checkedCount = bulkCheckboxes.filter(function (checkbox) {
                            return checkbox.checked;
                        }).length;

                        if (checkedCount === 0) {
                            event.preventDefault();
                            return;
                        }

                        if (window.Swal && typeof window.Swal.fire === 'function') {
                            event.preventDefault();

                            window.Swal.fire({
                                title: 'Approve selected certificates?',
                                input: 'textarea',
                                inputLabel: 'Remarks (optional)',
                                inputPlaceholder: 'Enter remarks for all selected students',
                                inputAttributes: {
                                    'aria-label': 'Remarks'
                                },
                                showCancelButton: true,
                                confirmButtonText: 'Approve',
                                cancelButtonText: 'Cancel'
                            }).then(function (result) {
                                if (!result.isConfirmed) {
                                    return;
                                }

                                if (bulkRemarks) {
                                    bulkRemarks.value = result.value || '';
                                }

                                bulkForm.submit();
                            });

                            return;
                        }

                        if (!window.confirm('Approve ' + checkedCount + ' selected certificate request(s)?')) {
                            event.preventDefault();
                        }
                    });
                }

                syncBulkSelectionState();
            });
        })();
    </script>
@endpush
