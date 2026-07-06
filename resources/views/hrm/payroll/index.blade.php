@extends('layouts.theme')

@section('title', 'Payroll')

@section('content')
    @php
        $payoutSummary = $payoutSummary ?? ['bank' => 0, 'cash' => 0, 'cheque' => 0];
    @endphp

    <div class="hrm-shell">
        @include('partials.session-status-alert')
        @include('partials.validation-errors-alert')

        <!-- <div class="row mb-3 bg-white p-2 m-2">
            <div class="col-md-6 col-lg-4">
                <div class="payroll-stat tone-bank">
                    <div class="payroll-value">Rs. {{ number_format((float) ($payoutSummary['bank'] ?? 0), 0) }}</div>
                    <div class="payroll-label">Bank Payout List</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="payroll-stat tone-cash">
                    <div class="payroll-value">Rs. {{ number_format((float) ($payoutSummary['cash'] ?? 0), 0) }}</div>
                    <div class="payroll-label">Cash Payout List</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="payroll-stat tone-cheque">
                    <div class="payroll-value">Rs. {{ number_format((float) ($payoutSummary['cheque'] ?? 0), 0) }}</div>
                    <div class="payroll-label">Cheque Payout List</div>
                </div>
            </div>
        </div> -->

        <div class="row">
            <div class="col-lg-6">
                <section class="box-typical box-typical-dashboard panel panel-default hrm-card">
                    <header class="box-typical-header panel-heading">
                        <h3 class="panel-title form-label">Salary Structure</h3>
                    </header>
                    <div class="box-typical-body panel-body">
                        <form method="POST" action="{{ route('hrm.payroll.structures.store') }}" class="pb-0 hrm-box">
                            @csrf
                            <div class="form-row mb-0" >
                                <div class="form-group col-md-4 mt-0">
                                    <label class="form-label required" >Employee</label>
                                    <select name="employee_id" class="form-control" required>
                                        <option value="">- Select -</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->employee_code ?: 'EMP' }} - {{ trim($employee->first_name . ' ' . $employee->last_name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-4 col-md-4">
                                    <label class="form-label required" >From</label>
                                    <input type="date" name="effective_from" class="form-control" value="{{ now()->toDateString() }}" required>
                                </div>
                                <div class="form-group col-md-4 col-md-4">
                                    <label class="form-label required" >To</label>
                                    <input type="date" name="effective_to" class="form-control">
                                </div>
                                
                            </div>
                            <div class="form-row mt-0">
                                <div class="form-group col-md-4 ">
                                    <label class="form-label required" >Basic</label>
                                    <input type="number" min="0" step="0.01" name="basic_salary" class="form-control" required>
                                </div>
                                 <div class="form-group col-md-4 ">
                                    <label class="form-label required" >OT Rate</label>
                                    <input type="number" min="0" step="0.01" name="overtime_rate" class="form-control" value="0">
                                </div>
                                <div class="form-group col-md-4 d-flex justify-center align-center mt-2" style="gap:5px;">
                                        <input type="checkbox" class="mt-4" name="is_active" value="1" checked>
                                        <label class="form-label required justify-center align-middle" style="margin-top:22px;" > Active Structure</label>
                                    </div>
                                    
                                    
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6 col-lg-4">
                                        <label class="form-label required" >Allowances JSON</label>
                                        <input type="text" name="allowances_json" class="form-control" placeholder='{"house_rent":5000,"transport":2000}'>
                                    </div>

                                    <div class="form-group col-md-6 col-lg-4">
                                        <label class="form-label required" >Deductions JSON</label>
                                        <input type="text" name="deductions_json" class="form-control" placeholder='{"tax":1000,"eobi":200}'>
                                    </div>
                                    
                                </div>
                            <div class="text-right">

                                <button class="btn btn-inline btn-primary-outline" type="submit">Save Structure</button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('hrm.payroll.advances.store') }}" class=" pb-0 hrm-box">
                            @csrf
                            <h5 class="form-label pl-2">Advance / Loan</h5>
                            <div class="form-row">
                                <div class="form-group col-md-6 col-md-4">
                                    <label class="form-label required" >Employee</label>
                                    <select name="employee_id" class="form-control" required>
                                        <option value="">- Select -</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->employee_code ?: 'EMP' }} - {{ trim($employee->first_name . ' ' . $employee->last_name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6 "><label class="form-label required" >Amount</label><input type="number" min="1" step="0.01" name="amount" class="form-control" required></div>
                                <div class="form-group col-md-6"><label class="form-label required" >Installment</label><input type="number" min="0" step="0.01" name="installment_amount" class="form-control"></div>
                                <div class="form-group col-md-6"><label class="form-label required" >Issued Date</label><input type="date" name="issued_date" class="form-control" value="{{ now()->toDateString() }}"></div>
                                <div class="form-group col-md-12"><label class="form-label required" >Remarks</label><input type="text" name="remarks" class="form-control"></div>
                            </div>
                            <div class="text-right">

                                <button class="btn btn-inline btn-primary-outline" type="submit">Save Advance</button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>

            <div class="col-lg-6">
                <section class="box-typical box-typical-dashboard panel panel-default hrm-card">
                    <header class="box-typical-header panel-heading">
                        <h3 class="panel-title form-label">Payroll Month Closing</h3>
                    </header>
                    <div class="box-typical-body panel-body">
                        <form method="POST" action="{{ route('hrm.payroll.runs.store') }}" class="mb-3 hrm-box">
                            @csrf
                            <div class="form-row m-0">
                                <div class="form-group col-md-6 ">
                                    <label class="form-label required" >Campus</label>
                                    <select name="campus_id" class="form-control">
                                        <option value="">All</option>
                                        @foreach($campuses as $campus)
                                            <option value="{{ $campus->id }}">{{ $campus->code }} - {{ $campus->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="form-label required" >Month</label>
                                    <input type="month" name="payroll_month" class="form-control" value="{{ now()->format('Y-m') }}" required>
                                </div>
                                <div class="form-group col-md-6"><label class="form-label required" >From</label><input type="date" name="from_date" class="form-control" value="{{ now()->startOfMonth()->toDateString() }}" required></div>
                                <div class="form-group col-md-6"><label class="form-label required" >To</label><input type="date" name="to_date" class="form-control" value="{{ now()->endOfMonth()->toDateString() }}" required></div>
                                <div class="form-group col-md-12"><label class="form-label required" >Notes</label><input type="text" name="notes" class="form-control"></div>
                                
                            </div>
                            <div class="  payrol-button text-right" >
                                    <button class="btn btn-inline btn-primary-outline" type="submit">Generate Payroll Run</button>
                                </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered hrm-table">
                                <thead><tr><th>Month</th><th>Campus</th><th>Period</th><th>Status</th><th>Action</th></tr></thead>
                                <tbody>
                                    @forelse($runs as $run)
                                        <tr>
                                            <td>{{ $run->payroll_month }}</td>
                                            <td>{{ $run->campus->code ?? 'All' }}</td>
                                            <td>{{ optional($run->from_date)->format('Y-m-d') }} to {{ optional($run->to_date)->format('Y-m-d') }}</td>
                                            <td>{{ ucfirst($run->status) }}</td>
                                            <td>
                                                @if($run->status !== 'closed')
                                                    <form method="POST" action="{{ route('hrm.payroll.runs.close', $run) }}">
                                                        @csrf
                                                        <button class="btn btn-sm btn-outline-danger" type="submit">Close</button>
                                                    </form>
                                                @else
                                                    <span class="text-success">Closed</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted">No payroll runs found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $runs->links() }}
                    </div>
                </section>
            </div>
        </div>

        <section class="box-typical box-typical-dashboard panel panel-default hrm-card mt-3">
            <header class="box-typical-header panel-heading">
                <h3 class="panel-title form-label">Payslips / Payroll Items</h3>
            </header>
            <div class="box-typical-body panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered hrm-table">
                        <thead>
                            <tr>
                                <th>Payslip</th>
                                <th>Month</th>
                                <th>Employee</th>
                                <th>Basic</th>
                                <th>Allowances</th>
                                <th>Deductions</th>
                                <th>Net</th>
                                <th>Mode</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td>{{ $item->payslip_no ?: 'N/A' }}</td>
                                    <td>{{ $item->payrollRun->payroll_month ?? '-' }}</td>
                                    <td>{{ $item->employee?->employee_code ?: 'EMP' }} - {{ $item->employee?->full_name ?: '-' }}</td>
                                    <td>Rs. {{ number_format((float) $item->basic_salary, 0) }}</td>
                                    <td>Rs. {{ number_format((float) $item->allowance_total, 0) }}</td>
                                    <td>Rs. {{ number_format((float) ($item->deduction_total + $item->advance_deduction + $item->loan_deduction), 0) }}</td>
                                    <td>Rs. {{ number_format((float) $item->net_payable, 0) }}</td>
                                    <td>{{ strtoupper($item->payment_mode) }}</td>
                                    <td>{{ ucfirst($item->status) }}</td>
                                    <td>
                                        @if($item->status !== 'paid')
                                            <form method="POST" action="{{ route('hrm.payroll.items.paid', $item) }}" class="form-inline">
                                                @csrf
                                                <select name="payment_mode" class="form-control form-control-sm mr-2">
                                                    @foreach(['bank','cash','cheque'] as $mode)
                                                        <option value="{{ $mode }}" @selected($item->payment_mode === $mode)>{{ strtoupper($mode) }}</option>
                                                    @endforeach
                                                </select>
                                                <button class="btn btn-sm btn-outline-primary" type="submit">Mark Paid</button>
                                            </form>
                                        @else
                                            <span class="text-success">{{ optional($item->paid_at)->format('Y-m-d') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center text-muted">No payroll items found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $items->links() }}
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --space-hrm-payroll-index-1: 10px;
        }

      
        /* .hrm-shell { padding: 8px 0 16px; } */
        .payroll-stat {
            border-radius: 10px;
            padding: 12px 14px;
            color: #fff;
            text-align: center;
            height: 25vh;
            margin-bottom: 12px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, .12);
            min-height: 86px;
        }
        .payroll-label { font-size: 0.875rem; margin-top: 1rem; text-transform: uppercase; opacity: .88; font-weight: 600; }
        .payroll-value { margin-top: 30px; font-size: 1.125rem; font-weight: 700; }
        .tone-bank { background: #f35f62; }
        .tone-cash { background: #fdc518; }
        .tone-cheque { background: #a2cf37 }
        .hrm-table thead th { background: #eef2f7; color: #334155; }
        .hrm-box { border: 1px solid #e6ebf1; border-radius: 8px; padding: var(--space-hrm-payroll-index-1); }
        @media (max-width: 760px)  {
            .payroll-label { margin-top: 5px; }
            .payroll-value { margin-top: var(--space-hrm-payroll-index-1); }
        }
    </style>
@endpush
