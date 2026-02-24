@php
    $stats = $stats ?? [
        'total_income' => 0,
        'total_expense' => 0,
        'payables' => 0,
        'receivables' => 0,
        'net_cashflow' => 0,
    ];
    $incomeMix = $incomeMix ?? [];
    $expenseMix = $expenseMix ?? [];
    $filters = $filters ?? [
        'campus_id' => null,
        'from' => now()->startOfMonth()->toDateString(),
        'to' => now()->endOfMonth()->toDateString(),
    ];
    $campuses = $campuses ?? collect();
    $selectedCampus = $campuses->firstWhere('id', $filters['campus_id'] ?? null);
@endphp

<div class="finance-dashboard">
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="finance-header">
        <div>
            <h1 class="finance-title form-label">{{ $pageTitle ?? 'Finance Dashboard' }}</h1>
            <p class="finance-subtitle">Live finance summary by campus/franchise.</p>
        </div>
        <div class="finance-filter-summary-wrap">
            <div class="finance-filter-summary">
                <span><strong>Campus:</strong> {{ $selectedCampus ? ($selectedCampus->code . ' - ' . $selectedCampus->name) : 'All Campuses' }}</span>
                <span><strong>From:</strong> {{ $filters['from'] ?? '' }}</span>
                <span><strong>To:</strong> {{ $filters['to'] ?? '' }}</span>
            </div>
            <button class="btn btn-primary" type="button" id="finance-filter-trigger">Filter</button>
        </div>
    </div>

    <div class="finance-filter-modal" id="finance-filter-modal" aria-hidden="true">
        <div class="finance-filter-backdrop" data-close-filter-modal></div>
        <div class="finance-filter-dialog" role="dialog" aria-modal="true" aria-labelledby="finance-filter-title">
            <div class="finance-filter-dialog-header">
                <h2 id="finance-filter-title">Filter Dashboard</h2>
                <button type="button" class="finance-filter-close" data-close-filter-modal aria-label="Close filter popup">&times;</button>
            </div>
            <form class="finance-filter-form" method="GET" action="{{ route('finance.dashboard') }}">
                <div class="finance-filter-field">
                     <label class="form-label required" for="filter-campus-id">Campus</label>
                    <select class="form-control" id="filter-campus-id" name="campus_id">
                        <option value="">All Campuses</option>
                        @foreach($campuses as $campus)
                            <option value="{{ $campus->id }}" @selected(($filters['campus_id'] ?? null) == $campus->id)>
                                {{ $campus->code }} - {{ $campus->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="finance-filter-field">
                     <label class="form-label required" for="filter-from">From</label>
                    <input type="date" class="form-control" id="filter-from" name="from" value="{{ $filters['from'] ?? '' }}">
                </div>
                <div class="finance-filter-field">
                     <label class="form-label required" for="filter-to">To</label>
                    <input type="date" class="form-control" id="filter-to" name="to" value="{{ $filters['to'] ?? '' }}">
                </div>
                <div class="finance-filter-actions">
                    <a class="btn btn-secondary" href="{{ route('finance.dashboard') }}">Reset</a>
                    <button class="btn btn-primary" type="submit">Apply</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row finance-kpis">
        <div class="col-xl-3 col-md-6">
            <a
                class="finance-kpi finance-kpi-link kpi-income"
                href="{{ route('finance.dashboard.income', ['campus_id' => $filters['campus_id'] ?? null, 'from' => $filters['from'] ?? null, 'to' => $filters['to'] ?? null]) }}"
            >
                <div class="kpi-label">Total Income</div>
                <div class="kpi-value">Rs. {{ number_format((float) ($stats['total_income'] ?? 0), 0) }}</div>
            </a>
        </div>
        <div class="col-xl-3 col-md-6">
            <a
                class="finance-kpi finance-kpi-link kpi-expense"
                href="{{ route('finance.dashboard.expense', ['campus_id' => $filters['campus_id'] ?? null, 'from' => $filters['from'] ?? null, 'to' => $filters['to'] ?? null]) }}"
            >
                <div class="kpi-label">Total Expense</div>
                <div class="kpi-value">Rs. {{ number_format((float) ($stats['total_expense'] ?? 0), 0) }}</div>
            </a>
        </div>
        <div class="col-xl-2 col-md-6">
            <a
                class="finance-kpi finance-kpi-link kpi-payable"
                href="{{ route('finance.dashboard.payables', ['campus_id' => $filters['campus_id'] ?? null, 'from' => $filters['from'] ?? null, 'to' => $filters['to'] ?? null]) }}"
            >
                <div class="kpi-label">Payables</div>
                <div class="kpi-value">Rs. {{ number_format((float) ($stats['payables'] ?? 0), 0) }}</div>
            </a>
        </div>
        <div class="col-xl-2 col-md-6">
            <a
                class="finance-kpi finance-kpi-link kpi-receivable"
                href="{{ route('finance.dashboard.receivables', ['campus_id' => $filters['campus_id'] ?? null, 'from' => $filters['from'] ?? null, 'to' => $filters['to'] ?? null]) }}"
            >
                <div class="kpi-label">Receivables</div>
                <div class="kpi-value">Rs. {{ number_format((float) ($stats['receivables'] ?? 0), 0) }}</div>
            </a>
        </div>
        <div class="col-xl-2 col-md-6">
            <a
                class="finance-kpi finance-kpi-link kpi-cash"
                href="{{ route('finance.dashboard.netcashflow', ['campus_id' => $filters['campus_id'] ?? null, 'from' => $filters['from'] ?? null, 'to' => $filters['to'] ?? null]) }}"
            >
                <div class="kpi-label">Net Cashflow</div>
                <div class="kpi-value">Rs. {{ number_format((float) ($stats['net_cashflow'] ?? 0), 0) }}</div>
            </a>
        </div>
    </div>

    <div class="row finance-charts">
        <div class="col-lg-6">
            <section class="box-typical box-typical-dashboard panel panel-default finance-card">
                <header class="box-typical-header panel-heading">
                    <h3 class="panel-title">Income vs Expense</h3>
                </header>
                <div class="box-typical-body panel-body">
                    <div id="finance-income-expense-chart"></div>
                </div>
            </section>
        </div>
        <div class="col-lg-6">
            <section class="box-typical box-typical-dashboard panel panel-default finance-card">
                <header class="box-typical-header panel-heading">
                    <h3 class="panel-title">Receivables vs Payables</h3>
                </header>
                <div class="box-typical-body panel-body">
                    <div id="finance-receivables-payables-chart"></div>
                </div>
            </section>
        </div>
    </div>

    <div class="row finance-charts">
        <div class="col-lg-6">
            <section class="box-typical box-typical-dashboard panel panel-default finance-card">
                <header class="box-typical-header panel-heading">
                    <h3 class="panel-title">Income Sources</h3>
                </header>
                <div class="box-typical-body panel-body">
                    <div id="finance-income-source-chart"></div>
                </div>
            </section>
        </div>
        <div class="col-lg-6">
            <section class="box-typical box-typical-dashboard panel panel-default finance-card">
                <header class="box-typical-header panel-heading">
                    <h3 class="panel-title">Expense Mix</h3>
                </header>
                <div class="box-typical-body panel-body">
                    <div id="finance-expense-mix-chart"></div>
                </div>
            </section>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/c3/0.7.20/c3.min.css">
    <style>
         * {
    font-family: 'Proxima Nova', sans-serif !important;
    font-size: 12px !important; 
    margin: 0;
    padding: 0;
    
}

.form-label{
    font-size: 11px;
    font-weight: 600 ;
    color: #343434;
    text-transform: uppercase;
    margin-bottom: 3px;
    
}

body, button, html, input, select, textarea {
    color: #343434;
    height: 32px;
    font-family: 'Proxima Nova', sans-serif;
    line-height: 1.4;
    text-rendering: optimizeLegibility;
    -moz-osx-font-smoothing: grayscale;
    -webkit-font-smoothing: antialiased;
    -moz-font-smoothing: antialiased;
    -o-font-smoothing: antialiased;
}
        .finance-dashboard { padding: 6px 0 16px; }
        .finance-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .finance-title { margin: 0 0 4px; font-weight: 700; color: #2f3b52; }
        .finance-subtitle { margin: 0; color: #6c7a89; }
        .finance-filter-summary-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        .finance-filter-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 6px 12px;
            font-size: 12px;
            color: #6c7a89;
        }
        .finance-filter-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1050;
            padding: 14px;
        }
        .finance-filter-modal.is-open { display: flex; }
        .finance-filter-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
        }
        .finance-filter-dialog {
            position: relative;
            width: min(460px, 100%);
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.3);
            padding: 14px;
            z-index: 1;
        }
        .finance-filter-dialog-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .finance-filter-dialog-header h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #2f3b52;
        }
        .finance-filter-close {
            border: 0;
            background: transparent;
            font-size: 24px;
            line-height: 1;
            color: #6c7a89;
            padding: 0 4px;
            cursor: pointer;
        }
        .finance-filter-form { display: grid; gap: 10px; }
        .finance-filter-field { display: grid; gap: 4px; }
        .finance-filter-field label {
            margin: 0;
            font-size: 12px;
            font-weight: 600;
            color: #4b5563;
        }
        .finance-filter-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 6px;
        }
        body.finance-filter-open { overflow: hidden; }
        .finance-kpis { margin-bottom: 14px; }
        .finance-kpi {
            border-radius: 10px;
            color: #fff;
            padding: 12px 14px;
            margin-bottom: 12px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
        }
        .finance-kpi-link {
            display: block;
            color: inherit;
            text-decoration: none;
            transition: transform 0.12s ease, box-shadow 0.12s ease, filter 0.12s ease;
        }
        .finance-kpi-link:hover,
        .finance-kpi-link:focus {
            color: inherit;
            text-decoration: none;
            transform: translateY(-1px);
            filter: brightness(1.02);
        }
        .finance-kpi .kpi-label { font-size: 12px; text-transform: uppercase; opacity: 0.88; }
        .finance-kpi .kpi-value { font-size: 20px; font-weight: 700; margin-top: 4px; }
        .kpi-income { background: linear-gradient(135deg, #22c55e, #16a34a); }
        .kpi-expense { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .kpi-payable { background: linear-gradient(135deg, #f97316, #ea580c); }
        .kpi-receivable { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .kpi-cash { background: linear-gradient(135deg, #14b8a6, #0f766e); }
        .finance-card .panel-heading { padding: 8px 14px; }
        .finance-card .panel-title { font-weight: 700; }
        .finance-card .panel-body { padding: 12px 14px 16px; }
        #finance-income-source-chart,
        #finance-income-expense-chart,
        #finance-receivables-payables-chart,
        #finance-expense-mix-chart {
            height: 260px;
        }
        @media (max-width: 576px) {
            .finance-filter-summary-wrap { width: 100%; justify-content: space-between; }
            .finance-filter-summary { width: 100%; }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/5.16.0/d3.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/c3/0.7.20/c3.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var filterModal = document.getElementById('finance-filter-modal');
            var filterTrigger = document.getElementById('finance-filter-trigger');

            if (filterModal && filterTrigger) {
                var closeTargets = filterModal.querySelectorAll('[data-close-filter-modal]');

                var openFilterModal = function () {
                    filterModal.classList.add('is-open');
                    filterModal.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('finance-filter-open');
                };

                var closeFilterModal = function () {
                    filterModal.classList.remove('is-open');
                    filterModal.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('finance-filter-open');
                };

                filterTrigger.addEventListener('click', openFilterModal);

                closeTargets.forEach(function (target) {
                    target.addEventListener('click', closeFilterModal);
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && filterModal.classList.contains('is-open')) {
                        closeFilterModal();
                    }
                });
            }

            if (!window.c3) {
                return;
            }

            var stats = @json($stats);
            var incomeMix = @json($incomeMix);
            var expenseMix = @json($expenseMix);

            c3.generate({
                bindto: '#finance-income-expense-chart',
                data: {
                    columns: [
                        ['Income', Number(stats.total_income || 0)],
                        ['Expense', Number(stats.total_expense || 0)]
                    ],
                    type: 'bar',
                    colors: {
                        Income: '#22c55e',
                        Expense: '#ef4444'
                    }
                },
                axis: {
                    x: {
                        type: 'category',
                        categories: ['Selected Period']
                    },
                    y: {
                        tick: { format: d3.format(',') }
                    }
                },
                legend: { position: 'right' },
                bar: { width: { ratio: 0.4 } }
            });

            c3.generate({
                bindto: '#finance-receivables-payables-chart',
                data: {
                    columns: [
                        ['Receivables', Number(stats.receivables || 0)],
                        ['Payables', Number(stats.payables || 0)]
                    ],
                    type: 'donut',
                    colors: {
                        Receivables: '#3b82f6',
                        Payables: '#f97316'
                    }
                },
                donut: { title: 'Open' }
            });

            var incomeSourceColumns = [
                ['Admission Fee', Number(incomeMix.admission_fee || 0)],
                ['Coworking Fee', Number(incomeMix.coworking_fee || 0)],
                ['Franchise Royalty', Number(incomeMix.franchise_royalty || 0)],
                ['Other Income', Number(incomeMix.other_income || 0)]
            ].filter(function (row) { return row[1] > 0; });

            if (incomeSourceColumns.length === 0) {
                incomeSourceColumns = [['No Data', 1]];
            }

            c3.generate({
                bindto: '#finance-income-source-chart',
                data: {
                    columns: incomeSourceColumns,
                    type: 'donut',
                    colors: {
                        'Admission Fee': '#16a34a',
                        'Coworking Fee': '#0ea5e9',
                        'Franchise Royalty': '#dc2626',
                        'Other Income': '#f59e0b',
                        'No Data': '#9ca3af'
                    }
                },
                donut: { title: 'Income' }
            });

            var mixColumns = Object.keys(expenseMix || {}).map(function (key) {
                return [key.charAt(0).toUpperCase() + key.slice(1), Number(expenseMix[key] || 0)];
            }).filter(function (row) { return row[1] > 0; });

            if (mixColumns.length === 0) {
                mixColumns = [['No Data', 1]];
            }

            c3.generate({
                bindto: '#finance-expense-mix-chart',
                data: {
                    columns: mixColumns,
                    type: 'pie'
                }
            });
        });
    </script>
@endpush
