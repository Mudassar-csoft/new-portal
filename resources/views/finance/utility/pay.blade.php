@extends('layouts.theme')

@section('title', 'Pay Utility Bills')

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
                <h3 class="panel-title">Pay Utility Bill (Approval Required)</h3>
            </header>
            <div class="box-typical-body panel-body">
                <form method="POST" action="{{ route('finance.utility.pay.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-row mt-3">
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Select Bill</label>
                            <select name="bill_id" class="form-control" required>
                                <option value="">- Select -</option>
                                @foreach($bills as $bill)
                                    @php
                                        $balance = max(0, (float) $bill->amount - (float) $bill->paid_amount);
                                    @endphp
                                    <option value="{{ $bill->id }}" @selected(old('bill_id') == $bill->id)>
                                        {{ $bill->reference_number }} | {{ $bill->campus->code ?? 'N/A' }} | {{ $bill->billType->name ?? 'N/A' }} | Balance Rs. {{ number_format($balance, 0) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Payment Date</label>
                            <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', now()->toDateString()) }}" required>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Paid Amount</label>
                            <input type="number" step="0.01" min="1" name="paid_amount" class="form-control" value="{{ old('paid_amount') }}" required>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Payment Method</label>
                            <select name="payment_method" class="form-control" required>
                                <option value="cash" @selected(old('payment_method') === 'cash')>Cash</option>
                                <option value="bank" @selected(old('payment_method') === 'bank')>Bank</option>
                                <option value="cheque" @selected(old('payment_method') === 'cheque')>Cheque</option>
                            </select>
                        </div>
</div>
                        <div class="form-row" >
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">Payment Ref No</label>
                            <input type="text" name="payment_ref_no" class="form-control" value="{{ old('payment_ref_no') }}">
                        </div>
                        
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}">
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">Cheque No</label>
                            <input type="text" name="cheque_no" class="form-control" value="{{ old('cheque_no') }}">
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">Bank Receipt No</label>
                            <input type="text" name="bank_receipt_no" class="form-control" value="{{ old('bank_receipt_no') }}">
                        </div>
                    </div>
                    <div class="form-row" >
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Transaction Image</label>
                            <input type="file" name="attachment" class="form-control-file" required>
                        </div>
                    </div>

                    <div class="col-12 mt-0">
        <label class="form-label small fw-semibold text-dark">
            Remarks
        </label>
       <textarea name="remarks"
    class="form-control form-control-sm @error('remarks') is-invalid @enderror"
    rows="3"
    placeholder="Remarks" style= "padding:10px; margin-bottom:7px;" >{{ old('remarks') }}</textarea>
        @error('remarks')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-inline btn-primary-outline">Submit For Approval</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="box-typical box-typical-dashboard panel panel-default finance-card mt-3">
            <header class="box-typical-header panel-heading finance-header">
                <h3 class="panel-title">Utility Payment History</h3>
            </header>
            <div class="box-typical-body panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered finance-table">
                        <thead>
                            <tr>
                                <th>Sr#</th>
                                <th>Receipt</th>
                                <th>Bill Ref</th>
                                <th>Campus</th>
                                <th>Bill Type</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $index => $payment)
                                <tr>
                                    <td>{{ $payments->firstItem() + $index }}</td>
                                    <td>{{ $payment->receipt_no ?? '-' }}</td>
                                    <td>{{ $payment->bill->reference_number ?? 'N/A' }}</td>
                                    <td>{{ $payment->bill->campus->code ?? 'N/A' }}</td>
                                    <td>{{ $payment->bill->billType->name ?? 'N/A' }}</td>
                                    <td>Rs. {{ number_format((float) $payment->paid_amount, 0) }}</td>
                                    <td>{{ ucfirst($payment->payment_method ?? '-') }}</td>
                                    <td>{{ optional($payment->payment_date)->format('d-M-Y') }}</td>
                                    <td>
                                        @if($payment->attachment_path)
                                            <a class="btn btn-primary btn-sm" href="{{ asset('storage/' . $payment->attachment_path) }}" target="_blank">Image</a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No utility payment found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $payments->links() }}
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
       
.box-typical .panel-heading {
    padding: 7px 20px;
}
.box-typical.box-typical-dashboard .box-typical-body {
    overflow: hidden;
    /* padding: 1px; */
    margin: 5px;
}
        .finance-shell { padding: 8px 0 16px; }
        .finance-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .required::after { content: ' *'; color: #343434; }
        .finance-table thead th { background: #1ea7ff; color: #fff; }
    </style>
@endpush
