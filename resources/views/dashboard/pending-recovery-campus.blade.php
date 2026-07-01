@extends('layouts.theme')

@section('title', 'Campus Pending Recovery Report')
@section('body_class', 'pending-recovery-report-page')

@section('content')
    @php
        $sections = collect($sections ?? []);
        $selectedMonth = (int) ($selectedMonth ?? now()->month);
        $selectedYear = (int) ($selectedYear ?? now()->year);
        $reportGrandTotal = (float) $sections->sum('section_total');
        $programCount = (int) $sections->count();
        $studentCount = (int) $sections->sum(fn ($section) => count($section['rows'] ?? []));
        $totalReceived = (float) $sections->sum(fn ($section) => collect($section['rows'] ?? [])->sum('total_received'));
        $overallPending = (float) $sections->sum(fn ($section) => collect($section['rows'] ?? [])->sum('total_pending'));
        $campusTitle = trim(implode('-', array_filter([
            $campus->code ?? null,
            $campus->title ?: ($campus->name ?? null),
        ])));
        $periodLabel = trim(implode(' to ', array_filter([
            optional($reportStart)->format('d-M-Y'),
            optional($reportEnd)->format('d-M-Y'),
        ])));
    @endphp

    <div class="campus-recovery-report">
        <div class="campus-recovery-toolbar no-print">
            <a href="{{ route('dashboard.pending-recovery', ['month' => $selectedMonth, 'year' => $selectedYear]) }}" class="campus-recovery-toolbar-link">Back</a>
            <button type="button" class="campus-recovery-toolbar-button" onclick="window.print()">Print Report</button>
        </div>

        <section class="campus-recovery-hero">
            <div class="campus-recovery-hero-top">
                <div class="campus-recovery-brand">
                    <span class="campus-recovery-brand-mark"></span>
                    <div>
                        <div class="campus-recovery-brand-eyebrow">Career Institute</div>
                        <div class="campus-recovery-campus-name">{{ $campusTitle !== '' ? $campusTitle : ($campus->name ?? 'Campus') }}</div>
                        <div class="campus-recovery-report-title">Campus Monthly Pending Recovery Report</div>
                    </div>
                </div>
                <div class="campus-recovery-period-card">
                    <span>Reporting Period</span>
                    <strong>{{ $periodLabel }}</strong>
                </div>
            </div>

            <div class="campus-recovery-metrics">
                <div class="campus-recovery-metric-card">
                    <span class="campus-recovery-metric-label">Programs</span>
                    <strong class="campus-recovery-metric-value">{{ number_format($programCount) }}</strong>
                </div>
                <div class="campus-recovery-metric-card">
                    <span class="campus-recovery-metric-label">Students Due</span>
                    <strong class="campus-recovery-metric-value">{{ number_format($studentCount) }}</strong>
                </div>
                <div class="campus-recovery-metric-card">
                    <span class="campus-recovery-metric-label">Total Received</span>
                    <strong class="campus-recovery-metric-value">Rs. {{ number_format($totalReceived, 0) }}</strong>
                </div>
                <div class="campus-recovery-metric-card">
                    <span class="campus-recovery-metric-label">Overall Pending</span>
                    <strong class="campus-recovery-metric-value">Rs. {{ number_format($overallPending, 0) }}</strong>
                </div>
                <div class="campus-recovery-metric-card campus-recovery-metric-card--highlight">
                    <span class="campus-recovery-metric-label">This Month Recovery</span>
                    <strong class="campus-recovery-metric-value">Rs. {{ number_format($reportGrandTotal, 0) }}</strong>
                </div>
            </div>
        </section>

        @forelse($sections as $section)
            <div class="campus-recovery-section">
                <div class="campus-recovery-section-head">
                    <div>
                        <div class="campus-recovery-section-label">Course Title</div>
                        <div class="campus-recovery-course-title">{{ $section['program_title'] ?? 'Program' }}</div>
                    </div>
                    <div class="campus-recovery-section-total">
                        <span>This Month Due</span>
                        <strong>Rs. {{ number_format((float) ($section['section_total'] ?? 0), 0) }}</strong>
                    </div>
                </div>

                <div class="campus-recovery-table-wrap">
                    <table class="table table-bordered campus-recovery-table">
                        <colgroup>
                            <col class="col-sr">
                            <col class="col-roll">
                            <col class="col-name">
                            <col class="col-father">
                            <col class="col-date">
                            <col class="col-money">
                            <col class="col-money">
                            <col class="col-money">
                            <col class="col-money">
                            <col class="col-installment">
                            <col class="col-date">
                        </colgroup>
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
                                <th>Installment</th>
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
                                    <td class="numeric-cell">{{ number_format((float) ($row['fee_package'] ?? 0), 0) }}</td>
                                    <td class="numeric-cell">{{ number_format((float) ($row['total_received'] ?? 0), 0) }}</td>
                                    <td class="numeric-cell">{{ number_format((float) ($row['total_pending'] ?? 0), 0) }}</td>
                                    <td class="numeric-cell">{{ number_format((float) ($row['this_month_due'] ?? 0), 0) }}</td>
                                    <td>{{ $row['installment_label'] ?? 'N/A' }}</td>
                                    <td>{{ $row['due_date'] ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                            <tr class="campus-recovery-total-row">
                                <td colspan="10" class="campus-recovery-total-label">Total Amount</td>
                                <td class="campus-recovery-total-value">Rs. {{ number_format((float) ($section['section_total'] ?? 0), 0) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="campus-recovery-empty">No pending recovery data found for this campus in the selected month.</div>
        @endforelse

        @if($sections->isNotEmpty())
            <div class="campus-recovery-summary">
                <div class="campus-recovery-summary-title">Report Summary</div>
                <div class="campus-recovery-summary-grid">
                    <div class="campus-recovery-summary-card">
                        <span>Total Programs</span>
                        <strong>{{ number_format($programCount) }}</strong>
                    </div>
                    <div class="campus-recovery-summary-card">
                        <span>Total Students</span>
                        <strong>{{ number_format($studentCount) }}</strong>
                    </div>
                    <div class="campus-recovery-summary-card">
                        <span>Overall Pending</span>
                        <strong>Rs. {{ number_format($overallPending, 0) }}</strong>
                    </div>
                    <div class="campus-recovery-summary-card campus-recovery-summary-card--accent">
                        <span>Grand Total</span>
                        <strong>Rs. {{ number_format($reportGrandTotal, 0) }}</strong>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body.pending-recovery-report-page .site-header,
        body.pending-recovery-report-page .side-menu,
        body.pending-recovery-report-page .control-panel-container {
            display: none !important;
        }

        body.pending-recovery-report-page.with-side-menu .page-content,
        body.pending-recovery-report-page .page-content {
            padding: 0 !important;
            margin: 0 !important;
            background: linear-gradient(180deg, #eef6ff 0%, #f7fbff 100%);
        }

        body.pending-recovery-report-page.with-side-menu .page-content > .container-fluid,
        body.pending-recovery-report-page .page-content > .container-fluid {
            padding: 0 !important;
        }

        .campus-recovery-report {
            max-width: 1280px;
            margin: 0 auto;
            padding: 24px 18px 30px;
            color: #12263f;
            font-family: "Segoe UI", "Helvetica Neue", sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .campus-recovery-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
        }

        .campus-recovery-toolbar-link,
        .campus-recovery-toolbar-button {
            border: 0;
            border-radius: 999px;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .campus-recovery-toolbar-link {
            background: #fff;
            color: #0f4c81;
            box-shadow: 0 10px 30px rgba(15, 76, 129, 0.12);
        }

        .campus-recovery-toolbar-button {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: #fff;
            box-shadow: 0 14px 36px rgba(2, 132, 199, 0.28);
            cursor: pointer;
        }

        .campus-recovery-toolbar-link:hover,
        .campus-recovery-toolbar-button:hover {
            transform: translateY(-1px);
            text-decoration: none;
        }

        .campus-recovery-hero {
            border: 1px solid #d7e3f2;
            border-top: 6px solid #0ea5e9;
            background: linear-gradient(140deg, #ffffff 0%, #eef7ff 100%);
            border-radius: 24px;
            padding: 24px 28px;
            box-shadow: 0 22px 50px rgba(15, 76, 129, 0.10);
            margin-bottom: 18px;
        }

        .campus-recovery-hero-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
            margin-bottom: 18px;
        }

        .campus-recovery-brand {
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .campus-recovery-brand-mark {
            width: 18px;
            min-width: 18px;
            height: 72px;
            border-radius: 999px;
            background: linear-gradient(180deg, #0ea5e9 0%, #0369a1 100%);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.35);
        }

        .campus-recovery-brand-eyebrow {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #0284c7;
            margin-bottom: 8px;
        }

        .campus-recovery-campus-name {
            font-size: 32px;
            line-height: 1.15;
            font-weight: 700;
            color: #12263f;
            margin-bottom: 8px;
        }

        .campus-recovery-report-title {
            font-size: 20px;
            line-height: 1.3;
            font-weight: 600;
            color: #35516d;
        }

        .campus-recovery-period-card {
            min-width: 240px;
            padding: 16px 18px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid #dce7f4;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.45);
        }

        .campus-recovery-period-card span,
        .campus-recovery-metric-label,
        .campus-recovery-section-label,
        .campus-recovery-section-total span,
        .campus-recovery-summary-card span {
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #64819c;
            margin-bottom: 6px;
        }

        .campus-recovery-period-card strong,
        .campus-recovery-metric-value,
        .campus-recovery-section-total strong,
        .campus-recovery-summary-card strong {
            display: block;
            font-size: 20px;
            line-height: 1.2;
            color: #12263f;
        }

        .campus-recovery-metrics {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
        }

        .campus-recovery-metric-card,
        .campus-recovery-summary-card {
            border-radius: 18px;
            border: 1px solid #dce7f4;
            background: rgba(255, 255, 255, 0.95);
            padding: 14px 16px;
            box-shadow: 0 12px 30px rgba(15, 76, 129, 0.06);
        }

        .campus-recovery-metric-card--highlight,
        .campus-recovery-summary-card--accent {
            background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%);
            border-color: #0369a1;
        }

        .campus-recovery-metric-card--highlight .campus-recovery-metric-label,
        .campus-recovery-summary-card--accent span,
        .campus-recovery-metric-card--highlight .campus-recovery-metric-value,
        .campus-recovery-summary-card--accent strong {
            color: #fff;
        }

        .campus-recovery-section {
            margin-bottom: 18px;
            border: 1px solid #d7e3f2;
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 18px 44px rgba(15, 76, 129, 0.08);
            overflow: hidden;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .campus-recovery-section-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 16px;
            padding: 16px 20px 14px;
            border-bottom: 1px solid #e4edf7;
            background: linear-gradient(180deg, #fcfdff 0%, #f5f9ff 100%);
        }

        .campus-recovery-course-title {
            font-size: 24px;
            line-height: 1.2;
            font-weight: 700;
            color: #17324d;
        }

        .campus-recovery-section-total {
            min-width: 200px;
            text-align: right;
        }

        .campus-recovery-table-wrap {
            overflow: hidden;
        }

        .campus-recovery-table {
            margin-bottom: 0;
            background: #fff;
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .campus-recovery-table .col-sr { width: 3%; }
        .campus-recovery-table .col-roll { width: 14%; }
        .campus-recovery-table .col-name { width: 10%; }
        .campus-recovery-table .col-father { width: 14%; }
        .campus-recovery-table .col-date { width: 8%; }
        .campus-recovery-table .col-money { width: 9%; }
        .campus-recovery-table .col-installment { width: 8%; }

        .campus-recovery-table thead th {
            background: #eef6ff;
            color: #28435d;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            vertical-align: middle;
        }

        .campus-recovery-table th,
        .campus-recovery-table td {
            border: 1px solid #d9e3ee !important;
            padding: 9px 8px;
            color: #17324d;
            vertical-align: top;
            font-size: 12px;
            line-height: 1.35;
            word-break: break-word;
        }

        .campus-recovery-table tbody tr:nth-child(even) td {
            background: #fbfdff;
        }

        .numeric-cell,
        .campus-recovery-total-value {
            text-align: right;
            white-space: nowrap;
        }

        .campus-recovery-total-row td {
            background: #edf7ff;
            font-size: 13px;
            line-height: 1.3;
            border-top: 2px solid #0ea5e9 !important;
        }

        .campus-recovery-total-label {
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .campus-recovery-total-value {
            font-weight: 800;
            color: #0f4c81;
        }

        .campus-recovery-empty {
            border: 1px dashed #c7d7e9;
            border-radius: 20px;
            background: #fff;
            padding: 28px;
            text-align: center;
            color: #6b7280;
            font-size: 16px;
            box-shadow: 0 18px 44px rgba(15, 76, 129, 0.06);
        }

        .campus-recovery-summary {
            margin-top: 24px;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .campus-recovery-summary-title {
            font-size: 22px;
            line-height: 1.2;
            font-weight: 700;
            color: #17324d;
            margin-bottom: 12px;
        }

        .campus-recovery-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .campus-recovery-summary-card strong {
            font-size: 22px;
        }

        .no-print {
            display: flex;
        }

        @media print {
            body.pending-recovery-report-page,
            body.pending-recovery-report-page * {
                visibility: visible !important;
            }

            body.pending-recovery-report-page.with-side-menu .page-content,
            body.pending-recovery-report-page .page-content {
                background: #fff !important;
            }

            .no-print {
                display: none !important;
            }

            .campus-recovery-report {
                max-width: none;
                padding: 0;
            }

            .campus-recovery-hero,
            .campus-recovery-section,
            .campus-recovery-metric-card,
            .campus-recovery-summary-card {
                box-shadow: none !important;
            }

            .campus-recovery-hero {
                border-radius: 0;
                margin-bottom: 12px;
                padding: 16px 18px;
            }

            .campus-recovery-section {
                border-radius: 0;
                margin-bottom: 12px;
            }

            .campus-recovery-metrics {
                gap: 8px;
            }

            .campus-recovery-metric-card,
            .campus-recovery-summary-card {
                border-radius: 0;
                padding: 10px 12px;
            }

            .campus-recovery-brand-mark {
                height: 56px;
            }

            .campus-recovery-campus-name {
                font-size: 24px;
            }

            .campus-recovery-report-title {
                font-size: 17px;
            }

            .campus-recovery-course-title {
                font-size: 18px;
            }

            .campus-recovery-table th,
            .campus-recovery-table td {
                font-size: 10.5px;
                padding: 6px 5px;
            }

            .campus-recovery-total-row td,
            .campus-recovery-summary-card strong,
            .campus-recovery-section-total strong {
                font-size: 12px;
            }
        }

        @media (max-width: 767px) {
            .campus-recovery-report {
                padding: 16px 10px 24px;
            }

            .campus-recovery-toolbar,
            .campus-recovery-hero-top,
            .campus-recovery-section-head {
                flex-direction: column;
                align-items: stretch;
            }

            .campus-recovery-brand {
                gap: 12px;
            }

            .campus-recovery-campus-name {
                font-size: 24px;
            }

            .campus-recovery-report-title {
                font-size: 18px;
            }

            .campus-recovery-metrics,
            .campus-recovery-summary-grid {
                grid-template-columns: 1fr;
            }

            .campus-recovery-period-card,
            .campus-recovery-section-total {
                min-width: 0;
                text-align: left;
            }
        }
    </style>
@endpush
@push('scripts')
<script>
    window.addEventListener('load', function () {
        window.setTimeout(function () {
            window.print();
        }, 180);
    });
</script>
@endpush
