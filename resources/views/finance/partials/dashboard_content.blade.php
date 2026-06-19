@php
    $stats = $stats ?? [
        'total_income' => 0,
        'total_expense' => 0,
        'payables' => 0,
        'receivables' => 0,
        'net_cashflow' => 0,
    ];
    $incomeSourceChart = $incomeSourceChart ?? [];
    $expenseSourceChart = $expenseSourceChart ?? [];
    $filters = $filters ?? [
        'campus_id' => null,
        'from' => now()->startOfMonth()->toDateString(),
        'to' => now()->endOfMonth()->toDateString(),
    ];
    $campuses = $campuses ?? collect();
    $selectedCampus = $campuses->firstWhere('id', $filters['campus_id'] ?? null);
    $recentIncomeRows = $recentIncomeRows ?? collect();
    $recentExpenseRows = $recentExpenseRows ?? collect();
@endphp

<div class="finance-dashboard">
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="finance-header">
        <div>
            <h1 class="finance-title panel-title">{{ $pageTitle ?? 'Finance Dashboard' }}</h1>
            <!-- <p class="finance-subtitle">Live finance summary by campus/franchise.</p> -->
        </div>
        <div class="finance-filter-summary-wrap">
            <!-- <div class="finance-filter-summary">
                <span><strong>Campus:</strong> {{ $selectedCampus ? ($selectedCampus->code . ' - ' . $selectedCampus->name) : 'All Campuses' }}</span>
                <span><strong>From:</strong> {{ $filters['from'] ?? '' }}</span>
                <span><strong>To:</strong> {{ $filters['to'] ?? '' }}</span>
            </div> -->
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
                class="finance-kpi-link finance-stat-link"
                href="{{ route('finance.dashboard.income', ['campus_id' => $filters['campus_id'] ?? null, 'from' => $filters['from'] ?? null, 'to' => $filters['to'] ?? null]) }}"
            >
                <article class="statistic-box red finance-stat-card">
                    <div class="stat-inner">
                        <button class="stat-eye stat-eye-inline finance-stat-eye" data-target="finance-stat-income" aria-label="Show total income"><i class="fa fa-eye"></i></button>
                        <div class="number stat-number" data-value="Rs. {{ number_format((float) ($stats['total_income'] ?? 0), 0) }}" data-target="finance-stat-income" data-stat-key="totalIncome" data-format="currency" data-mask-mode="icon"></div>
                        <div class="caption mt-3">
                            <div class="caption-text">Total Income</div>
                        </div>
                    </div>
                </article>
            </a>
        </div>
        <div class="col-xl-3 col-md-6">
            <a
                class="finance-kpi-link finance-stat-link"
                href="{{ route('finance.dashboard.expense', ['campus_id' => $filters['campus_id'] ?? null, 'from' => $filters['from'] ?? null, 'to' => $filters['to'] ?? null]) }}"
            >
                <article class="statistic-box yellow finance-stat-card">
                    <div class="stat-inner">
                        <button class="stat-eye stat-eye-inline finance-stat-eye" data-target="finance-stat-expense" aria-label="Show total expense"><i class="fa fa-eye"></i></button>
                        <div class="number stat-number" data-value="Rs. {{ number_format((float) ($stats['total_expense'] ?? 0), 0) }}" data-target="finance-stat-expense" data-stat-key="totalExpense" data-format="currency" data-mask-mode="icon"></div>
                        <div class="caption mt-3">
                            <div class="caption-text">Total Expense</div>
                        </div>
                    </div>
                </article>
            </a>
        </div>
        <div class="col-xl-3 col-md-6">
            <a
                class="finance-kpi-link finance-stat-link"
                href="{{ route('finance.dashboard.payables', ['campus_id' => $filters['campus_id'] ?? null, 'from' => $filters['from'] ?? null, 'to' => $filters['to'] ?? null]) }}"
            >
                <article class="statistic-box purple finance-stat-card">
                    <div class="stat-inner">
                        <button class="stat-eye stat-eye-inline finance-stat-eye" data-target="finance-stat-payables" aria-label="Show payables"><i class="fa fa-eye"></i></button>
                        <div class="number stat-number" data-value="Rs. {{ number_format((float) ($stats['payables'] ?? 0), 0) }}" data-target="finance-stat-payables" data-stat-key="payables" data-format="currency" data-mask-mode="icon"></div>
                        <div class="caption mt-3">
                            <div class="caption-text">Payables</div>
                        </div>
                    </div>
                </article>
            </a>
        </div>
        <div class="col-xl-3 col-md-6">
            <a
                class="finance-kpi-link finance-stat-link"
                href="{{ route('finance.dashboard.receivables', ['campus_id' => $filters['campus_id'] ?? null, 'from' => $filters['from'] ?? null, 'to' => $filters['to'] ?? null]) }}"
            >
                <article class="statistic-box green finance-stat-card">
                    <div class="stat-inner">
                        <button class="stat-eye stat-eye-inline finance-stat-eye" data-target="finance-stat-receivables" aria-label="Show receivables"><i class="fa fa-eye"></i></button>
                        <div class="number stat-number" data-value="Rs. {{ number_format((float) ($stats['receivables'] ?? 0), 0) }}" data-target="finance-stat-receivables" data-stat-key="receivables" data-format="currency" data-mask-mode="icon"></div>
                        <div class="caption mt-3">
                            <div class="caption-text">Receivables</div>
                        </div>
                    </div>
                </article>
            </a>
        </div>
        <!-- <div class="col-xl-4 col-md-6">
            <a
                class="finance-kpi finance-kpi-link kpi-cash"
                href="{{ route('finance.dashboard.netcashflow', ['campus_id' => $filters['campus_id'] ?? null, 'from' => $filters['from'] ?? null, 'to' => $filters['to'] ?? null]) }}"
            >
            <div class="kpi-value">Rs. {{ number_format((float) ($stats['net_cashflow'] ?? 0), 0) }}</div>
                <div class="kpi-label">Net Cashflow</div>
            </a>
        </div> -->
    </div>

    <div class="row finance-charts">
        <div class="col-lg-6">
            <section class="box-typical box-typical-dashboard panel panel-default finance-card">
                <header class="box-typical-header panel-heading month-chart-header">
                    <div class="month-chart-header-content">
                        <div class="month-chart-header-wrap">
                            <h3 class="panel-title month-chart-header-title">
                                <h3 class="month-chart-header-label">Income</h3>
                            </h3>
                        </div>
                        <div class="month-chart-header-actions">
                            <button type="button" class="action-btn dashboard-panel-action" data-action="refresh" aria-label="Refresh recent income chart">
                                <i class="font-icon font-icon-refresh"></i>
                            </button>
                            <button type="button" class="action-btn dashboard-panel-action" data-action="collapse" aria-label="Collapse recent income chart" aria-expanded="true">
                                <i class="font-icon font-icon-minus"></i>
                            </button>
                            <button type="button" class="action-btn dashboard-panel-action" data-action="fullscreen" aria-label="Maximize recent income chart">
                                <i class="font-icon font-icon-expand"></i>
                            </button>
                        </div>
                    </div>
                </header>
                <div class="box-typical-body panel-body">
                    <div id="finance-income-chart"></div>
                </div>
            </section>
        </div>
        <div class="col-lg-6">
            <section class="box-typical box-typical-dashboard panel panel-default finance-card">
                <header class="box-typical-header panel-heading month-chart-header">
                    <div class="month-chart-header-content">
                        <div class="month-chart-header-wrap">
                            <h3 class="panel-title month-chart-header-title">
                                <h3 class="month-chart-header-label">Expense</h3>
                            </h3>
                        </div>
                        <div class="month-chart-header-actions">
                            <button type="button" class="action-btn dashboard-panel-action" data-action="refresh" aria-label="Refresh recent expense chart">
                                <i class="font-icon font-icon-refresh"></i>
                            </button>
                            <button type="button" class="action-btn dashboard-panel-action" data-action="collapse" aria-label="Collapse recent expense chart" aria-expanded="true">
                                <i class="font-icon font-icon-minus"></i>
                            </button>
                            <button type="button" class="action-btn dashboard-panel-action" data-action="fullscreen" aria-label="Maximize recent expense chart">
                                <i class="font-icon font-icon-expand"></i>
                            </button>
                        </div>
                    </div>
                </header>
                <div class="box-typical-body panel-body">
                    <div id="finance-expense-chart"></div>
                </div>
            </section>
        </div>
        <!-- <div class="col-lg-6">
            <section class="box-typical box-typical-dashboard panel panel-default finance-card">
                <header class="box-typical-header panel-heading">
                    <h3 class="panel-title">Receivables</h3>
                </header>
                <div class="box-typical-body panel-body">
                    <div id="finance-receivables-chart"></div>
                </div>
            </section>
        </div>
        <div class="col-lg-6">
            <section class="box-typical box-typical-dashboard panel panel-default finance-card">
                <header class="box-typical-header panel-heading">
                    <h3 class="panel-title">Payables</h3>
                </header>
                <div class="box-typical-body panel-body">
                    <div id="finance-payables-chart"></div>
                </div>
            </section>
        </div> -->
    </div>

    <!-- <div class="row finance-charts">
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
    </div> -->

    <div class="row finance-charts">
        <div class="col-lg-6">
            <section class="box-typical box-typical-dashboard panel panel-default finance-card finance-table-card">
                <header class="box-typical-header panel-heading month-chart-header">
                    <div class="month-chart-header-content">
                        <div class="month-chart-header-wrap">
                            <h3 class="panel-title month-chart-header-title">
                                <h3 class="month-chart-header-label">Recent Income</h3>
                            </h3>
                        </div>
                        <div class="month-chart-header-actions">
                            <button type="button" class="action-btn dashboard-panel-action" data-action="refresh" aria-label="Refresh recent income table">
                                <i class="font-icon font-icon-refresh"></i>
                            </button>
                            <button type="button" class="action-btn dashboard-panel-action" data-action="collapse" aria-label="Collapse recent income table" aria-expanded="true">
                                <i class="font-icon font-icon-minus"></i>
                            </button>
                            <button type="button" class="action-btn dashboard-panel-action" data-action="fullscreen" aria-label="Maximize recent income table">
                                <i class="font-icon font-icon-expand"></i>
                            </button>
                        </div>
                    </div>
                </header>
                <div class="box-typical-body panel-body table-responsive">
                    <table class="table table-bordered finance-table finance-dashboard-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Name</th>
                                <th>Campus</th>
                                <th>Date</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentIncomeRows as $row)
                                <tr>
                                    <td>
                                        <div>{{ $row['type'] ?? 'Income' }}</div>
                                        <small class="text-muted">{{ $row['reference'] ?? 'N/A' }}</small>
                                    </td>
                                    <td>{{ $row['name'] ?? 'N/A' }}</td>
                                    <td>{{ $row['campus'] ?? 'N/A' }}</td>
                                    <td>{{ $row['date'] ?? 'N/A' }}</td>
                                    <td>Rs. {{ number_format((float) ($row['amount'] ?? 0), 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No recent income found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
        <div class="col-lg-6">
            <section class="box-typical box-typical-dashboard panel panel-default finance-card finance-table-card">
                <header class="box-typical-header panel-heading month-chart-header">
                    <div class="month-chart-header-content">
                        <div class="month-chart-header-wrap">
                            <h3 class="panel-title month-chart-header-title">
                                <h3 class="month-chart-header-label">Recent Expense</h3>
                            </h3>
                        </div>
                        <div class="month-chart-header-actions">
                            <button type="button" class="action-btn dashboard-panel-action" data-action="refresh" aria-label="Refresh recent expense table">
                                <i class="font-icon font-icon-refresh"></i>
                            </button>
                            <button type="button" class="action-btn dashboard-panel-action" data-action="collapse" aria-label="Collapse recent expense table" aria-expanded="true">
                                <i class="font-icon font-icon-minus"></i>
                            </button>
                            <button type="button" class="action-btn dashboard-panel-action" data-action="fullscreen" aria-label="Maximize recent expense table">
                                <i class="font-icon font-icon-expand"></i>
                            </button>
                        </div>
                    </div>
                </header>
                <div class="box-typical-body panel-body table-responsive">
                    <table class="table table-bordered finance-table finance-dashboard-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Payee</th>
                                <th>Campus</th>
                                <th>Date</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentExpenseRows as $row)
                                <tr>
                                    <td>
                                        <div>{{ $row['type'] ?? 'Expense' }}</div>
                                        <small class="text-muted">{{ $row['reference'] ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $row['name'] ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $row['status'] ?? 'N/A' }}</small>
                                    </td>
                                    <td>{{ $row['campus'] ?? 'N/A' }}</td>
                                    <td>{{ $row['date'] ?? 'N/A' }}</td>
                                    <td>Rs. {{ number_format((float) ($row['amount'] ?? 0), 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No recent expense found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/c3/0.7.20/c3.min.css">
    <style>
       

        .finance-dashboard { padding:19px 20px;background-color: white; }
        .finance-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .kpi-label {
                font-size: 12px;
    text-transform: uppercase;
    opacity: .88;
    text-align: center;
    margin-top: 1rem;
        }
        .kpi-value {
           /* margin-top: 6px; */
    font-size: 24px;
    text-align: center;
    font-weight: 700;
        }
        .finance-title { 
            margin: 0 0 4px;  
               font-size: 22px !important;
    font-weight: 500 !important; }
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
        .finance-stat-link {
            display: block;
            text-decoration: none;
            color: inherit;
            margin-bottom: 12px;
        }
        .finance-stat-card {
            height: 150px;
            margin-bottom: 0;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
            transition: transform 0.12s ease, box-shadow 0.12s ease, filter 0.12s ease;
        }
        .finance-stat-card .stat-inner {
            min-height: 170px;
        }
        .finance-stat-card .stat-eye {
            position: absolute;
            right: 12px;
            top: 12px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: #fff;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 2;
        }
        .finance-stat-card .stat-eye:hover {
            background: rgba(255, 255, 255, 0.35);
        }
        .finance-stat-card .stat-eye-inline,
        .finance-stat-card .stat-eye-inline.is-revealed {
            left: auto !important;
            right: 12px !important;
            top: 12px !important;
            transform: none !important;
            width: 32px;
            height: 32px;
        }
        .finance-kpi-link {
            display: block;
            /* color: inherit; */
            text-decoration: none;
            transition: transform 0.12s ease, box-shadow 0.12s ease, filter 0.12s ease;
        }
        .finance-kpi-link:hover,
        .finance-kpi-link:focus,
        .finance-stat-link:hover .finance-stat-card,
        .finance-stat-link:focus .finance-stat-card {
            /* color: inherit; */
            text-decoration: none;
            transform: translateY(-1px);
            filter: brightness(1.02);
        }
        .finance-stat-card .number {
            font-size: 26px;
            line-height: 1.15;
            word-break: break-word;
            min-height: 44px;
        }
        .finance-stat-card .caption {
            margin-top: 18px !important;
        }
        .finance-stat-card .caption-text {
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .finance-card .panel-heading { padding: 8px 14px; }
        .finance-card .panel-title { font-weight: 700; }
        .finance-card .panel-body { padding: 12px 14px 16px; }
        .month-chart-header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            /* gap: auto !important; */
            flex-wrap: wrap;
            width: 100%;
        }
        .month-chart-header-wrap {
            min-width: 0;
            flex: 1 1 auto;
        }
        .month-chart-header-title {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
        }
        .month-chart-header-label {
            display: inline-block;
        }
        .month-chart-header-actions {
            display: inline-flex;
            align-items: center;
            margin-left: auto;
        }
        .month-chart-header-actions .action-btn {
            border: 0;
            background: transparent;
            color: #6b7c93;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border-left: 1px solid #e5e7eb;
        }
        .month-chart-header-actions .action-btn:first-child {
            border-left: 0;
        }
        /* .month-chart-header-actions .action-btn:hover,
        .month-chart-header-actions .action-btn:focus {
            color: #1d4ed8;
            background: #eff6ff;
            outline: 0;
        } */
        .dashboard-panel-action.is-active {
            color: #1d4ed8;
        }
        .dashboard-panel-action.is-spinning .font-icon {
            animation: financeSpin 0.8s linear infinite;
        }
        .finance-card.box-typical-full-screen {
            position: fixed;
            inset: 18px;
            z-index: 1060;
            background: #fff;
            overflow: auto;
        }
        body.finance-panel-fullscreen-active {
            overflow: hidden;
        }
        @keyframes financeSpin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .finance-table-card .panel-body { padding: 0; }
        .finance-dashboard-table { margin-bottom: 0; }
        .finance-dashboard-table thead th {
            background: #eef2f7;
            color: #334155;
            font-weight: 700;
        }
        .finance-dashboard-table td,
        .finance-dashboard-table th {
            vertical-align: middle;
            padding: 8px 10px;
        }
        .finance-dashboard-table tbody tr:nth-child(even) td {
            background: #fafcff;
        }
        #finance-income-source-chart,
        #finance-income-chart,
        #finance-expense-chart,
        #finance-receivables-chart,
        #finance-payables-chart,
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

            if (window.jQuery) {
                var maskedValue = '***';

                $('.finance-stat-card .stat-number').each(function () {
                    var stat = $(this);
                    var maskMode = stat.data('maskMode');
                    stat.data('hidden', true);
                    stat.text(maskMode === 'icon' ? '' : maskedValue);
                });

                $(document).off('click.financeStatEye', '.finance-stat-eye');
                $(document).on('click.financeStatEye', '.finance-stat-eye', function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    var eye = $(this);
                    var target = eye.data('target');
                    var stat = $('.finance-stat-card .stat-number[data-target="' + target + '"]').first();

                    if (!stat.length) {
                        return;
                    }

                    var hidden = stat.data('hidden') !== false;
                    var maskMode = stat.data('maskMode');

                    stat.text(hidden ? stat.data('value') : (maskMode === 'icon' ? '' : maskedValue));
                    stat.data('hidden', !hidden);

                    if (maskMode === 'icon') {
                        eye.toggleClass('is-revealed', hidden);
                    }

                    eye.find('i').toggleClass('fa-eye fa-eye-slash');
                });

                $(document).off('click.financeDashboardPanel', '.dashboard-panel-action');
                $(document).on('click.financeDashboardPanel', '.dashboard-panel-action', function (event) {
                    event.preventDefault();

                    var button = $(this);
                    var action = button.data('action');
                    var panel = button.closest('.box-typical');
                    var body = panel.find('.box-typical-body').first();
                    var icon = button.find('.font-icon').first();

                    if (!panel.length) {
                        return;
                    }

                    if (action === 'refresh') {
                        button.addClass('is-spinning');
                        setTimeout(function () {
                            window.location.reload();
                        }, 250);
                        return;
                    }

                    if (action === 'collapse') {
                        if (!body.length) {
                            return;
                        }

                        if (panel.hasClass('box-typical-collapsed')) {
                            panel.removeClass('box-typical-collapsed');
                            body.stop(true, true).slideDown(150);
                            button.attr('aria-expanded', 'true');
                            if (icon.length) {
                                icon.removeClass('font-icon-plus').addClass('font-icon-minus');
                            }
                        } else {
                            panel.addClass('box-typical-collapsed');
                            body.stop(true, true).slideUp(150);
                            button.attr('aria-expanded', 'false');
                            if (icon.length) {
                                icon.removeClass('font-icon-minus').addClass('font-icon-plus');
                            }
                        }
                        return;
                    }

                    if (action === 'fullscreen') {
                        var wasFullScreen = panel.hasClass('box-typical-full-screen');
                        $('.finance-card.box-typical-full-screen').not(panel).removeClass('box-typical-full-screen');
                        $('.dashboard-panel-action[data-action="fullscreen"]').not(button).removeClass('is-active');
                        panel.toggleClass('box-typical-full-screen');
                        button.toggleClass('is-active', !wasFullScreen);
                        $('body').toggleClass('finance-panel-fullscreen-active', $('.finance-card.box-typical-full-screen').length > 0);
                    }
                });
            }

            if (!window.c3) {
                return;
            }

            var stats = @json($stats);
            var incomeSourceChart = @json($incomeSourceChart);
            var expenseSourceChart = @json($expenseSourceChart);

            function buildSourceColumns(rows, fallbackLabel) {
                var columns = (rows || []).map(function (row) {
                    return [row.label, Number(row.amount || 0)];
                }).filter(function (row) {
                    return row[1] > 0;
                });

                if (columns.length === 0) {
                    return [[fallbackLabel, 1]];
                }

                return columns;
            }

            c3.generate({
                bindto: '#finance-income-chart',
                data: {
                    columns: buildSourceColumns(incomeSourceChart, 'No Income'),
                    type: 'pie',
                    colors: {
                        'Admission Fee': '#f35f62',
                        'Registration Fee': '#16a34a',
                        'Coworking Fee': '#0ea5e9',
                        'Franchise Royalty': '#dc2626',
                        'Invoice Collections': '#f59e0b',
                        'No Income': '#9ca3af'
                    }
                }
            });

            c3.generate({
                bindto: '#finance-expense-chart',
                data: {
                    columns: buildSourceColumns(expenseSourceChart, 'No Expense'),
                    type: 'pie',
                    colors: {
                        'Rent': '#475569',
                        'Utility': '#0ea5e9',
                        'Marketing': '#f97316',
                        'Asset': '#8b5cf6',
                        'Payroll': '#ef4444',
                        'General': '#22c55e',
                        'Reversed': '#e11d48',
                        'No Expense': '#9ca3af'
                    }
                }
            });

            c3.generate({
                bindto: '#finance-receivables-chart',
                data: {
                    columns: [
                        ['Receivables', Number(stats.receivables || 0)]
                    ],
                    type: 'donut',
                    colors: {
                        Receivables: '#3b82f6'
                    }
                },
                donut: { title: 'Receivables' }
            });

            c3.generate({
                bindto: '#finance-payables-chart',
                data: {
                    columns: [
                        ['Payables', Number(stats.payables || 0)]
                    ],
                    type: 'donut',
                    colors: {
                        Payables: '#f97316'
                    }
                },
                donut: { title: 'Payables' }
            });
        });
    </script>
@endpush
