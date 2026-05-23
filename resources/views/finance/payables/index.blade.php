@extends('layouts.theme')

@section('title', 'Payables')

@section('content')
    @php
        $statusColors = [
            'pending' => 'badge-warning',
            'approved' => 'badge-info',
            'paid' => 'badge-success',
            'rejected' => 'badge-danger',
            'reversed' => 'badge-secondary',
        ];
    @endphp

    <div class="finance-shell">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="box-typical box-typical-dashboard panel panel-default finance-card">
            <header class="box-typical-header panel-heading finance-header d-flex">
                <h3 class="panel-title">{{ (($filters['scope'] ?? '') === 'open') ? 'Open Payables' : 'Payables' }}</h3>
                <!-- <a href="{{ route('finance.expense.add') }}" class="btn btn-primary btn-sm">Add Expense</a> -->
            </header>
            <div class="box-typical-body panel-body">
                <form class="mb-3" method="GET" action="{{ route('finance.payables') }}">
                    @if(($filters['scope'] ?? '') === 'open')
                        <input type="hidden" name="scope" value="open">
                    @endif
                    <div class="form-row mt-3">
                        <div class="form-group col-md-4">
                            <label class="form-label required">Campus</label>
                            <select class="form-control" name="campus_id">
                                <option value="">All Campuses</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected(($filters['campus_id'] ?? null) == $campus->id)>
                                        {{ $campus->code }} - {{ $campus->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label required">Status</label>
                            <select class="form-control" name="status">
                                <option value="">All</option>
                                @foreach(['pending', 'approved', 'paid', 'rejected', 'reversed'] as $status)
                                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3 d-flex align-items-end mt-4 pt-2 justify-content-end ">
                            <button type="submit" class="btn btn-inline btn-primary-outline ">Filter</button>
                            <a
                                href="{{ route('finance.payables', (($filters['scope'] ?? '') === 'open') ? ['scope' => 'open'] : []) }}"
                                class="btn btn-inline btn-danger-outline"
                            >
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered finance-table">
                        <thead>
                            <tr>
                                <th>Voucher</th>
                                <th>Expense Type</th>
                                <th>Payee</th>
                                <th>Campus</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payables as $expense)
                                <tr>
                                    <td>{{ $expense->voucher_no ?? 'N/A' }}</td>
                                    <td>{{ $expense->expenseType->name ?? ucfirst($expense->category ?? 'expense') }}</td>
                                    <td>{{ $expense->payee->full_name ?? 'N/A' }}</td>
                                    <td>{{ $expense->campus->code ?? 'N/A' }}</td>
                                    <td>{{ optional($expense->payment_date)->format('d-M-Y') }}</td>
                                    <td>Rs. {{ number_format((float) $expense->amount, 0) }}</td>
                                    <td><span class="badge {{ $statusColors[$expense->status] ?? 'badge-secondary' }}">{{ ucfirst($expense->status) }}</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                                Action
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                @if($expense->attachment_path)
                                                    <a class="dropdown-item" href="{{ asset('storage/' . $expense->attachment_path) }}" target="_blank">View Image</a>
                                                @endif
                                                @if($isAdmin && $expense->status === 'pending')
                                                    <form method="POST" action="{{ route('finance.expense.approve', $expense) }}">
                                                        @csrf
                                                        <button class="dropdown-item text-success" type="submit">Approve</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('finance.expense.reject', $expense) }}">
                                                        @csrf
                                                        <input type="hidden" name="reason" value="Rejected by admin">
                                                        <button class="dropdown-item text-danger" type="submit">Regret / Reject</button>
                                                    </form>
                                                @endif
                                                @if($expense->status === 'approved')
                                                    @include('finance.partials.pay_now_modal', ['expense' => $expense, 'paymentMethods' => $paymentMethods, 'isAdmin' => $isAdmin])
                                                @endif
                                                @if(!$isAdmin && $expense->status !== 'approved')
                                                    <span class="dropdown-item text-muted">Admin action required</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No payable records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $payables->links() }}
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
         

        .finance-shell { padding: 8px 0 16px; }
        .finance-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .finance-table thead th { background: #1ea7ff; color: #fff; }
        .dropdown-menu form { margin: 0; }
        .dropdown-menu form .dropdown-item { width: 100%; text-align: left; background: transparent; border: 0; }
    </style>
@endpush

@push('scripts')
    @include('finance.partials.pay_now_modal_script')
@endpush
