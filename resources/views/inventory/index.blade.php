@extends('layouts.theme')

@section('title', 'Campus Stock Register')

@section('content')
    <div class="inventory-shell">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <section class="box-typical box-typical-dashboard panel panel-default inventory-card">
            <header class="box-typical-header panel-heading inventory-header">
                <h3 class="panel-title">Inventory Management <span class="text-muted">|</span> Campus Stock Register</h3>
                <a href="{{ route('inventory.create', array_filter(['campus_id' => $filters['campus_id']])) }}" class="btn btn-primary btn-sm">Feed Inventory</a>
            </header>
            <div class="box-typical-body panel-body">
                <div class="inventory-summary">
                    <div class="summary-tile">
                        <strong>{{ number_format($summary['records']) }}</strong>
                        <span class="summary-label">Records</span>
                    </div>
                    <div class="summary-tile">
                        <strong>{{ number_format($summary['total_quantity']) }}</strong>
                        <span class="summary-label">Total Quantity</span>
                    </div>
                    <div class="summary-tile warning">
                        <strong>{{ number_format($summary['low_stock']) }}</strong>
                        <span class="summary-label">Low Stock Items</span>
                    </div>
                </div>

                <form method="GET" action="{{ route('inventory.index') }}" class="inventory-filter-form">
                    <div class="form-row mt-2">
                        <div class="form-group col-lg-4 col-md-6">
                            <label class="form-label">Campus / Franchise</label>
                            <select name="campus_id" class="form-control">
                                <option value="">All Campuses</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected($filters['campus_id'] == $campus->id)>
                                        {{ $campus->code }} - {{ $campus->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-4 col-md-6">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-control">
                                <option value="">All Categories</option>
                                @foreach($categories as $value => $label)
                                    <option value="{{ $value }}" @selected($filters['category'] === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-4 col-md-6">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" value="{{ $filters['search'] }}" placeholder="Search by code, item, brand, model">
                        </div>
                    </div>
                    <div class="text-right inventory-actions">
                        <a href="{{ route('inventory.index') }}" class="btn btn-inline btn-danger-outline">Reset</a>
                        <button type="submit" class="btn btn-inline btn-primary-outline">Apply Filter</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="box-typical box-typical-dashboard panel panel-default inventory-card mt-3">
            <div class="box-typical-body panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered inventory-table">
                        <thead>
                        <tr>
                            <th>Code</th>
                            <th>Campus</th>
                            <th>Category</th>
                            <th>Item Details</th>
                            <th>Qty</th>
                            <th>Location</th>
                            <th>Condition</th>
                            <th>Created</th>
                            @if($isAdmin)
                                <th>Action</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($items as $item)
                            @php
                                $isLowStock = $item->minimum_stock > 0 && $item->quantity <= $item->minimum_stock;
                            @endphp
                            <tr>
                                <td>{{ $item->item_code }}</td>
                                <td>
                                    <strong>{{ $item->campus->code ?? 'N/A' }}</strong>
                                    <div class="text-muted">{{ $item->campus->name ?? 'N/A' }}</div>
                                </td>
                                <td>{{ $item->categoryLabel() }}</td>
                                <td>
                                    <strong>{{ $item->item_name }}</strong>
                                    @if($item->brand || $item->model_no)
                                        <div class="text-muted">
                                            {{ collect([$item->brand, $item->model_no])->filter()->join(' | ') }}
                                        </div>
                                    @endif
                                    @if($item->serial_no)
                                        <div class="text-muted">Serial: {{ $item->serial_no }}</div>
                                    @endif
                                    @if($item->remarks)
                                        <div class="text-muted">{{ $item->remarks }}</div>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ number_format($item->quantity) }} {{ strtoupper($item->unit) }}</strong>
                                    @if($isLowStock)
                                        <div><span class="stock-badge low">Low stock</span></div>
                                    @elseif($item->minimum_stock > 0)
                                        <div><span class="stock-badge ok">In stock</span></div>
                                    @endif
                                </td>
                                <td>{{ $item->room_location ?: 'N/A' }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $item->condition_status)) }}</td>
                                <td>
                                    {{ optional($item->created_at)->format('d-M-Y') ?? 'N/A' }}
                                    @if($item->creator)
                                        <div class="text-muted">{{ $item->creator->name }}</div>
                                    @endif
                                </td>
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
                                <td colspan="{{ $isAdmin ? 9 : 8 }}" class="text-center text-muted">No campus inventory found for the selected filter.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="inventory-pagination">
                    {{ $items->links() }}
                </div>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .inventory-shell { padding: 8px 0 16px; }
        .inventory-card { margin: 0 0 6px !important; }
        .inventory-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            flex-wrap: wrap;
        }
        .inventory-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
            margin-bottom: 12px;
            padding: 23px;
        }
        .summary-tile {
            height: 25vh;
            padding: 14px 16px;
            text-align: center;
            border: 1px solid #00a8ff;
            border-radius: 12px;
            background: #00a8ff;
            color: white;
        }

        .summary-tile,
        .summary-tile strong,
        .summary-tile:hover,
        .summary-tile:hover strong,
        .summary-tile:focus,
        .summary-tile:focus strong {
            color: #fff !important;
        }

        .summary-tile:nth-child(5n + 1) {
            background: #f35f62;
            border-color: #f35f62;
        }

        .summary-tile:nth-child(5n + 2) {
            background: #fdc518;
            border-color: #fdc518;
        }

        .summary-tile:nth-child(5n + 3) {
            background: #975ce7;
            border-color: #975ce7;
        }

        .summary-tile:nth-child(5n + 4) {
            background: #a2cf37;
            border-color: #a2cf37;
        }

        .summary-tile:nth-child(5n + 5) {
            background: #00a8ff;
            border-color: #00a8ff;
        }

        .summary-tile strong {
            display: block;
            font-size: 22px;
            margin-top: 25px;
        }
        .summary-label {
            display: block;
            margin-bottom: 4px;
            color: white;
            font-size: 14px;
            font-weight:600;
            text-transform: uppercase;
        }
        .inventory-filter-form {
            margin-top: 2px;
        }
        .inventory-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 4px;
        }
        .inventory-table thead th {
            background: #1f8ef1;
            color: #fff;
        }
        .stock-badge {
            display: inline-block;
            margin-top: 4px;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }
        .stock-badge.ok {
            background: #e8f7ee;
            color: #1e8e4d;
        }
        .stock-badge.low {
            background: #fff0ef;
            color: #d93025;
        }
        .inventory-pagination {
            margin-top: 12px;
        }
        .inventory-action-cell {
            min-width: 100px;
        }
        .inventory-action-dropdown .dropdown-menu {
            min-width: 140px;
        }

        @media (max-width: 767px) {
            .summary-tile {
                display: flex;
                min-height: 150px;
                height: auto;
                padding: 18px 16px;
                align-items: center;
                justify-content: center;
                flex-direction: column;
            }

            .summary-tile strong {
                margin-top: 0;
                margin-bottom: 8px;
            }
        }
    </style>
@endpush
