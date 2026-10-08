@extends('layouts.theme')

@section('title', $pageTitle)

@section('content')
    @php
        $filters = $filters ?? ['scope' => 'all', 'program_type' => null, 'status' => null, 'campus_id' => null, 'search' => null];
        $scopeCards = $scopeCards ?? [];
        $activeScope = $filters['scope'] ?? 'all';
        $perPage = $perPage ?? 10;
        $exportQuery = request()->except('page');

        if (($exportQuery['scope'] ?? 'all') === 'all') {
            unset($exportQuery['scope']);
        }

        $scopeBadgeColors = [
            'all' => 'badge-secondary',
            'ongoing' => 'badge-primary',
            'suspended' => 'badge-warning',
            'discounted' => 'badge-success',
        ];

        $statusLabelClasses = [
            'active' => 'label-success',
            'inactive' => 'label-default',
        ];
    @endphp

    <div class="lead-status-shell">
        @include('partials.status-loader', ['id' => 'program-status-loader', 'message' => 'Loading programmes...'])

        <div id="program-status-content" class="follow-content">
        @include('partials.session-status-alert')

            <div class="follow-card box-typical box-typical-dashboard panel panel-default">
                <div class="follow-tab-bar">
                    @foreach ($scopeCards as $card)
                        @php
                            $isActive = $activeScope === $card['scope'];
                            $url = route('program.index', array_filter(array_merge(
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
                    <form method="GET" action="{{ route('program.index') }}" id="program-filter-form">
                        <input type="hidden" name="scope" value="{{ $activeScope }}">

                        <div class="follow-controls">
                            <div class="d-flex ci-inline-gap-05-center">
                                <label class="">Show</label>
                                <select class="form-select form-select-sm" name="per_page" onchange="this.form.submit()">
                                    @foreach ([10, 20, 50, 100] as $option)
                                        <option value="{{ $option }}" @selected((int) $perPage === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                                <label class="">Entries</label>
                            </div>
                            <div class="follow-search">
                                <input type="text" name="search" id="program-status-search" class="form-control form-control-sm"
                                       placeholder="Search..." value="{{ $filters['search'] ?? '' }}">
                                <i class="fa fa-search"></i>
                            </div>
                        </div>

                        <div class="program-filter-row">
                            <div class="program-filter-field">
                                <label class="form-label">Programme Type</label>
                                <select class="form-control form-control-sm" name="program_type">
                                    <option value="">All Types</option>
                                    @foreach($typeOptions as $type)
                                        <option value="{{ $type }}" @selected(($filters['program_type'] ?? '') === $type)>{{ ucwords($type) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @include('partials.filter-active-status-select')
                            <div class="program-filter-field">
                                <label class="form-label">Discount Campus</label>
                                <select class="form-control form-control-sm" name="campus_id">
                                    <option value="">All Campuses</option>
                                    @foreach($campuses as $campus)
                                        <option value="{{ $campus->id }}" @selected(($filters['campus_id'] ?? null) == $campus->id)>
                                            {{ $campus->code }} - {{ $campus->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="program-filter-actions">
                                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                                <a href="{{ route('program.index', array_filter([
                                    'scope' => $activeScope !== 'all' ? $activeScope : null,
                                    'per_page' => (int) $perPage !== 10 ? $perPage : null,
                                ])) }}" class="btn btn-danger-outline">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered follow-table" id="program-status-table" data-export-url="{{ route('program.export', $exportQuery) }}">
                            <thead>
                                <tr>
                                    <th>Sr</th>
                                    <th>Title</th>
                                    <th>Code</th>
                                    <th>Duration</th>
                                    <th>Fee</th>
                                    <th>Discounts</th>
                                    <th>Status</th>
                                    <th class="text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($programs as $idx => $program)
                                    @php
                                        $rowIndex = ($programs->firstItem() ?? 0) + $idx;
                                        $statusKey = $program->status ?? 'active';
                                    @endphp
                                    <tr data-status="{{ $statusKey }}">
                                        <td class="text-center">{{ $rowIndex }}</td>
                                        <td>
                                            <strong>{{ $program->title ?? $program->name }}</strong>
                                            @if($program->remarks)
                                                <br>
                                                <span class="text-muted">{{ \Illuminate\Support\Str::limit($program->remarks, 80) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $program->code }}</td>
                                        <td>{{ number_format((int) ($program->duration_weeks ?? 0)) }} weeks</td>
                                        <td>{{ number_format((float) $program->fee, 2) }}</td>
                                        <td>
                                            @if($program->campusDiscounts->isEmpty())
                                                <span class="text-muted">No discounts</span>
                                            @else
                                                @foreach($program->campusDiscounts->sortBy(fn ($discount) => [$discount->campus_id === null ? 0 : 1, $discount->campus?->name ?? '']) as $discount)
                                                    <div class="program-discount-line">
                                                        <strong>{{ rtrim(rtrim(number_format((float) $discount->discount_percent, 2), '0'), '.') }}%</strong>
                                                        <span class="text-muted">{{ $discount->campus?->name ?? 'All campuses' }}</span>
                                                        <span class="label {{ ($discount->status ?? 'active') === 'active' ? 'label-success' : 'label-default' }}">
                                                            {{ ucfirst($discount->status ?? 'active') }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            <span class="label {{ $statusLabelClasses[$statusKey] ?? 'label-default' }}">
                                                {{ ucfirst($statusKey) }}
                                            </span>
                                        </td>
                                        <td class="action-cell">
                                            @include('program.partials.action', ['actionId' => 'program-action-' . $program->id])
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No programmes found for the selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="follow-footer">
                        @include('partials.follow-pagination', ['paginator' => $programs, 'countId' => 'program-status-count'])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --dimension-program-index-1: 100%;
            --dimension-program-index-2: 100vh;
            --dimension-program-index-3: 12px;
            --dimension-program-index-4: 180px;
            --space-program-index-1: 14px;
            --space-program-index-2: 6px;
            --space-program-index-3: 8px;
            --color-program-index-1: #54667a;
            --typo-program-index-font-weight-1: 600;
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
            min-height: var(--dimension-program-index-2);
            width: var(--dimension-program-index-1);
            overflow: hidden;
        }

        .follow-loader {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: var(--dimension-program-index-2);
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
            gap: var(--space-program-index-3);
        }

        .follow-spinner .dot {
            width: var(--dimension-program-index-3);
            height: var(--dimension-program-index-3);
            border-radius: 50%;
            background: #12a0ff;
            animation: bounce 0.9s ease-in-out infinite;
        }

        .follow-spinner .dot:nth-child(2) {
            animation-delay: 0.15s;
            background: #1f8ef1;
        }

        .follow-spinner .dot:nth-child(3) {
            animation-delay: 0.3s;
            background: #36b1ff;
        }

        .follow-loader p {
            margin: 0;
            color: var(--color-program-index-1);
            font-weight: var(--typo-program-index-font-weight-1);
        }

        .follow-content {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.4s ease;
            position: relative;
            min-height: 400px;
        }

        body.programs-ready .follow-content {
            opacity: 1;
            visibility: visible;
        }

        body.programs-ready #program-status-loader {
            display: none;
        }

        @keyframes bounce {
            0%, 80%, 100% {
                transform: translateY(0);
                opacity: 0.6;
            }
            40% {
                transform: translateY(-12px);
                opacity: 1;
            }
        }

        .follow-table .action-cell {
            min-width: 110px;
            white-space: nowrap;
        }

        .table-responsive {
            overflow: visible !important;
        }

        .follow-action-dropdown {
            position: relative;
        }

        .follow-action-dropdown .dropdown-menu {
            min-width: var(--dimension-program-index-4);
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

        .table td {
            padding: 2px 2px;
            height: 38px;
        }

        .program-filter-row {
            display: flex;
            gap: var(--space-program-index-1);
            flex-wrap: wrap;
            align-items: end;
            margin-bottom: var(--space-program-index-1);
        }

        .program-filter-field {
            flex: 1 1 200px;
            min-width: var(--dimension-program-index-4);
        }

        .program-filter-field .form-label {
            font-size: 0.8125rem;
            font-weight: var(--typo-program-index-font-weight-1);
            color: var(--color-program-index-1);
            margin-bottom: 4px;
        }

        .program-filter-actions {
            display: flex;
            gap: var(--space-program-index-3);
            margin-left: auto;
            align-items: center;
        }

        .program-discount-line {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: var(--space-program-index-2);
            margin-bottom: var(--space-program-index-2);
        }

        .select2-container--arrow .select2-selection--single .select2-selection__rendered,
        .select2-container--default .select2-selection--single .select2-selection__rendered,
        .select2-container--white .select2-selection--single .select2-selection__rendered {
            border: solid 1px #d8e2e7;
            -webkit-border-radius: .25rem;
            border-radius: .25rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #343434;
            padding: .375rem 25px .375rem 1rem;
            min-height: 21px !important;
            background: #fff;
        }

        @media (max-width: 767px) {
            .program-filter-actions {
                width: var(--dimension-program-index-1);
                margin-left: 0;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            function revealProgramPage() {
                setTimeout(function () {
                    document.body.classList.add('programs-ready');
                }, 150);
            }

            document.addEventListener('DOMContentLoaded', function () {
                revealProgramPage();

                var searchInput = document.getElementById('program-status-search');
                var form = document.getElementById('program-filter-form');
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
