@extends('layouts.embed')

@section('title', 'DBR Report')

@section('content')
    @php
        $selectedCampusLabel = $selectedCampus
            ? trim((string) (($selectedCampus->code ? $selectedCampus->code . ' - ' : '') . $selectedCampus->name))
            : 'ALL Campus';
        $campusHeading = $selectedCampusLabel . ' - Career Institute';
        $selectedUserLabel = $selectedUser?->name ?: 'All User';
        $formatAmount = fn ($value) => number_format((float) $value, 0);

        $compactMatrixRows = function ($matrixRows) {
            return collect($matrixRows ?? [])
                ->map(function ($row) {
                    $items = collect($row['counts'] ?? [])
                        ->map(fn ($count, $label) => [
                            'label' => $label,
                            'count' => (int) $count,
                        ])
                        ->filter(fn ($item) => $item['count'] > 0)
                        ->values();

                    return [
                        'campus' => $row['campus'] ?? null,
                        'user' => $row['user'] ?? null,
                        'items' => $items,
                        'total' => (int) $items->sum('count'),
                    ];
                })
                ->filter(fn ($row) => $row['items']->isNotEmpty())
                ->values();
        };

        $leadCompactRows = $compactMatrixRows(data_get($leadMatrix, 'rows', []));
        $registrationCompactRows = $compactMatrixRows(data_get($registrationMatrix, 'rows', []));
        $enrollmentCompactRows = $compactMatrixRows(data_get($enrollmentMatrix, 'rows', []));

        $feeRows = collect($feeDetailRows ?? [])
            ->filter(fn ($row) => (float) ($row['amount'] ?? 0) > 0)
            ->values();

        $registrationRows = collect($registrationFeeRows ?? [])
            ->filter(fn ($row) => (int) ($row['count'] ?? 0) > 0 || (float) ($row['amount'] ?? 0) > 0)
            ->values();

        $coworkingRows = collect($coworkingReceiptRows ?? [])
            ->filter(fn ($row) => (float) ($row['amount'] ?? 0) > 0)
            ->values();

        $expenseRows = collect($expenseReportRows ?? [])
            ->filter(fn ($row) => (float) ($row['amount'] ?? 0) > 0)
            ->values();

        $paymentRows = collect($paymentSummary ?? [])
            ->filter(fn ($row) => (int) ($row['count'] ?? 0) > 0 || (float) ($row['amount'] ?? 0) > 0)
            ->values();

        $summaryRows = collect([
            ['label' => 'Registrations', 'amount' => (float) ($summaryTotals['registrations'] ?? 0)],
            ['label' => 'Enrollment + Installments', 'amount' => (float) ($summaryTotals['enrollment_installments'] ?? 0)],
            ['label' => 'Coworking Space', 'amount' => (float) ($summaryTotals['coworking'] ?? 0)],
        ])->filter(fn ($row) => $row['amount'] > 0)->values();

        $metricRows = collect([
            ['label' => 'Leads', 'raw' => (float) ($topline['leads'] ?? 0), 'value' => number_format((int) ($topline['leads'] ?? 0))],
            ['label' => 'Enroll', 'raw' => (float) ($topline['enroll_amount'] ?? 0), 'value' => $formatAmount($topline['enroll_amount'] ?? 0)],
            ['label' => 'Registrations', 'raw' => (float) ($topline['registration_amount'] ?? 0), 'value' => $formatAmount($topline['registration_amount'] ?? 0)],
            ['label' => 'Installments', 'raw' => (float) ($topline['installment_amount'] ?? 0), 'value' => $formatAmount($topline['installment_amount'] ?? 0)],
            ['label' => 'Coworking', 'raw' => (float) ($topline['coworking_amount'] ?? 0), 'value' => $formatAmount($topline['coworking_amount'] ?? 0)],
        ])->filter(fn ($row) => $row['raw'] > 0)->values();

        $hasAnySection = $leadCompactRows->isNotEmpty()
            || $registrationCompactRows->isNotEmpty()
            || $enrollmentCompactRows->isNotEmpty()
            || $feeRows->isNotEmpty()
            || $registrationRows->isNotEmpty()
            || $coworkingRows->isNotEmpty()
            || $expenseRows->isNotEmpty()
            || $paymentRows->isNotEmpty()
            || $summaryRows->isNotEmpty();
    @endphp

    <style>
        .dbr-page {
            --dbr-ink: #17324d;
            --dbr-accent: #2f8fdc;
            --dbr-accent-soft: #edf6ff;
            --dbr-border: #d5e2ef;
            --dbr-muted: #607790;
            --dbr-paper: #fff;
            --dbr-bg: linear-gradient(180deg, #f3f8fd 0%, #f8fbff 100%);
            padding: 16px;
        }

        .dbr-panel {
            border: 1px solid rgba(47, 143, 220, 0.14);
            box-shadow: 0 18px 40px rgba(23, 50, 77, 0.08);
            background: var(--dbr-bg);
        }

        .dbr-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .dbr-filter {
            flex: 1 1 760px;
        }

        .dbr-filter .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 0;
        }

        .dbr-filter .form-group {
            margin-bottom: 0;
            flex: 1 1 170px;
        }

        .dbr-label {
            display: block;
            margin-bottom: 4px;
            color: var(--dbr-muted);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .dbr-toolbar-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .dbr-sheet {
            background: var(--dbr-paper);
            border: 1px solid var(--dbr-border);
            border-radius: 14px;
            padding: 16px 16px 12px;
            max-width: 1360px;
            margin: 0 auto;
        }

        .dbr-header {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 10px;
            align-items: end;
            border-bottom: 1px solid var(--dbr-border);
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .dbr-campus-heading {
            grid-column: 1 / -1;
            text-align: center;
            color: var(--dbr-ink);
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .dbr-title {
            margin: 0;
            color: var(--dbr-ink);
            font-size: 22px;
            font-weight: 800;
        }

        .dbr-meta {
            color: var(--dbr-muted);
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .dbr-metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
            gap: 8px;
            margin-bottom: 10px;
        }

        .dbr-metric {
            border: 1px solid var(--dbr-border);
            border-radius: 10px;
            background: #fbfdff;
            padding: 8px 10px;
            text-align: center;
        }

        .dbr-metric-label {
            display: block;
            color: var(--dbr-muted);
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .dbr-metric-value {
            display: block;
            color: var(--dbr-ink);
            font-size: 18px;
            font-weight: 800;
            line-height: 1.1;
            margin-top: 4px;
        }

        .dbr-columns {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 10px;
            align-items: start;
        }

        .dbr-column {
            display: grid;
            gap: 10px;
            align-content: start;
        }

        .dbr-card {
            border: 1px solid var(--dbr-border);
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }

        .dbr-card-title {
            margin: 0;
            padding: 7px 10px;
            background: var(--dbr-accent-soft);
            color: var(--dbr-ink);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .dbr-card-body {
            padding: 8px 10px;
        }

        .dbr-table {
            width: 100%;
            margin: 0;
            border-collapse: collapse;
        }

        .dbr-table th,
        .dbr-table td {
            border: 1px solid var(--dbr-border);
            padding: 6px 7px;
            color: #314960;
            font-size: 11px;
            vertical-align: top;
        }

        .dbr-table th {
            background: #f7fbff;
            color: var(--dbr-ink);
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .dbr-table td.amount,
        .dbr-table td.count {
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .dbr-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }

        .dbr-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 7px;
            border-radius: 999px;
            background: #eef6ff;
            color: #21415f;
            font-size: 10px;
            line-height: 1.25;
            font-weight: 700;
        }

        .dbr-tag strong {
            font-size: 10px;
            font-weight: 800;
        }

        .dbr-mini-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 8px;
        }

        .dbr-mini-stat {
            border: 1px solid var(--dbr-border);
            border-radius: 10px;
            background: #fbfdff;
            padding: 8px;
            text-align: center;
        }

        .dbr-mini-stat span {
            display: block;
            color: var(--dbr-muted);
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .dbr-mini-stat strong {
            display: block;
            color: var(--dbr-ink);
            font-size: 15px;
            font-weight: 800;
            margin-top: 4px;
            line-height: 1.1;
        }

        .dbr-signatures {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 10px;
        }

        .dbr-signature {
            border-top: 1px solid var(--dbr-border);
            padding-top: 16px;
            text-align: center;
        }

        .dbr-signature strong {
            display: block;
            color: var(--dbr-ink);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .dbr-signature span {
            display: block;
            margin-top: 4px;
            color: var(--dbr-muted);
            font-size: 10px;
        }

        .dbr-empty-sheet {
            border: 1px dashed var(--dbr-border);
            border-radius: 12px;
            padding: 18px 12px;
            text-align: center;
            color: var(--dbr-muted);
            font-size: 12px;
            font-style: italic;
        }

        @media (max-width: 1100px) {
            .dbr-header,
            .dbr-columns,
            .dbr-metrics,
            .dbr-mini-grid,
            .dbr-signatures {
                grid-template-columns: 1fr;
            }

            .dbr-meta {
                white-space: normal;
            }
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 6mm;
            }

            body.crm-embed-body .no-print {
                display: none !important;
            }

            body.crm-embed-body {
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }

            .dbr-page {
                padding: 0 !important;
            }

            .dbr-panel,
            .dbr-sheet,
            .dbr-card {
                border-radius: 0 !important;
                box-shadow: none !important;
            }

            .dbr-panel {
                border: 0 !important;
                background: #fff !important;
            }

            .dbr-sheet {
                border: 0 !important;
                padding: 0 !important;
            }

            .dbr-card,
            .dbr-signatures {
                page-break-inside: avoid;
            }

            .dbr-campus-heading {
                font-size: 16px;
            }

            .dbr-title {
                font-size: 18px;
            }

            .dbr-meta,
            .dbr-table th,
            .dbr-table td,
            .dbr-tag,
            .dbr-mini-stat span,
            .dbr-signature strong,
            .dbr-signature span {
                font-size: 9px !important;
            }

            .dbr-metric-value {
                font-size: 15px;
            }

            .dbr-card-title {
                padding: 5px 8px;
                font-size: 10px;
            }

            .dbr-card-body {
                padding: 6px 8px;
            }

            .dbr-table th,
            .dbr-table td {
                padding: 4px 5px;
            }

            .dbr-metrics,
            .dbr-columns,
            .dbr-column,
            .dbr-mini-grid,
            .dbr-signatures {
                gap: 6px;
            }
        }
    </style>

    <div class="dbr-page">
        <div class="box-typical box-typical-dashboard panel panel-default dbr-panel">
            <div class="panel-body">
                <div class="dbr-toolbar no-print">
                    <form method="GET" action="{{ route('reports.dbr') }}" class="dbr-filter">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="dbr-label">Date</label>
                                <input type="date" name="report_date" class="form-control" value="{{ $filters['report_date'] ?? now()->toDateString() }}">
                            </div>
                            <div class="form-group">
                                <label class="dbr-label">Campus</label>
                                <select name="campus_id" class="form-control">
                                    <option value="">All Campus</option>
                                    @foreach($campuses as $campus)
                                        <option value="{{ $campus->id }}" @selected((int) ($filters['campus_id'] ?? 0) === (int) $campus->id)>
                                            {{ $campus->code ?? $campus->name }} - {{ $campus->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="dbr-label">User</label>
                                <select name="user_id" class="form-control">
                                    <option value="">All User</option>
                                    @foreach($users as $reportUser)
                                        <option value="{{ $reportUser->id }}" @selected((int) ($filters['user_id'] ?? 0) === (int) $reportUser->id)>
                                            {{ $reportUser->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group dbr-toolbar-actions">
                                <button type="submit" class="btn btn-primary-outline">Filter</button>
                                <a href="{{ route('reports.dbr') }}" class="btn btn-danger-outline">Clear</a>
                            </div>
                        </div>
                    </form>

                    <div class="dbr-toolbar-actions">
                        <button type="button" class="btn btn-primary-outline" onclick="window.print()">Print Report</button>
                    </div>
                </div>

                <div class="dbr-sheet">
                    <div class="dbr-header">
                        <div class="dbr-campus-heading">{{ $campusHeading }}</div>
                        <h2 class="dbr-title">Daily Business Report</h2>
                        <div class="dbr-meta">{{ $selectedUserLabel }}</div>
                        <div class="dbr-meta">Date {{ $reportDate->format('d-m-Y') }}</div>
                    </div>

                    <div class="dbr-metrics">
                        @foreach($metricRows as $metric)
                            <div class="dbr-metric">
                                <span class="dbr-metric-label">{{ $metric['label'] }}</span>
                                <strong class="dbr-metric-value">{{ $metric['value'] }}</strong>
                            </div>
                        @endforeach
                    </div>

                    @if(!$hasAnySection)
                        <div class="dbr-empty-sheet">No business activity found for this date.</div>
                    @else
                        <div class="dbr-columns">
                            <div class="dbr-column">
                                @if($leadCompactRows->isNotEmpty())
                                    <section class="dbr-card">
                                        <h3 class="dbr-card-title">Daily Leads</h3>
                                        <div class="dbr-card-body">
                                            <table class="dbr-table">
                                                <thead>
                                                    <tr>
                                                        @if($showCampusColumn)
                                                            <th>Campus</th>
                                                        @endif
                                                        @if($showUserColumn)
                                                            <th>User</th>
                                                        @endif
                                                        <th>Details</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($leadCompactRows as $row)
                                                        <tr>
                                                            @if($showCampusColumn)
                                                                <td>{{ $row['campus'] ?? 'N/A' }}</td>
                                                            @endif
                                                            @if($showUserColumn)
                                                                <td>{{ $row['user'] ?? 'Unassigned' }}</td>
                                                            @endif
                                                            <td>
                                                                <div class="dbr-tags">
                                                                    @foreach($row['items'] as $item)
                                                                        <span class="dbr-tag">{{ $item['label'] }} <strong>{{ number_format($item['count']) }}</strong></span>
                                                                    @endforeach
                                                                </div>
                                                            </td>
                                                            <td class="count">{{ number_format($row['total']) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </section>
                                @endif

                                @if($registrationCompactRows->isNotEmpty())
                                    <section class="dbr-card">
                                        <h3 class="dbr-card-title">Registrations</h3>
                                        <div class="dbr-card-body">
                                            <table class="dbr-table">
                                                <thead>
                                                    <tr>
                                                        @if($showCampusColumn)
                                                            <th>Campus</th>
                                                        @endif
                                                        @if($showUserColumn)
                                                            <th>User</th>
                                                        @endif
                                                        <th>Details</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($registrationCompactRows as $row)
                                                        <tr>
                                                            @if($showCampusColumn)
                                                                <td>{{ $row['campus'] ?? 'N/A' }}</td>
                                                            @endif
                                                            @if($showUserColumn)
                                                                <td>{{ $row['user'] ?? 'Unassigned' }}</td>
                                                            @endif
                                                            <td>
                                                                <div class="dbr-tags">
                                                                    @foreach($row['items'] as $item)
                                                                        <span class="dbr-tag">{{ $item['label'] }} <strong>{{ number_format($item['count']) }}</strong></span>
                                                                    @endforeach
                                                                </div>
                                                            </td>
                                                            <td class="count">{{ number_format($row['total']) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </section>
                                @endif

                                @if($enrollmentCompactRows->isNotEmpty())
                                    <section class="dbr-card">
                                        <h3 class="dbr-card-title">Enrollment</h3>
                                        <div class="dbr-card-body">
                                            <table class="dbr-table">
                                                <thead>
                                                    <tr>
                                                        @if($showCampusColumn)
                                                            <th>Campus</th>
                                                        @endif
                                                        @if($showUserColumn)
                                                            <th>User</th>
                                                        @endif
                                                        <th>Details</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($enrollmentCompactRows as $row)
                                                        <tr>
                                                            @if($showCampusColumn)
                                                                <td>{{ $row['campus'] ?? 'N/A' }}</td>
                                                            @endif
                                                            @if($showUserColumn)
                                                                <td>{{ $row['user'] ?? 'Unassigned' }}</td>
                                                            @endif
                                                            <td>
                                                                <div class="dbr-tags">
                                                                    @foreach($row['items'] as $item)
                                                                        <span class="dbr-tag">{{ $item['label'] }} <strong>{{ number_format($item['count']) }}</strong></span>
                                                                    @endforeach
                                                                </div>
                                                            </td>
                                                            <td class="count">{{ number_format($row['total']) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </section>
                                @endif

                                @if($paymentRows->isNotEmpty())
                                    <section class="dbr-card">
                                        <h3 class="dbr-card-title">Payment Methods</h3>
                                        <div class="dbr-card-body">
                                            <div class="dbr-mini-grid">
                                                @foreach($paymentRows as $row)
                                                    <div class="dbr-mini-stat">
                                                        <span>{{ $row['label'] }}</span>
                                                        <strong>{{ number_format((int) $row['count']) }} / {{ $formatAmount($row['amount']) }}</strong>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </section>
                                @endif
                            </div>

                            <div class="dbr-column">
                                @if($feeRows->isNotEmpty())
                                    <section class="dbr-card">
                                        <h3 class="dbr-card-title">Enrollment + Installments</h3>
                                        <div class="dbr-card-body">
                                            <table class="dbr-table">
                                                <thead>
                                                    <tr>
                                                        @if($showCampusColumn)
                                                            <th>Campus</th>
                                                        @endif
                                                        @if($showUserColumn)
                                                            <th>User</th>
                                                        @endif
                                                        <th>Course</th>
                                                        <th>Fee Type</th>
                                                        <th>Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($feeRows as $row)
                                                        <tr>
                                                            @if($showCampusColumn)
                                                                <td>{{ $row['campus'] ?? 'N/A' }}</td>
                                                            @endif
                                                            @if($showUserColumn)
                                                                <td>{{ $row['user'] ?? 'Unassigned' }}</td>
                                                            @endif
                                                            <td>{{ $row['course'] }}</td>
                                                            <td>{{ $row['fee_type'] }}</td>
                                                            <td class="amount">{{ $formatAmount($row['amount']) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </section>
                                @endif

                                @if($registrationRows->isNotEmpty())
                                    <section class="dbr-card">
                                        <h3 class="dbr-card-title">Registration Fees</h3>
                                        <div class="dbr-card-body">
                                            <table class="dbr-table">
                                                <thead>
                                                    <tr>
                                                        @if($showCampusColumn)
                                                            <th>Campus</th>
                                                        @endif
                                                        @if($showUserColumn)
                                                            <th>User</th>
                                                        @endif
                                                        <th>Course</th>
                                                        <th>Number</th>
                                                        <th>Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($registrationRows as $row)
                                                        <tr>
                                                            @if($showCampusColumn)
                                                                <td>{{ $row['campus'] ?? 'N/A' }}</td>
                                                            @endif
                                                            @if($showUserColumn)
                                                                <td>{{ $row['user'] ?? 'Unassigned' }}</td>
                                                            @endif
                                                            <td>{{ $row['course'] }}</td>
                                                            <td class="count">{{ number_format((int) $row['count']) }}</td>
                                                            <td class="amount">{{ $formatAmount($row['amount']) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </section>
                                @endif

                                @if($coworkingRows->isNotEmpty())
                                    <section class="dbr-card">
                                        <h3 class="dbr-card-title">Coworking Space</h3>
                                        <div class="dbr-card-body">
                                            <table class="dbr-table">
                                                <thead>
                                                    <tr>
                                                        @if($showCampusColumn)
                                                            <th>Campus</th>
                                                        @endif
                                                        @if($showUserColumn)
                                                            <th>User</th>
                                                        @endif
                                                        <th>Space Type</th>
                                                        <th>Type</th>
                                                        <th>Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($coworkingRows as $row)
                                                        <tr>
                                                            @if($showCampusColumn)
                                                                <td>{{ $row['campus'] ?? 'N/A' }}</td>
                                                            @endif
                                                            @if($showUserColumn)
                                                                <td>{{ $row['user'] ?? 'Unassigned' }}</td>
                                                            @endif
                                                            <td>{{ $row['space_type'] }}</td>
                                                            <td>{{ $row['type'] }}</td>
                                                            <td class="amount">{{ $formatAmount($row['amount']) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </section>
                                @endif

                                @if($expenseRows->isNotEmpty())
                                    <section class="dbr-card">
                                        <h3 class="dbr-card-title">Expense</h3>
                                        <div class="dbr-card-body">
                                            <table class="dbr-table">
                                                <thead>
                                                    <tr>
                                                        @if($showCampusColumn)
                                                            <th>Campus</th>
                                                        @endif
                                                        @if($showUserColumn)
                                                            <th>User</th>
                                                        @endif
                                                        <th>Expense Type</th>
                                                        <th>Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($expenseRows as $row)
                                                        <tr>
                                                            @if($showCampusColumn)
                                                                <td>{{ $row['campus'] ?? 'N/A' }}</td>
                                                            @endif
                                                            @if($showUserColumn)
                                                                <td>{{ $row['user'] ?? 'Unassigned' }}</td>
                                                            @endif
                                                            <td>{{ $row['expense_type'] }}</td>
                                                            <td class="amount">{{ $formatAmount($row['amount']) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </section>
                                @endif

                                @if($summaryRows->isNotEmpty())
                                    <section class="dbr-card">
                                        <h3 class="dbr-card-title">Summary</h3>
                                        <div class="dbr-card-body">
                                            <div class="dbr-mini-grid">
                                                @foreach($summaryRows as $row)
                                                    <div class="dbr-mini-stat">
                                                        <span>{{ $row['label'] }}</span>
                                                        <strong>{{ $formatAmount($row['amount']) }}</strong>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </section>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="dbr-signatures">
                        <div class="dbr-signature">
                            <strong>Prepared By</strong>
                            <span>{{ $preparedBy ?: ' ' }}</span>
                        </div>
                        <div class="dbr-signature">
                            <strong>Checked By</strong>
                            <span>&nbsp;</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
