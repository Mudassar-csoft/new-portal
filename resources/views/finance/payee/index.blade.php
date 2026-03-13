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
                <h3 class="panel-title form-label">Expense Management <span class="text-muted">|</span> Manage Supplier, Payee & Employee</h3>
            </header>
            <div class="box-typical-body panel-body">
                <form method="POST" action="{{ route('finance.payees.store') }}">
                    @csrf
                    <h4 class="section-title mt-2" style = "gap:18px;padding-left:15px">Basic Profile</h4>
                    <div class="form-row">
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Type</label class="form-label required">
                            <select name="type" class="form-control" required>
                                @foreach(['supplier' => 'Supplier', 'payee' => 'Payee', 'employee' => 'Employee'] as $k => $v)
                                    <option value="{{ $k }}" @selected(old('type') === $k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Full Name</label class="form-label required">
                            <input type="text" name="full_name" class="form-control" value="{{ old('full_name') }}" required>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Display Name</label class="form-label required">
                            <input type="text" name="display_name" class="form-control" value="{{ old('display_name') }}">
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Campus / Franchise</label class="form-label required">
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
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Phone</label class="form-label required">
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Mobile</label class="form-label required">
                            <input type="text" name="mobile" class="form-control" value="{{ old('mobile') }}">
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Email</label class="form-label required">
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">CNIC</label class="form-label required">
                            <input type="text" name="cnic" class="form-control" value="{{ old('cnic') }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Company Name</label class="form-label required">
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}">
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Payment Terms</label class="form-label required">
                            <input type="text" name="payment_terms" class="form-control" value="{{ old('payment_terms') }}" placeholder="Due on receipt / Net 30">
                        </div>
                        <div class="form-group custom-col-6">
                            <label class="form-label required">Address</label class="form-label required">
                            <input type="text" name="postal_address" class="form-control" value="{{ old('postal_address') }}">
                        </div>
                    </div>

                    <h4 class="section-title mt-1" style = "gap:18px;padding-left:15px">Employee Fields (for payroll)</h4>
                    <div class="form-row" >
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Employee Code</label class="form-label required">
                            <input type="text" name="employee_code" class="form-control" value="{{ old('employee_code') }}">
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Designation</label class="form-label required">
                            <input type="text" name="designation" class="form-control" value="{{ old('designation') }}">
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Monthly Salary (PKR)</label class="form-label required">
                            <input type="number" step="0.01" min="0" name="monthly_salary" class="form-control" value="{{ old('monthly_salary') }}">
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Joining Date</label class="form-label required">
                            <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date') }}">
                        </div>
                    </div>

                    <h4 class="section-title mt-1" style = "gap:18px;padding-left:15px">Bank Details</h4>
                    <div class="form-row">
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Bank Name</label class="form-label required">
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}">
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Account Title</label class="form-label required">
                            <input type="text" name="account_title" class="form-control" value="{{ old('account_title') }}">
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Account Number</label class="form-label required">
                            <input type="text" name="account_number" class="form-control" value="{{ old('account_number') }}">
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">IBAN</label class="form-label required">
                            <input type="text" name="iban" class="form-control" value="{{ old('iban') }}">
                        </div>
                    </div>

                    <div class="col-12 pr-1">
        <label class="form-label small fw-semibold text-dark required">
            Remarks
        </label class="form-label required">
       <textarea name="details[remarks]"
    class="form-control form-control-sm @error('details.remarks') is-invalid @enderror"
    rows="3"
    placeholder="Remarks" style= "padding:10px; margin-bottom:7px;" >{{ old('details.remarks', '') }}</textarea>
        @error('details.remarks')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

                    <div class="text-right" >
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
                            <th>Sr#</th>
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
       .custom-col-6{
        width: 48%;
       }
.finance-shell {
    padding: 0px 0 10px !important;
}
        .finance-shell { padding: 8px 0 16px; }
        .finance-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .section-title { font-weight: 400;  color: #343434; margin-bottom: 0px; text-transform: uppercase;font-size:14px; }
        .required::after { content: ' *'; color: #e53935; }
        .finance-table thead th { background: #1ea7ff; color: #fff; }


       @media (max-width: 480px) {
        *{
            white-space: normal;
        }
    .custom-col-6,.col-lg-3 col-md-6 {
        max-width: 95% !important;
        flex: 0 0 95% !important;
    }
}
    </style>
@endpush
