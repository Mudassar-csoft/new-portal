@extends('layouts.theme')

@section('title', 'Supplier & Payee')

@section('content')
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
                <h3 class="panel-title">Expense Management <span class="text-muted">|</span> Manage Supplier, Payee & Employee</h3>
            </header>
            <div class="box-typical-body panel-body">
                <form method="POST" action="{{ route('finance.payees.store') }}">
                    @csrf
                    <h4 class="section-title">Basic Profile</h4>
                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label class="required">Type</label>
                            <select name="type" class="form-control" required>
                                @foreach(['supplier' => 'Supplier', 'payee' => 'Payee', 'employee' => 'Employee'] as $k => $v)
                                    <option value="{{ $k }}" @selected(old('type') === $k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="required">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="{{ old('full_name') }}" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Display Name</label>
                            <input type="text" name="display_name" class="form-control" value="{{ old('display_name') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Campus / Franchise</label>
                            <select name="campus_id" class="form-control">
                                <option value="">- Select -</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected(old('campus_id') == $campus->id)>
                                        {{ $campus->code }} - {{ $campus->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Mobile</label>
                            <input type="text" name="mobile" class="form-control" value="{{ old('mobile') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>CNIC</label>
                            <input type="text" name="cnic" class="form-control" value="{{ old('cnic') }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Address</label>
                            <input type="text" name="postal_address" class="form-control" value="{{ old('postal_address') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Company Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Payment Terms</label>
                            <input type="text" name="payment_terms" class="form-control" value="{{ old('payment_terms') }}" placeholder="Due on receipt / Net 30">
                        </div>
                    </div>

                    <h4 class="section-title mt-3">Employee Fields (for payroll)</h4>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Employee Code</label>
                            <input type="text" name="employee_code" class="form-control" value="{{ old('employee_code') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Designation</label>
                            <input type="text" name="designation" class="form-control" value="{{ old('designation') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Monthly Salary (PKR)</label>
                            <input type="number" step="0.01" min="0" name="monthly_salary" class="form-control" value="{{ old('monthly_salary') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Joining Date</label>
                            <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date') }}">
                        </div>
                    </div>

                    <h4 class="section-title mt-3">Bank Details</h4>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Account Title</label>
                            <input type="text" name="account_title" class="form-control" value="{{ old('account_title') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Account Number</label>
                            <input type="text" name="account_number" class="form-control" value="{{ old('account_number') }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>IBAN</label>
                            <input type="text" name="iban" class="form-control" value="{{ old('iban') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Remarks</label>
                        <input type="text" name="remarks" class="form-control" value="{{ old('remarks') }}">
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-inline btn-primary-outline">Save</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="box-typical box-typical-dashboard panel panel-default finance-card mt-3">
            <div class="box-typical-body panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered finance-table">
                        <thead>
                        <tr>
                            <th style="width: 60px;">Sr#</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Campus</th>
                            <th>Mobile</th>
                            <th>Employee Code</th>
                            <th>Monthly Salary</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($payees as $index => $payee)
                            <tr>
                                <td>{{ $payees->firstItem() + $index }}</td>
                                <td>{{ $payee->full_name }}</td>
                                <td>{{ ucfirst($payee->type) }}</td>
                                <td>{{ $payee->campus->code ?? 'N/A' }}</td>
                                <td>{{ $payee->mobile ?: $payee->phone ?: 'N/A' }}</td>
                                <td>{{ $payee->employee_code ?: '-' }}</td>
                                <td>
                                    @if($payee->monthly_salary)
                                        Rs. {{ number_format((float) $payee->monthly_salary, 0) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $payee->status === 'active' ? 'badge-success' : 'badge-secondary' }}">
                                        {{ ucfirst($payee->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No payees/suppliers/employees found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $payees->links() }}
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .finance-shell { padding: 8px 0 16px; }
        .finance-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .section-title { font-weight: 700; color: #2f3b52; margin-bottom: 10px; }
        .required::after { content: ' *'; color: #e53935; }
        .finance-table thead th { background: #1ea7ff; color: #fff; }
    </style>
@endpush
