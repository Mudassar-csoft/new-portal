@extends('layouts.theme')

@section('title', 'Income Details')

@section('content')
    @php
        $summary = $summary ?? [
            'admission_fee' => 0,
            'coworking_fee' => 0,
            'franchise_royalty' => 0,
            'other_income' => 0,
            'total_income' => 0,
        ];
        $filters = $filters ?? [
            'campus_id' => null,
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->endOfMonth()->toDateString(),
        ];
        $campuses = $campuses ?? collect();
    @endphp

    <div class="finance-shell">
        <section class="box-typical box-typical-dashboard panel panel-default finance-card">
            <header class="box-typical-header panel-heading finance-header">
                <h3 class="panel-title">Income Detail</h3>
                <a class="btn btn-inline btn-danger-outline" href="{{ route('finance.dashboard', ['campus_id' => $filters['campus_id'] ?? null, 'from' => $filters['from'] ?? null, 'to' => $filters['to'] ?? null]) }}">
                    Back to Dashboard
                </a>
            </header>
            <div class="box-typical-body panel-body">
                <form method="GET" action="{{ route('finance.dashboard.income') }}">
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
                            <a href="{{ route('finance.dashboard.income') }}" class="btn btn-inline btn-danger-outline">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <div class="row finance-summary-row">
            <div class="col-lg-2 custom-col-4 col-6">
                <div class="income-summary-card tone-admission">
                    <div class="summary-label">Admission Fee</div>
                    <div class="summary-value">Rs. {{ number_format((float) ($summary['admission_fee'] ?? 0), 0) }}</div>
                </div>
            </div>
            <div class="col-lg-2 custom-col-4 col-6">
                <div class="income-summary-card tone-coworking">
                    <div class="summary-label">Coworking Fee</div>
                    <div class="summary-value">Rs. {{ number_format((float) ($summary['coworking_fee'] ?? 0), 0) }}</div>
                </div>
            </div>
            <div class="col-lg-2 custom-col-4 col-6">
                <div class="income-summary-card tone-royalty">
                    <div class="summary-label">Royalty</div>
                    <div class="summary-value">Rs. {{ number_format((float) ($summary['franchise_royalty'] ?? 0), 0) }}</div>
                </div>
            </div>
            <div class="col-lg-2 custom-col-4 col-6">
                <div class="income-summary-card tone-other">
                    <div class="summary-label">Other Income</div>
                    <div class="summary-value">Rs. {{ number_format((float) ($summary['other_income'] ?? 0), 0) }}</div>
                </div>
            </div>
            <div class="col-lg-4 custom-col-8 col-12">
                <div class="income-summary-card tone-total">
                    <div class="summary-label">Total Income</div>
                    <div class="summary-value">Rs. {{ number_format((float) ($summary['total_income'] ?? 0), 0) }}</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <section class="box-typical box-typical-dashboard panel panel-default finance-card">
                    <header class="box-typical-header panel-heading">
                        <h3 class="panel-title">Admission Income</h3>
                    </header>
                    <div class="box-typical-body panel-body table-responsive">
                        <table class="table table-bordered finance-table">
                            <thead>
                                <tr>
                                    <th>Reg #</th>
                                    <th>Student</th>
                                    <th>Campus</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($registrations as $registration)
                                    <tr>
                                        <td>{{ $registration->registration_number }}</td>
                                        <td>{{ $registration->student_name }}</td>
                                        <td>{{ $registration->campus->code ?? 'N/A' }}</td>
                                        <td>{{ optional($registration->registered_at)->format('Y-m-d') }}</td>
                                        <td>Rs. {{ number_format((float) $registration->net_payable, 0) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">No admission income found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <div class="col-lg-6">
                <section class="box-typical box-typical-dashboard panel panel-default finance-card">
                    <header class="box-typical-header panel-heading">
                        <h3 class="panel-title">Coworking Income</h3>
                    </header>
                    <div class="box-typical-body panel-body table-responsive">
                        <table class="table table-bordered finance-table">
                            <thead>
                                <tr>
                                    <th>Voucher</th>
                                    <th>Source</th>
                                    <th>Campus</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($coworkingCharges as $charge)
                                    <tr>
                                        <td>{{ $charge->voucher_number ?: 'N/A' }}</td>
                                        <td>{{ $charge->student_name ?: ($charge->chargeType->name ?? 'Coworking') }}</td>
                                        <td>{{ $charge->campus->code ?? 'N/A' }}</td>
                                        <td>{{ optional($charge->paid_at)->format('Y-m-d') }}</td>
                                        <td>Rs. {{ number_format((float) $charge->net_amount, 0) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">No coworking income found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <section class="box-typical box-typical-dashboard panel panel-default finance-card">
                    <header class="box-typical-header panel-heading">
                        <h3 class="panel-title">Other Income</h3>
                    </header>
                    <div class="box-typical-body panel-body table-responsive">
                        <table class="table table-bordered finance-table">
                            <thead>
                                <tr>
                                    <th>Voucher</th>
                                    <th>Type</th>
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
                                        <td>{{ $charge->campus->code ?? 'N/A' }}</td>
                                        <td>{{ optional($charge->paid_at)->format('Y-m-d') }}</td>
                                        <td>Rs. {{ number_format((float) $charge->net_amount, 0) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">No other income found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <div class="col-lg-6">
                <section class="box-typical box-typical-dashboard panel panel-default finance-card">
                    <header class="box-typical-header panel-heading">
                        <h3 class="panel-title">Franchise Royalty Income</h3>
                    </header>
                    <div class="box-typical-body panel-body table-responsive">
                        <table class="table table-bordered finance-table">
                            <thead>
                                <tr>
                                    <th>Campus</th>
                                    <th>Due Date</th>
                                    <th>Paid Date</th>
                                    <th>Remarks</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($royalties as $royalty)
                                    <tr>
                                        <td>{{ $royalty->campus->code ?? 'N/A' }}</td>
                                        <td>{{ optional($royalty->due_date)->format('Y-m-d') }}</td>
                                        <td>{{ optional($royalty->paid_at)->format('Y-m-d') }}</td>
                                        <td>{{ $royalty->remarks ?: '-' }}</td>
                                        <td>Rs. {{ number_format((float) $royalty->amount, 0) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">No royalty income found.</td></tr>
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
        .income-summary-card {
            border-radius: 10px;
            padding: 12px 14px;
            color: #fff;
            margin-bottom: 12px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
            min-height: 86px;
        }
        .income-summary-card .summary-label {
            font-size: 12px;
            text-transform: uppercase;
            opacity: 0.88;
        }
        .income-summary-card .summary-value {
            margin-top: 6px;
            font-size: 22px;
            font-weight: 700;
        }
        .tone-admission { background: linear-gradient(135deg, #16a34a, #15803d); }
        .tone-coworking { background: linear-gradient(135deg, #0ea5e9, #0284c7); }
        .tone-royalty { background: linear-gradient(135deg, #dc2626, #b91c1c); }
        .tone-other { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .tone-total { background: linear-gradient(135deg, #1f2937, #111827); }
        .finance-table thead th {
            background: #eef2f7;
            color: #334155;
            font-weight: 700;
        }
    </style>
@endpush
