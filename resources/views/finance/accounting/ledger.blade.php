@extends('layouts.theme')

@section('title', 'Finance Ledger')

@section('content')
    @php
        $filters = $filters ?? ['campus_id' => null, 'from' => now()->startOfMonth()->toDateString(), 'to' => now()->endOfMonth()->toDateString(), 'account_code' => ''];
    @endphp

    <div class="finance-shell">
        <section class="box-typical box-typical-dashboard panel panel-default finance-card">
            <header class="box-typical-header panel-heading finance-header">
                <h3 class="panel-title form-label">Finance Ledger</h3>
            </header>
            <div class="box-typical-body panel-body">
                <form method="GET" action="{{ route('finance.ledger') }}">
                    <div class="form-row">
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">Campus</label>
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
                            <label class="form-label">Account</label>
                            <select name="account_code" class="form-control">
                                <option value="">All Accounts</option>
                                @foreach($accountOptions as $account)
                                    <option value="{{ $account['code'] }}" @selected(($filters['account_code'] ?? '') === $account['code'])>
                                        {{ $account['code'] }} - {{ $account['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">From</label>
                            <input type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class = "form-label">To</label>
                            <input type="date" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
                        </div>
                        
                    </div>
                    <div class="form-group text-right mt-3">
                            <button type="submit" class="btn btn-inline btn-primary-outline mr-2">Apply</button>
                            <a href="{{ route('finance.ledger') }}" class="btn btn-inline btn-danger-outline">Reset</a>
                        </div>
                </form>
            </div>
        </section>

        @if($selectedAccount)
            <div class="row finance-summary-row">
                <div class="col-lg-3 col-md-6">
                    <div class="ledger-summary-card tone-slate">
                        <div class="summary-value">Rs. {{ number_format((float) $openingBalance, 2) }}</div>
                        <div class="summary-label">Opening Balance</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="ledger-summary-card tone-green">
                        <div class="summary-value">Rs. {{ number_format((float) $periodDebit, 2) }}</div>
                        <div class="summary-label">Period Debit</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="ledger-summary-card tone-red">
                        <div class="summary-value">Rs. {{ number_format((float) $periodCredit, 2) }}</div>
                        <div class="summary-label">Period Credit</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="ledger-summary-card tone-blue">
                        <div class="summary-value">Rs. {{ number_format((float) $closingBalance, 2) }}</div>
                        <div class="summary-label">Closing Balance</div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-5">
                <section class="box-typical box-typical-dashboard panel panel-default finance-card">
                    <header class="box-typical-header panel-heading">
                        <h3 class="panel-title">Account Summary</h3>
                    </header>
                    <div class="box-typical-body panel-body table-responsive">
                        <table class="table table-bordered finance-table">
                            <thead>
                                <tr>
                                    <th>Account</th>
                                    <th>Debit</th>
                                    <th>Credit</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accountSummaries as $summary)
                                    <tr class="{{ ($filters['account_code'] ?? '') === $summary->account_code ? 'selected-ledger-row' : '' }}">
                                        <td>
                                            <div>{{ $summary->account_name }}</div>
                                            <small class="text-muted">{{ $summary->account_code }}</small>
                                        </td>
                                        <td>Rs. {{ number_format((float) $summary->total_debit, 2) }}</td>
                                        <td>Rs. {{ number_format((float) $summary->total_credit, 2) }}</td>
                                        <td>Rs. {{ number_format((float) $summary->balance, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No ledger activity found for the selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <div class="col-lg-7">
                <section class="box-typical box-typical-dashboard panel panel-default finance-card">
                    <header class="box-typical-header panel-heading">
                        <h3 class="panel-title">
                            @if($selectedAccount)
                                Ledger Detail: {{ $selectedAccount['code'] }} - {{ $selectedAccount['name'] }}
                            @else
                                Ledger Detail
                            @endif
                        </h3>
                    </header>
                    <div class="box-typical-body panel-body table-responsive">
                        @if($selectedAccount)
                            <table class="table table-bordered finance-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Journal</th>
                                        <th>Campus</th>
                                        <th>Description</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                        <th>Running</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="ledger-opening-row">
                                        <td>{{ \Carbon\Carbon::parse($filters['from'])->format('d-M-Y') }}</td>
                                        <td colspan="5"><strong>Opening Balance</strong></td>
                                        <td>Rs. {{ number_format((float) $openingBalance, 2) }}</td>
                                    </tr>
                                    @forelse($ledgerRows as $row)
                                        <tr>
                                            <td>{{ $row->entry_date ? \Carbon\Carbon::parse($row->entry_date)->format('d-M-Y') : 'N/A' }}</td>
                                            <td>
                                                <div>{{ $row->journal_no }}</div>
                                                <small class="text-muted">{{ $row->reference_number ?: 'N/A' }}</small>
                                            </td>
                                            <td>{{ $row->campus_code ?: ($row->campus_name ?: 'N/A') }}</td>
                                            <td>
                                                <div>{{ $row->description ?: '-' }}</div>
                                                <small class="text-muted">{{ $row->memo ?: ucwords(str_replace('_', ' ', (string) $row->entry_type)) }}</small>
                                            </td>
                                            <td>Rs. {{ number_format((float) $row->debit_amount, 2) }}</td>
                                            <td>Rs. {{ number_format((float) $row->credit_amount, 2) }}</td>
                                            <td>Rs. {{ number_format((float) $row->running_balance, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No ledger lines found for this account in the selected period.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        @else
                            <div class="text-center text-muted py-4">
                                Select an account to view detailed ledger movement with running balance.
                            </div>
                        @endif
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --space-finance-accounting-ledger-1: 12px;
            --color-finance-accounting-ledger-1: #fff;
            --typo-finance-accounting-ledger-font-weight-1: 700;
        }

        .finance-shell { padding: 8px 0 16px; background: var(--color-finance-accounting-ledger-1); }
        .finance-header { display: flex; justify-content: space-between; align-items: center; gap: var(--space-finance-accounting-ledger-1); flex-wrap: wrap; }
        .finance-summary-row { margin: 2px 0 10px; padding: 7px; }
        .ledger-summary-card {
            border-radius: 10px;
            padding: 16px 18px;
            color: var(--color-finance-accounting-ledger-1);
            margin-bottom: var(--space-finance-accounting-ledger-1);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
        }
        .ledger-summary-card .summary-value { font-size: 20px; font-weight: var(--typo-finance-accounting-ledger-font-weight-1); }
        .ledger-summary-card .summary-label { margin-top: 8px; font-size: 13px; text-transform: uppercase; opacity: 0.92; }
        .tone-slate { background: #475569; }
        .tone-green { background: #16a34a; }
        .tone-red { background: #dc2626; }
        .tone-blue { background: #2563eb; }
        .finance-table thead th { background: #eef2f7; color: #334155; font-weight: var(--typo-finance-accounting-ledger-font-weight-1); }
        .selected-ledger-row td { background: #eff6ff !important; }
        .ledger-opening-row td { background: #f8fafc; font-weight: var(--typo-finance-accounting-ledger-font-weight-1); }
    </style>
@endpush
