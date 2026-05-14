@extends('layouts.theme')

@section('title', $pageTitle)

@section('content')
    @php
        $filters = $filters ?? ['scope' => 'all', 'campus_type' => null, 'status' => null, 'country' => null, 'city' => null, 'search' => null];
        $scopeCards = $scopeCards ?? [];
        $activeScope = $activeScope ?? 'all';

        $scopeBadgeColors = [
            'all' => 'badge-secondary',
            'campuses' => 'badge-primary',
            'franchise' => 'badge-warning',
            'suspended_campuses' => 'badge-danger',
            'suspended_franchise' => 'badge-danger',
        ];

        $typeLabels = $typeOptions ?? ['company' => 'Company Owned', 'franchise' => 'Franchise'];
        $typeBadgeClasses = ['company' => 'label-primary', 'franchise' => 'label-warning'];
        $statusBadgeClasses = ['active' => 'label-success', 'inactive' => 'label-default'];
    @endphp

    <div class="lead-status-shell campus-status-shell">
        <div id="campus-status-loader" class="follow-loader">
            <div class="follow-spinner">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
            <p>Loading campuses...</p>
        </div>

        <div id="campus-status-content" class="follow-content">
            @if(session('status'))
                <div class="alert alert-success mb-3">{{ session('status') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger mb-3">{{ session('error') }}</div>
            @endif

            <div class="follow-card box-typical box-typical-dashboard panel panel-default">
                <div class="follow-tab-bar">
                    @foreach ($scopeCards as $card)
                        @php
                            $isActive = $activeScope === $card['scope'];
                            $url = route('campus.index', array_filter(array_merge(
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

                <div class="box-typical-body panel-body follow-body campus-body">
                    <form method="GET" action="{{ route('campus.index') }}" id="campus-filter-form">
                        <input type="hidden" name="scope" value="{{ $activeScope }}">

                        <div class="follow-controls">
                            <div class="d-flex" style="gap:0.5rem;align-items: center;">
                                <label class="">Show</label>
                                <select class="form-select form-select-sm">
                                    <option>10</option>
                                    <option>25</option>
                                    <option>50</option>
                                </select>
                                <label class="">Entries</label>
                            </div>
                            <div class="follow-search">
                                <input type="text" name="search" id="campus-status-search" class="form-control form-control-sm"
                                       placeholder="Search..." value="{{ $filters['search'] ?? '' }}">
                                <i class="fa fa-search"></i>
                            </div>
                        </div>

                        <div class="program-filter-row">
                            <div class="program-filter-field">
                                <label class="form-label">Type</label>
                                <select class="form-control form-control-sm" name="campus_type">
                                    <option value="">All Types</option>
                                    @foreach($typeLabels as $key => $label)
                                        <option value="{{ $key }}" @selected(($filters['campus_type'] ?? '') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="program-filter-field">
                                <label class="form-label">Status</label>
                                <select class="form-control form-control-sm" name="status">
                                    <option value="">All Statuses</option>
                                    @foreach(['active' => 'Active', 'inactive' => 'Inactive'] as $key => $label)
                                        <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="program-filter-field">
                                <label class="form-label">Country</label>
                                <input type="text" class="form-control form-control-sm" name="country" value="{{ $filters['country'] ?? '' }}" placeholder="e.g. Pakistan">
                            </div>
                            <div class="program-filter-field">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control form-control-sm" name="city" value="{{ $filters['city'] ?? '' }}" placeholder="e.g. Lahore">
                            </div>
                            <div class="program-filter-actions">
                                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                                <a href="{{ route('campus.index', array_filter(['scope' => $activeScope !== 'all' ? $activeScope : null])) }}" class="btn btn-default btn-sm">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive campus-table-wrap">
                        <table class="table table-bordered follow-table campus-table" id="campus-status-table">
                            <thead>
                                <tr>
                                    <th>Sr</th>
                                    <th>Campus / Franchise</th>
                                    <th>Campus Code</th>
                                    <th>City</th>
                                    <th>Landline</th>
                                    <th>Campus Type</th>
                                    <!-- <th>Royalty</th> -->
                                    <th>Status</th>
                                    <th class="text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($campuses as $idx => $campus)
                                    @php
                                        $rowIndex = ($campuses->firstItem() ?? 0) + $idx;
                                        $typeKey = (string) ($campus->campus_type ?? '');
                                        $statusKey = (string) ($campus->status ?? 'inactive');
                                    @endphp
                                    <tr data-type="{{ $typeKey }}" data-status="{{ $statusKey }}">
                                        <td class="text-center">{{ $rowIndex }}</td>
                                        <td>{{ $campus->title ?? $campus->name ?? 'N/A' }}</td>
                                        <td>{{ $campus->code ?? 'N/A' }}</td>
                                        <td>{{ $campus->city ?: 'N/A' }}{{ $campus->country ? ', ' . $campus->country : '' }}</td>
                                        <td>{{ $campus->campus_email ?: ($campus->mobile ?: ($campus->landline ?: 'N/A')) }}</td>
                                        <td>
                                            <span class="label {{ $typeBadgeClasses[$typeKey] ?? 'label-default' }}">
                                                {{ $typeLabels[$typeKey] ?? ucfirst(str_replace('_', ' ', $typeKey)) }}
                                            </span>
                                        </td>
                                        <!-- <td>
                                            @if($typeKey === 'franchise' && $campus->royalty_rate !== null)
                                                {{ rtrim(rtrim(number_format((float) $campus->royalty_rate, 2), '0'), '.') }}%
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td> -->
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
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No campuses found for the selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="follow-footer campus-footer">
                        <div id="campus-status-count">
                            @if($campuses->total() > 0)
                                Showing {{ $campuses->firstItem() }} to {{ $campuses->lastItem() }} of {{ $campuses->total() }} entries
                            @else
                                Showing 0 to 0 of 0 entries
                            @endif
                        </div>
                        {{ $campuses->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .bootstrap-table .table a, .fixed-table-body .table a, .table a {
            border-bottom: none;
            position: relative;
            top: -1px;
        }

        .lead-status-shell {
            position: relative;
            min-height: 100vh;
            width: 100%;
            overflow: hidden;
        }

        .follow-loader {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100vh;
            background: rgba(245, 247, 251, 0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            z-index: 10;
            gap: 12px;
        }

        .follow-spinner { display: inline-flex; align-items: center; gap: 8px; }

        .follow-spinner .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #12a0ff;
            animation: bounce 0.9s ease-in-out infinite;
        }

        .follow-spinner .dot:nth-child(2) { animation-delay: 0.15s; background: #1f8ef1; }
        .follow-spinner .dot:nth-child(3) { animation-delay: 0.3s;  background: #36b1ff; }

        .follow-loader p { margin: 0; color: #54667a; font-weight: 600; }

        .follow-content {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.4s ease;
            position: relative;
            min-height: 400px;
        }

        body.campuses-ready .follow-content { opacity: 1; visibility: visible; }
        body.campuses-ready #campus-status-loader { display: none; }

        @keyframes bounce {
            0%, 80%, 100% { transform: translateY(0); opacity: 0.6; }
            40% { transform: translateY(-12px); opacity: 1; }
        }

        .campus-table .action-cell {
            min-width: 110px;
            white-space: nowrap;
        }

        .campus-table-wrap { overflow: visible !important; }
        .table td { padding: 2px 6px; height: 38px; }

        .follow-action-dropdown { position: relative; }

        .follow-action-dropdown .dropdown-menu {
            min-width: 180px;
            position: absolute !important;
            top: 0 !important;
            right: 100% !important;
            margin-right: 0 !important;
            left: auto !important;
            transform: none !important;
            z-index: 9999;
        }

        .follow-action-dropdown .dropdown-menu.dropdown-menu-upward {
            top: 0 !important;
            left: auto !important;
            right: 100% !important;
            transform: none !important;
        }

        .program-filter-row {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            align-items: end;
            margin-bottom: 14px;
        }

        .program-filter-field {
            flex: 1 1 200px;
            min-width: 180px;
        }

        .program-filter-field .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #54667a;
            margin-bottom: 4px;
        }

        .program-filter-actions {
            display: flex;
            gap: 8px;
            margin-left: auto;
            align-items: center;
        }

        .campus-footer #campus-status-count {
            color: #54667a;
            font-weight: 600;
        }

        @media (max-width: 767px) {
            .program-filter-actions { width: 100%; margin-left: 0; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            function revealCampusPage() {
                setTimeout(function () {
                    document.body.classList.add('campuses-ready');
                }, 150);
            }

            document.addEventListener('DOMContentLoaded', function () {
                revealCampusPage();

                var searchInput = document.getElementById('campus-status-search');
                var form = document.getElementById('campus-filter-form');
                if (searchInput && form) {
                    searchInput.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            form.submit();
                        }
                    });
                }

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
            });
        })();
    </script>
@endpush
