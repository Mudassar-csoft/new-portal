@extends('layouts.theme')

@section('title', 'Edit Expense Type')

@section('content')
    <div class="finance-shell">
        @include('partials.validation-errors-alert')

        <section class="box-typical box-typical-dashboard panel panel-default finance-card">
            <header class="box-typical-header panel-heading finance-header">
                <h3 class="form-label panel-title">Expense Management <span class="text-muted">|</span> Edit Expense Type</h3>
            </header>
            <div class="box-typical-body panel-body">
                <form method="POST" action="{{ route('finance.expense.type.update', $type) }}">
                    @csrf
                    @method('PUT')
                    <div class="form-row mt-2">
                        <div class="form-group col-lg-5 col-md-6">
                            <label class="form-label required">Expense Type</label>
                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="{{ old('name', $type->name) }}"
                                placeholder="Enter Expense Type"
                                required
                            >
                        </div>
                        <div class="form-group col-lg-5 col-md-6">
                            <label class="form-label required">Category</label>
                            <select name="category" class="form-control" required>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" @selected(old('category', $type->category ?? 'general') === $category)>
                                        {{ ucfirst($category) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-2 col-md-12">
                            <label class="form-label d-block">Status</label>
                            <label class="checkbox-toggle mb-0">
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $type->is_active))>
                                <span>Active</span>
                            </label>
                        </div>
                    </div>

                    <div class="text-right mt-3">
                        <button type="submit" class="btn btn-inline btn-primary-outline">Update</button>
                        <a href="{{ route('finance.expense.types') }}" class="btn btn-inline btn-danger-outline">Cancel</a>
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
