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
                        <div class="form-group  col-md-9">
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
                        <div class="form-group col-md-3 d-flex align-items-end leave-button">
                            <button type="submit" class="btn btn-inline btn-primary-outline ">Apply</button>
                            <a href="{{ route('finance.dashboard.receivables') }}" class="btn btn-inline btn-danger-outline">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <div class="row finance-summary-row">
            <div class="col-lg-4 col-md-6">
                <div class="receivable-card tone-other">
                    <div class="receivable-value">Rs. {{ number_format((float) ($summary['pending_other'] ?? 0), 0) }}</div>
                    <div class="receivable-label">Pending Other Charges</div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="receivable-card tone-royalty">
                    <div class="receivable-value">Rs. {{ number_format((float) ($summary['pending_royalty'] ?? 0), 0) }}</div>
                    <div class="receivable-label">Pending Royalties</div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="receivable-card tone-total">
                    <div class="receivable-value">Rs. {{ number_format((float) ($summary['total'] ?? 0), 0) }}</div>
                    <div class="receivable-label">Total Receivables</div>
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
            height:25vh;
            border-radius: 10px;
            padding: 12px 14px;
            color: #fff;
            margin-bottom: 12px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
            min-height: 86px;
        }
        .receivable-label {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            opacity: .88;
            text-align: center;
            margin-top: 1rem;
        }
        .receivable-value {
            margin-top: 30px;
            font-size: 18px;
            text-align: center;
            font-weight: 700;
        }
        .tone-other {background: #f35f62;  }
        .tone-royalty { background: #fdc518; }
        .tone-total { background:  #975ce7; }
        .finance-table thead th {
            background: #eef2f7;
            color: #334155;
            font-weight: 700;
        }
         .leave-button{
                margin: auto;
    padding-top: 21px;
    justify-content: end;
        }
        @media (max-width: 760px)  {
            .receivable-label {
                margin-top: 5px;
            }
            .receivable-value {
                margin-top: 10px;
            }
        }
    </style>
@endpush
