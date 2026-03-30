@extends('layouts.theme')

@section('title', 'Add Utility Bill')

@section('content')
    @php
        $statusColors = [
            'unpaid' => 'badge-warning',
            'partial' => 'badge-info',
            'pending_approval' => 'badge-primary',
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
                <h3 class="panel-title form-label" >Bill Management <span class="text-muted">|</span> Add Bill</h3>
            </header>
            <div class="box-typical-body panel-body" >
                <form method="POST" action="{{ route('finance.utility.bills.store') }}">
                    @csrf
                    <div class="form-row mt-3" >
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
                            <label class="form-label required">Bill Type</label>
                            <select name="bill_type_id" class="form-control" required>
                                <option value="">- Select -</option>
                                @foreach($billTypes as $type)
                                    <option value="{{ $type->id }}" @selected(old('bill_type_id') == $type->id)>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Reference Number</label>
                            <input type="text" name="reference_number" class="form-control" value="{{ old('reference_number') }}" required>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Bill Month</label>
                            <input type="date" name="bill_month" class="form-control" value="{{ old('bill_month', now()->startOfMonth()->toDateString()) }}" required>
                        </div>
                    </div>

                    <div class="form-row" >
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Issue Date</label>
                            <input type="date" name="issue_date" class="form-control" value="{{ old('issue_date') }}">
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Due Date</label>
                            <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Amount Within Due Date</label>
                            <input type="number" step="0.01" min="0" name="amount_within_due_date" class="form-control" value="{{ old('amount_within_due_date') }}" required>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Fine</label>
                            <input type="number" step="0.01" min="0" name="fine" class="form-control" value="{{ old('fine', 0) }}">
                        </div>
                    </div>

                    <div class="row mt-3 ml-3">
    <div class="col-12 ">
        <label class="form-label small fw-semibold text-dark required">
            Remarks
        </label>
       <textarea name="details[remarks]"
    class="form-control form-control-sm @error('details.remarks') is-invalid @enderror"
    rows="3"
    placeholder="Remarks" style="margin-bottom:7px; width:99.5%;">{{ old('details.remarks', '') }}</textarea>
        @error('details.remarks')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
</div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-inline btn-primary-outline">Save Bill</button>
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
                                <th >Sr#</th>
                                <th>Reference</th>
                                <th>Campus</th>
                                <th>Bill Type</th>
                                <th>Bill Month</th>
                                <th>Amount</th>
                                <th>Paid</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bills as $index => $bill)
                                <tr>
                                    <td>{{ $bills->firstItem() + $index }}</td>
                                    <td>{{ $bill->reference_number }}</td>
                                    <td>{{ $bill->campus->code ?? 'N/A' }}</td>
                                    <td>{{ $bill->billType->name ?? 'N/A' }}</td>
                                    <td>{{ optional($bill->bill_month)->format('M-Y') }}</td>
                                    <td>Rs. {{ number_format((float) $bill->amount, 0) }}</td>
                                    <td>Rs. {{ number_format((float) $bill->paid_amount, 0) }}</td>
                                    <td><span class="badge {{ $statusColors[$bill->status] ?? 'badge-secondary' }}">{{ ucfirst(str_replace('_', ' ', $bill->status)) }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No utility bills found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $bills->links() }}
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        
.box-typical.box-typical-dashboard{
    margin:0px 0px 5px !important;
    
}
.box-typical.box-typical-dashboard .box-typical-header{
    display:flex;

}
    

        .finance-shell { padding: 8px 0 16px; }
        .finance-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .required::after { content: ' *'; color: #e53935; }
        .finance-table thead th { background: #1ea7ff; color: #fff; }
    </style>
@endpush
