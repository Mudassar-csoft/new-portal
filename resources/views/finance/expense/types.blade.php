@extends('layouts.theme')

@section('title', 'Manage Expense Types')

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
                <h3 class="form-label panel-title">Expense Management <span class="text-muted">|</span> Manage Expense Type</h3>
            </header>
            <div class="box-typical-body panel-body">
                <form class="mb-3" method="POST" action="{{ route('finance.expense.types.store') }}">
                    @csrf
                    <div class="form-row mt-2" style = "padding-left:15px">
                        <div class="form-group col-md-6">
                            <label class=" form-label required">Expense Type</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Enter Expense Type" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label required">Category</label>
                            <select name="category" class="form-control">
                                <option value="">General</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" @selected(old('category') === $category)>{{ ucfirst($category) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-2 d-flex align-items-end mt-2 pt-3">
                            <button type="submit" class="btn btn-inline btn-primary-outline w-100">Save</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered finance-table">
                        <thead>
                        <tr>
                            <th style="width: 80px;">Sr#</th>
                            <th>Type Name</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th style="width: 160px;">Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($types as $index => $type)
                            <tr>
                                <td>{{ $types->firstItem() + $index }}</td>
                                <td>{{ $type->name }}</td>
                                <td>{{ ucfirst($type->category ?? 'general') }}</td>
                                <td>
                                    <span class="badge {{ $type->is_active ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $type->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ optional($type->created_at)->format('d-M-Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No expense types found.</td>
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
        * {
    font-family: 'Proxima Nova', sans-serif !important;
    font-size: 12px !important; 
    margin: 0;
    padding: 0;
    
    }
    
body, button, html, input, select, textarea {
    color: #343434;
    height: 32px;
    font-family: 'Proxima Nova', sans-serif;
    line-height: 1.4;
    text-rendering: optimizeLegibility;
    -moz-osx-font-smoothing: grayscale;
    -webkit-font-smoothing: antialiased;
    -moz-font-smoothing: antialiased;
    -o-font-smoothing: antialiased;
}
.select2-container--arrow .select2-selection--single .select2-selection__rendered,
.select2-container--default .select2-selection--single .select2-selection__rendered,
.select2-container--white .select2-selection--single .select2-selection__rendered {
    border: solid 1px #d8e2e7;
    -webkit-border-radius: .25rem;
    border-radius: .25rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #343434;
    padding: .375rem 25px .375rem 1rem;
    min-height: 32px;
    background: #fff
}
.form-label{
    font-size: 11px;
    font-weight: 600 ;
    color: #343434;
    text-transform: uppercase;
    margin-bottom: 3px;
    
}
        .finance-shell { padding: 8px 0 16px; }
        .finance-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .required::after { content: ' *'; color: #e53935; }
        .finance-table thead th { background: #1ea7ff; color: #fff; }

    </style>
@endpush
