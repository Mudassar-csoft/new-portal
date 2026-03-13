@extends('layouts.theme')

@section('title', 'Payroll')

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
            <header class="box-typical-header panel-heading finance-header">
                <h3 class="panel-title form-label">Generate Payroll Expense</h3>
            </header>
            <div class="box-typical-body panel-body">
                <form method="POST" action="{{ route('finance.expense.payroll.generate') }}">
                    @csrf
                    <div class="form-row mt-3">
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Campus / Franchise</label>
                            <select name="campus_id" class="form-control" required>
                                <option value="">- Select -</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected(old('campus_id') == $campus->id)>
                                        {{ $campus->code }} - {{ $campus->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Payroll Month</label>
                            <input type="month" name="month" class="form-control" value="{{ old('month', now()->format('Y-m')) }}" required>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Payment Method</label>
                            <select name="payment_method" class="form-control" required>
                                <option value="cash" @selected(old('payment_method') === 'cash')>Cash</option>
                                <option value="bank" @selected(old('payment_method') === 'bank')>Bank</option>
                                <option value="cheque" @selected(old('payment_method') === 'cheque')>Cheque</option>
                            </select>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Remarks</label>
                            <input type="text" name="remarks" class="form-control" value="{{ old('remarks') }}">
                        </div>
                    </div>

                    <div class="form-group" style = "padding-left:15px;width:99.5%;">
                        <label class="form-label required">Employees</label>
                        <select name="employee_ids[]" class="form-control" multiple required size="8">
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" @selected(collect(old('employee_ids', []))->contains($employee->id))>
                                    {{ $employee->full_name }} | {{ $employee->employee_code ?: 'N/A' }} | Rs. {{ number_format((float) ($employee->monthly_salary ?? 0), 0) }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hold Ctrl (Windows) to select multiple employees.</small>
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-inline btn-primary-outline">Generate Payroll Request</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="box-typical box-typical-dashboard panel panel-default finance-card mt-3">
            <header class="box-typical-header panel-heading finance-header">
                <h3 class="panel-title">Payroll Expenses</h3>
            </header>
            <div class="box-typical-body panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered finance-table">
                        <thead>
                            <tr>
                                <th>Voucher</th>
                                <th>Employee</th>
                                <th>Campus</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th style="width: 160px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payrollExpenses as $expense)
                                <tr>
                                    <td>{{ $expense->voucher_no ?? 'N/A' }}</td>
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
                                                @if($isAdmin && $expense->status === 'pending')
                                                    <form method="POST" action="{{ route('finance.expense.approve', $expense) }}">
                                                        @csrf
                                                        <button class="dropdown-item text-success" type="submit">Approve</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('finance.expense.reject', $expense) }}">
                                                        @csrf
                                                        <input type="hidden" name="reason" value="Payroll rejected by admin">
                                                        <button class="dropdown-item text-danger" type="submit">Regret / Reject</button>
                                                    </form>
                                                @endif
                                                @if($isAdmin && $expense->status === 'approved')
                                                    <form method="POST" action="{{ route('finance.expense.markPaid', $expense) }}">
                                                        @csrf
                                                        <button class="dropdown-item text-primary" type="submit">Mark Paid</button>
                                                    </form>
                                                @endif
                                                @if(!$isAdmin)
                                                    <span class="dropdown-item text-muted">Admin action required</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No payroll expense found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $payrollExpenses->links() }}
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
      
.box-typical.box-typical-dashboard .box-typical-body {
    overflow: hidden;
}
.box-typical.box-typical-dashboard{
    margin:0px 0px 5px !important;
    
}
.box-typical.box-typical-dashboard .box-typical-header{
    display:flex;

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


        .finance-shell { padding: 8px 0 16px; }
        .finance-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .required::after { content: ' *'; color: #e53935; }
        .finance-table thead th { background: #1ea7ff; color: #fff; }
        .dropdown-menu form { margin: 0; }
        .dropdown-menu form .dropdown-item { width: 100%; text-align: left; background: transparent; border: 0; }
    </style>
@endpush
