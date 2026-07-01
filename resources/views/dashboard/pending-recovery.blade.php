@extends('layouts.theme')

@section('title', 'Pending Recovery')

@section('content')
    @php
        $rows = collect($rows ?? []);
        $monthOptions = $monthOptions ?? [];
        $selectedMonth = (int) ($selectedMonth ?? now()->month);
        $selectedYear = (int) ($selectedYear ?? now()->year);
        $monthLabel = $monthOptions[$selectedMonth] ?? now()->format('F');
        $campusLabel = $selectedCampus?->code ?: $selectedCampus?->name;
        $monthGrandTotal = (float) $rows->sum('month_total');
        $overallGrandTotal = (float) $rows->sum('overall_total');
    @endphp

    <div class="pending-recovery-shell">
        <div class="pending-recovery-heading">
            Showing Pending Recovery {{ $monthLabel }} {{ $selectedYear }}
        </div>

        @if($campusLabel)
            <div class="pending-recovery-campus">{{ $campusLabel }}</div>
        @endif

        <div class="pending-recovery-divider"></div>

        <form method="GET" action="{{ route('dashboard.pending-recovery') }}" class="pending-recovery-filter">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label class="pending-recovery-label">Select Month:</label>
                    <select name="month" class="form-control">
                        @foreach($monthOptions as $monthNumber => $label)
                            <option value="{{ $monthNumber }}" @selected($selectedMonth === (int) $monthNumber)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label class="pending-recovery-label">Select Year:</label>
                    <select name="year" class="form-control">
                        @foreach(($yearOptions ?? []) as $yearValue)
                            <option value="{{ $yearValue }}" @selected($selectedYear === (int) $yearValue)>{{ $yearValue }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4 d-flex align-items-end pending-recovery-action-cell mt-4 pt-2">
                    <button type="submit" class="btn btn-primary pending-recovery-button ">Filter</button>
                </div>
            </div>
        </form>

        <div class=" pending-recovery-table-wrap m-3 mr-3">
            <table class="table table-bordered pending-recovery-table">
                <thead>
                    <tr>
                        <th class=" pending-border" colspan="6"><h4 class="text-center mt-2"> Pending Recovery {{ $monthLabel }} {{ $selectedYear }}  </h4></th>
                        <th rowspan="2"><h4 class="text-center mt-2">Overall Pending</h4></th>
                    </tr>
                    <tr>
                        <th>Campus Code</th>
                        <th>1st Week</th>
                        <th>2nd Week</th>
                        <th>3rd Week</th>
                        <th>4th Week</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="pending-recovery-campus-code">
                                @if(!empty($row['campus_id']))
                                    <a
                                        href="{{ route('dashboard.pending-recovery.campus', ['campus' => $row['campus_id'], 'month' => $selectedMonth, 'year' => $selectedYear]) }}"
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
                            <td>{{ number_format((float) ($row['week_1'] ?? 0), 0) }}</td>
                            <td>{{ number_format((float) ($row['week_2'] ?? 0), 0) }}</td>
                            <td>{{ number_format((float) ($row['week_3'] ?? 0), 0) }}</td>
                            <td>{{ number_format((float) ($row['week_4'] ?? 0), 0) }}</td>
                            <td>{{ number_format((float) ($row['month_total'] ?? 0), 0) }}</td>
                            <td>{{ number_format((float) ($row['overall_total'] ?? 0), 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="pending-recovery-empty">No pending recovery data found.</td>
                        </tr>
                    @endforelse
                    @if($rows->isNotEmpty())
                        <tr class="pending-recovery-total-row">
                            <td colspan="5" class="pending-recovery-total-label">Total</td>
                            <td class="pending-recovery-total-value">{{ number_format($monthGrandTotal, 0) }}</td>
                            <td class="pending-recovery-total-value">{{ number_format($overallGrandTotal, 0) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .pending-border{
            border-bottom:1px solid gray;
        }
        .pending-recovery-shell {
            padding: 8px 0 18px;
            background:white;
            border-radius:5px;
        }
        .pending-recovery-heading {
            font-size: 22px;
            font-weight: 500;
            color: #2d2d2d;
            margin-bottom: 6px;
            padding:14px;
        }
        .pending-recovery-campus {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 14px;
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
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 8px;
        }
        .pending-recovery-filter .form-control {
            height: 48px;
            border: 1px solid #d6e0ef;
            border-radius: 6px;
            box-shadow: none;
        }
        .page-content .table thead th{
            text-align:center !important;
        }
        .pending-recovery-button {
            min-width: 78px;
            height: 48px;
            border-radius: 6px;
            background: #17a8f5;
            border-color: #17a8f5;
            font-weight: 600;
        }
        .pending-recovery-button:hover,
        .pending-recovery-button:focus {
            background: #0994de;
            border-color: #0994de;
        }
        .pending-recovery-table-wrap {
            border-radius: 0;
            overflow-x: auto;
            overflow-y: hidden;
            width: auto;
        }
        .pending-recovery-table {
            margin-bottom: 0;
            background: #fff;
            width: max-content;
            min-width: 100%;
        }
        .pending-recovery-table thead th{
            /* background: #1ea7ef !important; */
            color: #fff;
            text-align: center;
            font-size: 16px;
            font-weight: 700 !important;
            vertical-align: middle;
            border-color: white !important;
            padding: 16px 12px;
            white-space: nowrap;
            word-break: normal;
            overflow-wrap: normal;
        }
         .pending-recovery-table thead th h4{
           
            font-size: 16px;
            font-weight: 700 !important;
          
        }
        .pending-recovery-table tbody td{
            text-align: center;
            vertical-align: middle;
            border-color: #d6dbe7;
            padding: 16px 12px;
            font-size: 15px;
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
            font-weight: 700;
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
            background: #ffffff !important;
            font-size: 20px;
            font-weight: 700;
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
            color: #6b7280 !important;
            background: #fff !important;
        }
        @media (max-width: 767px) {
            .pending-recovery-heading {
                font-size: 18px;
            }
            .pending-recovery-action-cell {
                /* align-items: stretch !important; */
            }
            .pending-recovery-button {
                width: 100%;
            }
        }
    </style>
@endpush
