@extends('layouts.theme')

@section('title', 'Campus Pending Recovery Report')

@section('content')
    @php
        $sections = collect($sections ?? []);
        $selectedMonth = (int) ($selectedMonth ?? now()->month);
        $selectedYear = (int) ($selectedYear ?? now()->year);
        $monthOptions = $monthOptions ?? [];
        $campusTitle = trim(implode('-', array_filter([
            $campus->code ?? null,
            $campus->title ?: ($campus->name ?? null),
        ])));
    @endphp

    <div class="campus-recovery-shell">
        <div class="campus-recovery-heading-box">
            <div class="campus-recovery-campus-name">{{ $campusTitle !== '' ? $campusTitle : ($campus->name ?? 'Campus') }}</div>
            <div class="campus-recovery-report-title">
                Campus Monthly Pending Recovery Report - ({{ optional($reportStart)->format('d-M-Y') }}) to ({{ optional($reportEnd)->format('d-M-Y') }})
            </div>
        </div>

        <form method="GET" action="{{ route('dashboard.pending-recovery.campus', $campus) }}" class="campus-recovery-filter">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label class="campus-recovery-label">Select Month:</label>
                    <select name="month" class="form-control">
                        @foreach($monthOptions as $monthNumber => $label)
                            <option value="{{ $monthNumber }}" @selected($selectedMonth === (int) $monthNumber)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label class="campus-recovery-label">Select Year:</label>
                    <select name="year" class="form-control">
                        @foreach(($yearOptions ?? []) as $yearValue)
                            <option value="{{ $yearValue }}" @selected($selectedYear === (int) $yearValue)>{{ $yearValue }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4 d-flex align-items-end justify-content-between flex-wrap campus-recovery-actions">
                    <button type="submit" class="btn btn-primary campus-recovery-button">Filter</button>
                    <a href="{{ route('dashboard.pending-recovery', ['month' => $selectedMonth, 'year' => $selectedYear]) }}" class="btn btn-default campus-recovery-back">Back</a>
                </div>
            </div>
        </form>

        @forelse($sections as $section)
            <div class="campus-recovery-section">
                <div class="campus-recovery-course-title">Course Title : {{ $section['program_title'] ?? 'Program' }}</div>

                <div class="table-responsive">
                    <table class="table table-bordered campus-recovery-table">
                        <thead>
                            <tr>
                                <th>Sr</th>
                                <th>Roll No</th>
                                <th>Name</th>
                                <th>Father Name</th>
                                <th>Admission Date</th>
                                <th>Fee Package</th>
                                <th>Total Received</th>
                                <th>Total Pending</th>
                                <th>This Month Due</th>
                                <th>Installment no.</th>
                                <th>Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(($section['rows'] ?? []) as $row)
                                <tr>
                                    <td>{{ $row['sr'] ?? 0 }}</td>
                                    <td>{{ $row['roll_no'] ?? 'N/A' }}</td>
                                    <td>{{ $row['name'] ?? 'N/A' }}</td>
                                    <td>{{ $row['father_name'] ?? 'N/A' }}</td>
                                    <td>{{ $row['admission_date'] ?? 'N/A' }}</td>
                                    <td>{{ number_format((float) ($row['fee_package'] ?? 0), 0) }}</td>
                                    <td>{{ number_format((float) ($row['total_received'] ?? 0), 0) }}</td>
                                    <td>{{ number_format((float) ($row['total_pending'] ?? 0), 0) }}</td>
                                    <td>{{ number_format((float) ($row['this_month_due'] ?? 0), 0) }}</td>
                                    <td>{{ $row['installment_label'] ?? 'N/A' }}</td>
                                    <td>{{ $row['due_date'] ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                            <tr class="campus-recovery-total-row">
                                <td colspan="10" class="campus-recovery-total-label">Total Amount</td>
                                <td class="campus-recovery-total-value">{{ number_format((float) ($section['section_total'] ?? 0), 0) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="campus-recovery-empty">No pending recovery data found for this campus in the selected month.</div>
        @endforelse
    </div>
@endsection

@push('styles')
    <style>
        .campus-recovery-shell {
            padding: 8px 0 20px;
        }
        .campus-recovery-heading-box {
            border: 1px solid #121212;
            background: #fff;
            padding: 14px 18px 18px;
            text-align: center;
            margin-bottom: 14px;
        }
        .campus-recovery-campus-name {
            font-size: 28px;
            line-height: 1.25;
            font-weight: 500;
            color: #1f2937;
            margin-bottom: 24px;
        }
        .campus-recovery-report-title {
            font-size: 24px;
            line-height: 1.35;
            font-weight: 600;
            color: #1f2937;
        }
        .campus-recovery-filter {
            margin-bottom: 18px;
        }
        .campus-recovery-label {
            color: #1f2937;
            font-size: 15px;
            font-weight: 500;
            margin-bottom: 8px;
        }
        .campus-recovery-filter .form-control {
            height: 46px;
            border: 1px solid #d5dceb;
            border-radius: 4px;
            box-shadow: none;
        }
        .campus-recovery-actions {
            gap: 8px;
        }
        .campus-recovery-button,
        .campus-recovery-back {
            min-width: 86px;
            height: 46px;
            border-radius: 4px;
        }
        .campus-recovery-section {
            margin-bottom: 18px;
        }
        .campus-recovery-course-title {
            border: 1px solid #121212;
            border-bottom: 0;
            background: #fff;
            padding: 8px 10px;
            font-size: 28px;
            line-height: 1.25;
            font-weight: 500;
            color: #1f2937;
        }
        .campus-recovery-table {
            margin-bottom: 0;
            background: #fff;
        }
        .campus-recovery-table th,
        .campus-recovery-table td {
            border: 1px solid #121212 !important;
            padding: 8px 4px;
            color: #1f2937;
            vertical-align: top;
            font-size: 15px;
        }
        .campus-recovery-table th {
            font-weight: 700;
            vertical-align: middle;
        }
        .campus-recovery-total-row td {
            background: #8788db;
            font-size: 20px;
            line-height: 1.2;
            border-color: #121212 !important;
        }
        .campus-recovery-total-label {
            font-weight: 500;
        }
        .campus-recovery-total-value {
            font-weight: 500;
            text-align: right;
        }
        .campus-recovery-empty {
            border: 1px solid #121212;
            background: #fff;
            padding: 18px;
            text-align: center;
            color: #6b7280;
            font-size: 16px;
        }
        @media (max-width: 767px) {
            .campus-recovery-campus-name {
                font-size: 20px;
                margin-bottom: 14px;
            }
            .campus-recovery-report-title,
            .campus-recovery-course-title {
                font-size: 18px;
            }
            .campus-recovery-button,
            .campus-recovery-back {
                width: 100%;
            }
            .campus-recovery-actions {
                display: grid !important;
                width: 100%;
            }
        }
    </style>
@endpush
