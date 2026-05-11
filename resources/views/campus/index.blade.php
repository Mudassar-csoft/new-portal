@extends('layouts.theme')

@section('title', 'All Campuses / Franchise')

@section('content')
    @php
        $scopeTabs = [
            'all' => 'All Campuses / Franchise',
            'campuses' => 'All Campuses',
            'franchise' => 'All Franchise',
            'suspended_campuses' => 'Suspended Campuses',
            'suspended_franchise' => 'Suspended Franchise',
        ];
        $scopeCountMap = collect($scopeCards ?? [])->pluck('count', 'scope')->all();
        $activeScope = $activeScope ?? 'all';
        if (!array_key_exists($activeScope, $scopeTabs)) {
            $activeScope = 'all';
        }
        $typeLabels = $typeOptions ?? ['company' => 'Company Owned', 'franchise' => 'Franchise'];
        $typeBadgeClasses = ['company' => 'label-primary', 'franchise' => 'label-warning'];
        $statusBadgeClasses = ['active' => 'label-success', 'inactive' => 'label-default'];
    @endphp

    <div class="lead-status-shell campus-status-shell">
        <div id="follow-loader" class="follow-loader">
            <div class="follow-spinner">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
            <p>Loading campuses...</p>
        </div>

        <div id="campus-status-content" class="follow-content campus-content">
            <div class="follow-card box-typical box-typical-dashboard panel panel-default">
                <div class="follow-tab-bar">
                    @foreach ($scopeTabs as $scopeKey => $scopeLabel)
                        <div class="follow-tab {{ $activeScope === $scopeKey ? 'active' : '' }}" data-scope="{{ $scopeKey }}">
                            <span class="label-text">{{ $scopeLabel }}</span>
                            <span class="badge badge-secondary">{{ number_format((int) ($scopeCountMap[$scopeKey] ?? 0)) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="box-typical-body panel-body follow-body campus-body">
                    @if(session('status'))
                        <div class="alert alert-success mb-3">{{ session('status') }}</div>
                    @endif

                    <div class="follow-controls">
                        <div class="d-flex campus-entries-control">
                            <label>Show</label>
                            <select id="campus-status-page-size" class="form-select form-select-sm">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="all">All</option>
                            </select>
                            <label>Entries</label>
                        </div>
                        <div class="follow-search">
                            <input type="text" id="campus-status-search" class="form-control form-control-sm" placeholder="Search...">
                            <i class="fa fa-search"></i>
                        </div>
                    </div>

                    <div class="table-responsive campus-table-wrap">
                        <table class="table table-bordered follow-table campus-table" id="campus-status-table">
                            <thead>
                                <tr>
                                    <th>Sr</th>
                                    <th>Code</th>
                                    <th>Campus / Franchise</th>
                                    <th>Location</th>
                                    <th>Type</th>
                                    <th>Contact</th>
                                    <th>Royalty</th>
                                    <th>Status</th>
                                    <th class="text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($campuses as $campus)
                                    @php
                                        $typeKey = (string) ($campus->campus_type ?? '');
                                        $statusKey = (string) ($campus->status ?? 'inactive');
                                    @endphp
                                    <tr data-type="{{ $typeKey }}" data-status="{{ $statusKey }}">
                                        <td class="text-center campus-row-index"></td>
                                        <td>{{ $campus->code ?? 'N/A' }}</td>
                                        <td>{{ $campus->title ?? $campus->name ?? 'N/A' }}</td>
                                        <td>{{ $campus->city ?: 'N/A' }}{{ $campus->country ? ', ' . $campus->country : '' }}</td>
                                        <td>
                                            <span class="label {{ $typeBadgeClasses[$typeKey] ?? 'label-default' }}">
                                                {{ $typeLabels[$typeKey] ?? ucfirst(str_replace('_', ' ', $typeKey)) }}
                                            </span>
                                        </td>
                                        <td>{{ $campus->campus_email ?: ($campus->mobile ?: ($campus->landline ?: 'N/A')) }}</td>
                                        <td>
                                            @if($typeKey === 'franchise' && $campus->royalty_rate !== null)
                                                {{ rtrim(rtrim(number_format((float) $campus->royalty_rate, 2), '0'), '.') }}%
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="label {{ $statusBadgeClasses[$statusKey] ?? 'label-default' }}">
                                                {{ ucfirst($statusKey ?: 'inactive') }}
                                            </span>
                                        </td>
                                        <td class="action-cell">
                                            @include('campus.partials.action', ['actionId' => 'campus-action-' . $campus->id])
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="campus-empty-row">
                                        <td colspan="9" class="text-center text-muted">No campuses found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="follow-footer campus-footer">
                        <div id="campus-status-count">Showing 0 to 0 of 0 entries</div>
                        <ul class="pagination pagination-sm mb-0" id="campus-status-pagination"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .campus-status-shell {
            position: relative;
        }

        .campus-content {
            min-height: 400px;
        }

        .campus-body {
            padding: 16px;
        }

        .campus-entries-control {
            gap: 8px;
            align-items: center;
        }

        .campus-entries-control label {
            margin: 0;
        }

        .campus-entries-control select {
            width: 84px;
        }

        .campus-status-shell .follow-search {
            width: auto;
        }

        .campus-table-wrap {
            overflow: visible !important;
        }

        .campus-table .action-cell {
            min-width: 110px;
            white-space: nowrap;
        }

        .campus-table td,
        .campus-table th {
            white-space: nowrap !important;
        }

        .campus-footer #campus-status-count {
            color: #54667a;
            font-weight: 600;
        }

        @media (max-width: 767px) {
            .campus-status-shell .follow-search {
                width: 100%;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            function revealCampusPage() {
                setTimeout(function () {
                    document.body.classList.add('follow-ready');
                }, 150);
            }

            function matchesScope(row, scope) {
                var type = String(row.getAttribute('data-type') || '').toLowerCase();
                var status = String(row.getAttribute('data-status') || '').toLowerCase();

                switch (scope) {
                    case 'campuses':
                        return type === 'company';
                    case 'franchise':
                        return type === 'franchise';
                    case 'suspended_campuses':
                        return type === 'company' && status === 'inactive';
                    case 'suspended_franchise':
                        return type === 'franchise' && status === 'inactive';
                    default:
                        return true;
                }
            }

            function updateScopeQuery(scope) {
                var url = new URL(window.location.href);
                if (scope === 'all') {
                    url.searchParams.delete('scope');
                } else {
                    url.searchParams.set('scope', scope);
                }
                window.history.replaceState({}, '', url.toString());
            }

            function renderCampusPagination(totalPages, currentPage) {
                var pagination = document.getElementById('campus-status-pagination');
                if (!pagination) {
                    return;
                }

                pagination.innerHTML = '';

                function buildItem(label, page, disabled, active) {
                    var item = document.createElement('li');
                    item.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');

                    var link = document.createElement(disabled ? 'span' : 'button');
                    link.className = 'page-link';
                    link.textContent = label;

                    if (!disabled) {
                        link.type = 'button';
                        link.addEventListener('click', function () {
                            var table = document.getElementById('campus-status-table');
                            if (table) {
                                table.setAttribute('data-current-page', String(page));
                            }
                            renderCampusTable();
                        });
                    }

                    item.appendChild(link);
                    pagination.appendChild(item);
                }

                buildItem('Previous', currentPage - 1, currentPage <= 1, false);

                for (var page = 1; page <= totalPages; page++) {
                    buildItem(String(page), page, false, page === currentPage);
                }

                buildItem('Next', currentPage + 1, currentPage >= totalPages, false);
            }

            function renderCampusTable() {
                var table = document.getElementById('campus-status-table');
                if (!table) {
                    return;
                }

                var activeTab = document.querySelector('.follow-tab.active');
                var activeScope = activeTab ? activeTab.getAttribute('data-scope') : 'all';
                var searchValue = String(document.getElementById('campus-status-search')?.value || '').toLowerCase();
                var pageSizeValue = String(document.getElementById('campus-status-page-size')?.value || '10').toLowerCase();
                var rows = Array.from(table.querySelectorAll('tbody tr:not(.campus-empty-row)'));
                var filteredRows = rows.filter(function (row) {
                    var matchesTab = matchesScope(row, activeScope);
                    var matchesSearch = row.innerText.toLowerCase().indexOf(searchValue) !== -1;
                    return matchesTab && matchesSearch;
                });
                var pageSize = pageSizeValue === 'all' ? filteredRows.length || 1 : parseInt(pageSizeValue, 10);
                var currentPage = parseInt(table.getAttribute('data-current-page') || '1', 10);
                var totalPages = Math.max(1, Math.ceil(filteredRows.length / pageSize));

                if (currentPage > totalPages) {
                    currentPage = 1;
                }

                var startIndex = filteredRows.length ? ((currentPage - 1) * pageSize) : 0;
                var endIndex = filteredRows.length ? Math.min(startIndex + pageSize, filteredRows.length) : 0;
                var visibleRows = filteredRows.slice(startIndex, endIndex);
                var countNode = document.getElementById('campus-status-count');
                var emptyRow = table.querySelector('.campus-empty-row');

                rows.forEach(function (row) {
                    row.style.display = 'none';
                });

                visibleRows.forEach(function (row, index) {
                    row.style.display = '';
                    var indexCell = row.querySelector('.campus-row-index');
                    if (indexCell) {
                        indexCell.textContent = startIndex + index + 1;
                    }
                });

                if (emptyRow) {
                    emptyRow.style.display = filteredRows.length ? 'none' : '';
                }

                if (countNode) {
                    countNode.textContent = 'Showing ' + (filteredRows.length ? (startIndex + 1) : 0) + ' to ' + endIndex + ' of ' + filteredRows.length + ' entries';
                }

                renderCampusPagination(totalPages, currentPage);
                table.setAttribute('data-current-page', String(currentPage));
            }

            function initCampusTabs() {
                var tabs = document.querySelectorAll('.follow-tab');
                tabs.forEach(function (tab) {
                    tab.addEventListener('click', function () {
                        tabs.forEach(function (item) {
                            item.classList.remove('active');
                        });

                        this.classList.add('active');

                        var table = document.getElementById('campus-status-table');
                        if (table) {
                            table.setAttribute('data-current-page', '1');
                        }

                        updateScopeQuery(this.getAttribute('data-scope') || 'all');
                        renderCampusTable();
                    });
                });
            }

            function initCampusControls() {
                var search = document.getElementById('campus-status-search');
                var pageSize = document.getElementById('campus-status-page-size');
                var table = document.getElementById('campus-status-table');

                if (search) {
                    search.addEventListener('input', function () {
                        if (table) {
                            table.setAttribute('data-current-page', '1');
                        }
                        renderCampusTable();
                    });
                }

                if (pageSize) {
                    pageSize.addEventListener('change', function () {
                        if (table) {
                            table.setAttribute('data-current-page', '1');
                        }
                        renderCampusTable();
                    });
                }
            }

            function initCampusActionMenus() {
                var dropdownButtons = document.querySelectorAll('.follow-action-dropdown .dropdown-toggle');
                dropdownButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        var wrapper = this.closest('.follow-action-dropdown');
                        if (!wrapper) return;
                        var menu = wrapper.querySelector('.dropdown-menu');
                        if (!menu) return;

                        menu.classList.remove('dropdown-menu-upward');
                        var rect = wrapper.getBoundingClientRect();
                        var approxMenuHeight = 220;
                        var needsUpward = (window.innerHeight - rect.bottom) < approxMenuHeight;
                        if (needsUpward) {
                            menu.classList.add('dropdown-menu-upward');
                        }
                    });
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                revealCampusPage();
                initCampusTabs();
                initCampusControls();
                initCampusActionMenus();
                renderCampusTable();
            });
        })();
    </script>
@endpush
