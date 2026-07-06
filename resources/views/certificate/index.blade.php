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
        $canBulkSendToPrinting = $activeScope === 'approved'
            && ($currentUser?->isAdmin() ?? false)
            && ($currentUser?->hasAnyPermission(['certificate.send-to-printing']) ?? false);
        $canBulkMarkReady = $activeScope === 'printing'
            && ($currentUser?->isAdmin() ?? false)
            && ($currentUser?->hasAnyPermission(['certificate.mark-ready']) ?? false);
        $canBulkPreview = in_array($activeScope, ['printing', 'ready'], true)
            && ($currentUser?->hasAnyPermission(['certificate.view']) ?? false);
        $showBulkSelection = $canBulkApprove || $canBulkSendToPrinting || $canBulkMarkReady || $canBulkPreview;
        $bulkActionConfig = null;

        if ($canBulkApprove) {
            $bulkActionConfig = [
                'mode' => 'approve',
                'method' => 'POST',
                'spoof' => 'PATCH',
                'action' => route('certificate.bulk-approve'),
                'button' => 'Approve Selected',
            ];
        } elseif ($canBulkSendToPrinting) {
            $bulkActionConfig = [
                'mode' => 'send-to-printing',
                'method' => 'POST',
                'spoof' => 'PATCH',
                'action' => route('certificate.bulk-send-to-printing'),
                'button' => 'Send Selected to Printing',
                'confirm' => 'Send selected certificates to printing?',
            ];
        } elseif ($canBulkPreview && $activeScope === 'printing') {
            $bulkActionConfig = [
                'mode' => 'preview',
                'method' => 'POST',
                'action' => route('certificate.bulk-preview'),
                'button' => 'Print Selected',
                'target' => '_blank',
            ];
        } elseif ($canBulkMarkReady) {
            $bulkActionConfig = [
                'mode' => 'mark-ready',
                'method' => 'POST',
                'spoof' => 'PATCH',
                'action' => route('certificate.bulk-mark-ready'),
                'button' => 'Mark Selected Ready',
                'confirm' => 'Mark selected certificates ready for collection?',
            ];
        } elseif ($canBulkPreview) {
            $bulkActionConfig = [
                'mode' => 'preview',
                'method' => 'POST',
                'action' => route('certificate.bulk-preview'),
                'button' => 'Print Selected',
                'target' => '_blank',
            ];
        }

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
                                <input type="text" name="search" id="certificate-status-search" class="form-control form-control-sm certificate-search-input"
                                       placeholder="Search..." value="{{ $filters['search'] ?? '' }}">
                                <span class="certificate-search-shell" id="certificate-search-shell" aria-hidden="true">
                                    <span class="certificate-search-icon"><i class="fa fa-search"></i></span>
                                    <span class="certificate-search-loader" id="certificate-search-loader">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </span>
                                </span>
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
                                @if($showBulkSelection && $bulkActionConfig)
                                    <button
                                        type="submit"
                                        class="btn btn-primary-outline"
                                        id="certificate-bulk-action-button"
                                        form="certificate-bulk-action-form"
                                        disabled
                                    >
                                        {{ $bulkActionConfig['button'] }}
                                    </button>
                                    <span class="certificate-bulk-count">
                                        Selected: <strong id="certificate-selected-count">0</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </form>

                    @if($showBulkSelection && $bulkActionConfig)
                        <form
                            method="{{ $bulkActionConfig['method'] }}"
                            action="{{ $bulkActionConfig['action'] }}"
                            id="certificate-bulk-action-form"
                            data-bulk-mode="{{ $bulkActionConfig['mode'] }}"
                            data-confirm-message="{{ $bulkActionConfig['confirm'] ?? '' }}"
                            @if(!empty($bulkActionConfig['target'])) target="{{ $bulkActionConfig['target'] }}" @endif
                            hidden
                        >
                            @if($bulkActionConfig['method'] !== 'GET')
                                @csrf
                            @endif
                            @if(($bulkActionConfig['spoof'] ?? null) === 'PATCH')
                                @method('PATCH')
                            @endif
                            @if($bulkActionConfig['mode'] === 'approve')
                                <input type="hidden" name="remarks" id="certificate-bulk-remarks" value="">
                            @endif
                            @if($bulkActionConfig['mode'] === 'preview')
                                <input type="hidden" name="scope" value="{{ $activeScope }}">
                            @endif
                            <div id="certificate-bulk-selected-inputs"></div>
                        </form>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered follow-table">
                            <thead>
                                <tr>
                                    @if($showBulkSelection)
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
                                        @if($showBulkSelection)
                                            <td class="text-center certificate-select-col">
                                                <input
                                                    type="checkbox"
                                                    class="certificate-bulk-checkbox"
                                                    data-admission-id="{{ $cert->id }}"
                                                    value="{{ $cert->id }}"
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
                                        <td colspan="{{ ($showBulkSelection ? 1 : 0) + ($showActionColumn ? 6 : 5) }}" class="text-center text-muted">No certificates found for the selected filters.</td>
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
            display: none !important;
        }
        .follow-spinner { display: inline-flex; align-items: center; gap: var(--space-certificate-index-3); }
        .follow-spinner .dot { width: var(--dimension-certificate-index-3); height: var(--dimension-certificate-index-3); border-radius: 50%; background: #12a0ff; animation: bounce 0.9s ease-in-out infinite; }
        .follow-spinner .dot:nth-child(2) { animation-delay: 0.15s; background: #1f8ef1; }
        .follow-spinner .dot:nth-child(3) { animation-delay: 0.3s; background: #36b1ff; }
        .follow-loader p { margin: 0; color: var(--color-certificate-index-1); font-weight: var(--typo-certificate-index-font-weight-1); }
        @keyframes bounce { 0%, 80%, 100% { transform: translateY(0); opacity: 0.6; } 40% { transform: translateY(-12px); opacity: 1; } }

        .follow-content { opacity: 1; visibility: visible; transition: opacity 0.2s ease; position: relative; min-height: 400px; }
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

        .follow-controls .follow-search {
            position: relative;
            width: min(340px, 100%);
            margin-left: auto;
        }
        .certificate-search-input {
            height: 46px;
            padding-right: 58px !important;
            border-radius: 999px !important;
        }
        .certificate-search-shell {
            position: absolute;
            top: 50%;
            right: 18px;
            transform: translateY(-50%);
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            z-index: 2;
        }
        .certificate-search-icon {
            color: #50697d;
            font-size: 1.125rem;
            transition: opacity 0.2s ease;
        }
        .certificate-search-loader {
            position: absolute;
            inset: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 3px;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .certificate-search-loader span {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #169de8;
            animation: certificate-search-pulse 1s ease-in-out infinite;
        }
        .certificate-search-loader span:nth-child(2) {
            animation-delay: 0.16s;
        }
        .certificate-search-loader span:nth-child(3) {
            animation-delay: 0.32s;
        }
        .certificate-search-shell.is-loading .certificate-search-icon {
            opacity: 0;
        }
        .certificate-search-shell.is-loading .certificate-search-loader {
            opacity: 1;
        }
        .certificate-search-input.is-loading {
            border-color: #19a6f0 !important;
            box-shadow: 0 0 0 3px rgba(25, 166, 240, 0.12);
        }
        @keyframes certificate-search-pulse {
            0%, 80%, 100% { transform: translateY(0); opacity: 0.35; }
            40% { transform: translateY(-3px); opacity: 1; }
        }

        .program-filter-row { display: flex; gap: var(--space-certificate-index-2); flex-wrap: wrap; align-items: end; margin-bottom: var(--space-certificate-index-2); }
        .program-filter-field { flex: 1 1 200px; min-width: 180px; }
        .program-filter-field .form-label { font-size: 0.8125rem; font-weight: var(--typo-certificate-index-font-weight-1); color: var(--color-certificate-index-1); margin-bottom: 4px; }
        .program-filter-actions { display: flex; gap: var(--space-certificate-index-3); margin-left: auto; align-items: center; }

        .user-mgmt-header { display: flex; align-items: stretch; justify-content: space-between; gap: var(--space-certificate-index-1); flex-wrap: wrap; }
        .user-mgmt-header .follow-tab-bar { flex: 1 1 auto; }
        @media (max-width: 767px) {
            .program-filter-actions { width: var(--dimension-certificate-index-1); margin-left: 0; }
            .follow-controls .follow-search { width: 100%; margin-left: 0; }
        }
    </style>
@endpush

@push('scripts')
        <script>
        (function () {
            var activeRequestController = null;
            var selectedCertificateIdsByScope = {};

            function reveal() {
                document.body.classList.add('certificates-ready');
            }

            function setLoadingState(isLoading) {
                var content = document.getElementById('certificate-status-content');
                var pageLoader = document.getElementById('certificate-status-loader');
                var searchShell = document.getElementById('certificate-search-shell');
                var searchInput = document.getElementById('certificate-status-search');

                if (!content) {
                    return;
                }

                if (pageLoader) {
                    pageLoader.style.display = 'none';
                }

                content.style.pointerEvents = isLoading ? 'none' : '';

                if (searchShell) {
                    searchShell.classList.toggle('is-loading', !!isLoading);
                }

                if (searchInput) {
                    searchInput.classList.toggle('is-loading', !!isLoading);
                }
            }

            function buildFilterUrl(form) {
                var params = new URLSearchParams(new FormData(form));
                var normalized = new URL(form.getAttribute('action') || window.location.href, window.location.origin);

                Array.from(params.keys()).forEach(function (key) {
                    var values = params.getAll(key).filter(function (value) {
                        return String(value).trim() !== '';
                    });

                    normalized.searchParams.delete(key);

                    values.forEach(function (value) {
                        normalized.searchParams.append(key, value);
                    });
                });

                normalized.searchParams.delete('page');

                return normalized.toString();
            }

            function resolveCurrentScope() {
                var scopeField = document.querySelector('#certificate-filter-form input[name="scope"]');

                return scopeField && scopeField.value ? scopeField.value : 'all';
            }

            function loadCertificatePage(url) {
                var requestUrl = url || window.location.href;
                var content = document.getElementById('certificate-status-content');

                if (!content) {
                    window.location.assign(requestUrl);
                    return;
                }

                if (activeRequestController) {
                    activeRequestController.abort();
                }

                var requestController = new AbortController();
                activeRequestController = requestController;
                setLoadingState(true);

                fetch(requestUrl, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    signal: requestController.signal
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Request failed with status ' + response.status);
                        }

                        return response.text();
                    })
                    .then(function (html) {
                        var parser = new DOMParser();
                        var doc = parser.parseFromString(html, 'text/html');
                        var nextContent = doc.getElementById('certificate-status-content');

                        if (!nextContent) {
                            throw new Error('Certificate content container not found in response.');
                        }

                        content.innerHTML = nextContent.innerHTML;
                        window.history.replaceState({}, '', requestUrl);
                        initializeCertificatePage();
                        reveal();
                    })
                    .catch(function (error) {
                        if (error && error.name === 'AbortError') {
                            return;
                        }

                        window.location.assign(requestUrl);
                    })
                    .finally(function () {
                        if (activeRequestController === requestController) {
                            activeRequestController = null;
                            setLoadingState(false);
                        }
                    });
            }

            function currentSelectionBucket() {
                var scope = resolveCurrentScope();

                if (!Array.isArray(selectedCertificateIdsByScope[scope])) {
                    selectedCertificateIdsByScope[scope] = [];
                }

                return selectedCertificateIdsByScope[scope];
            }

            function hasSelectedCertificateId(id) {
                return currentSelectionBucket().indexOf(String(id)) !== -1;
            }

            function addSelectedCertificateId(id) {
                var normalizedId = String(id);
                var bucket = currentSelectionBucket();

                if (!hasSelectedCertificateId(normalizedId)) {
                    bucket.push(normalizedId);
                }
            }

            function removeSelectedCertificateId(id) {
                var normalizedId = String(id);
                var scope = resolveCurrentScope();

                selectedCertificateIdsByScope[scope] = currentSelectionBucket().filter(function (value) {
                    return value !== normalizedId;
                });
            }

            function initializeCertificatePage() {
                var content = document.getElementById('certificate-status-content');

                if (!content) {
                    return;
                }

                var search = document.getElementById('certificate-status-search');
                var form = document.getElementById('certificate-filter-form');
                if (search && form) {
                    var searchTimer = null;
                    var lastSearchValue = search.value;

                    search.addEventListener('input', function () {
                        var currentValue = search.value;

                        if (searchTimer) {
                            window.clearTimeout(searchTimer);
                        }

                        searchTimer = window.setTimeout(function () {
                            if (currentValue === lastSearchValue) {
                                return;
                            }

                            lastSearchValue = currentValue;
                            loadCertificatePage(buildFilterUrl(form));
                        }, 400);
                    });

                    search.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            loadCertificatePage(buildFilterUrl(form));
                        }
                    });

                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                        loadCertificatePage(buildFilterUrl(form));
                    });
                }

                content.querySelectorAll('.follow-tab-bar a.follow-tab').forEach(function (link) {
                    link.addEventListener('click', function (event) {
                        event.preventDefault();
                        var href = link.getAttribute('href');

                        if (href) {
                            loadCertificatePage(href);
                        }
                    });
                });

                content.querySelectorAll('.follow-footer .pagination a.page-link').forEach(function (link) {
                    link.addEventListener('click', function (event) {
                        var href = link.getAttribute('href');

                        if (!href || href === '#') {
                            event.preventDefault();
                            return;
                        }

                        event.preventDefault();
                        loadCertificatePage(href);
                    });
                });

                content.querySelectorAll('.program-filter-actions a.btn-danger-outline').forEach(function (link) {
                    link.addEventListener('click', function (event) {
                        event.preventDefault();
                        var href = link.getAttribute('href');

                        if (href) {
                            loadCertificatePage(href);
                        }
                    });
                });

                content.querySelectorAll('.follow-action-dropdown .dropdown-toggle').forEach(function (button) {
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

                var bulkForm = document.getElementById('certificate-bulk-action-form');
                var selectAll = document.getElementById('certificate-select-all');
                var selectedCount = document.getElementById('certificate-selected-count');
                var bulkButton = document.getElementById('certificate-bulk-action-button');
                var bulkRemarks = document.getElementById('certificate-bulk-remarks');
                var bulkSelectedInputs = document.getElementById('certificate-bulk-selected-inputs');
                var bulkCheckboxes = Array.prototype.slice.call(document.querySelectorAll('.certificate-bulk-checkbox'));

                function renderBulkSelectionInputs() {
                    if (!bulkSelectedInputs) {
                        return;
                    }

                    bulkSelectedInputs.innerHTML = '';

                    currentSelectionBucket().forEach(function (id) {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'admission_ids[]';
                        input.value = id;
                        bulkSelectedInputs.appendChild(input);
                    });
                }

                function syncBulkSelectionState() {
                    var selectedIds = currentSelectionBucket();

                    bulkCheckboxes.forEach(function (checkbox) {
                        var id = checkbox.getAttribute('data-admission-id') || checkbox.value;
                        checkbox.checked = hasSelectedCertificateId(id);
                    });

                    renderBulkSelectionInputs();

                    var checkedCount = selectedIds.length;
                    var currentPageCheckedCount = bulkCheckboxes.filter(function (checkbox) {
                        return checkbox.checked;
                    }).length;

                    if (selectedCount) {
                        selectedCount.textContent = String(checkedCount);
                    }

                    if (bulkButton) {
                        bulkButton.disabled = checkedCount === 0;
                    }

                    if (selectAll) {
                        selectAll.checked = bulkCheckboxes.length > 0 && currentPageCheckedCount === bulkCheckboxes.length;
                        selectAll.indeterminate = currentPageCheckedCount > 0 && currentPageCheckedCount < bulkCheckboxes.length;
                    }
                }

                if (selectAll) {
                    selectAll.addEventListener('change', function () {
                        bulkCheckboxes.forEach(function (checkbox) {
                            var id = checkbox.getAttribute('data-admission-id') || checkbox.value;
                            checkbox.checked = selectAll.checked;

                            if (selectAll.checked) {
                                addSelectedCertificateId(id);
                            } else {
                                removeSelectedCertificateId(id);
                            }
                        });

                        syncBulkSelectionState();
                    });
                }

                bulkCheckboxes.forEach(function (checkbox) {
                    checkbox.addEventListener('change', function () {
                        var id = checkbox.getAttribute('data-admission-id') || checkbox.value;

                        if (checkbox.checked) {
                            addSelectedCertificateId(id);
                        } else {
                            removeSelectedCertificateId(id);
                        }

                        syncBulkSelectionState();
                    });
                });

                if (bulkForm) {
                    bulkForm.addEventListener('submit', function (event) {
                        var checkedCount = currentSelectionBucket().length;
                        var bulkMode = bulkForm.getAttribute('data-bulk-mode') || '';
                        var confirmMessage = bulkForm.getAttribute('data-confirm-message') || '';

                        if (checkedCount === 0) {
                            event.preventDefault();
                            return;
                        }

                        if (bulkMode === 'preview') {
                            return;
                        }

                        if (bulkMode === 'approve' && window.Swal && typeof window.Swal.fire === 'function') {
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

                        if (bulkMode === 'approve') {
                            if (!window.confirm('Approve ' + checkedCount + ' selected certificate request(s)?')) {
                                event.preventDefault();
                            }

                            return;
                        }

                        event.preventDefault();

                        if (window.swal) {
                            swal({
                                title: confirmMessage || 'Continue?',
                                type: 'warning',
                                showCancelButton: true,
                                closeOnConfirm: false,
                                confirmButtonText: 'Yes',
                                cancelButtonText: 'Cancel'
                            }, function (isConfirm) {
                                if (!isConfirm) {
                                    return false;
                                }

                                swal.close();
                                bulkForm.submit();
                            });

                            return;
                        }

                        if (window.confirm(confirmMessage || 'Continue?')) {
                            bulkForm.submit();
                        }
                    });
                }

                syncBulkSelectionState();
            }

            document.addEventListener('DOMContentLoaded', function () {
                reveal();
                initializeCertificatePage();
            });
        })();
    </script>
@endpush
