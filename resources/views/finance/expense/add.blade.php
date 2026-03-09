@extends('layouts.theme')

@section('title', 'Add Expense')

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
                <h3 class="panel-title">Expense Management <span class="text-muted">|</span> Add Expense</h3>
                <a href="{{ route('finance.expense.types') }}" class="btn btn-primary btn-sm">Add Expense Type</a>
            </header>
            <div class="box-typical-body panel-body">
                <form method="POST" action="{{ route('finance.expense.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-row mt-2 " style = "gap:18px;padding-left:15px">
                        <div class="form-group custom-col-3">
                            <label class="form-label required">Campus / Franchise</label>
                            <select name="campus_id" class="form-control" required>
                                <option value="">- Select -</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected(old('campus_id') == $campus->id)>
                                        {{ $campus->code }} - {{ $campus->name }} ({{ ucfirst($campus->campus_type) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group custom-col-3">
                            <label class="form-label required">Supplier / Payee / Employee</label>
                            <select name="payee_id" class="form-control">
                                <option value="">- Select -</option>
                                @foreach($payees as $payee)
                                    <option value="{{ $payee->id }}" @selected(old('payee_id') == $payee->id)>
                                        {{ $payee->full_name }} ({{ ucfirst($payee->type) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group custom-col-3">
                            <label class="form-label required">Expense Type</label>
                            <select name="expense_type_id" class="form-control" required>
                                <option value="">- Select -</option>
                                @foreach($expenseTypes as $type)
                                    <option value="{{ $type->id }}" @selected(old('expense_type_id') == $type->id)>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group custom-col-3">
                            <label class="form-label required">Category</label>
                            <select name="category" class="form-control form-label" required>
                                @foreach(['general','rent','marketing','asset','payroll','utility'] as $cat)
                                    <option value="{{ $cat }}" @selected(old('category', $selectedCategory ?? 'general') === $cat)>
                                        {{ ucfirst($cat) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row" style = "gap:18px;padding-left:15px">
                        <div class="form-group custom-col-3">
                            <label class="form-label required">Payment Date</label>
                            <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', now()->toDateString()) }}" required>
                        </div>
                        <div class="form-group custom-col-3">
                            <label class="form-label required">Amount (PKR)</label>
                            <input type="number" step="0.01" min="1" name="amount" class="form-control" value="{{ old('amount') }}" required>
                        </div>
                        <div class="form-group custom-col-3">
                            <label class="form-label required">Payment Method</label>
                            <select name="payment_method" class="form-control" required>
                                <option value="cash" @selected(old('payment_method') === 'cash')>Cash</option>
                                <option value="bank" @selected(old('payment_method') === 'bank')>Bank</option>
                                <option value="cheque" @selected(old('payment_method') === 'cheque')>Cheque</option>
                            </select>
                        </div>
                        <div class="form-group custom-col-3">
                            <label class="form-label required">Payment Ref No</label>
                            <input type="text" name="payment_ref_no" class="form-control" value="{{ old('payment_ref_no') }}" placeholder="Txn/Receipt reference">
                        </div>
                    </div>

                    <div class="form-row" style = "gap:18px;padding-left:15px">
                        <div class="form-group custom-col-3">
                            <label class="form-label required">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}">
                        </div>
                        <div class="form-group custom-col-3">
                            <label class="form-label required">Cheque No</label>
                            <input type="text" name="cheque_no" class="form-control" value="{{ old('cheque_no') }}">
                        </div>
                        <div class="form-group custom-col-3">
                            <label class="form-label required" >Bank Receipt No</label>
                            <input type="text" name="bank_receipt_no" class="form-control" value="{{ old('bank_receipt_no') }}">
                        </div>
                        <div class="form-group custom-col-3">
                            <label class="form-label required">Transaction Image</label>
                            <input type="file" name="attachment" class="form-control-file" required>
                        </div>
                    </div>

                    <div class="row " style = "gap:18px; padding-left:15px">
    <div class="col-12">
        <label class="form-label small fw-semibold text-dark required">
            Remarks
        </label>
       <textarea name="details[remarks]"
    class="form-control form-control-sm @error('details.remarks') is-invalid @enderror"
    rows="3"
    placeholder="Remarks" style= "padding:10px; width:99%; margin-bottom:7px;" >{{ old('details.remarks', '') }}</textarea>
        @error('details.remarks')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
</div>

                    <div class="text-right" style = "gap:18px;padding-left:15px">
                        <button type="submit" class="btn btn-inline btn-primary-outline">Submit For Approval</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="box-typical box-typical-dashboard panel panel-default finance-card mt-3">
            <header class="box-typical-header panel-heading finance-header">
                <h3 class="panel-title">Recent Expense Requests</h3>
                <a href="{{ route('finance.expense.all') }}" class="btn btn-danger btn-sm">View All</a>
            </header>
            <div class="box-typical-body panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered finance-table">
                        <thead>
                        <tr>
                            <th>Voucher</th>
                            <th>Category</th>
                            <th>Payee</th>
                            <th>Campus</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th style="width: 160px;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($recentExpenses as $expense)
                            <tr>
                                <td>{{ $expense->voucher_no }}</td>
                                <td>{{ ucfirst($expense->category ?? 'general') }}</td>
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
                                                    <button class="dropdown-item text-danger" type="submit">Reject</button>
                                                </form>
                                            @endif
                                            @if($isAdmin && $expense->status === 'approved')
                                                <form method="POST" action="{{ route('finance.expense.markPaid', $expense) }}">
                                                    @csrf
                                                    <button class="dropdown-item text-primary" type="submit">Mark Paid</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No expense requests yet.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
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

      
.box-typical .panel-heading {
    padding: 7px 20px;
}
        .finance-shell { padding: 8px 0 16px; }
        .finance-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .required::after { content: ' *'; color: #e53935; }
        .finance-table thead th { background: #1ea7ff; color: #fff; }
        .dropdown-menu form { margin: 0; }
        .dropdown-menu form .dropdown-item { width: 100%; text-align: left; background: transparent; border: 0; }
    </style>
@endpush
