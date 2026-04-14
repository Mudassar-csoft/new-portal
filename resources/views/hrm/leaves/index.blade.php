@extends('layouts.theme')

@section('title', 'Leave Management')

@section('content')
    @php
        $filters = $filters ?? ['status' => null];
    @endphp

    <div class="hrm-shell">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
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

        <section class="box-typical box-typical-dashboard panel panel-default hrm-card">
            <header class="box-typical-header panel-heading">
                <h3 class="panel-title form-label">Leave Requests</h3>
            </header>
            <div class="box-typical-body panel-body">
                <form method="GET" action="{{ route('hrm.leaves.index') }}" class="mb-3">
                    <div class="form-row ">
                        <div class="form-group col-md-8">
                            <label class="form-label required" >Status</label>
                            <select class="form-control" name="status">
                                <option value="">All</option>
                                @foreach(['pending','approved','rejected','cancelled'] as $status)
                                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group leave-button col-md-4 text-right ">
                            <button class="btn btn-inline btn-primary-outline " type="submit">Filter</button>
                            <a href="{{ route('hrm.leaves.index') }}" class="btn btn-inline btn-danger-outline ">Reset</a>
                        </div>
                    </div>
                </form>

                <form method="POST" action="{{ route('hrm.leaves.requests.store') }}" class="mb-3 hrm-box">
                    @csrf
                    <div class="form-row justify-between" >
                        <div class="col-md-4">
                            <label class="form-label required" >Employee</label>
                            <select name="employee_id" class="form-control" required>
                                <option value="">- Select -</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_code ?: 'EMP' }} - {{ trim($employee->first_name . ' ' . $employee->last_name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class=" col-md-4">
                            <label class="form-label required" >Leave Type</label>
                            <select name="leave_type_id" class="form-control" required>
                                <option value="">- Select -</option>
                                @foreach($leaveTypes as $leaveType)
                                    <option value="{{ $leaveType->id }}">{{ $leaveType->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class=" col-md-4">
                            <label class="form-label required" >From</label>
                            <input type="date" name="from_date" class="form-control" required>
                        </div>
                        </div>
                        <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="form-label required" >To</label>
                            <input type="date" name="to_date" class="form-control" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label required" >Days</label>
                            <input type="number" step="0.5" min="0.5" name="days" class="form-control" placeholder="Auto">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label required" >Reason</label>
                            <input type="text" name="reason" class="form-control">
                        </div>
                    </div>
                        <div class="text-right">
                        <button class="btn btn-inline btn-primary-outline" type="submit">Submit Leave</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered hrm-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Days</th>
                                <th>Status</th>
                                <th>Reason</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $leaveRequest)
                                <tr>
                                    <td>{{ $leaveRequest->employee?->employee_code ?: 'EMP' }} - {{ $leaveRequest->employee?->full_name ?: '-' }}</td>
                                    <td>{{ $leaveRequest->leaveType->name ?? '-' }}</td>
                                    <td>{{ optional($leaveRequest->from_date)->format('Y-m-d') }}</td>
                                    <td>{{ optional($leaveRequest->to_date)->format('Y-m-d') }}</td>
                                    <td>{{ number_format((float) $leaveRequest->days, 2) }}</td>
                                    <td>{{ ucfirst($leaveRequest->status) }}</td>
                                    <td>{{ $leaveRequest->reason ?: '-' }}</td>
                                    <td>
                                        @if($leaveRequest->status === 'pending')
                                            <form method="POST" action="{{ route('hrm.leaves.requests.approve', $leaveRequest) }}" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-success" type="submit">Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('hrm.leaves.requests.reject', $leaveRequest) }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="rejection_reason" value="Rejected by approver">
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Reject</button>
                                            </form>
                                        @else
                                            <span class="text-muted">Processed</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted">No leave requests found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $requests->links() }}
            </div>
        </section>

        <section class="box-typical box-typical-dashboard panel panel-default hrm-card mt-3">
            <header class="box-typical-header panel-heading">
                <h3 class="panel-title form-label">Leave Balances / Accrual</h3>
            </header>
            <div class="box-typical-body panel-body">
                <form method="POST" action="{{ route('hrm.leaves.balances.store') }}" class="mb-3 hrm-box">
                    @csrf
                    <div class="form-row " >
                        <div class="form-group col-md-4 ">
                            <label class="form-label required" >Employee</label>
                            <select name="employee_id" class="form-control" required>
                                <option value="">- Select -</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_code ?: 'EMP' }} - {{ trim($employee->first_name . ' ' . $employee->last_name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label required" >Leave Type</label>
                            <select name="leave_type_id" class="form-control" required>
                                <option value="">- Select -</option>
                                @foreach($leaveTypes as $leaveType)
                                    <option value="{{ $leaveType->id }}">{{ $leaveType->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label required" >Year</label>
                            <input type="number" name="year" class="form-control" value="{{ now()->year }}" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label class="form-label required" >Opening</label>
                            <input type="number" step="0.01" min="0" name="opening_balance" class="form-control">
                        </div>
    <div class="form-group col-md-3">
        <label class="form-label required" >Accrued</label>
        <input type="number" step="0.01" min="0" name="accrued" class="form-control">
    </div>
                        <div class="form-group col-md-3">
                            <label class="form-label required" >Used</label>
                            <input type="number" step="0.01" min="0" name="used" class="form-control">
                        </div>
                        <div class="form-group col-md-3">
                            <label class="form-label required" >Encashed</label>
                            <input type="number" step="0.01" min="0" name="encashed" class="form-control">
                        </div>
                    </div>
                        <div class="text-right">
                            <button class="btn btn-inline btn-primary-outline" type="submit">Save Balance</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered hrm-table">
                        <thead class=""><tr><th>Employee</th><th>Type</th><th>Year</th><th>Opening</th><th>Accrued</th><th>Used</th><th>Closing</th></tr></thead>
                        <tbody>
                            @forelse($balances as $balance)
                                <tr>
                                    <td>{{ $balance->employee?->employee_code ?: 'EMP' }} - {{ $balance->employee?->full_name ?: '-' }}</td>
                                    <td>{{ $balance->leaveType->name ?? '-' }}</td>
                                    <td>{{ $balance->year }}</td>
                                    <td>{{ number_format((float) $balance->opening_balance, 2) }}</td>
                                    <td>{{ number_format((float) $balance->accrued, 2) }}</td>
                                    <td>{{ number_format((float) $balance->used, 2) }}</td>
                                    <td>{{ number_format((float) $balance->closing_balance, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted">No leave balances found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $balances->links() }}
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
      
        .leave-button{
                margin: auto;
    padding-top: 33px;
        }
        /* .hrm-shell { padding: 8px 0 16px; } */
        .hrm-table thead th { background: #eef2f7; color: #334155; }
        .hrm-box { border: 1px solid #e6ebf1; border-radius: 8px; padding: 10px; }
    </style>
@endpush

