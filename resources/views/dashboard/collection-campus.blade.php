@extends('layouts.theme')

@section('title', 'Campus Collection Report')
@section('body_class', 'collection-report-page')

@section('content')
    @php
        $sections = collect($sections ?? []);
        $monthOptions = $monthOptions ?? [];
        $selectedMonths = collect($selectedMonths ?? [now()->month])->map(fn ($month) => (int) $month)->values()->all();
        $selectedYear = (int) ($selectedYear ?? now()->year);
        $monthLabel = count($selectedMonths) === 1
            ? ($monthOptions[$selectedMonths[0]] ?? now()->format('F'))
            : '(' . collect($selectedMonths)
                ->map(fn ($month) => \Carbon\Carbon::createFromDate(2000, $month, 1)->format('M'))
                ->implode(', ') . ')';
        $reportLabel = trim($monthLabel . ' ' . $selectedYear);
        $reportGrandTotal = (float) $sections->sum('section_total');
        $entryCount = (int) $sections->sum(fn ($section) => count($section['rows'] ?? []));
        $campusTitle = trim(implode('-', array_filter([
            $campus->code ?? null,
            $campus->title ?: ($campus->name ?? null),
        ])));
        $periodLabel = trim(implode(' to ', array_filter([
            optional($reportStart)->format('d-M-Y'),
            optional($reportEnd)->format('d-M-Y'),
        ])));
    @endphp

    <div class="collection-report">
        <div class="collection-toolbar no-print">
            <a
                href="{{ route('dashboard.collection', ['months' => $selectedMonths, 'year' => $selectedYear]) }}"
                class="collection-toolbar-link"
            >
                Back
            </a>
            <button type="button" class="collection-toolbar-button" onclick="window.print()">Print Report</button>
        </div>

        <section class="collection-hero">
            <div class="collection-hero-top">
                <div class="collection-brand">
                    <span class="collection-brand-mark"></span>
                    <div>
                        <div class="collection-brand-eyebrow">Career Institute</div>
                        <div class="collection-campus-name">{{ $campusTitle !== '' ? $campusTitle : ($campus->name ?? 'Campus') }}</div>
                        <div class="collection-report-title">Collections Report - {{ $reportLabel }}</div>
                    </div>
                </div>
                <div class="collection-period-card">
                    <span>Reporting Period</span>
                    <strong>{{ $periodLabel }}</strong>
                </div>
            </div>

            <div class="collection-metrics">
                @foreach($sections as $section)
                    <div class="collection-metric-card">
                        <span class="collection-metric-label">{{ $section['title'] ?? 'Section' }}</span>
                        <strong class="collection-metric-value">Rs. {{ number_format((float) ($section['section_total'] ?? 0), 0) }}</strong>
                    </div>
                @endforeach
                <div class="collection-metric-card">
                    <span class="collection-metric-label">Entries</span>
                    <strong class="collection-metric-value">{{ number_format($entryCount) }}</strong>
                </div>
                <div class="collection-metric-card collection-metric-card--highlight">
                    <span class="collection-metric-label">Grand Total</span>
                    <strong class="collection-metric-value">Rs. {{ number_format($reportGrandTotal, 0) }}</strong>
                </div>
            </div>
        </section>

        @forelse($sections as $section)
            <div class="collection-section">
                <div class="collection-section-head">
                    <div class="collection-section-title">{{ $section['title'] ?? 'Collection' }}</div>
                    <div class="collection-section-total">
                        <span>Total Amount</span>
                        <strong>Rs. {{ number_format((float) ($section['section_total'] ?? 0), 0) }}</strong>
                    </div>
                </div>

                <div class="collection-table-wrap">
                    @if(($section['key'] ?? null) === 'registration')
                        <table class="table table-bordered collection-detail-table">
                            <thead>
                                <tr>
                                    <th>Sr</th>
                                    <th>Reg. No</th>
                                    <th>Name</th>
                                    <th>Father Name</th>
                                    <th>Paid Amount</th>
                                    <th>Paid Date</th>
                                    <th>Create DateTime</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(($section['rows'] ?? []) as $row)
                                    <tr>
                                        <td>{{ $row['sr'] ?? 0 }}</td>
                                        <td>{{ $row['registration_number'] ?? 'N/A' }}</td>
                                        <td>{{ $row['name'] ?? 'N/A' }}</td>
                                        <td>{{ $row['father_name'] ?? 'N/A' }}</td>
                                        <td class="numeric-cell">{{ number_format((float) ($row['paid_amount'] ?? 0), 0) }}</td>
                                        <td>{{ $row['paid_date'] ?? 'N/A' }}</td>
                                        <td>{{ $row['created_datetime'] ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                                <tr class="collection-total-row">
                                    <td colspan="6" class="collection-total-label">Total Amount</td>
                                    <td class="collection-total-value">Rs. {{ number_format((float) ($section['section_total'] ?? 0), 0) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @elseif(($section['key'] ?? null) === 'admission')
                        <table class="table table-bordered collection-detail-table">
                            <thead>
                                <tr>
                                    <th>Sr</th>
                                    <th>Roll No</th>
                                    <th>Name</th>
                                    <th>Father Name</th>
                                    <th>Course Title</th>
                                    <th>Admission Date</th>
                                    <th>Paid Amount</th>
                                    <th>Fee Type</th>
                                    <th>Paid Date</th>
                                    <th>Create DateTime</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(($section['rows'] ?? []) as $row)
                                    <tr>
                                        <td>{{ $row['sr'] ?? 0 }}</td>
                                        <td>{{ $row['roll_number'] ?? 'N/A' }}</td>
                                        <td>{{ $row['name'] ?? 'N/A' }}</td>
                                        <td>{{ $row['father_name'] ?? 'N/A' }}</td>
                                        <td>{{ $row['course_title'] ?? 'N/A' }}</td>
                                        <td>{{ $row['admission_date'] ?? 'N/A' }}</td>
                                        <td class="numeric-cell">{{ number_format((float) ($row['paid_amount'] ?? 0), 0) }}</td>
                                        <td>{{ $row['fee_type_label'] ?? 'N/A' }}</td>
                                        <td>{{ $row['paid_date'] ?? 'N/A' }}</td>
                                        <td>{{ $row['created_datetime'] ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                                <tr class="collection-total-row">
                                    <td colspan="9" class="collection-total-label">Total Amount</td>
                                    <td class="collection-total-value">Rs. {{ number_format((float) ($section['section_total'] ?? 0), 0) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @else
                        <table class="table table-bordered collection-detail-table">
                            <thead>
                                <tr>
                                    <th>Sr</th>
                                    <th>Receipt No</th>
                                    <th>Paid Amount</th>
                                    <th>Paid Date</th>
                                    <th>Create DateTime</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(($section['rows'] ?? []) as $row)
                                    <tr>
                                        <td>{{ $row['sr'] ?? 0 }}</td>
                                        <td>{{ $row['receipt_number'] ?? 'N/A' }}</td>
                                        <td class="numeric-cell">{{ number_format((float) ($row['paid_amount'] ?? 0), 0) }}</td>
                                        <td>{{ $row['paid_date'] ?? 'N/A' }}</td>
                                        <td>{{ $row['created_datetime'] ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                                <tr class="collection-total-row">
                                    <td colspan="4" class="collection-total-label">Total Amount</td>
                                    <td class="collection-total-value">Rs. {{ number_format((float) ($section['section_total'] ?? 0), 0) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        @empty
            <div class="collection-empty">No collection data found for this campus in the selected period.</div>
        @endforelse

        @if($sections->isNotEmpty())
            <div class="collection-summary">
                <div class="collection-summary-title">Report Summary</div>
                <div class="collection-summary-grid">
                    @foreach($sections as $section)
                        <div class="collection-summary-card">
                            <span>{{ $section['title'] ?? 'Section' }}</span>
                            <strong>Rs. {{ number_format((float) ($section['section_total'] ?? 0), 0) }}</strong>
                        </div>
                    @endforeach
                    <div class="collection-summary-card">
                        <span>Total Entries</span>
                        <strong>{{ number_format($entryCount) }}</strong>
                    </div>
                    <div class="collection-summary-card collection-summary-card--accent">
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
            --dimension-dashboard-collection-campus-1: 18px;
            --space-dashboard-collection-campus-1: 0 !important;
            --space-dashboard-collection-campus-2: 12px;
            --space-dashboard-collection-campus-3: 16px;
            --space-dashboard-collection-campus-4: 16px 18px;
            --space-dashboard-collection-campus-5: 18px;
            --space-dashboard-collection-campus-6: 8px;
            --color-dashboard-collection-campus-1: #00A8FF;
            --color-dashboard-collection-campus-2: #12263f;
            --color-dashboard-collection-campus-3: #17324d;
            --color-dashboard-collection-campus-4: #fff;
            --color-dashboard-collection-campus-5: #ffffff;
            --color-dashboard-collection-campus-6: rgba(138, 45, 29, 0.06);
        }

        :root {
            --dimension-dashboard-collection-campus-1: 18px;
            --space-dashboard-collection-campus-1: 0 !important;
            --space-dashboard-collection-campus-2: 12px;
            --space-dashboard-collection-campus-3: 16px;
            --space-dashboard-collection-campus-4: 16px 18px;
            --space-dashboard-collection-campus-5: 18px;
            --space-dashboard-collection-campus-6: 8px;
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
            size: A4 portrait;
            margin: 2mm;
            white-space: nowrap;
        }

        body.collection-report-page .site-header,
        body.collection-report-page .side-menu,
        body.collection-report-page .control-panel-container {
            display: none !important;
            
        }

        body.collection-report-page.with-side-menu .page-content,
        body.collection-report-page .page-content {
            padding: var(--space-dashboard-collection-campus-1);
            margin: var(--space-dashboard-collection-campus-1);
            background: linear-gradient(180deg, var(--color-dashboard-collection-campus-4)8f1 0%, var(--color-dashboard-collection-campus-4)fff 100%);
        }

        body.collection-report-page.with-side-menu .page-content > .container-fluid,
        body.collection-report-page .page-content > .container-fluid {
            padding: var(--space-dashboard-collection-campus-1);
        }

        .collection-report {
            /* max-width: 1280px; */
            margin: 0 auto;
            padding: 24px 18px 30px;
            color: var(--color-dashboard-collection-campus-2);
            font-family: "Segoe UI", "Helvetica Neue", sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            background:white;
        }

        .collection-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--space-dashboard-collection-campus-2);
            margin-bottom: var(--space-dashboard-collection-campus-3);
        }

        .collection-toolbar-link,
        .collection-toolbar-button {
            border: 0;
            border-radius: 999px;
            padding: 10px 16px;
            font-size: var(--campus-report-font-sm);
            font-weight: var(--campus-report-weight-bold);
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .collection-toolbar-link {
            background: var(--color-dashboard-collection-campus-4);
            color: var(--color-dashboard-collection-campus-1);
            /* box-shadow: 0 10px 30px rgba(138, 45, 29, 0.12); */
        }

        .collection-toolbar-button {
            background: var(--color-dashboard-collection-campus-1);
            color: var(--color-dashboard-collection-campus-4);
            /* box-shadow: 0 14px 36px rgba(234, 88, 12, 0.28); */
            cursor: pointer;
        }

        .collection-toolbar-link:hover,
        .collection-toolbar-button:hover {
            transform: translateY(-1px);
            text-decoration: none;
        }

        .collection-hero {
            border: 1px solid var(--color-dashboard-collection-campus-1);
            border-top: 6px solid var(--color-dashboard-collection-campus-1);
            background: ;
            border-radius: 24px;
            padding: 24px 28px;
            /* box-shadow: 0 22px 50px rgba(138, 45, 29, 0.10); */
            margin-bottom: var(--space-dashboard-collection-campus-5);
        }

        .collection-hero-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: var(--space-dashboard-collection-campus-5);
            margin-bottom: var(--space-dashboard-collection-campus-5);
        }

        .collection-brand {
            display: flex;
            align-items: flex-start;
            gap: var(--space-dashboard-collection-campus-3);
        }

        .collection-brand-mark {
            width: var(--dimension-dashboard-collection-campus-1);
            min-width: var(--dimension-dashboard-collection-campus-1);
            height: 72px;
            border-radius: 999px;
            background: linear-gradient(180deg, #3ccffb 0%, #0c90c2 100%);
        }

        .collection-brand-eyebrow {
            font-size: var(--campus-report-font-xs);
            font-weight: var(--campus-report-weight-bold);
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: gray;
            margin-bottom: var(--space-dashboard-collection-campus-6);
        }

        .collection-campus-name {
            font-size: 32px;
            line-height: 1.15;
            font-weight: var(--campus-report-weight-bold);
            color: var(--color-dashboard-collection-campus-2);
            margin-bottom: var(--space-dashboard-collection-campus-6);
        }

        .collection-report-title {
            font-size: var(--campus-report-font-xl);
            line-height: var(--campus-report-line-base);
            font-weight: 600;
            color: gray;
        }

        .collection-period-card {
            min-width: 240px;
            padding: var(--space-dashboard-collection-campus-4);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid var(--color-dashboard-collection-campus-1);
        }

        .collection-period-card span,
        .collection-metric-label,
        .collection-section-total span,
        .collection-summary-card span {
            display: block;
            font-size: var(--campus-report-font-xxs);
            font-weight: var(--campus-report-weight-bold);
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: gray;
            margin-bottom: 6px;
        }

        .collection-period-card strong,
        .collection-metric-value,
        .collection-section-total strong,
        .collection-summary-card strong {
            display: block;
            font-size: var(--campus-report-font-lg);
            line-height: var(--campus-report-line-tight);
            color: var(--color-dashboard-collection-campus-2);
        }

        .collection-metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: var(--space-dashboard-collection-campus-2);
        }

        .collection-metric-card,
        .collection-summary-card {
            border-radius: 18px;
             border: 1px solid var(--color-dashboard-collection-campus-1);
             text-align: center;
             padding:auto;
            background: rgba(255, 255, 255, 0.95);
            padding: 14px 16px;
            /* box-shadow: 0 12px 30px var(--color-dashboard-collection-campus-6); */
        }

        .collection-metric-card--highlight,
        .collection-summary-card--accent {
            background: var(--color-dashboard-collection-campus-1);
            border-color: var(--color-dashboard-collection-campus-1);
        }

        .collection-metric-card--highlight .collection-metric-label,
        .collection-summary-card--accent span,
        .collection-metric-card--highlight .collection-metric-value,
        .collection-summary-card--accent strong {
            color: var(--color-dashboard-collection-campus-4);
        }

        .collection-section {
            margin-bottom: var(--space-dashboard-collection-campus-5);
            border: 1px solid var(--color-dashboard-collection-campus-1);;
            border-radius: 22px;
            background: var(--color-dashboard-collection-campus-4);
            /* box-shadow: 0 18px 44px rgba(138, 45, 29, 0.08); */
            overflow: hidden;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .collection-section-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: var(--space-dashboard-collection-campus-3);
            padding: 10px 20px 14px;
            border-bottom: 1px solid #f6e6db;
            /* background: linear-gradient(180deg, var(--color-dashboard-collection-campus-4)dfa 0%, var(--color-dashboard-collection-campus-4)5ed 100%); */
        }

        .collection-section-title {
            font-size: 24px;
            line-height: var(--campus-report-line-tight);
            font-weight: var(--campus-report-weight-bold);
            color: var(--color-dashboard-collection-campus-3);
        }

        .collection-section-total {
            min-width: 200px;
            text-align: right;
        }

        .collection-table-wrap {
            overflow-x: auto;
            overflow-y: hidden;
        }

        .collection-detail-table {
            margin-bottom: 0;
            background: var(--color-dashboard-collection-campus-4);
            width: max-content;
            min-width: 100%;
            table-layout: auto;
            border-collapse: collapse;
        }

        .collection-detail-table thead th {
            background: var(--color-dashboard-collection-campus-4)3e8;
            color: #6f3f2d;
            font-size: var(--campus-report-font-xxs);
            font-weight: var(--campus-report-weight-heavy);
            letter-spacing: 0.05em;
            text-transform: uppercase;
            vertical-align: middle;
        }

        .collection-detail-table th,
        .collection-detail-table td {
            border: 1px solid var(--color-dashboard-collection-campus-1) !important;
            padding: 9px 8px;
            color: var(--color-dashboard-collection-campus-3);
            vertical-align: top;
            font-size: var(--campus-report-font-xs);
            line-height: 1.35;
            white-space: nowrap;
            word-break: normal;
            overflow-wrap: normal;
        }

        .collection-detail-table tbody tr:nth-child(even) td {
            background: var(--color-dashboard-collection-campus-4)fff;
        }

        .numeric-cell,
        .collection-total-value {
            text-align: right;
            white-space: nowrap;
        }

        .collection-total-row td {
            background: #;
            font-size: var(--campus-report-font-sm);
            line-height: var(--campus-report-line-base);
            border-top: 2px solid #00a8ff !important;
        }

        .collection-total-label {
            font-weight: var(--campus-report-weight-heavy);
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .collection-total-value {
            font-weight: var(--campus-report-weight-heavy);
            color: #8a2d1d;
        }

        .collection-empty {
            border: 1px dashed #e4c6b3;
            border-radius: 20px;
            background: var(--color-dashboard-collection-campus-4);
            padding: 28px;
            text-align: center;
            color: #6b7280;
            font-size: 16px;
            /* box-shadow: 0 18px 44px var(--color-dashboard-collection-campus-6); */
        }

        .collection-summary {
            margin-top: 24px;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .collection-summary-title {
            font-size: 22px;
            line-height: var(--campus-report-line-tight);
            font-weight: var(--campus-report-weight-bold);
            color: var(--color-dashboard-collection-campus-3);
            margin-bottom: var(--space-dashboard-collection-campus-2);
        }

        .collection-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: var(--space-dashboard-collection-campus-2);
        }

        .collection-summary-card strong {
            font-size: var(--campus-report-font-lg);
        }

        .no-print {
            display: flex;
        }

        @media print {
            body.collection-report-page,
            body.collection-report-page * {
                visibility: visible !important;
            }

            body.collection-report-page.with-side-menu .page-content,
            body.collection-report-page .page-content {
                background: var(--color-dashboard-collection-campus-4) !important;
            }

            .no-print {
                display: none !important;
            }

            .collection-report {
                max-width: none;
                padding: 0;
            }

            .collection-hero,
            .collection-section,
            .collection-metric-card,
            .collection-summary-card {
                box-shadow: none !important;
            }

            .collection-hero {
                border-radius: 0;
                margin-bottom: var(--space-dashboard-collection-campus-2);
                padding: var(--space-dashboard-collection-campus-4);
            }

            .collection-section {
                border-radius: 0;
                margin-bottom: var(--space-dashboard-collection-campus-2);
            }

            .collection-metric-card,
            .collection-summary-card {
                border-radius: 0;
                padding: 10px 12px;
            }

            .collection-brand-mark {
                height: 56px;
            }

            .collection-campus-name {
                font-size: var(--campus-report-font-xl);
            }

            .collection-report-title {
                font-size: 17px;
            }

            .collection-section-title {
                font-size: var(--campus-report-font-lg);
            }

            .collection-detail-table th,
            .collection-detail-table td {
                font-size: 10.5px;
                padding: 6px 5px;
            }

            .collection-total-row td,
            .collection-summary-card strong,
            .collection-section-total strong {
                font-size: var(--campus-report-font-xs);
            }
        }

        @media (max-width: 767px) {
            .collection-report {
                padding: 16px 10px 24px;
            }

            .collection-toolbar,
            .collection-hero-top,
            .collection-section-head {
                /* flex-direction: column;
                align-items: stretch; */
                font-size:15px;
            }

            .collection-brand {
                gap: var(--space-dashboard-collection-campus-2);
            }

            .collection-campus-name {
                font-size: var(--campus-report-font-xl);
            }

            .collection-report-title {
                font-size: var(--campus-report-font-lg);
            }

            .collection-section-total,
            .collection-period-card {
                min-width: 0;
                text-align: left;
            }
        }
    </style><style>
        :root {
            --space-dashboard-collection-campus-1: 0 !important;
            --space-dashboard-collection-campus-2: 12px;
            --space-dashboard-collection-campus-3: 16px;
            --space-dashboard-collection-campus-4: 16px 18px;
            --space-dashboard-collection-campus-5: 18px;
            --space-dashboard-collection-campus-6: 8px;
            --color-dashboard-collection-campus-1: #00A8FF;
            --color-dashboard-collection-campus-2: #12263f;
            --color-dashboard-collection-campus-3: #17324d;
            --color-dashboard-collection-campus-4: #fff;
            --color-dashboard-collection-campus-5: #ffffff;
            --color-dashboard-collection-campus-6: rgba(138, 45, 29, 0.06);
        }

@media print {
    @page {
        size: A4 portrait;
        margin: 1mm;
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
