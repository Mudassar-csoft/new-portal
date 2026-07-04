@extends('layouts.theme')

@section('title', $pageTitle)

@section('content')
    @php
        $filters = $filters ?? ['scope' => 'all', 'campus_id' => null, 'program_id' => null, 'session' => null, 'status' => null, 'search' => null];
        $scopeCards = $scopeCards ?? [];
        $activeScope = $filters['scope'] ?? 'all';

        $scopeBadgeColors = [
            'all' => 'badge-secondary',
            'upcoming' => 'badge-primary',
            'recently_started' => 'badge-info',
            'in_progress' => 'badge-success',
            'recently_ended' => 'badge-warning',
            'completed' => 'badge-default',
        ];

    @endphp

    <div class="lead-status-shell">
        @include('partials.status-loader', ['id' => 'batch-status-loader', 'message' => 'Loading batches...'])

        <div id="batch-status-content" class="follow-content">
        @include('partials.session-status-alert')

            <div class="follow-card box-typical box-typical-dashboard panel panel-default">
                <div class="follow-tab-bar">
                    @foreach ($scopeCards as $card)
                        @php
                            $isActive = $activeScope === $card['scope'];
                            $url = route('batch.index', array_filter(array_merge(
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

                <div class="box-typical-body panel-body follow-body">
                    <form method="GET" action="{{ route('batch.index') }}" id="batch-filter-form">
                        <input type="hidden" name="scope" value="{{ $activeScope }}">

                        <div class="follow-controls">
                            <div class="d-flex ci-inline-gap-05-center">
                                <label class="">Show</label>
                                <select class="form-select form-select-sm">
                                    <option>10</option>
                                    <option>25</option>
                                    <option>50</option>
                                </select>
                                <label class="">Entries</label>
                            </div>
                            <div class="follow-search">
                                <input type="text" name="search" id="batch-status-search" class="form-control form-control-sm"
                                       placeholder="Search..." value="{{ $filters['search'] ?? '' }}">
                                <i class="fa fa-search"></i>
                            </div>
                        </div>

                        <div class="program-filter-row">
                            <div class="program-filter-field">
                                <label class="form-label">Campus</label>
                                <select class="form-control form-control-sm" name="campus_id">
                                    <option value="">All Campuses</option>
                                    @foreach($campuses as $campus)
                                        <option value="{{ $campus->id }}" @selected(($filters['campus_id'] ?? null) == $campus->id)>
                                            {{ $campus->code }} - {{ $campus->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @include('partials.filter-program-select')
                            <div class="program-filter-field">
                                <label class="form-label">Session</label>
                                <select class="form-control form-control-sm" name="session">
                                    <option value="">All Sessions</option>
                                    @foreach(['morning' => 'Morning', 'evening' => 'Evening', 'weekend' => 'Weekend'] as $key => $label)
                                        <option value="{{ $key }}" @selected(($filters['session'] ?? '') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="program-filter-field">
                                <label class="form-label">Status</label>
                                <select class="form-control form-control-sm" name="status">
                                    <option value="">All Statuses</option>
                                    @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $key => $label)
                                        <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="program-filter-actions">
                                <button type="submit" class="btn btn-primary-outline">Filter</button>
                                <a href="{{ route('batch.index', array_filter(['scope' => $activeScope !== 'all' ? $activeScope : null])) }}" class="btn btn-danger-outline">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered follow-table" id="batch-status-table">
                            <thead>
                                <tr>
                                    <th>Sr</th>
                                    <th>Batch Code</th>
                                    <th>Programme</th>
                                    <th>Instructor</th>
                                    <th>Campus</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Batch Timing</th>
                                    <th>Session</th>
                                    <th>No. of Students</th>
                                    <th>Lab</th>
                                    <th class="text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($batches as $idx => $batch)
                                    @php
                                        $rowIndex = ($batches->firstItem() ?? 0) + $idx;
                                        $statusKey = $batch->status ?? 'active';
                                        $startDate = $batch->start_date?->format('d-M-Y') ?? 'N/A';
                                        $endDate = $batch->end_date?->format('d-M-Y') ?? 'N/A';
                                        $batchTiming = ($batch->start_time && $batch->end_time)
                                            ? \Illuminate\Support\Carbon::parse($batch->start_time)->format('h:i A') . ' - ' . \Illuminate\Support\Carbon::parse($batch->end_time)->format('h:i A')
                                            : 'N/A';
                                    @endphp
                                    <tr data-status="{{ $statusKey }}">
                                        <td class="text-center">{{ $rowIndex }}</td>
                                        <td>{{ $batch->code }}</td>
                                        <td>{{ $batch->program?->title ?? $batch->program?->name ?? 'N/A' }}</td>
                                        <td>{{ $batch->instructor ?? 'N/A' }}</td>
                                        <td>{{ $batch->campus?->code ?? $batch->campus?->name ?? 'N/A' }}</td>
                                        <td>{{ $startDate }}</td>
                                        <td>{{ $endDate }}</td>
                                        <td>{{ $batchTiming }}</td>
                                        <td>{{ ucfirst($batch->session ?? 'n/a') }}</td>
                                        <td class="text-center">{{ $batch->admissions_count }}</td>
                                        <td>{{ $batch->lab ?: 'N/A' }}</td>
                                        <td class="action-cell">
                                            @include('batch.partials.action', ['actionId' => 'batch-action-' . $batch->id])
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center text-muted">No batches found for the selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="follow-footer">
                        @include('partials.follow-pagination', ['paginator' => $batches, 'countId' => 'batch-status-count'])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --dimension-batch-index-1: 100%;
            --dimension-batch-index-2: 100vh;
            --dimension-batch-index-3: 12px;
            --dimension-batch-index-4: 180px;
            --space-batch-index-1: 14px;
            --space-batch-index-2: 8px;
            --color-batch-index-1: #54667a;
            --typo-batch-index-font-weight-1: 600;
        }

        .ci-inline-gap-05-center {
            gap: 0.5rem;
            align-items: center;
        }

        .bootstrap-table .table a, .fixed-table-body .table a, .table a {
            border-bottom: none;
            position: relative;
            top: -1px;
        }

        .lead-status-shell {
            position: relative;
            min-height: var(--dimension-batch-index-2);
            width: var(--dimension-batch-index-1);
            overflow: hidden;
        }

        .follow-loader {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: var(--dimension-batch-index-2);
            background: rgba(245, 247, 251, 0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            z-index: 10;
            gap: 12px;
        }

        .follow-spinner {
            display: inline-flex;
            align-items: center;
            gap: var(--space-batch-index-2);
        }

        .follow-spinner .dot {
            width: var(--dimension-batch-index-3);
            height: var(--dimension-batch-index-3);
            border-radius: 50%;
            background: #12a0ff;
            animation: bounce 0.9s ease-in-out infinite;
        }

        .follow-spinner .dot:nth-child(2) { animation-delay: 0.15s; background: #1f8ef1; }
        .follow-spinner .dot:nth-child(3) { animation-delay: 0.3s;  background: #36b1ff; }

        .follow-loader p {
            margin: 0;
            color: var(--color-batch-index-1);
            font-weight: var(--typo-batch-index-font-weight-1);
        }

        .follow-content {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.4s ease;
            position: relative;
            min-height: 400px;
        }

        body.batches-ready .follow-content { opacity: 1; visibility: visible; }
        body.batches-ready #batch-status-loader { display: none; }

        @keyframes bounce {
            0%, 80%, 100% { transform: translateY(0); opacity: 0.6; }
            40% { transform: translateY(-12px); opacity: 1; }
        }

        .follow-table .action-cell { min-width: 110px; white-space: nowrap; }

        .table-responsive { overflow: visible !important; }

        .follow-action-dropdown { position: relative; }

        .follow-action-dropdown .dropdown-menu {
            min-width: var(--dimension-batch-index-4);
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

        .table td { padding: 2px 2px; height: 38px; }

        .program-filter-row {
            display: flex;
            gap: var(--space-batch-index-1);
            flex-wrap: wrap;
            align-items: end;
            margin-bottom: var(--space-batch-index-1);
        }

        .program-filter-field {
            flex: 1 1 200px;
            min-width: var(--dimension-batch-index-4);
        }

        .program-filter-field .form-label {
            font-size: 13px;
            font-weight: var(--typo-batch-index-font-weight-1);
            color: var(--color-batch-index-1);
            margin-bottom: 4px;
        }

        .program-filter-actions {
            display: flex;
            gap: var(--space-batch-index-2);
            margin-left: auto;
            align-items: center;
        }

        @media (max-width: 767px) {
            .program-filter-actions { width: var(--dimension-batch-index-1); margin-left: 0; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            function revealBatchPage() {
                setTimeout(function () {
                    document.body.classList.add('batches-ready');
                }, 150);
            }

            document.addEventListener('DOMContentLoaded', function () {
                revealBatchPage();

                var searchInput = document.getElementById('batch-status-search');
                var form = document.getElementById('batch-filter-form');
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
