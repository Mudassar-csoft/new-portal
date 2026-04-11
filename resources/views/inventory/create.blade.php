@extends('layouts.theme')

@section('title', $inventoryItem ? 'Edit Campus Inventory' : 'Feed Campus Inventory')

@section('content')
    @php
        $isEditMode = $inventoryItem !== null;
        $formAction = $isEditMode ? route('inventory.update', $inventoryItem) : route('inventory.store');
        $pageHeading = $isEditMode ? 'Edit Campus Inventory' : 'Feed Campus Inventory';
        $submitLabel = $isEditMode ? 'Update Inventory Item' : 'Save Inventory Item';
        $selectedCampus = old('campus_id', $inventoryItem?->campus_id ?? $selectedCampusId);
        $selectedCategory = old('category', $inventoryItem?->category);
        $selectedUnit = old('unit', $inventoryItem?->unit ?? 'pcs');
        $selectedCondition = old('condition_status', $inventoryItem?->condition_status ?? 'good');
    @endphp
    <div class="inventory-shell">
        <section class="box-typical box-typical-dashboard panel panel-default inventory-card">
            <header class="box-typical-header panel-heading inventory-header">
                <h3 class="panel-title">Inventory Management <span class="text-muted">|</span> {{ $pageHeading }}</h3>
                <a href="{{ route('inventory.index', array_filter(['campus_id' => $selectedCampus])) }}" class="btn btn-primary btn-sm">View Stock Register</a>
            </header>
            <div class="box-typical-body panel-body">
                <form method="POST" action="{{ $formAction }}">
                    @csrf
                    @if($isEditMode)
                        @method('PUT')
                    @endif
                    <div class="form-row mt-2">
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Campus / Franchise</label>
                            <select name="campus_id" class="form-control @error('campus_id') is-invalid @enderror" required>
                                <option value="">- Select Campus -</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected($selectedCampus == $campus->id)>
                                        {{ $campus->code }} - {{ $campus->name }} ({{ ucfirst($campus->campus_type) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('campus_id')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Inventory Category</label>
                            <select name="category" class="form-control @error('category') is-invalid @enderror" required>
                                <option value="">- Select Category -</option>
                                @if($selectedCategory && !array_key_exists($selectedCategory, $categories))
                                    <option value="{{ $selectedCategory }}" selected>
                                        Legacy: {{ $categoryLabels[$selectedCategory] ?? ucfirst(str_replace('_', ' ', $selectedCategory)) }} - choose a new category
                                    </option>
                                @endif
                                @foreach($categories as $value => $label)
                                    <option value="{{ $value }}" @selected($selectedCategory === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('category')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Item Name</label>
                            <input type="text" name="item_name" class="form-control @error('item_name') is-invalid @enderror" value="{{ old('item_name', $inventoryItem?->item_name) }}" placeholder="Dell OptiPlex / Office Chair / A4 Paper" required>
                            @error('item_name')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">Brand</label>
                            <input type="text" name="brand" class="form-control @error('brand') is-invalid @enderror" value="{{ old('brand', $inventoryItem?->brand) }}" placeholder="Dell / HP / Local">
                            @error('brand')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">Model No</label>
                            <input type="text" name="model_no" class="form-control @error('model_no') is-invalid @enderror" value="{{ old('model_no', $inventoryItem?->model_no) }}" placeholder="Model / Part number">
                            @error('model_no')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">Serial No</label>
                            <input type="text" name="serial_no" class="form-control @error('serial_no') is-invalid @enderror" value="{{ old('serial_no', $inventoryItem?->serial_no) }}" placeholder="Optional serial number">
                            @error('serial_no')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">Room / Location</label>
                            <input type="text" name="room_location" class="form-control @error('room_location') is-invalid @enderror" value="{{ old('room_location', $inventoryItem?->room_location) }}" placeholder="Lab 1 / Store / Reception">
                            @error('room_location')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Quantity</label>
                            <input type="number" min="1" name="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', $inventoryItem?->quantity ?? 1) }}" required>
                            @error('quantity')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Unit</label>
                            <select name="unit" class="form-control @error('unit') is-invalid @enderror" required>
                                @foreach($units as $value => $label)
                                    <option value="{{ $value }}" @selected($selectedUnit === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('unit')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">Minimum Stock Level</label>
                            <input type="number" min="0" name="minimum_stock" class="form-control @error('minimum_stock') is-invalid @enderror" value="{{ old('minimum_stock', $inventoryItem?->minimum_stock ?? 0) }}">
                            @error('minimum_stock')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label required">Condition</label>
                            <select name="condition_status" class="form-control @error('condition_status') is-invalid @enderror" required>
                                @foreach($conditions as $value => $label)
                                    <option value="{{ $value }}" @selected($selectedCondition === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('condition_status')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">Purchase Date</label>
                            <input type="date" name="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror" value="{{ old('purchase_date', optional($inventoryItem?->purchase_date)->format('Y-m-d')) }}">
                            @error('purchase_date')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        
                        <div class="form-group col-md-12">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control inventory-remarks @error('remarks') is-invalid @enderror" rows="3" placeholder="Any note about condition, warranty, or assigned room">{{ old('remarks', $inventoryItem?->remarks) }}</textarea>
                            @error('remarks')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="text-right mt-4">
                        <button type="submit" class="btn btn-inline btn-primary-outline">{{ $submitLabel }}</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="box-typical box-typical-dashboard panel panel-default inventory-card mt-3">
            <header class="box-typical-header panel-heading inventory-header">
                <h3 class="panel-title">Recent Campus Inventory Entries</h3>
                <span class="inventory-muted">Latest {{ $recentItems->count() }} items</span>
            </header>
            <div class="box-typical-body panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered inventory-table">
                        <thead>
                        <tr>
                            <th>Code</th>
                            <th>Campus</th>
                            <th>Category</th>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Condition</th>
                            @if($isAdmin)
                                <th>Action</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($recentItems as $item)
                            <tr>
                                <td>{{ $item->item_code }}</td>
                                <td>{{ $item->campus->code ?? 'N/A' }}</td>
                                <td>{{ $item->categoryLabel() }}</td>
                                <td>
                                    <strong>{{ $item->item_name }}</strong>
                                    @if($item->brand)
                                        <div class="text-muted">{{ $item->brand }}</div>
                                    @endif
                                </td>
                                <td>{{ number_format($item->quantity) }} {{ $units[$item->unit] ?? strtoupper($item->unit) }}</td>
                                <td>{{ $conditions[$item->condition_status] ?? ucfirst(str_replace('_', ' ', $item->condition_status)) }}</td>
                                @if($isAdmin)
                                    <td class="inventory-action-cell">
                                        <div class="dropdown inventory-action-dropdown">
                                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                                                Action
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right action-key">
                                                <a class="dropdown-item" href="{{ route('inventory.edit', $item) }}">Edit</a>
                                            </div>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? 7 : 6 }}" class="text-center text-muted">No inventory items added yet.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .inventory-shell { padding: 0 6px 0 16px; }
        .inventory-card { margin: 0 0 6px !important; }
        .inventory-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            flex-wrap: wrap;
        }
        .inventory-muted {
            color: #6c7a89;
            font-size: 12px;
            font-weight: 600;
        }
        .inventory-table thead th {
            background: #1f8ef1;
            color: #fff;
        }
        .inventory-remarks {
            min-height: 84px !important;
            height: auto !important;
            padding: 10px !important;
        }
        .required::after {
            content: ' *';
            color: #e53935;
        }
        .inventory-action-cell {
            min-width: 100px;
        }
        .inventory-action-dropdown .dropdown-menu {
            min-width: 140px;
        }
    </style>
@endpush
