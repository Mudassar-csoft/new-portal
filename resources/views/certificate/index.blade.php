@extends('layouts.theme')

@section('title', $pageTitle)

@section('content')
    @php
        $activeScope = $activeScope ?? 'all';
        $scopeCards = $scopeCards ?? [];
        $filters = $filters ?? ['scope' => 'all', 'campus_id' => null, 'program_id' => null, 'search' => null];

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
            'rejected' => 'label-danger',
        ];
    @endphp

    <div class="lead-status-shell">
        <div id="certificate-status-loader" class="follow-loader">
            <div class="follow-spinner">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
            <p>Loading certificates...</p>
        </div>

        <div id="certificate-status-content" class="follow-content">
            @if(session('status'))
                <div class="alert alert-success mb-3">{{ session('status') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger mb-3">{{ session('error') }}</div>
            @endif

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
                            <div class="d-flex" style="gap:0.5rem;align-items: center;">
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
                                    <option value="">All Campuses</option>
                                    @foreach($campuses as $campus)
                                        <option value="{{ $campus->id }}" @selected(($filters['campus_id'] ?? null) == $campus->id)>
                                            {{ $campus->code }} - {{ $campus->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="program-filter-field">
                                <label class="form-label">Programme</label>
                                <select class="form-control form-control-sm" name="program_id">
                                    <option value="">All Programmes</option>
                                    @foreach($programs as $program)
                                        <option value="{{ $program->id }}" @selected(($filters['program_id'] ?? null) == $program->id)>
                                            {{ $program->code }} - {{ $program->title ?? $program->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="program-filter-actions">
                                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                                <a href="{{ route('certificate.index', array_filter(['scope' => $activeScope !== 'all' ? $activeScope : null])) }}" class="btn btn-default btn-sm">Reset</a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-bordered follow-table">
                            <thead>
                                <tr>
                                    <th>Sr</th>
                                    <th>Certificate #</th>
                                    <th>Student</th>
                                    <th>Roll / Reg No</th>
                                    <th>Programme</th>
                                    <th>Campus</th>
                                    <th>Requested</th>
                                    <th>Status</th>
                                    <th class="text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($certificates as $idx => $cert)
                                    @php
                                        $rowIndex = ($certificates->firstItem() ?? 0) + $idx;
                                        $statusKey = $cert->status ?? 'requested';
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $rowIndex }}</td>
                                        <td><strong>{{ $cert->certificate_number }}</strong></td>
                                        <td>{{ $cert->admission?->student_name ?? 'N/A' }}</td>
                                        <td>
                                            {{ $cert->admission?->roll_number ?? 'N/A' }}
                                            <br>
                                            <span class="text-muted">{{ $cert->admission?->registration_number ?? '' }}</span>
                                        </td>
                                        <td>{{ $cert->program?->title ?? $cert->program?->name ?? 'N/A' }}</td>
                                        <td>{{ $cert->campus?->code ?? $cert->campus?->name ?? 'N/A' }}</td>
                                        <td>{{ optional($cert->requested_at)->format('d-M-Y') ?? 'N/A' }}</td>
                                        <td>
                                            <span class="label {{ $statusLabelClasses[$statusKey] ?? 'label-default' }}">
                                                {{ ucfirst($statusKey) }}
                                            </span>
                                        </td>
                                        <td class="action-cell">
                                            @include('certificate.partials.action', ['actionId' => 'cert-action-' . $cert->id])
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No certificates found for the selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="follow-footer">
                        <div>
                            @if($certificates->total() > 0)
                                Showing {{ $certificates->firstItem() }} to {{ $certificates->lastItem() }} of {{ $certificates->total() }} entries
                            @else
                                Showing 0 to 0 of 0 entries
                            @endif
                        </div>
                        {{ $certificates->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .lead-status-shell { position: relative; min-height: 100vh; width: 100%; overflow: hidden; }

        .follow-loader {
            position: absolute; top: 0; left: 0; right: 0; height: 100vh;
            background: rgba(245, 247, 251, 0.95);
            display: flex; align-items: center; justify-content: center; flex-direction: column;
            z-index: 10; gap: 12px;
        }
        .follow-spinner { display: inline-flex; align-items: center; gap: 8px; }
        .follow-spinner .dot { width: 12px; height: 12px; border-radius: 50%; background: #12a0ff; animation: bounce 0.9s ease-in-out infinite; }
        .follow-spinner .dot:nth-child(2) { animation-delay: 0.15s; background: #1f8ef1; }
        .follow-spinner .dot:nth-child(3) { animation-delay: 0.3s; background: #36b1ff; }
        .follow-loader p { margin: 0; color: #54667a; font-weight: 600; }
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

        .program-filter-row { display: flex; gap: 14px; flex-wrap: wrap; align-items: end; margin-bottom: 14px; }
        .program-filter-field { flex: 1 1 200px; min-width: 180px; }
        .program-filter-field .form-label { font-size: 13px; font-weight: 600; color: #54667a; margin-bottom: 4px; }
        .program-filter-actions { display: flex; gap: 8px; margin-left: auto; align-items: center; }

        .user-mgmt-header { display: flex; align-items: stretch; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
        .user-mgmt-header .follow-tab-bar { flex: 1 1 auto; }
        .create-action-btn { align-self: center; padding: 0.5rem 1rem !important; white-space: nowrap; margin: 8px 12px 8px 0; }

        @media (max-width: 767px) {
            .program-filter-actions { width: 100%; margin-left: 0; }
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
            });
        })();
    </script>
@endpush
