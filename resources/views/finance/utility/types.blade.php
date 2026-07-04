@extends('layouts.theme')

@section('title', 'Add Bill Type')

@section('content')
    <div class="finance-shell">
        @include('partials.session-status-alert')
        @include('partials.session-error-alert')
        @include('partials.validation-errors-alert')

        <section class="box-typical box-typical-dashboard panel panel-default finance-card">
            <header class="box-typical-header panel-heading finance-header">
                <h3 class="panel-title">Bill Management <span class="text-muted">|</span> Add Bill Type</h3>
            </header>
            <div class="box-typical-body panel-body">
                @if($canCreateBillTypes)
                    <form class="mb-3" method="POST" action="{{ route('finance.utility.types.store') }}">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-lg-3 col-md-6">
                                <label class="form-label required">Company Name</label>
                                <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}" placeholder="FESCO">
                            </div>
                            <div class="form-group col-lg-3 col-md-6">
                                <label class="form-label required">Bill Type</label>
                                <input type="text" name="service_name" class="form-control" value="{{ old('service_name') }}" placeholder="Electricity" required>
                            </div>
                            <div class="form-group col-lg-4 col-md-6">
                                <label class="form-label">Default Payee</label>
                                <select name="payee_id" class="form-control">
                                    <option value="">- Select -</option>
                                    @foreach($payees as $payee)
                                        <option value="{{ $payee->id }}" @selected(old('payee_id') == $payee->id)>
                                            {{ $payee->full_name }} ({{ ucfirst($payee->type) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-lg-2 col-md-6 d-flex align-items-end mt-3 pt-3">
                                <button type="submit" class="btn btn-inline btn-primary-outline w-100">Save</button>
                            </div>
                        </div>
                    </form>
                @endif

                @if($canViewBillTypes)
                    <div class="table-responsive">
                    <table class="table table-bordered finance-table">
                        <thead>
                        <tr>
                            <th>Sr#</th>
                            <th>Type Name</th>
                            <th>Payee</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($types as $index => $type)
                            <tr>
                                <td>{{ $types->firstItem() + $index }}</td>
                                <td>{{ $type->display_name }}</td>
                                <td>{{ $type->payee->full_name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge {{ $type->is_active ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $type->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ optional($type->created_at)->format('d-M-Y') }}</td>
                                <td>
                                    @if($canManageBillTypes)
                                        <div class="dropdown">
                                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                                Action
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="{{ route('finance.utility.type.edit', $type) }}">Edit</a>
                                                <form method="POST" action="{{ route('finance.utility.type.delete', $type) }}" onsubmit="return confirm('Are you sure you want to delete this utility type?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="dropdown-item text-danger" type="submit">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
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
                @endif
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
