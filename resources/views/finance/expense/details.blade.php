@extends('layouts.theme')

@section('title', 'Expense Details')

@section('content')
    @php
        $filters = $filters ?? [
            'campus_id' => null,
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->endOfMonth()->toDateString(),
        ];
        $summary = $summary ?? ['total_expense' => 0];
        $components = $components ?? [];
        $expenses = $expenses ?? collect();
        $campuses = $campuses ?? collect();
    @endphp

    <div class="finance-shell">
        <section class="box-typical box-typical-dashboard panel panel-default finance-card">
            <header class="box-typical-header panel-heading finance-header">
                <h3 class="panel-title">Expense Detail</h3>
                <!-- <a class="btn btn-inline btn-danger-outline" href="{{ route('finance.dashboard', ['campus_id' => $filters['campus_id'] ?? null, 'from' => $filters['from'] ?? null, 'to' => $filters['to'] ?? null]) }}">
                    Back to Dashboard
                </a> -->
            </header>
            <div class="box-typical-body panel-body">
                <form method="GET" action="{{ route('finance.dashboard.expense') }}">
                    <div class="form-row">
                        <div class="form-group col-lg-3 col-md-6">
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
                        <div class="form-group col-lg-3 col-md-6">
                            <label>From</label>
                            <input type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label>To</label>
                            <input type="date" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
                        </div>
                        <div class="form-group custom-col-2 d-flex align-items-end mt-3 pt-1">
                            <button type="submit" class="btn btn-inline btn-primary-outline mr-2">Apply</button>
                            <a href="{{ route('finance.dashboard.expense') }}" class="btn btn-inline btn-danger-outline">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <div class="row finance-summary-row">
            <div class="col-lg-4 col-md-6">
                <div class="expense-card tone-total">
                    <div class="expense-value">Rs. {{ number_format((float) ($summary['total_expense'] ?? 0), 0) }}</div>
                    <div class="expense-label">Total Expense</div>
                </div>
            </div>
            @foreach($components as $component)
                @php
                    $toneClass = match (strtolower((string) ($component['label'] ?? ''))) {
                        'rent' => 'tone-rent',
                        'utility' => 'tone-utility',
                        'marketing' => 'tone-marketing',
                        'asset' => 'tone-asset',
                        'payroll' => 'tone-payroll',
                        'general' => 'tone-general',
                        'reversed' => 'tone-reversed',
                        default => 'tone-item',
                    };
                @endphp
                <div class="col-lg-4 col-md-6">
                    <div class="expense-card {{ $toneClass }}">
                        <div class="expense-value">Rs. {{ number_format((float) ($component['amount'] ?? 0), 0) }}</div>
                        <div class="expense-label">{{ $component['label'] ?? 'Component' }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <section class="box-typical box-typical-dashboard panel panel-default finance-card">
            <header class="box-typical-header panel-heading">
                <h3 class="panel-title">Expense Transactions (Approved / Paid / Reversed)</h3>
            </header>
            <div class="box-typical-body panel-body table-responsive">
                <table class="table table-bordered finance-table">
                    <thead>
                        <tr>
                            <th>Voucher</th>
                            <th>Category</th>
                            <th>Expense Type</th>
                            <th>Payee</th>
                            <th>Campus</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            <tr>
                                <td>{{ $expense->voucher_no ?? 'N/A' }}</td>
                                <td>{{ ucfirst($expense->category ?? 'expense') }}</td>
                                <td>{{ $expense->expenseType->name ?? 'N/A' }}</td>
                                <td>{{ $expense->payee->full_name ?? 'N/A' }}</td>
                                <td>{{ $expense->campus->code ?? 'N/A' }}</td>
                                <td>{{ optional($expense->payment_date)->format('Y-m-d') }}</td>
                                <td>{{ ucfirst($expense->status) }}</td>
                                <td>Rs. {{ number_format((float) $expense->amount, 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">No expense transactions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --space-finance-expense-details-1: 10px;
            --color-finance-expense-details-1: #00a8ff;
            --color-finance-expense-details-2: #975ce7;
            --color-finance-expense-details-3: #f35f62;
            --typo-finance-expense-details-font-weight-1: 700;
        }

        
        .finance-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: var(--space-finance-expense-details-1);
            flex-wrap: wrap;
        }
        .finance-summary-row { margin: 2px 0 10px; padding:7px;}
        .expense-card {
            border-radius: 10px;
            height:25vh;
            padding: 12px 14px;
            color: #fff;
            margin-bottom: 12px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
            min-height: 86px;
        }
        .expense-label {
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            opacity: .88;
            text-align: center;
            margin-top: 1rem;
        }
        .expense-value {
            margin-top: 30px;
            font-size: 1.125rem;
            text-align: center;
            font-weight: var(--typo-finance-expense-details-font-weight-1);
        }
        .tone-total { background: var(--color-finance-expense-details-3); }
        .tone-item { background: var(--color-finance-expense-details-1); }
        .tone-rent { background: #fdc518; }
        .tone-utility { background: var(--color-finance-expense-details-2); }
        .tone-marketing { background: #a2cf37; }
        .tone-asset { background: #4285f4; }
        .tone-payroll { background: var(--color-finance-expense-details-1); }
        .tone-general { background: var(--color-finance-expense-details-3); }
        .tone-reversed { background: var(--color-finance-expense-details-2); }
        .finance-table thead th {
            background: #eef2f7;
            color: #334155;
            font-weight: var(--typo-finance-expense-details-font-weight-1);
        }
        @media (max-width: 760px)  {
            .expense-label {
                margin-top: 5px;
            }
            .expense-value {
                margin-top: var(--space-finance-expense-details-1);
            }
        }
    </style>
@endpush
