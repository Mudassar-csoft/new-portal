@extends('layouts.theme')

@section('title', 'Collection Report')

@section('content')
    @php
        $summary = $summary ?? ['mode' => 'weekly', 'rows' => [], 'columns' => [], 'grand_totals' => []];
        $rows = collect($summary['rows'] ?? []);
        $columns = collect($summary['columns'] ?? []);
        $grandTotals = $summary['grand_totals'] ?? [];
        $monthOptions = $monthOptions ?? [];
        $selectedMonths = collect($selectedMonths ?? [now()->month])->map(fn ($month) => (int) $month)->values()->all();
        $selectedYear = (int) ($selectedYear ?? now()->year);
        $campusLabel = $selectedCampus?->code ?: $selectedCampus?->name;
        $monthLabel = count($selectedMonths) === 1
            ? ($monthOptions[$selectedMonths[0]] ?? now()->format('F'))
            : '(' . collect($selectedMonths)
                ->map(fn ($month) => \Carbon\Carbon::createFromDate(2000, $month, 1)->format('M'))
                ->implode(', ') . ')';
        $titleLabel = trim($monthLabel . ' ' . $selectedYear);
        $headerColspan = $columns->count() + 1;
    @endphp

    <div class="collection-shell">
        <div class="collection-heading">
            Showing Collection {{ $titleLabel }}
        </div>

        @if($campusLabel)
            <div class="collection-campus">{{ $campusLabel }}</div>
        @endif

        <div class="collection-divider"></div>

        <form method="GET" action="{{ route('dashboard.collection') }}" class="collection-filter">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label class="collection-label">Select Month(s):</label>
                    <select name="months[]" class="form-control collection-month-select" multiple size="6">
                        @foreach($monthOptions as $monthNumber => $label)
                            <option value="{{ $monthNumber }}" @selected(in_array((int) $monthNumber, $selectedMonths, true))>{{ $label }}</option>
                        @endforeach
                    </select>
                    <small class="collection-help">Select one month for week-wise view, multiple for month-wise.</small>
                    <div class="mt-3">
                        <a href="{{ route('dashboard.collection') }}" class="btn btn-danger">Clear</a>
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <label class="collection-label">Select Year:</label>
                    <select name="year" class="form-control">
                        @foreach(($yearOptions ?? []) as $yearValue)
                            <option value="{{ $yearValue }}" @selected($selectedYear === (int) $yearValue)>{{ $yearValue }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4 d-flex align-items-start collection-action-cell mt-4 pt-2">
                    <button type="submit" class="btn btn-primary collection-button">Filter</button>
                </div>
            </div>
        </form>

        <div class="collection-table-wrap m-3 mr-3">
            <table class="table table-bordered collection-table">
                <thead>
                    <tr>
                        <th colspan="{{ $headerColspan }}"><h4 class="text-center mt-2">Collection {{ $titleLabel }}</h4></th>
                        <th rowspan="2"><h4 class="text-center mt-2">Total Collection</h4></th>
                    </tr>
                    <tr>
                        <th>Campus Code</th>
                        @foreach($columns as $column)
                            <th>{{ $column['label'] ?? 'Amount' }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="collection-campus-code">
                                @if(!empty($row['campus_id']))
                                    <a
                                        href="{{ route('dashboard.collection.campus', ['campus' => $row['campus_id'], 'months' => $selectedMonths, 'year' => $selectedYear]) }}"
                                        class="collection-campus-link"
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
                            <td>{{ number_format((float) ($row['total_collection'] ?? 0), 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $headerColspan + 1 }}" class="collection-empty">No collection data found.</td>
                        </tr>
                    @endforelse
                    @if($rows->isNotEmpty())
                        <tr class="collection-total-row">
                            <td class="collection-total-label">Grand Total</td>
                            @foreach($columns as $column)
                                <td class="collection-total-value">{{ number_format((float) ($grandTotals[$column['key']] ?? 0), 0) }}</td>
                            @endforeach
                            <td class="collection-total-value">{{ number_format((float) ($grandTotals['total_collection'] ?? 0), 0) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .collection-shell {
            padding: 8px 0 18px;
            background: #fff;
            border-radius: 5px;
        }

        .collection-heading {
            font-size: 22px;
            font-weight: 500;
            color: #2d2d2d;
            margin-bottom: 6px;
            padding: 14px;
        }

        .collection-campus {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 14px;
            padding: 0 14px;
        }

        .collection-divider {
            height: 1px;
            background: #d8dee8;
            margin-bottom: 22px;
        }

        .collection-filter {
            margin-bottom: 34px;
            padding: 0 14px;
        }

        .collection-label {
            color: #2d3748;
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .collection-filter .form-control {
            border: 1px solid #d6e0ef;
            border-radius: 6px;
            box-shadow: none;
        }

        .collection-filter select.form-control:not(.collection-month-select) {
            height: 48px;
        }

        .collection-month-select {
            min-height: 170px;
            padding: 8px 10px;
        }

        .collection-help {
            display: block;
            margin-top: 10px;
            color: #6b7280;
            font-size: 13px;
        }

        .collection-button {
            min-width: 78px;
            height: 48px;
            border-radius: 6px;
            background: #17a8f5;
            border-color: #17a8f5;
            font-weight: 600;
        }

        .collection-button:hover,
        .collection-button:focus {
            background: #0994de;
            border-color: #0994de;
        }

        .collection-table-wrap {
            border-radius: 0;
            overflow: hidden;
            width: auto;
        }

        .collection-table {
            margin-bottom: 0;
            background: #fff;
        }

        .collection-table thead th,
        .collection-table tfoot th {
            background: #1ea7ef !important;
            color: #fff;
            text-align: center;
            font-size: 16px;
            font-weight: 700 !important;
            vertical-align: middle;
            border-color: #fff !important;
            padding: 16px 12px;
        }

        .collection-table thead th h4 {
            font-size: 16px;
            font-weight: 700 !important;
        }

        .collection-table tbody td {
            text-align: center;
            vertical-align: middle;
            border-color: #d6dbe7;
            padding: 16px 12px;
            font-size: 15px;
            color: #2f3b52;
        }

        .collection-table tbody tr:nth-child(odd) td {
            background: #f5f4ff;
        }

        .collection-campus-code {
            color: #0078c9 !important;
            font-weight: 700;
        }

        .collection-campus-link {
            color: inherit;
            text-decoration: none;
        }

        .collection-campus-link:hover,
        .collection-campus-link:focus {
            color: #005d9a;
            text-decoration: underline;
        }

        .collection-total-row td {
            background: #ffffff !important;
            font-size: 20px;
            font-weight: 700;
            color: #12314c;
        }

        .collection-total-label {
            text-align: center !important;
            color: #0ea5c6 !important;
        }

        .collection-total-value {
            text-align: center !important;
        }

        .collection-empty {
            color: #6b7280 !important;
            background: #fff !important;
        }

        @media (max-width: 767px) {
            .collection-heading {
                font-size: 18px;
            }

            .collection-button {
                width: 100%;
            }

            .collection-month-select {
                min-height: 140px;
            }
        }
    </style>
@endpush
