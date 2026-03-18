@extends('layouts.theme')

@section('title', $pageTitle)

@section('content')

	<div class="campus-shell">
		<div class="box-typical box-typical-dashboard panel panel-default campus-card">
			<header class="box-typical-header panel-heading d-flex align-items-center justify-content-between">
				<div>
					<h3 class="panel-title mb-0">Campuses</h3>
					<!-- <small class="text-muted">List of campuses.</small> -->
				</div>
				<a href="{{ route('campus.create') }}" class="btn btn-primary">New Campus</a>
			</header>
			<div class="box-typical-body panel-body">
				<div class="table-responsive">
					<table class="table table-hover">
						<thead>
							<tr>
								<th>Code</th>
								<th>Title</th>
								<th>City</th>
								<th>Type</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							@forelse($campuses as $campus)
								<tr>
									<td>{{ $campus->code }}</td>
									<td>{{ $campus->name }}</td>
									<td>{{ $campus->city }}</td>
									<td>{{ ucfirst($campus->campus_type) }}</td>
									<td>{{ ucfirst($campus->status) }}</td>
								</tr>
							@empty
								<tr>
									<td colspan="5" class="text-center text-muted">No campuses found.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
				@if(method_exists($campuses, 'links'))
					<div class="mt-3">
						{{ $campuses->links() }}
					</div>
				@endif
			</div>
		</div>
	</div>
    @php
        $filters = $filters ?? ['scope' => 'all', 'campus_type' => null, 'status' => null, 'country' => null, 'city' => null, 'search' => null];
        $scopeCards = $scopeCards ?? [];
        $typeLabels = $typeOptions ?? ['company' => 'Company Owned', 'franchise' => 'Franchise'];
        $typeBadgeClasses = ['company' => 'label-primary', 'franchise' => 'label-warning'];
        $statusBadgeClasses = ['active' => 'label-success', 'inactive' => 'label-default'];
    @endphp

    <div class="campus-index-shell">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="box-typical box-typical-dashboard panel panel-default campus-index-card">
            <header class="box-typical-header panel-heading d-flex align-items-center justify-content-between">
                <div>
                    <h3 class="panel-title mb-0">{{ $pageTitle }}</h3>
                    <small class="text-muted">{{ $pageDescription }}</small>
                </div>
                <a href="{{ route('campus.create') }}" class="btn btn-primary">Create Campus / Franchise</a>
            </header>
            <div class="box-typical-body panel-body">
                <div class="campus-scope-grid">
                    @foreach($scopeCards as $card)
                        <a href="{{ route('campus.index', array_filter(array_merge(request()->except('page', 'scope'), ['scope' => $card['scope'] !== 'all' ? $card['scope'] : null]))) }}"
                           class="campus-scope-card {{ ($filters['scope'] ?? 'all') === $card['scope'] ? 'is-active' : '' }}">
                            <span class="campus-scope-label">{{ $card['label'] }}</span>
                            <strong>{{ number_format((int) $card['count']) }}</strong>
                        </a>
                    @endforeach
                </div>

                <form method="GET" action="{{ route('campus.index') }}" class="campus-filter-form">
                    <input type="hidden" name="scope" value="{{ $filters['scope'] ?? 'all' }}">
                    <div class="form-row campus-filter-row">
                        <div class="form-group campus-filter-col">
                            <label class="form-label">Campus Type</label>
                            <select class="form-control" name="campus_type">
                                <option value="">All Types</option>
                                @foreach($typeLabels as $key => $label)
                                    <option value="{{ $key }}" @selected(($filters['campus_type'] ?? '') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group campus-filter-col">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status">
                                <option value="">All Statuses</option>
                                @foreach(['active' => 'Active', 'inactive' => 'Inactive'] as $key => $label)
                                    <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group campus-filter-col">
                            <label class="form-label">Country</label>
                            <select class="form-control" name="country">
                                <option value="">All Countries</option>
                                @foreach($countryOptions as $country)
                                    <option value="{{ $country }}" @selected(($filters['country'] ?? '') === $country)>{{ $country }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group campus-filter-col">
                            <label class="form-label">City</label>
                            <select class="form-control" name="city">
                                <option value="">All Cities</option>
                                @foreach($cityOptions as $city)
                                    <option value="{{ $city }}" @selected(($filters['city'] ?? '') === $city)>{{ $city }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group campus-filter-col campus-filter-col-wide">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Campus name, code, city, contact, address, or remarks">
                        </div>
                        <div class="form-group campus-filter-actions">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('campus.index', array_filter(['scope' => ($filters['scope'] ?? 'all') !== 'all' ? $filters['scope'] : null])) }}" class="btn btn-danger">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered campus-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Campus / Franchise</th>
                                <th>Location</th>
                                <th>Type</th>
                                <th>Contact</th>
                                <th>Labs</th>
                                <th>Batches</th>
                                <th>Students</th>
                                <th>Inventory</th>
                                <th>Discounts</th>
                                <th>Royalty</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($campuses as $campus)
                                <tr>
                                    <td>{{ $campus->code }}</td>
                                    <td>
                                        <strong>{{ $campus->title ?? $campus->name }}</strong>
                                        @if($campus->title && $campus->title !== $campus->name)
                                            <br>
                                            <span class="text-muted">{{ $campus->name }}</span>
                                        @endif
                                        @if($campus->remarks)
                                            <br>
                                            <span class="text-muted">{{ \Illuminate\Support\Str::limit($campus->remarks, 70) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $campus->city ?: 'N/A' }}{{ $campus->country ? ', ' . $campus->country : '' }}
                                        @if($campus->address)
                                            <br>
                                            <span class="text-muted">{{ \Illuminate\Support\Str::limit($campus->address, 70) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="label {{ $typeBadgeClasses[$campus->campus_type] ?? 'label-default' }}">
                                            {{ $typeLabels[$campus->campus_type] ?? ucfirst((string) $campus->campus_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $campus->campus_email ?: 'N/A' }}
                                        @if($campus->mobile || $campus->landline)
                                            <br>
                                            <span class="text-muted">{{ $campus->mobile ?: $campus->landline }}</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format((int) ($campus->labs_count ?? 0)) }}</td>
                                    <td>{{ number_format((int) ($campus->batches_count ?? 0)) }}</td>
                                    <td>{{ number_format((int) ($campus->admissions_count ?? 0)) }}</td>
                                    <td>{{ number_format((int) ($campus->inventory_items_count ?? 0)) }}</td>
                                    <td>{{ number_format((int) ($campus->program_discounts_count ?? 0)) }}</td>
                                    <td>
                                        @if($campus->campus_type === 'franchise' && $campus->royalty_rate !== null)
                                            {{ rtrim(rtrim(number_format((float) $campus->royalty_rate, 2), '0'), '.') }}%
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="label {{ $statusBadgeClasses[$campus->status] ?? 'label-default' }}">
                                            {{ ucfirst($campus->status ?? 'inactive') }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        @include('campus.partials.action', ['actionId' => 'campus-action-' . $campus->id])
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center text-muted">No campuses found for the selected filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $campuses->links() }}
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .campus-index-shell {
            padding: 10px;
        }

        .campus-index-card {
            max-width: 1500px;
            margin: 0 auto;
        }

        .campus-scope-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .campus-scope-card {
            display: block;
            border: 1px solid #dbe5f1;
            border-radius: 12px;
            padding: 14px 16px;
            background: #fff8f3;
            color: #334155;
            text-decoration: none;
            transition: all .18s ease;
        }

        .campus-scope-card:hover,
        .campus-scope-card:focus {
            text-decoration: none;
            border-color: #ef7c2e;
            box-shadow: 0 8px 18px rgba(239, 124, 46, 0.12);
        }

        .campus-scope-card.is-active {
            background: #fff0e2;
            border-color: #ef7c2e;
        }

        .campus-scope-card strong {
            display: block;
            font-size: 22px;
            margin-top: 8px;
        }

        .campus-scope-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #64748b;
        }

        .campus-filter-form {
            margin-bottom: 18px;
        }

        .campus-filter-row {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            align-items: end;
        }

        .campus-filter-col {
            flex: 1 1 180px;
            min-width: 180px;
        }

        .campus-filter-col-wide {
            flex: 2 1 300px;
            min-width: 280px;
        }

        .campus-filter-actions {
            display: flex;
            gap: 10px;
            margin-left: auto;
        }

        .campus-table thead th {
            background: #ef7c2e;
            color: #fff;
            border-color: #d86e28;
        }

        .campus-table td,
        .campus-table th {
            vertical-align: middle;
        }

        .campus-action-dropdown .dropdown-menu {
            z-index: 1050;
        }

        @media (max-width: 767px) {
            .campus-filter-actions {
                width: 100%;
                margin-left: 0;
            }
        }
    </style>
@endpush
