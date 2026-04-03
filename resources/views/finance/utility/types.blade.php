@extends('layouts.theme')

@section('title', 'Add Bill Type')

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
                <h3 class="panel-title">Bill Management <span class="text-muted">|</span> Add Bill Type</h3>
            </header>
            <div class="box-typical-body panel-body">
                <form class="mb-3" method="POST" action="{{ route('finance.utility.types.store') }}">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="required">Company Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}" placeholder="FESCO">
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="required">Bill Type</label>
                            <input type="text" name="service_name" class="form-control" value="{{ old('service_name') }}" placeholder="Electricity" required>
                        </div>
                        <div class="form-group col-lg-4 col-md-6">
                            <label>Default Payee</label>
                            <select name="payee_id" class="form-control">
                                <option value="">- Select -</option>
                                @foreach($payees as $payee)
                                    <option value="{{ $payee->id }}" @selected(old('payee_id') == $payee->id)>
                                        {{ $payee->full_name }} ({{ ucfirst($payee->type) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-2 col-md-6 d-flex align-items-end mt-1 pt-3">
                            <button type="submit" class="btn btn-inline btn-primary-outline w-100">Save</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered finance-table">
                        <thead>
                        <tr>
                            <th>Sr#</th>
                            <th>Company</th>
                            <th>Bill Type</th>
                            <th>Display Name</th>
                            <th>Default Payee</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($types as $index => $type)
                            <tr>
                                <td>{{ $types->firstItem() + $index }}</td>
                                <td>{{ $type->company_name ?? '-' }}</td>
                                <td>{{ $type->service_name ?? $type->name }}</td>
                                <td>{{ $type->display_name }}</td>
                                <td>{{ $type->payee->full_name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge {{ $type->is_active ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $type->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No bill type found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $types->links() }}
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .finance-shell { padding: 8px 0 16px; }
        .finance-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .required::after { content: ' *'; color: #e53935; }
        .finance-table thead th { background: #1ea7ff; color: #fff; }
    </style>
@endpush
