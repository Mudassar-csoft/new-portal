@extends('layouts.theme')

@section('title', 'Pending Recovery')

@section('content')
    @php
        $rows = collect($summary['rows'] ?? []);
        $columns = collect($summary['columns'] ?? []);
        $monthOptions = $monthOptions ?? [];
        $campusLabel = $selectedCampus?->code ?: $selectedCampus?->name;
        $periodGrandTotal = (float) $rows->sum('period_total');
        $overallGrandTotal = (float) $rows->sum('overall_total');
        $periodColspan = $columns->count() + 2;
        $tableColspan = $periodColspan + ($allPending ? 0 : 1);
    @endphp

    <div class="pending-recovery-shell">
        <div class="pending-recovery-heading">
            Showing Pending Recovery — {{ $periodLabel }}
         @if($campusLabel) <span class="pending-recovery-campus mr-4">{{ $campusLabel }}</span> @endif
        </div>

       

        <div class="pending-recovery-divider"></div>

        <form method="GET" action="{{ route('dashboard.pending-recovery') }}" class="pending-recovery-filter">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="recovery-months" class="pending-recovery-label">Select Month(s):</label>
                    <select id="recovery-months" name="months[]" class="form-control pending-recovery-month-select" multiple size="6" @disabled($allPending)>
                        @foreach($monthOptions as $monthNumber => $label)
                            <option value="{{ $monthNumber }}" @selected(in_array((int) $monthNumber, $selectedMonths, true))>{{ $label }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Hold Ctrl (Windows) or Command (Mac) to select multiple months.</small>
                </div>
                <div class="form-group col-md-4">
                    <label for="recovery-year" class="pending-recovery-label">Select Year:</label>
                    <select id="recovery-year" name="year" class="form-control" @disabled($allPending)>
                        @foreach(($yearOptions ?? []) as $yearValue)
                            <option value="{{ $yearValue }}" @selected($selectedYear === (int) $yearValue)>{{ $yearValue }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4 pending-recovery-action-cell mt-4 pt-2">
                    <label class="d-block" for="recovery-all-pending">
                        <input id="recovery-all-pending" type="checkbox" name="all_pending" value="1" @checked($allPending)>
                        All pending recovery (all years)
                    </label>
                    <button type="submit" class="btn btn-primary pending-recovery-button">Filter</button>
                    <a href="{{ route('dashboard.pending-recovery') }}" class="btn btn-default">Reset</a>
                </div>
            </div>
        </form>

        <div class=" pending-recovery-table-wrap m-3 mr-3">
            <table class="table table-bordered pending-recovery-table">
                <thead>
                    <tr>
                        <th class="pending-border" colspan="{{ $periodColspan }}"><h4 class="text-center mt-2">Pending Recovery — {{ $periodLabel }}</h4></th>
                        @unless($allPending)
                            <th rowspan="2"><h4 class="text-center mt-2">Overall Pending</h4></th>
                        @endunless
                    </tr>
                    <tr>
                        <th>Campus Code</th>
                        @foreach($columns as $column)
                            <th>{{ $column['label'] }}</th>
                        @endforeach
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="pending-recovery-campus-code">
                                @if(!empty($row['campus_id']))
                                    <a
                                        href="{{ route('dashboard.pending-recovery.campus', ['campus' => $row['campus_id']] + $periodFilters) }}"
                                        class="pending-recovery-campus-link"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        {{ $row['campus_code'] ?? 'N/A' }}
                                    </a>
                                @else
                                    {{ $row['campus_code'] ?? 'N/A' }}
                                @endif
                            </td>
                            @foreach($columns as $column)
                                <td>{{ number_format((float) ($row[$column['key']] ?? 0), 0) }}</td>
                            @endforeach
                            <td>{{ number_format((float) ($row['period_total'] ?? 0), 0) }}</td>
                            @unless($allPending)
                                <td>
                                    <a href="{{ route('dashboard.pending-recovery.campus', ['campus' => $row['campus_id'], 'all_pending' => 1]) }}" target="_blank" rel="noopener" aria-label="View all pending recovery for {{ $row['campus_code'] }}">
                                        {{ number_format((float) ($row['overall_total'] ?? 0), 0) }}
                                    </a>
                                </td>
                            @endunless
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $tableColspan }}" class="pending-recovery-empty">No pending recovery data found.</td>
                        </tr>
                    @endforelse
                    @if($rows->isNotEmpty())
                        <tr class="pending-recovery-total-row">
                            <td class="pending-recovery-total-label">Total</td>
                            @foreach($columns as $column)
                                <td class="pending-recovery-total-value">{{ number_format((float) $rows->sum($column['key']), 0) }}</td>
                            @endforeach
                            <td class="pending-recovery-total-value">{{ number_format($periodGrandTotal, 0) }}</td>
                            @unless($allPending)
                                <td class="pending-recovery-total-value">{{ number_format($overallGrandTotal, 0) }}</td>
                            @endunless
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --dimension-dashboard-pending-recovery-1: 100%;
            --dimension-dashboard-pending-recovery-2: 48px;
            --space-dashboard-pending-recovery-1: 14px;
            --space-dashboard-pending-recovery-2: 16px 12px;
            --color-dashboard-pending-recovery-1: #0994de;
            --color-dashboard-pending-recovery-2: #17a8f5;
            --color-dashboard-pending-recovery-3: #6b7280;
            --color-dashboard-pending-recovery-4: #fff;
        }

        :root {
            --dimension-dashboard-pending-recovery-1: 100%;
            --dimension-dashboard-pending-recovery-2: 48px;
            --space-dashboard-pending-recovery-1: 14px;
            --space-dashboard-pending-recovery-2: 16px 12px;
            --pending-recovery-font-md: 16px;
            --pending-recovery-font-lg: 18px;
            --pending-recovery-font-xl: 20px;
            --pending-recovery-font-xxl: 22px;
            --pending-recovery-weight-medium: 500;
            --pending-recovery-weight-semibold: 600;
            --pending-recovery-weight-bold: 700;
        }0___

        .pending-border{
            border-bottom:1px solid gray;
        }
        .pending-recovery-shell {
            padding: 8px 0 18px;
            background:white;
            border-radius:5px;
        }
        .pending-recovery-heading {
            font-size: clamp(1.125rem, 2.5vw, 1.375rem);
            font-weight: var(--pending-recovery-weight-medium);
            color: #2d2d2d;
            margin-bottom: 6px;
            padding:var(--space-dashboard-pending-recovery-1);
        }
        .pending-recovery-campus {
            color: var(--color-dashboard-pending-recovery-3);
            font-size: 0.875rem;
            margin-bottom: var(--space-dashboard-pending-recovery-1);
        }
        .pending-recovery-divider {
            height: 1px;
            background: #d8dee8;
            margin-bottom: 22px;
        }
        .pending-recovery-filter {
            margin-bottom: 34px;
        }
        .pending-recovery-label {
            color: #2d3748;
            font-size: clamp(0.8rem, 1.5vw, 1rem);
            font-weight: var(--pending-recovery-weight-medium);
            margin-bottom: 8px;
        }
        .pending-recovery-filter .form-control {
            height: var(--dimension-dashboard-pending-recovery-2);
            border: 1px solid #d6e0ef;
            border-radius: 6px;
            box-shadow: none;
        }
        .pending-recovery-filter .pending-recovery-month-select {
            height: auto;
            min-height: 170px;
        }
        .page-content .table thead th{
            text-align:center !important;
        }
        .pending-recovery-button {
            min-width: 78px;
            height: var(--dimension-dashboard-pending-recovery-2);
            border-radius: 6px;
            background: var(--color-dashboard-pending-recovery-2);
            border-color: var(--color-dashboard-pending-recovery-2);
            font-weight: var(--pending-recovery-weight-semibold);
        }
        .pending-recovery-button:hover,
        .pending-recovery-button:focus {
            background: var(--color-dashboard-pending-recovery-1);
            border-color: var(--color-dashboard-pending-recovery-1);
        }
        .pending-recovery-table-wrap {
            border-radius: 0;
            overflow-x: auto;
            overflow-y: hidden;
            width: auto;
        }
        .pending-recovery-table {
            margin-bottom: 0;
            background: var(--color-dashboard-pending-recovery-4);
            width: max-content;
            min-width: var(--dimension-dashboard-pending-recovery-1);
        }
        .pending-recovery-table thead th{
            /* background: #1ea7ef !important; */
            color: var(--color-dashboard-pending-recovery-4);
            text-align: center;
            font-size: var(--pending-recovery-font-md);
            font-weight: var(--pending-recovery-weight-bold) !important;
            vertical-align: middle;
            border-color: white !important;
            padding: var(--space-dashboard-pending-recovery-2);
            white-space: nowrap;
            word-break: normal;
            overflow-wrap: normal;
        }
         .pending-recovery-table thead th h4{
            font-size: var(--pending-recovery-font-md);
            font-weight: var(--pending-recovery-weight-bold) !important;
        }
        .pending-recovery-table tbody td{
            text-align: center;
            vertical-align: middle;
            border-color: #d6dbe7;
            padding: var(--space-dashboard-pending-recovery-2);
            font-size: 0.9375rem;
            color: #2f3b52;
            white-space: nowrap;
            word-break: normal;
            overflow-wrap: normal;
        }
        .pending-recovery-table tbody tr:nth-child(odd) td {
            background: #f5f4ff;
        }
        .pending-recovery-campus-code {
            color: #0078c9 !important;
            font-weight: var(--pending-recovery-weight-bold);
        }
        .pending-recovery-campus-link {
            color: inherit;
            text-decoration: none;
        }
        .pending-recovery-campus-link:hover,
        .pending-recovery-campus-link:focus {
            color: #005d9a;
            text-decoration: underline;
        }
        .pending-recovery-total-row td {
            background: var(--color-dashboard-pending-recovery-4)fff !important;
            font-size: var(--pending-recovery-font-xl);
            font-weight: var(--pending-recovery-weight-bold);
            color: #12314c;
        }
        .pending-recovery-total-label {
            text-align: center !important;
            color: #0ea5c6 !important;
        }
        .pending-recovery-total-value {
            text-align: center !important;
        }
        .pending-recovery-empty {
            color: var(--color-dashboard-pending-recovery-3) !important;
            background: var(--color-dashboard-pending-recovery-4) !important;
        }
        @media (max-width: 767px) {
            .pending-recovery-heading {
                font-size: clamp(1.125rem, 2.5vw, 1.375rem);
            }
            .pending-recovery-action-cell {
                /* align-items: stretch !important; */
            }
            .pending-recovery-button {
                width: var(--dimension-dashboard-pending-recovery-1);
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const allPending = document.getElementById('recovery-all-pending');
            const months = document.getElementById('recovery-months');
            const year = document.getElementById('recovery-year');
            allPending.addEventListener('change', function () {
                months.disabled = allPending.checked;
                year.disabled = allPending.checked;
            });
        });
    </script>
@endpush
