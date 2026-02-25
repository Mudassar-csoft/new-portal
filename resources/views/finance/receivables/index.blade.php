@extends('layouts.theme')

@section('title', 'Receivables')

@section('content')
    @php
        $statusColors = [
            'pending' => 'badge-warning',
            'paid' => 'badge-success',
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
                <h3 class="panel-title form-label">Manual Invoice (Fine / Certificate / Other)</h3>
            </header>
            <div class="box-typical-body panel-body">
                <form method="POST" action="{{ route('finance.receivables.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="form-label required" >Campus / Franchise</label>
                            <select name="campus_id" class="form-control" required>
                                <option value="">- Select -</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected(old('campus_id') == $campus->id)>
                                        {{ $campus->code }} - {{ $campus->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label required" >Charge Type</label>
                            <select name="charge_type_id" class="form-control" required>
                                <option value="">- Select -</option>
                                @foreach($chargeTypes as $type)
                                    <option value="{{ $type->id }}" @selected(old('charge_type_id') == $type->id)>
                                        {{ $type->name }} @if($type->default_amount) (Rs. {{ number_format((float) $type->default_amount, 0) }}) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label required" >Student Name / Source</label>
                            <input type="text" name="student_name" class="form-control" value="{{ old('student_name') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label required" >Amount (PKR)</label>
                            <input type="number" step="0.01" min="1" name="amount" class="form-control" value="{{ old('amount') }}" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="form-label required" >Discount</label>
                            <input type="number" step="0.01" min="0" name="discount_amount" class="form-control" value="{{ old('discount_amount', 0) }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label required" >Payment Method (optional)</label>
                            <select name="payment_method" class="form-control">
                                <option value="">Pending Invoice</option>
                                <option value="cash" @selected(old('payment_method') === 'cash')>Cash</option>
                                <option value="bank" @selected(old('payment_method') === 'bank')>Bank</option>
                                <option value="cheque" @selected(old('payment_method') === 'cheque')>Cheque</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label required" >Payment Ref No</label>
                            <input type="text" name="payment_ref_no" class="form-control" value="{{ old('payment_ref_no') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label required" >Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="form-label required" >Cheque No</label>
                            <input type="text" name="cheque_no" class="form-control" value="{{ old('cheque_no') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label required" >Bank Receipt No</label>
                            <input type="text" name="bank_receipt_no" class="form-control" value="{{ old('bank_receipt_no') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label required" >Transaction Image</label>
                            <input type="file" name="attachment" class="form-control-file">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label required" >Remarks</label>
                            <input type="text" name="remarks" class="form-control" value="{{ old('remarks') }}">
                        </div>
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-inline btn-primary-outline" style="    margin-right: 45px;">Save Manual Invoice</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="box-typical box-typical-dashboard panel panel-default finance-card mt-3">
            <header class="box-typical-header panel-heading finance-header">
                <h3 class="panel-title form-label">Receivables</h3>
            </header>
            <div class="box-typical-body panel-body">
                <form class="mb-3" method="GET" action="{{ route('finance.receivables') }}">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="form-label required" >Campus</label>
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
                            <label class="form-label required" >Status</label>
                            <select class="form-control" name="status">
                                <option value="">All</option>
                                <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending</option>
                                <option value="paid" @selected(($filters['status'] ?? '') === 'paid')>Paid</option>
                            </select>
                        </div>
                         <div class="form-group leave-button col-md-8 text-right mt-5 ml-4">
                            <button type="submit" class="btn btn-inline btn-primary-outline">Filter</button>
                            <a href="{{ route('finance.receivables') }}" class="btn btn-inline btn-danger-outline p-2">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered finance-table">
                        <thead>
                            <tr>
                                <th>Voucher</th>
                                <th>Campus</th>
                                <th>Student / Source</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Net</th>
                                <th>Status</th>
                                <th>Payment Ref</th>
                                <th style="width: 170px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($charges as $charge)
                                <tr>
                                    <td>{{ $charge->voucher_number ?? 'N/A' }}</td>
                                    <td>{{ $charge->campus->code ?? 'N/A' }}</td>
                                    <td>{{ $charge->student_name ?: 'N/A' }}</td>
                                    <td>{{ $charge->chargeType->name ?? 'N/A' }}</td>
                                    <td>Rs. {{ number_format((float) $charge->amount, 0) }}</td>
                                    <td>Rs. {{ number_format((float) $charge->net_amount, 0) }}</td>
                                    <td><span class="badge {{ $statusColors[$charge->status] ?? 'badge-secondary' }}">{{ ucfirst($charge->status) }}</span></td>
                                    <td>{{ $charge->payment_ref_no ?: '-' }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                                Action
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                @if($charge->attachment_path)
                                                    <a class="dropdown-item" href="{{ asset('storage/' . $charge->attachment_path) }}" target="_blank">View Image</a>
                                                @endif
                                                @if($charge->status === 'pending')
                                                    <a class="dropdown-item text-primary" data-toggle="collapse" href="#collect-{{ $charge->id }}">
                                                        Collect Payment
                                                    </a>
                                                @else
                                                    <span class="dropdown-item text-success">Already Paid</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @if($charge->status === 'pending')
                                    <tr class="collapse" id="collect-{{ $charge->id }}">
                                        <td colspan="9">
                                            <form method="POST" action="{{ route('finance.receivables.collect', $charge) }}" enctype="multipart/form-data">
                                                @csrf
                                                <div class="form-row">
                                                    <div class="form-group col-md-2">
                                                        <label class="form-label required" >Payment Method</label>
                                                        <select name="payment_method" class="form-control" required>
                                                            <option value="cash">Cash</option>
                                                            <option value="bank">Bank</option>
                                                            <option value="cheque">Cheque</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label class="form-label required" >Payment Ref No</label>
                                                        <input type="text" name="payment_ref_no" class="form-control" placeholder="Auto if blank">
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label class="form-label required" >Bank Name</label>
                                                        <input type="text" name="bank_name" class="form-control">
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label class="form-label required" >Cheque No</label>
                                                        <input type="text" name="cheque_no" class="form-control">
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label class="form-label required" >Bank Receipt No</label>
                                                        <input type="text" name="bank_receipt_no" class="form-control">
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label class="form-label required" >Transaction Image</label>
                                                        <input type="file" name="attachment" class="form-control-file">
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-sm btn-primary">Submit Collection</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No receivable records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $charges->links() }}
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .col-md-4 {
    flex: 0 0 33.333333% ;
    max-width: 22.333333%;
    margin-top: 10px;
}
        .finance-shell { padding: 8px 0 16px; }
        .finance-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .required::after { content: ' *'; color: #e53935; }
        .finance-table thead th { background: #1ea7ff; color: #fff; }
    </style>
@endpush
