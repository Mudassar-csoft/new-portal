@extends('layouts.theme')

@section('title', 'Receivables Details')

@section('content')
    @php
        $filters = $filters ?? [
            'campus_id' => null,
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->endOfMonth()->toDateString(),
        ];
        $summary = $summary ?? ['pending_other' => 0, 'pending_royalty' => 0, 'total' => 0];
        $otherCharges = $otherCharges ?? collect();
        $royalties = $royalties ?? collect();
        $campuses = $campuses ?? collect();
    @endphp

    <div class="finance-shell">
        <section class="box-typical box-typical-dashboard panel panel-default finance-card">
            <header class="box-typical-header panel-heading finance-header">
                <h3 class="panel-title">Receivables Detail</h3>
                <a
                    class="btn btn-inline btn-danger-outline"
                    href="{{ route('finance.dashboard', ['campus_id' => $filters['campus_id'] ?? null, 'from' => $filters['from'] ?? null, 'to' => $filters['to'] ?? null]) }}"
                >
                    Back to Dashboard
                </a>
            </header>
            <div class="box-typical-body panel-body">
                <form method="GET" action="{{ route('finance.dashboard.receivables') }}">
                    <input type="hidden" name="from" value="{{ $filters['from'] ?? '' }}">
                    <input type="hidden" name="to" value="{{ $filters['to'] ?? '' }}">
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
                        <div class="form-group custom-col-7 d-flex align-items-end mt-3 pt-1">
                            <button type="submit" class="btn btn-inline btn-primary-outline mr-2">Apply</button>
                            <a href="{{ route('finance.dashboard.receivables') }}" class="btn btn-inline btn-danger-outline">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <div class="row finance-summary-row">
            <div class="custom-col-4">
                <div class="receivable-card tone-other">
                    <div class="receivable-label">Pending Other Charges</div>
                    <div class="receivable-value">Rs. {{ number_format((float) ($summary['pending_other'] ?? 0), 0) }}</div>
                </div>
            </div>
            <div class="custom-col-4">
                <div class="receivable-card tone-royalty">
                    <div class="receivable-label">Pending Royalties</div>
                    <div class="receivable-value">Rs. {{ number_format((float) ($summary['pending_royalty'] ?? 0), 0) }}</div>
                </div>
            </div>
            <div class="custom-col-4">
                <div class="receivable-card tone-total">
                    <div class="receivable-label">Total Receivables</div>
                    <div class="receivable-value">Rs. {{ number_format((float) ($summary['total'] ?? 0), 0) }}</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <section class="box-typical box-typical-dashboard panel panel-default finance-card">
                    <header class="box-typical-header panel-heading">
                        <h3 class="panel-title">Pending Other Charges</h3>
                    </header>
                    <div class="box-typical-body panel-body table-responsive">
                        <table class="table table-bordered finance-table">
                            <thead>
                                <tr>
                                    <th>Voucher</th>
                                    <th>Type</th>
                                    <th>Source</th>
                                    <th>Campus</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($otherCharges as $charge)
                                    <tr>
                                        <td>{{ $charge->voucher_number ?: 'N/A' }}</td>
                                        <td>{{ $charge->chargeType->name ?? 'Other' }}</td>
                                        <td>{{ $charge->student_name ?: '-' }}</td>
                                        <td>{{ $charge->campus->code ?? 'N/A' }}</td>
                                        <td>{{ optional($charge->created_at)->format('Y-m-d') }}</td>
                                        <td>Rs. {{ number_format((float) $charge->net_amount, 0) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">No pending charge receivables found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
            <div class="col-lg-6">
                <section class="box-typical box-typical-dashboard panel panel-default finance-card">
                    <header class="box-typical-header panel-heading">
                        <h3 class="panel-title">Pending Franchise Royalties</h3>
                    </header>
                    <div class="box-typical-body panel-body table-responsive">
                        <table class="table table-bordered finance-table">
                            <thead>
                                <tr>
                                    <th>Campus</th>
                                    <th>Due Date</th>
                                    <th>Remarks</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($royalties as $royalty)
                                    <tr>
                                        <td>{{ $royalty->campus->code ?? 'N/A' }}</td>
                                        <td>{{ optional($royalty->due_date)->format('Y-m-d') }}</td>
                                        <td>{{ $royalty->remarks ?: '-' }}</td>
                                        <td>Rs. {{ number_format((float) $royalty->amount, 0) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">No pending royalty receivables found.</td></tr>
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
        .receivable-card {
            border-radius: 10px;
            padding: 12px 14px;
            color: #fff;
            margin-bottom: 12px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
        }
        .receivable-label { font-size: 12px; text-transform: uppercase; opacity: 0.88; }
        .receivable-value { margin-top: 6px; font-size: 20px; font-weight: 700; }
        .tone-other { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .tone-royalty { background: linear-gradient(135deg, #7c3aed, #6d28d9); }
        .tone-total { background: linear-gradient(135deg, #1f2937, #111827); }
        .finance-table thead th {
            background: #eef2f7;
            color: #334155;
            font-weight: 700;
        }
    </style>
@endpush
