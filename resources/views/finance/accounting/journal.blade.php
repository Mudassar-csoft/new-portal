@extends('layouts.theme')

@section('title', 'Finance Journal')

@section('content')
    @php
        $summary = $summary ?? ['entries' => 0, 'debit' => 0, 'credit' => 0];
        $filters = $filters ?? ['campus_id' => null, 'from' => now()->startOfMonth()->toDateString(), 'to' => now()->endOfMonth()->toDateString(), 'search' => ''];
    @endphp

    <div class="finance-shell">
        <section class="box-typical box-typical-dashboard panel panel-default finance-card">
            <header class="box-typical-header panel-heading finance-header">
                <h3 class="panel-title form-label">Finance Journal</h3>
            </header>
            <div class="box-typical-body panel-body">
                <form method="GET" action="{{ route('finance.journal') }}">
                    <div class="form-row">
                        <div class="form-group col-lg-3 col-md-6">
                            <label class= "form-label">Campus</label>
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
                            <label class= "form-label">From</label>
                            <input type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class= "form-label">To</label>
                            <input type="date" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class= "form-label">Search</label>
                            <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Journal no, reference, description">
                        </div>
                       
                    </div>
                     <div class="form-group text-right mt-3">
                            <button type="submit" class="btn btn-inline btn-primary-outline mr-2">Apply</button>
                            <a href="{{ route('finance.journal') }}" class="btn btn-inline btn-danger-outline">Reset</a>
                        </div>
                </form>
            </div>
        </section>

        <div class="row finance-summary-row">
            <div class="col-lg-4 col-md-6">
                <div class="journal-summary-card tone-blue">
                    <div class="summary-value mt-md-4 pt-md-2">{{ number_format((int) ($summary['entries'] ?? 0)) }}</div>
                    <div class="summary-label">Entries</div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="journal-summary-card tone-green">
                    <div class="summary-value mt-md-4 pt-md-2">Rs. {{ number_format((float) ($summary['debit'] ?? 0), 0) }}</div>
                    <div class="summary-label">Total Debit</div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="journal-summary-card tone-red">
                    <div class="summary-value mt-md-4 pt-md-2">Rs. {{ number_format((float) ($summary['credit'] ?? 0), 0) }}</div>
                    <div class="summary-label">Total Credit</div>
                </div>
            </div>
        </div>

        @forelse($entries as $entry)
            <section class="box-typical box-typical-dashboard panel panel-default finance-card journal-entry-card">
                <header class="box-typical-header panel-heading journal-entry-header">
                    <div>
                        <div class="journal-entry-title">{{ $entry->journal_no }}</div>
                        <div class="journal-entry-meta">
                            {{ optional($entry->entry_date)->format('d-M-Y') ?: 'N/A' }}
                            <span class="meta-dot">•</span>
                            {{ $entry->campus->code ?? 'All Campuses' }}
                            <span class="meta-dot">•</span>
                            {{ ucwords(str_replace('_', ' ', $entry->entry_type)) }}
                        </div>
                    </div>
                    <div class="journal-entry-ref">
                        <strong>Ref:</strong> {{ $entry->reference_number ?: 'N/A' }}
                    </div>
                </header>
                <div class="box-typical-body panel-body">
                    <p class="journal-entry-description">{{ $entry->description ?: 'No description provided.' }}</p>
                    <div class="table-responsive">
                        <table class="table table-bordered finance-table">
                            <thead>
                                <tr>
                                    <th>Account</th>
                                    <th>Memo</th>
                                    <th>Debit</th>
                                    <th>Credit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($entry->lines as $line)
                                    <tr>
                                        <td>
                                            <div>{{ $line->account_name }}</div>
                                            <small class="text-muted">{{ $line->account_code }}</small>
                                        </td>
                                        <td>{{ $line->memo ?: '-' }}</td>
                                        <td>Rs. {{ number_format((float) $line->debit_amount, 2) }}</td>
                                        <td>Rs. {{ number_format((float) $line->credit_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-right">Entry Total</th>
                                    <th>Rs. {{ number_format((float) $entry->lines->sum('debit_amount'), 2) }}</th>
                                    <th>Rs. {{ number_format((float) $entry->lines->sum('credit_amount'), 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </section>
        @empty
            <section class="box-typical box-typical-dashboard panel panel-default finance-card">
                <div class="box-typical-body panel-body text-center text-muted">
                    No journal entries found for the selected filters.
                </div>
            </section>
        @endforelse

        {{ $entries->links() }}
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --space-finance-accounting-journal-1: 12px;
            --color-finance-accounting-journal-1: #334155;
            --color-finance-accounting-journal-2: #fff;
            --typo-finance-accounting-journal-font-weight-1: 700;
            --typo-finance-accounting-journal-font-size-2: 13px;
        }

        .finance-shell { padding: 8px 0 16px; background: var(--color-finance-accounting-journal-2); }
        .finance-header { display: flex; justify-content: space-between; align-items: center; gap: var(--space-finance-accounting-journal-1); flex-wrap: wrap; }
        .finance-summary-row { margin: 2px 0 10px; padding: 7px; }
        .journal-summary-card {
            border-radius: 10px;
            padding: 16px 18px;
            height:25vh;
            
            color: var(--color-finance-accounting-journal-2);
            margin-bottom: var(--space-finance-accounting-journal-1);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
        }
        .journal-summary-card .summary-value { font-size: 1.375rem; font-weight: var(--typo-finance-accounting-journal-font-weight-1); text-align:center}
        .journal-summary-card .summary-label { margin-top: 8px; font-size: var(--typo-finance-accounting-journal-font-size-2);text-align:center; text-transform: uppercase; opacity: 0.92; }
        .tone-blue { background: #2563eb; }
        .tone-green { background: #16a34a; }
        .tone-red { background: #dc2626; }
        .journal-entry-card { margin-bottom: var(--space-finance-accounting-journal-1); }
        .journal-entry-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: var(--space-finance-accounting-journal-1);
            flex-wrap: wrap;
        }
        .journal-entry-title { font-size: 1rem; font-weight: var(--typo-finance-accounting-journal-font-weight-1); color: #1e293b; }
        .journal-entry-meta { margin-top: 4px; color: #64748b; font-size: 0.75rem; }
        .journal-entry-ref { color: var(--color-finance-accounting-journal-1); font-size: var(--typo-finance-accounting-journal-font-size-2); }
        .journal-entry-description { margin-bottom: var(--space-finance-accounting-journal-1); color: var(--color-finance-accounting-journal-1); }
        .meta-dot { margin: 0 6px; }
        .finance-table thead th { background: #eef2f7; color: var(--color-finance-accounting-journal-1); font-weight: var(--typo-finance-accounting-journal-font-weight-1); }
    </style>
@endpush
