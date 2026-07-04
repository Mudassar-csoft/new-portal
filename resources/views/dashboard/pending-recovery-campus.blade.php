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
                    <strong class="campus-recovery-metric-value">{{ number_format($programCount) }}</strong>
                    <span class="campus-recovery-metric-label">Programs</span>
                </div>
                <div class="campus-recovery-metric-card">
                    <strong class="campus-recovery-metric-value">{{ number_format($studentCount) }}</strong>
                    <span class="campus-recovery-metric-label">Students Due</span>
                </div>
                <div class="campus-recovery-metric-card">
                    <strong class="campus-recovery-metric-value">Rs. {{ number_format($totalReceived, 0) }}</strong>
                    <span class="campus-recovery-metric-label">Total Received</span>
                </div>
                <div class="campus-recovery-metric-card">
                    <strong class="campus-recovery-metric-value">Rs. {{ number_format($overallPending, 0) }}</strong>
                    <span class="campus-recovery-metric-label">Overall Pending</span>
                </div>
                <div class="campus-recovery-metric-card campus-recovery-metric-card--highlight">
                    <strong class="campus-recovery-metric-value">Rs. {{ number_format($reportGrandTotal, 0) }}</strong>
                    <span class="campus-recovery-metric-label">This Month Recovery</span>
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
        :root {
            --dimension-dashboard-pending-recovery-campus-1: 14%;
            --dimension-dashboard-pending-recovery-campus-2: 18px;
            --dimension-dashboard-pending-recovery-campus-3: 8%;
            --space-dashboard-pending-recovery-campus-1: 0 !important;
            --space-dashboard-pending-recovery-campus-2: 10mm;
            --space-dashboard-pending-recovery-campus-3: 12px;
            --space-dashboard-pending-recovery-campus-4: 16px;
            --space-dashboard-pending-recovery-campus-5: 16px 18px;
            --space-dashboard-pending-recovery-campus-6: 18px;
            --space-dashboard-pending-recovery-campus-7: 8px;
            --color-dashboard-pending-recovery-campus-1: #00A8FF;
            --color-dashboard-pending-recovery-campus-2: #12263f;
            --color-dashboard-pending-recovery-campus-3: #17324d;
            --color-dashboard-pending-recovery-campus-4: #fff;
            --color-dashboard-pending-recovery-campus-5: #ffffff;
            --color-dashboard-pending-recovery-campus-6: rgba(138, 45, 29, 0.06);
        }

        :root {
            --dimension-dashboard-pending-recovery-campus-1: 14%;
            --dimension-dashboard-pending-recovery-campus-2: 18px;
            --dimension-dashboard-pending-recovery-campus-3: 8%;
            --space-dashboard-pending-recovery-campus-1: 0 !important;
            --space-dashboard-pending-recovery-campus-2: 10mm;
            --space-dashboard-pending-recovery-campus-3: 12px;
            --space-dashboard-pending-recovery-campus-4: 16px;
            --space-dashboard-pending-recovery-campus-5: 16px 18px;
            --space-dashboard-pending-recovery-campus-6: 18px;
            --space-dashboard-pending-recovery-campus-7: 8px;
            --campus-report-font-xxs: 11px;
            --campus-report-font-xs: 12px;
            --campus-report-font-sm: 13px;
            --campus-report-font-lg: 18px;
            --campus-report-font-xl: 20px;
            --campus-report-line-tight: 1.2;
            --campus-report-line-base: 1.3;
            --campus-report-weight-bold: 700;
            --campus-report-weight-heavy: 800;
        }0___

        @page {
            size: A4 landscape;
            margin: var(--space-dashboard-pending-recovery-campus-2);
        }

        body.pending-recovery-report-page .site-header,
        body.pending-recovery-report-page .side-menu,
        body.pending-recovery-report-page .control-panel-container {
            display: none !important;
        }

        body.pending-recovery-report-page.with-side-menu .page-content,
        body.pending-recovery-report-page .page-content {
            padding: var(--space-dashboard-pending-recovery-campus-1);
            margin: var(--space-dashboard-pending-recovery-campus-1);
            background: linear-gradient(180deg, var(--color-dashboard-pending-recovery-campus-4)8f1 0%, var(--color-dashboard-pending-recovery-campus-4)fff 100%);
        }

        body.pending-recovery-report-page.with-side-menu .page-content > .container-fluid,
        body.pending-recovery-report-page .page-content > .container-fluid {
            padding: var(--space-dashboard-pending-recovery-campus-1);
        }

        .campus-recovery-report {
            /* max-width: 1280px; */
            margin: 0 auto;
            padding: 24px 18px 30px;
            color: var(--color-dashboard-pending-recovery-campus-2);
            font-family: "Segoe UI", "Helvetica Neue", sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            background: white;
        }

        .campus-recovery-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--space-dashboard-pending-recovery-campus-3);
            margin-bottom: var(--space-dashboard-pending-recovery-campus-4);
        }

        .campus-recovery-toolbar-link,
        .campus-recovery-toolbar-button {
            border: 0;
            border-radius: 999px;
            padding: 10px 16px;
            font-size: var(--campus-report-font-sm);
            font-weight: var(--campus-report-weight-bold);
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .campus-recovery-toolbar-link {
            background: var(--color-dashboard-pending-recovery-campus-4);
            color: var(--color-dashboard-pending-recovery-campus-1);
            /* box-shadow: 0 10px 30px rgba(138, 45, 29, 0.12); */
        }

        .campus-recovery-toolbar-button {
            background: var(--color-dashboard-pending-recovery-campus-1);
            color: var(--color-dashboard-pending-recovery-campus-4);
            /* box-shadow: 0 14px 36px rgba(234, 88, 12, 0.28); */
            cursor: pointer;
        }

        .campus-recovery-toolbar-link:hover,
        .campus-recovery-toolbar-button:hover {
            transform: translateY(-1px);
            text-decoration: none;
        }

        .campus-recovery-hero {
            border: 1px solid var(--color-dashboard-pending-recovery-campus-1);
            border-top: 6px solid var(--color-dashboard-pending-recovery-campus-1);
            background: white;
            border-radius: 24px;
            padding: 24px 28px;
            /* box-shadow: 0 22px 50px rgba(138, 45, 29, 0.10); */
            margin-bottom: var(--space-dashboard-pending-recovery-campus-6);
        }

        .campus-recovery-hero-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: var(--space-dashboard-pending-recovery-campus-6);
            margin-bottom: var(--space-dashboard-pending-recovery-campus-6);
        }

        .campus-recovery-brand {
            display: flex;
            align-items: flex-start;
            gap: var(--space-dashboard-pending-recovery-campus-4);
        }

        .campus-recovery-brand-mark {
            width: var(--dimension-dashboard-pending-recovery-campus-2);
            min-width: var(--dimension-dashboard-pending-recovery-campus-2);
            height: 72px;
            border-radius: 999px;
            background: linear-gradient(180deg, #3ccffb 0%, #0c90c2 100%);
        }

        .campus-recovery-brand-eyebrow {
            font-size: var(--campus-report-font-xs);
            font-weight: var(--campus-report-weight-bold);
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: gray;
            margin-bottom: var(--space-dashboard-pending-recovery-campus-7);
        }

        .campus-recovery-campus-name {
            font-size: 32px;
            line-height: 1.15;
            font-weight: var(--campus-report-weight-bold);
            color: var(--color-dashboard-pending-recovery-campus-2);
            margin-bottom: var(--space-dashboard-pending-recovery-campus-7);
        }

        .campus-recovery-report-title {
            font-size: var(--campus-report-font-xl);
            line-height: var(--campus-report-line-base);
            font-weight: 600;
            color: gray;
        }

        .campus-recovery-period-card {
            min-width: 240px;
            padding: var(--space-dashboard-pending-recovery-campus-5);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid var(--color-dashboard-pending-recovery-campus-1);
        }

        .campus-recovery-period-card span,
        .campus-recovery-metric-label,
        .campus-recovery-section-label,
        .campus-recovery-section-total span,
        .campus-recovery-summary-card span {
            display: block;
            font-size: var(--campus-report-font-xxs);
            font-weight: var(--campus-report-weight-bold);
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: gray;
            margin-bottom: 6px;
        }

        .campus-recovery-period-card strong,
        .campus-recovery-metric-value,
        .campus-recovery-section-total strong,
        .campus-recovery-summary-card strong {
            display: block;
            font-size: var(--campus-report-font-lg);
            line-height: var(--campus-report-line-tight);
            color: var(--color-dashboard-pending-recovery-campus-2);
        }

        .campus-recovery-metrics {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: var(--space-dashboard-pending-recovery-campus-3);
        }

        .campus-recovery-metric-card,
        .campus-recovery-summary-card {
            border-radius: 18px;
            border: 1px solid var(--color-dashboard-pending-recovery-campus-1);
            background: rgba(255, 255, 255, 0.95);
            padding: 14px 16px;
            text-align:center;
            /* box-shadow: 0 12px 30px var(--color-dashboard-pending-recovery-campus-6); */
        }

        .campus-recovery-metric-card--highlight,
        .campus-recovery-summary-card--accent {
            background: var(--color-dashboard-pending-recovery-campus-1);
            border-color: var(--color-dashboard-pending-recovery-campus-1);
        }

        .campus-recovery-metric-card--highlight .campus-recovery-metric-label,
        .campus-recovery-summary-card--accent span,
        .campus-recovery-metric-card--highlight .campus-recovery-metric-value,
        .campus-recovery-summary-card--accent strong {
            color: var(--color-dashboard-pending-recovery-campus-4);
        }

        .campus-recovery-section {
            margin-bottom: var(--space-dashboard-pending-recovery-campus-6);
            border: 1px solid var(--color-dashboard-pending-recovery-campus-1);
            border-radius: 22px;
            background: var(--color-dashboard-pending-recovery-campus-4);
            /* box-shadow: 0 18px 44px rgba(138, 45, 29, 0.08); */
            overflow: hidden;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .campus-recovery-section-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: var(--space-dashboard-pending-recovery-campus-4);
            padding: 10px 20px 14px;
            border-bottom: 1px solid #f6e6db;
            /* background: linear-gradient(180deg, var(--color-dashboard-pending-recovery-campus-4)dfa 0%, var(--color-dashboard-pending-recovery-campus-4)5ed 100%); */
        }

        .campus-recovery-course-title {
            font-size: 24px;
            line-height: var(--campus-report-line-tight);
            font-weight: var(--campus-report-weight-bold);
            color: var(--color-dashboard-pending-recovery-campus-3);
        }

        .campus-recovery-section-total {
            min-width: 200px;
            text-align: right;
        }

        .campus-recovery-table-wrap {
            overflow-x: auto;
            overflow-y: hidden;
        }

        .campus-recovery-table {
            margin-bottom: 0;
            background: var(--color-dashboard-pending-recovery-campus-4);
            width: max-content;
            min-width: 100%;
            table-layout: auto;
            border-collapse: collapse;
        }

        .campus-recovery-table .col-sr { width: 3%; }
        .campus-recovery-table .col-roll { width: var(--dimension-dashboard-pending-recovery-campus-1); }
        .campus-recovery-table .col-name { width: 10%; }
        .campus-recovery-table .col-father { width: var(--dimension-dashboard-pending-recovery-campus-1); }
        .campus-recovery-table .col-date { width: var(--dimension-dashboard-pending-recovery-campus-3); }
        .campus-recovery-table .col-money { width: 9%; }
        .campus-recovery-table .col-installment { width: var(--dimension-dashboard-pending-recovery-campus-3); }

        .campus-recovery-table thead th {
            background: var(--color-dashboard-pending-recovery-campus-4)3e8;
            color: #6f3f2d;
            font-size: var(--campus-report-font-xxs);
            font-weight: var(--campus-report-weight-heavy);
            letter-spacing: 0.05em;
            text-transform: uppercase;
            vertical-align: middle;
        }

        .campus-recovery-table th,
        .campus-recovery-table td {
            border: 1px solid var(--color-dashboard-pending-recovery-campus-1) !important;
            padding: 9px 8px;
            color: var(--color-dashboard-pending-recovery-campus-3);
            vertical-align: top;
            font-size: var(--campus-report-font-xs);
            line-height: 1.35;
            white-space: nowrap;
            word-break: normal;
            overflow-wrap: normal;
        }

        .campus-recovery-table tbody tr:nth-child(even) td {
            background: var(--color-dashboard-pending-recovery-campus-4)fff;
        }

        .numeric-cell,
        .campus-recovery-total-value {
            text-align: right;
            white-space: nowrap;
        }

        .campus-recovery-total-row td {
            background: var(--color-dashboard-pending-recovery-campus-4);
            font-size: var(--campus-report-font-sm);
            line-height: var(--campus-report-line-base);
            border-top: 2px solid #00a8ff !important;
        }

        .campus-recovery-total-label {
            font-weight: var(--campus-report-weight-heavy);
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .campus-recovery-total-value {
            font-weight: var(--campus-report-weight-heavy);
            color: #8a2d1d;
        }

        .campus-recovery-empty {
            border: 1px dashed #e4c6b3;
            border-radius: 20px;
            background: var(--color-dashboard-pending-recovery-campus-4);
            padding: 28px;
            text-align: center;
            color: #6b7280;
            font-size: 16px;
            /* box-shadow: 0 18px 44px var(--color-dashboard-pending-recovery-campus-6); */
        }

        .campus-recovery-summary {
            margin-top: 24px;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .campus-recovery-summary-title {
            font-size: 22px;
            line-height: var(--campus-report-line-tight);
            font-weight: var(--campus-report-weight-bold);
            color: var(--color-dashboard-pending-recovery-campus-3);
            margin-bottom: var(--space-dashboard-pending-recovery-campus-3);
        }

        .campus-recovery-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: var(--space-dashboard-pending-recovery-campus-3);
        }

        .campus-recovery-summary-card strong {
            font-size: var(--campus-report-font-lg);
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
                background: var(--color-dashboard-pending-recovery-campus-4) !important;
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
                margin-bottom: var(--space-dashboard-pending-recovery-campus-3);
                padding: var(--space-dashboard-pending-recovery-campus-5);
            }

            .campus-recovery-section {
                border-radius: 0;
                margin-bottom: var(--space-dashboard-pending-recovery-campus-3);
            }

            .campus-recovery-metrics {
                gap: var(--space-dashboard-pending-recovery-campus-7);
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
                font-size: var(--campus-report-font-xl);
            }

            .campus-recovery-report-title {
                font-size: 17px;
            }

            .campus-recovery-course-title {
                font-size: var(--campus-report-font-lg);
            }

            .campus-recovery-table th,
            .campus-recovery-table td {
                font-size: 10.5px;
                padding: 6px 5px;
            }

            .campus-recovery-total-row td,
            .campus-recovery-summary-card strong,
            .campus-recovery-section-total strong {
                font-size: var(--campus-report-font-xs);
            }
        }

        @media (max-width: 767px) {
            .campus-recovery-report {
                padding: 16px 10px 24px;
            }

            .campus-recovery-toolbar,
            .campus-recovery-hero-top,
            .campus-recovery-section-head {
                /* flex-direction: column;
                align-items: stretch; */
            }

            .campus-recovery-brand {
                gap: var(--space-dashboard-pending-recovery-campus-3);
            }

            .campus-recovery-campus-name {
                font-size: var(--campus-report-font-xl);
            }

            .campus-recovery-report-title {
                font-size: var(--campus-report-font-lg);
            }

            .campus-recovery-metrics,
            .campus-recovery-summary-grid {
                /* grid-template-columns: 1fr; */
            }

            .campus-recovery-period-card,
            .campus-recovery-section-total {
                min-width: 0;
                text-align: left;
            }
        }
    </style>
    <style>
        :root {
            --space-dashboard-pending-recovery-campus-1: 0 !important;
            --space-dashboard-pending-recovery-campus-2: 10mm;
            --space-dashboard-pending-recovery-campus-3: 12px;
            --space-dashboard-pending-recovery-campus-4: 16px;
            --space-dashboard-pending-recovery-campus-5: 16px 18px;
            --space-dashboard-pending-recovery-campus-6: 18px;
            --space-dashboard-pending-recovery-campus-7: 8px;
            --color-dashboard-pending-recovery-campus-1: #00A8FF;
            --color-dashboard-pending-recovery-campus-2: #12263f;
            --color-dashboard-pending-recovery-campus-3: #17324d;
            --color-dashboard-pending-recovery-campus-4: #fff;
            --color-dashboard-pending-recovery-campus-5: #ffffff;
            --color-dashboard-pending-recovery-campus-6: rgba(138, 45, 29, 0.06);
        }

@media print {
    @page {
        size: A4 portrait;
        margin: var(--space-dashboard-pending-recovery-campus-2);
    }
}
</style>
@endpush
@push('scripts')

<script>
window.addEventListener('load', function () {
    setTimeout(function () {
        window.print();
    }, 180);
});
</script>
@endpush
