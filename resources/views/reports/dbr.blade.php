@extends('layouts.embed')

@section('title', 'DBR Report')

@section('content')
    @php
        $selectedCampusLabel = $selectedCampus
            ? trim((string) (($selectedCampus->code ? $selectedCampus->code . ' - ' : '') . $selectedCampus->name))
            : 'ALL Campus';
        $campusHeading = $selectedCampusLabel . ' - Career Institute';
        $selectedUserLabel = $selectedUser?->name ?: 'All User';

        $leadColumns = collect($leadMatrix['columns'] ?? []);
        $leadRows = collect($leadMatrix['rows'] ?? []);
        $registrationColumns = collect($registrationMatrix['columns'] ?? []);
        $registrationRows = collect($registrationMatrix['rows'] ?? []);
        $enrollmentColumns = collect($enrollmentMatrix['columns'] ?? []);
        $enrollmentRows = collect($enrollmentMatrix['rows'] ?? []);

        $leadColspan = max(1, ($leadColumns->count() ?: 1) + ($showCampusColumn ? 1 : 0) + ($showUserColumn ? 1 : 0));
        $registrationColspan = max(1, ($registrationColumns->count() ?: 1) + ($showCampusColumn ? 1 : 0) + ($showUserColumn ? 1 : 0));
        $enrollmentColspan = max(1, ($enrollmentColumns->count() ?: 1) + ($showCampusColumn ? 1 : 0) + ($showUserColumn ? 1 : 0));
        $detailBaseColumns = ($showCampusColumn ? 1 : 0) + ($showUserColumn ? 1 : 0);
    @endphp

    <style>
        .dbr-page {
            --dbr-ink: #17324d;
            --dbr-accent: #2f8fdc;
            --dbr-accent-soft: #eaf4ff;
            --dbr-border: #cddced;
            --dbr-muted: #627a93;
            --dbr-paper: #ffffff;
            --dbr-bg: linear-gradient(180deg, #f2f7fd 0%, #f7fbff 100%);
        }

        .dbr-panel {
            border: 1px solid rgba(47, 143, 220, 0.14);
            box-shadow: 0 24px 48px rgba(23, 50, 77, 0.08);
            background: var(--dbr-bg);
        }

        .dbr-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .dbr-filter {
            flex: 1 1 820px;
        }

        .dbr-filter .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 0;
        }

        .dbr-filter .form-group {
            margin-bottom: 0;
            flex: 1 1 180px;
        }

        .dbr-label {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            color: var(--dbr-muted);
        }

        .dbr-toolbar-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: flex-end;
            flex: 0 0 auto;
        }

        .dbr-sheet {
            background: var(--dbr-paper);
            border: 1px solid var(--dbr-border);
            border-radius: 16px;
            padding: 24px;
        }

        .dbr-sheet-header {
            border-bottom: 2px solid var(--dbr-accent-soft);
            padding-bottom: 16px;
            margin-bottom: 18px;
        }

        .dbr-campus-heading {
            font-size: 20px;
            font-weight: 700;
            color: var(--dbr-ink);
            letter-spacing: 0.02em;
            text-transform: uppercase;
            text-align: center;
        }

        .dbr-title-row {
            margin-top: 12px;
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 16px;
            align-items: center;
            color: var(--dbr-ink);
        }

        .dbr-title-row h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: var(--dbr-ink);
        }

        .dbr-title-row span {
            font-size: 14px;
            font-weight: 600;
            color: var(--dbr-muted);
            white-space: nowrap;
        }

        .dbr-section {
            margin-top: 18px;
        }

        .dbr-section-title {
            margin: 0 0 8px;
            padding: 8px 12px;
            border-left: 4px solid var(--dbr-accent);
            background: var(--dbr-accent-soft);
            color: var(--dbr-ink);
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .dbr-table-wrap {
            border: 1px solid var(--dbr-border);
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }

        .dbr-table {
            margin-bottom: 0;
        }

        .dbr-table thead th {
            background: #f4f9ff;
            color: var(--dbr-ink);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            border-color: var(--dbr-border) !important;
            vertical-align: middle;
        }

        .dbr-table td {
            border-color: var(--dbr-border) !important;
            color: #314960;
            vertical-align: middle;
        }

        .dbr-table td.amount,
        .dbr-table td.count {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .dbr-topline td,
        .dbr-topline th,
        .dbr-summary-table td,
        .dbr-summary-table th {
            text-align: center;
            font-variant-numeric: tabular-nums;
        }

        .dbr-empty {
            padding: 16px !important;
            text-align: center;
            color: var(--dbr-muted);
            font-style: italic;
        }

        .dbr-signatures {
            margin-top: 28px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 28px;
        }

        .dbr-signature {
            padding-top: 30px;
            border-top: 1px solid var(--dbr-border);
            text-align: center;
        }

        .dbr-signature strong {
            display: block;
            color: var(--dbr-ink);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .dbr-signature span {
            display: block;
            margin-top: 6px;
            color: var(--dbr-muted);
            font-size: 12px;
        }

        @media (max-width: 991px) {
            .dbr-title-row {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .dbr-title-row span {
                white-space: normal;
            }

            .dbr-sheet {
                padding: 16px;
            }
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }

            body.crm-embed-body .no-print {
                display: none !important;
            }

            body.crm-embed-body {
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }

            .dbr-panel,
            .dbr-sheet,
            .dbr-table-wrap {
                box-shadow: none !important;
                border-radius: 0 !important;
            }

            .dbr-panel {
                border: 0 !important;
                background: #fff !important;
            }

            .dbr-sheet {
                border: 0 !important;
                padding: 0 !important;
            }

            .dbr-section,
            .dbr-table-wrap,
            .dbr-signatures {
                page-break-inside: avoid;
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
                    <div class="dbr-sheet-header">
                        <div class="dbr-campus-heading">{{ $campusHeading }}</div>
                        <div class="dbr-title-row">
                            <h2>Daily Business Report</h2>
                            <span>{{ $selectedUserLabel }}</span>
                            <span>Date {{ $reportDate->format('d-m-Y') }}</span>
                        </div>
                    </div>

                    <div class="dbr-table-wrap">
                        <table class="table table-bordered dbr-table dbr-summary-table dbr-topline">
                            <thead>
                                <tr>
                                    <th>Leads</th>
                                    <th>Enroll</th>
                                    <th>Registrations</th>
                                    <th>Installments</th>
                                    <th>Coworking</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ number_format((int) ($topline['leads'] ?? 0)) }}</td>
                                    <td>{{ number_format((float) ($topline['enroll_amount'] ?? 0), 0) }}</td>
                                    <td>{{ number_format((float) ($topline['registration_amount'] ?? 0), 0) }}</td>
                                    <td>{{ number_format((float) ($topline['installment_amount'] ?? 0), 0) }}</td>
                                    <td>{{ number_format((float) ($topline['coworking_amount'] ?? 0), 0) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="dbr-section">
                        <h3 class="dbr-section-title">Daily Leads</h3>
                        <div class="dbr-table-wrap">
                            <table class="table table-bordered dbr-table">
                                <thead>
                                    <tr>
                                        @if($showCampusColumn)
                                            <th>Campus</th>
                                        @endif
                                        @if($showUserColumn)
                                            <th>User</th>
                                        @endif
                                        @if($leadColumns->isEmpty())
                                            <th>Record</th>
                                        @else
                                            @foreach($leadColumns as $column)
                                                <th>{{ $column }}</th>
                                            @endforeach
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($leadRows as $row)
                                        <tr>
                                            @if($showCampusColumn)
                                                <td>{{ $row['campus'] ?? 'N/A' }}</td>
                                            @endif
                                            @if($showUserColumn)
                                                <td>{{ $row['user'] ?? 'Unassigned' }}</td>
                                            @endif
                                            @foreach($leadColumns as $column)
                                                <td class="count">{{ number_format((int) ($row['counts'][$column] ?? 0)) }}</td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $leadColspan }}" class="dbr-empty">No leads found for this date.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="dbr-section">
                        <h3 class="dbr-section-title">Registrations</h3>
                        <div class="dbr-table-wrap">
                            <table class="table table-bordered dbr-table">
                                <thead>
                                    <tr>
                                        @if($showCampusColumn)
                                            <th>Campus</th>
                                        @endif
                                        @if($showUserColumn)
                                            <th>User</th>
                                        @endif
                                        @if($registrationColumns->isEmpty())
                                            <th>Record</th>
                                        @else
                                            @foreach($registrationColumns as $column)
                                                <th>{{ $column }}</th>
                                            @endforeach
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($registrationRows as $row)
                                        <tr>
                                            @if($showCampusColumn)
                                                <td>{{ $row['campus'] ?? 'N/A' }}</td>
                                            @endif
                                            @if($showUserColumn)
                                                <td>{{ $row['user'] ?? 'Unassigned' }}</td>
                                            @endif
                                            @foreach($registrationColumns as $column)
                                                <td class="count">{{ number_format((int) ($row['counts'][$column] ?? 0)) }}</td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $registrationColspan }}" class="dbr-empty">No registrations found for this date.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="dbr-section">
                        <h3 class="dbr-section-title">Enrollment</h3>
                        <div class="dbr-table-wrap">
                            <table class="table table-bordered dbr-table">
                                <thead>
                                    <tr>
                                        @if($showCampusColumn)
                                            <th>Campus</th>
                                        @endif
                                        @if($showUserColumn)
                                            <th>User</th>
                                        @endif
                                        @if($enrollmentColumns->isEmpty())
                                            <th>Record</th>
                                        @else
                                            @foreach($enrollmentColumns as $column)
                                                <th>{{ $column }}</th>
                                            @endforeach
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($enrollmentRows as $row)
                                        <tr>
                                            @if($showCampusColumn)
                                                <td>{{ $row['campus'] ?? 'N/A' }}</td>
                                            @endif
                                            @if($showUserColumn)
                                                <td>{{ $row['user'] ?? 'Unassigned' }}</td>
                                            @endif
                                            @foreach($enrollmentColumns as $column)
                                                <td class="count">{{ number_format((int) ($row['counts'][$column] ?? 0)) }}</td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $enrollmentColspan }}" class="dbr-empty">No enrollments found for this date.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="dbr-section">
                        <h3 class="dbr-section-title">Enrollment + Installments</h3>
                        <div class="dbr-table-wrap">
                            <table class="table table-bordered dbr-table">
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
                                    @forelse($feeDetailRows as $row)
                                        <tr>
                                            @if($showCampusColumn)
                                                <td>{{ $row['campus'] ?? 'N/A' }}</td>
                                            @endif
                                            @if($showUserColumn)
                                                <td>{{ $row['user'] ?? 'Unassigned' }}</td>
                                            @endif
                                            <td>{{ $row['course'] }}</td>
                                            <td>{{ $row['fee_type'] }}</td>
                                            <td class="amount">{{ number_format((float) $row['amount'], 0) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 3 + $detailBaseColumns }}" class="dbr-empty">No enrollment or installment receipts found for this date.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="dbr-section">
                        <h3 class="dbr-section-title">Registration</h3>
                        <div class="dbr-table-wrap">
                            <table class="table table-bordered dbr-table">
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
                                    @forelse($registrationFeeRows as $row)
                                        <tr>
                                            @if($showCampusColumn)
                                                <td>{{ $row['campus'] ?? 'N/A' }}</td>
                                            @endif
                                            @if($showUserColumn)
                                                <td>{{ $row['user'] ?? 'Unassigned' }}</td>
                                            @endif
                                            <td>{{ $row['course'] }}</td>
                                            <td class="count">{{ number_format((int) $row['count']) }}</td>
                                            <td class="amount">{{ number_format((float) $row['amount'], 0) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 3 + $detailBaseColumns }}" class="dbr-empty">No registration fee receipts found for this date.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="dbr-section">
                        <h3 class="dbr-section-title">Coworking Space Registered</h3>
                        <div class="dbr-table-wrap">
                            <table class="table table-bordered dbr-table">
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
                                    @forelse($coworkingReceiptRows as $row)
                                        <tr>
                                            @if($showCampusColumn)
                                                <td>{{ $row['campus'] ?? 'N/A' }}</td>
                                            @endif
                                            @if($showUserColumn)
                                                <td>{{ $row['user'] ?? 'Unassigned' }}</td>
                                            @endif
                                            <td>{{ $row['space_type'] }}</td>
                                            <td>{{ $row['type'] }}</td>
                                            <td class="amount">{{ number_format((float) $row['amount'], 0) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 3 + $detailBaseColumns }}" class="dbr-empty">No coworking receipts found for this date.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="dbr-section">
                        <h3 class="dbr-section-title">Expense</h3>
                        <div class="dbr-table-wrap">
                            <table class="table table-bordered dbr-table">
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
                                    @forelse($expenseReportRows as $row)
                                        <tr>
                                            @if($showCampusColumn)
                                                <td>{{ $row['campus'] ?? 'N/A' }}</td>
                                            @endif
                                            @if($showUserColumn)
                                                <td>{{ $row['user'] ?? 'Unassigned' }}</td>
                                            @endif
                                            <td>{{ $row['expense_type'] }}</td>
                                            <td class="amount">{{ number_format((float) $row['amount'], 0) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 2 + $detailBaseColumns }}" class="dbr-empty">No expenses found for this date.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="dbr-section">
                        <h3 class="dbr-section-title">Summary</h3>
                        <div class="dbr-table-wrap">
                            <table class="table table-bordered dbr-table dbr-summary-table">
                                <thead>
                                    <tr>
                                        <th>Registrations</th>
                                        <th>Enrollment + Installments</th>
                                        <th>Coworking Space</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ number_format((float) ($summaryTotals['registrations'] ?? 0), 0) }}</td>
                                        <td>{{ number_format((float) ($summaryTotals['enrollment_installments'] ?? 0), 0) }}</td>
                                        <td>{{ number_format((float) ($summaryTotals['coworking'] ?? 0), 0) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="dbr-section">
                        <h3 class="dbr-section-title">Payment Methods</h3>
                        <div class="dbr-table-wrap">
                            <table class="table table-bordered dbr-table">
                                <thead>
                                    <tr>
                                        @if($showCampusColumn)
                                            <th>Campus</th>
                                        @endif
                                        @if($showUserColumn)
                                            <th>User</th>
                                        @endif
                                        <th>Method</th>
                                        <th>Number</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($paymentSummary as $row)
                                        <tr>
                                            @if($showCampusColumn)
                                                <td>{{ $row['campus'] ?? 'N/A' }}</td>
                                            @endif
                                            @if($showUserColumn)
                                                <td>{{ $row['user'] ?? 'Unassigned' }}</td>
                                            @endif
                                            <td>{{ $row['label'] }}</td>
                                            <td class="count">{{ number_format((int) $row['count']) }}</td>
                                            <td class="amount">{{ number_format((float) $row['amount'], 0) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 3 + $detailBaseColumns }}" class="dbr-empty">No payment records found for this date.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

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
