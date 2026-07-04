@extends('layouts.theme')

@section('title', 'Manage Expense Types')

@section('content')
    <div class="finance-shell">
        @include('partials.session-status-alert')
        @include('partials.session-error-alert')
        @include('partials.validation-errors-alert')

        <section class="box-typical box-typical-dashboard panel panel-default finance-card">
            <header class="box-typical-header panel-heading finance-header">
                <h3 class="form-label panel-title">Expense Management <span class="text-muted">|</span> Manage Expense Type</h3>
            </header>
            <div class="box-typical-body panel-body">
                @if($canCreateExpenseTypes)
                    <form class="mb-3" method="POST" action="{{ route('finance.expense.types.store') }}">
                        @csrf
                        <div class="form-row mt-2" >
                            <div class="form-group col-lg-5 col-md-5">
                                <label class=" form-label required">Expense Type</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Enter Expense Type" required>
                            </div>
                            <div class="form-group col-lg-5 col-md-5">
                                <label class="form-label required">Category</label>
                                <select name="category" class="form-control" required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" @selected(old('category', 'general') === $category)>{{ ucfirst($category) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-lg-2 col-md-2 d-flex align-items-end mt-4 pt-2">
                                <button type="submit" class="btn btn-inline btn-primary-outline w-100">Save</button>
                            </div>
                        </div>
                    </form>
                @endif

                @if($canViewExpenseTypes)
                    <div class="table-responsive">
                    <table class="table table-bordered finance-table">
                        <thead>
                        <tr>
                            <th>Sr#</th>
                            <th>Type Name</th>
                            <!-- <th>Category</th>
                            <th>Status</th> -->
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($types as $index => $type)
                            <tr>
                                <td>{{ $types->firstItem() + $index }}</td>
                                <td>{{ $type->name }}</td>
                                <!-- <td>{{ ucfirst($type->category ?? 'general') }}</td>
                                <td>
                                    <span class="badge {{ $type->is_active ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $type->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td> -->
                                <td>{{ optional($type->created_at)->format('d-M-Y') }}</td>
                                <td>
                                    @if($canManageExpenseTypes)
                                        <div class="dropdown">
                                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                                Action
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="{{ route('finance.expense.type.edit', $type) }}">Edit</a>
                                                <form method="POST" action="{{ route('finance.expense.type.delete', $type) }}" onsubmit="return confirm('Are you sure you want to delete this expense type?')">
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
                                <td colspan="4" class="text-center text-muted">No expense types found.</td>
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
