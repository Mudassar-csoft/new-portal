@extends('layouts.theme')

@section('title', 'Payables Details')

@section('content')
    @php
        $filters = $filters ?? [
            'campus_id' => null,
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->endOfMonth()->toDateString(),
        ];
        $summary = $summary ?? ['pending' => 0, 'approved' => 0, 'total' => 0];
        $payables = $payables ?? collect();
        $campuses = $campuses ?? collect();
    @endphp

    <div class="finance-shell">
        <section class="box-typical box-typical-dashboard panel panel-default finance-card">
            <header class="box-typical-header panel-heading finance-header">
                <h3 class="panel-title">Payables Detail</h3>
                <a
                    class="btn btn-inline btn-secondary-outline"
                    href="{{ route('finance.dashboard', ['campus_id' => $filters['campus_id'] ?? null, 'from' => $filters['from'] ?? null, 'to' => $filters['to'] ?? null]) }}"
                >
                    Back to Dashboard
                </a>
            </header>
            <div class="box-typical-body panel-body">
                <form method="GET" action="{{ route('finance.dashboard.payables') }}">
                    <input type="hidden" name="from" value="{{ $filters['from'] ?? '' }}">
                    <input type="hidden" name="to" value="{{ $filters['to'] ?? '' }}">
                    <div class="form-row" style = "gap:18px; padding-left:15px">
                        <div class="form-group col-md-4" >
                            <label class="form-label required">Campus</label>
                            <select name="campus_id" class="form-control">
                                <option value="">All Campuses</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected(($filters['campus_id'] ?? null) == $campus->id)>
                                        {{ $campus->code }} - {{ $campus->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-8 d-flex align-items-end">
                            <button type="submit" class="btn btn-inline btn-primary-outline mr-2">Apply</button>
                            <a href="{{ route('finance.dashboard.payables') }}" class="btn btn-inline btn-secondary-outline">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <div class="row finance-summary-row">
            <div class="col-md-4">
                <div class="payable-card tone-pending">
                    <div class="payable-label">Pending</div>
                    <div class="payable-value">Rs. {{ number_format((float) ($summary['pending'] ?? 0), 0) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="payable-card tone-approved">
                    <div class="payable-label">Approved Unpaid</div>
                    <div class="payable-value">Rs. {{ number_format((float) ($summary['approved'] ?? 0), 0) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="payable-card tone-total">
                    <div class="payable-label">Total Payables</div>
                    <div class="payable-value">Rs. {{ number_format((float) ($summary['total'] ?? 0), 0) }}</div>
                </div>
            </div>
        </div>

        <section class="box-typical box-typical-dashboard panel panel-default finance-card">
            <header class="box-typical-header panel-heading">
                <h3 class="panel-title">Open Payables (Pending + Approved)</h3>
            </header>
            <div class="box-typical-body panel-body table-responsive">
                <table class="table table-bordered finance-table">
                    <thead>
                        <tr>
                            <th>Voucher</th>
                            <th>Expense Type</th>
                            <th>Payee</th>
                            <th>Campus</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payables as $expense)
                            <tr>
                                <td>{{ $expense->voucher_no ?? 'N/A' }}</td>
                                <td>{{ $expense->expenseType->name ?? ucfirst($expense->category ?? 'expense') }}</td>
                                <td>{{ $expense->payee->full_name ?? 'N/A' }}</td>
                                <td>{{ $expense->campus->code ?? 'N/A' }}</td>
                                <td>{{ optional($expense->payment_date)->format('Y-m-d') }}</td>
                                <td>{{ ucfirst($expense->status) }}</td>
                                <td>Rs. {{ number_format((float) $expense->amount, 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">No open payables found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
         * {
    font-family: 'Proxima Nova', sans-serif !important;
    font-size: 12px !important; 
    margin: 0;
    padding: 0;
    
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
.select2-container--arrow .select2-selection--single .select2-selection__rendered,
.select2-container--default .select2-selection--single .select2-selection__rendered,
.select2-container--white .select2-selection--single .select2-selection__rendered {
    border: solid 1px #d8e2e7;
    -webkit-border-radius: .25rem;
    border-radius: .25rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #343434;
    padding: .375rem 25px .375rem 1rem;
    min-height: 32px;
    background: #fff
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
        .finance-shell { padding: 8px 0 16px; }
        .finance-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .finance-summary-row { margin: 2px 0 10px; }
        .payable-card {
            border-radius: 10px;
            padding: 12px 14px;
            color: #fff;
            margin-bottom: 12px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
        }
        .payable-label { font-size: 12px; text-transform: uppercase; opacity: 0.88; }
        .payable-value { margin-top: 6px; font-size: 20px; font-weight: 700; }
        .tone-pending { background: linear-gradient(135deg, #f97316, #ea580c); }
        .tone-approved { background: linear-gradient(135deg, #0284c7, #0369a1); }
        .tone-total { background: linear-gradient(135deg, #1f2937, #111827); }
        .finance-table thead th {
            background: #eef2f7;
            color: #334155;
            font-weight: 700;
        }
    </style>
@endpush
