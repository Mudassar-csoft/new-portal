@extends('layouts.theme')

@section('title', 'Net Cashflow Details')

@section('content')
    @php
        $filters = $filters ?? [
            'campus_id' => null,
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->endOfMonth()->toDateString(),
        ];
        $summary = $summary ?? [
            'total_income' => 0,
            'total_expense' => 0,
            'net_cashflow' => 0,
        ];
        $incomeComponents = $incomeComponents ?? [];
        $expenseComponents = $expenseComponents ?? [];
        $campuses = $campuses ?? collect();
    @endphp

    <div class="finance-shell">
        <section class="box-typical box-typical-dashboard panel panel-default finance-card">
            <header class="box-typical-header panel-heading finance-header">
                <h3 class="panel-title">Net Cashflow Detail</h3>
                <a class="btn btn-inline btn-secondary-outline" href="{{ route('finance.dashboard', ['campus_id' => $filters['campus_id'] ?? null, 'from' => $filters['from'] ?? null, 'to' => $filters['to'] ?? null]) }}">
                    Back to Dashboard
                </a>
            </header>
            <div class="box-typical-body panel-body">
                <form method="GET" action="{{ route('finance.dashboard.netcashflow') }}">
                    <div class="form-row">
                        <div class="form-group custom-col-4">
                            <label>Campus</label>
                            <select name="campus_id" class="form-control">
                                <option value="">All Campuses</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected(($filters['campus_id'] ?? null) == $campus->id)>
                                        {{ $campus->code }} - {{ $campus->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group custom-col-3">
                            <label>From</label>
                            <input type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
                        </div>
                        <div class="form-group custom-col-3">
                            <label>To</label>
                            <input type="date" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
                        </div>
                        <div class="form-group custom-col-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-inline btn-primary-outline mr-2">Apply</button>
                            <a href="{{ route('finance.dashboard.netcashflow') }}" class="btn btn-inline btn-secondary-outline">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <div class="row finance-summary-row">
            <div class="custom-col-4">
                <div class="cash-card tone-income">
                    <div class="cash-label">Total Income</div>
                    <div class="cash-value">Rs. {{ number_format((float) ($summary['total_income'] ?? 0), 0) }}</div>
                </div>
            </div>
            <div class="custom-col-4">
                <div class="cash-card tone-expense">
                    <div class="cash-label">Total Expense</div>
                    <div class="cash-value">Rs. {{ number_format((float) ($summary['total_expense'] ?? 0), 0) }}</div>
                </div>
            </div>
            <div class="custom-col-4">
                <div class="cash-card tone-net {{ ((float) ($summary['net_cashflow'] ?? 0)) < 0 ? 'is-negative' : '' }}">
                    <div class="cash-label">Net Cashflow</div>
                    <div class="cash-value">Rs. {{ number_format((float) ($summary['net_cashflow'] ?? 0), 0) }}</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <section class="box-typical box-typical-dashboard panel panel-default finance-card">
                    <header class="box-typical-header panel-heading">
                        <h3 class="panel-title">Income Components</h3>
                    </header>
                    <div class="box-typical-body panel-body table-responsive">
                        <table class="table table-bordered finance-table">
                            <thead>
                                <tr>
                                    <th>Source</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($incomeComponents as $item)
                                    <tr>
                                        <td>{{ $item['label'] ?? 'N/A' }}</td>
                                        <td>Rs. {{ number_format((float) ($item['amount'] ?? 0), 0) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted">No income components found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
            <div class="col-lg-6">
                <section class="box-typical box-typical-dashboard panel panel-default finance-card">
                    <header class="box-typical-header panel-heading">
                        <h3 class="panel-title">Expense Components</h3>
                    </header>
                    <div class="box-typical-body panel-body table-responsive">
                        <table class="table table-bordered finance-table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenseComponents as $item)
                                    <tr>
                                        <td>{{ $item['label'] ?? 'N/A' }}</td>
                                        <td>Rs. {{ number_format((float) ($item['amount'] ?? 0), 0) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted">No expense components found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .finance-shell { padding: 8px 0 16px; }
        .finance-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .finance-summary-row { margin: 2px 0 10px; }
        .cash-card {
            border-radius: 10px;
            padding: 12px 14px;
            color: #fff;
            margin-bottom: 12px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
        }
        .cash-label {
            font-size: 12px;
            text-transform: uppercase;
            opacity: 0.88;
        }
        .cash-value {
            margin-top: 6px;
            font-size: 22px;
            font-weight: 700;
        }
        .tone-income { background: linear-gradient(135deg, #16a34a, #15803d); }
        .tone-expense { background: linear-gradient(135deg, #dc2626, #b91c1c); }
        .tone-net { background: linear-gradient(135deg, #0f766e, #115e59); }
        .tone-net.is-negative { background: linear-gradient(135deg, #7f1d1d, #450a0a); }
        .finance-table thead th {
            background: #eef2f7;
            color: #334155;
            font-weight: 700;
        }
    </style>
@endpush
