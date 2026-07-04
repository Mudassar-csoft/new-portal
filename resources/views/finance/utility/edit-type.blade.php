@extends('layouts.theme')

@section('title', 'Edit Bill Type')

@section('content')
    <div class="finance-shell">
        @include('partials.validation-errors-alert')

        <section class="box-typical box-typical-dashboard panel panel-default finance-card">
            <header class="box-typical-header panel-heading finance-header">
                <h3 class="panel-title">Bill Management <span class="text-muted">|</span> Edit Bill Type</h3>
            </header>
            <div class="box-typical-body panel-body">
                <form method="POST" action="{{ route('finance.utility.type.update', $type) }}">
                    @csrf
                    @method('PUT')
                    <div class="form-row">
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">Company Name</label>
                            <input
                                type="text"
                                name="company_name"
                                class="form-control"
                                value="{{ old('company_name', $type->company_name) }}"
                                placeholder="FESCO"
                            >
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Bill Type</label>
                            <input
                                type="text"
                                name="service_name"
                                class="form-control"
                                value="{{ old('service_name', $type->service_name ?? $type->name) }}"
                                placeholder="Electricity"
                                required
                            >
                        </div>
                        <div class="form-group col-lg-4 col-md-6">
                            <label class="form-label">Default Payee</label>
                            <select name="payee_id" class="form-control">
                                <option value="">- Select -</option>
                                @foreach($payees as $payee)
                                    <option value="{{ $payee->id }}" @selected(old('payee_id', $type->payee_id) == $payee->id)>
                                        {{ $payee->full_name }} ({{ ucfirst($payee->type) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-2 col-md-6">
                            <label class="d-block">Status</label>
                            <label class="checkbox-toggle mb-0">
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $type->is_active))>
                                <span>Active</span>
                            </label>
                        </div>
                    </div>

                    <div class="text-right mt-3">
                        <button type="submit" class="btn btn-inline btn-primary-outline">Update</button>
                        <a href="{{ route('finance.utility.types') }}" class="btn btn-inline btn-danger-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .finance-shell { padding: 8px 0 16px; }
        .finance-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .required::after { content: ' *'; color: #e53935; }
        .checkbox-toggle { display: inline-flex; align-items: center; gap: 8px; min-height: 38px; }
    </style>
@endpush
