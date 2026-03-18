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
                <a class="btn btn-inline btn-danger-outline" href="{{ route('finance.dashboard', ['campus_id' => $filters['campus_id'] ?? null, 'from' => $filters['from'] ?? null, 'to' => $filters['to'] ?? null]) }}">
                    Back to Dashboard
                </a>
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
                <div class="col-lg-4 col-md-6">
                    <div class="expense-card tone-item">
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
        
        .finance-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
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
        }
        .expense-label { font-size: 12px;
    text-transform: uppercase;
    opacity: .88;
    text-align: center;
    margin-top: 1rem; }
        .expense-value {  margin-top: 6px;
    font-size: 24px;
    text-align: center;
    font-weight: 700;}
        .tone-total { background: #f35f62; }
        .tone-item { background: #00a8ff }
        .finance-table thead th {
            background: #eef2f7;
            color: #334155;
            font-weight: 700;
        }
    </style>
@endpush
