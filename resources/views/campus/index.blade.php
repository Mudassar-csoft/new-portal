@extends('layouts.theme')

@section('title', $pageTitle)

@section('content')

	<div class="campus-shell">
		<div class="box-typical box-typical-dashboard panel panel-default campus-card">
			<header class="box-typical-header panel-heading d-flex justify-content-between">
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
            <header class="box-typical-header panel-heading d-flex justify-content-between">
                <div>
                    <h3 class="panel-title mb-0">{{ $pageTitle }}</h3>
                    <small class="text-muted">{{ $pageDescription }}</small>
                </div>
                <a href="{{ route('campus.create') }}" class="btn btn-primary">Create Campus / Franchise</a>
            </header>
            <div class="box-typical-body panel-body">
                <div class="campus-scope-grid p-2">
                    @foreach($scopeCards as $card)
                        <a href="{{ route('campus.index', array_filter(array_merge(request()->except('page', 'scope'), ['scope' => $card['scope'] !== 'all' ? $card['scope'] : null]))) }}"
                           class="campus-scope-card {{ ($filters['scope'] ?? 'all') === $card['scope'] ? 'is-active' : '' }}">
                           <strong>{{ number_format((int) $card['count']) }}</strong>
                           <span class="campus-scope-label">{{ $card['label'] }}</span>
                        </a>
                    @endforeach
                </div>

                <form method="GET" action="{{ route('campus.index') }}" class="campus-filter-form">
                    <input type="hidden" name="scope" value="{{ $filters['scope'] ?? 'all' }}">
                    <div class="form-row campus-filter-row">
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">Campus Type</label>
                            <select class="form-control" name="campus_type">
                                <option value="">All Types</option>
                                @foreach($typeLabels as $key => $label)
                                    <option value="{{ $key }}" @selected(($filters['campus_type'] ?? '') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status">
                                <option value="">All Statuses</option>
                                @foreach(['active' => 'Active', 'inactive' => 'Inactive'] as $key => $label)
                                    <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">Country</label>
                            <select class="form-control" name="country">
                                <option value="">All Countries</option>
                                @foreach($countryOptions as $country)
                                    <option value="{{ $country }}" @selected(($filters['country'] ?? '') === $country)>{{ $country }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-3 col-md-6">
                            <label class="form-label">City</label>
                            <select class="form-control" name="city">
                                <option value="">All Cities</option>
                                @foreach($cityOptions as $city)
                                    <option value="{{ $city }}" @selected(($filters['city'] ?? '') === $city)>{{ $city }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-9">
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
        /* .campus-index-shell {
            padding: 10px;
        } */

        .campus-index-card {
            /* max-width: 1500px; */
            margin: 0 auto;
        }

        .campus-scope-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .campus-scope-card {
            display: block;
            grid-column: span 2;
            height:25vh;
            border: 1px solid #00a8ff;
            border-radius: 12px;
            padding: 14px 16px;
            background: #00a8ff;
            color: white;
            text-align: center;
            text-decoration: none;
            transition: all .18s ease;
        }

        .campus-scope-card,
        .campus-scope-card strong,
        .campus-scope-card:hover,
        .campus-scope-card:hover strong,
        .campus-scope-card:focus,
        .campus-scope-card:focus strong {
            color: #fff !important;
        }

        .campus-scope-card:nth-child(5n + 1) {
            background: #f35f62;
            border-color: #f35f62;
        }

        .campus-scope-card:nth-child(5n + 2) {
            background: #fdc518;
            border-color: #fdc518;
        }

        .campus-scope-card:nth-child(5n + 3) {
            background: #975ce7;
            border-color: #975ce7;
        }

        .campus-scope-card:nth-child(5n + 4) {
            background: #a2cf37;
            border-color: #a2cf37;
        }

        .campus-scope-card:nth-child(5n + 5) {
            background: #00a8ff;
            border-color: #00a8ff;
        }

        .campus-scope-card:hover,
        .campus-scope-card:focus {
            text-decoration: none;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12);
            filter: brightness(1.02);
        }

        .campus-scope-card.is-active {
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.18);
            filter: brightness(0.94);
        }

        .campus-scope-card strong {
            display: block;
            font-size: 22px;
            margin-top: 25px;
        }

        .campus-scope-label {
            font-size: 14px;
            font-weight:600;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: white;
        }

        .campus-scope-card:nth-last-child(2):nth-child(3n + 1),
        .campus-scope-card:nth-last-child(2):nth-child(3n + 1) + .campus-scope-card {
            grid-column: span 3;
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

        .col-lg-3 col-md-6 {
            flex: 1 1 180px;
            min-width: 180px;
        }

        .col-lg-3 col-md-6-wide {
            flex: 2 1 300px;
            min-width: 280px;
        }

        .campus-filter-actions {
            display: flex;
            gap: 10px;
            margin: auto;
            margin-right: 15px;
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
            .campus-scope-grid {
                grid-template-columns: 1fr;
            }

            .campus-scope-card,
            .campus-scope-card:nth-last-child(2):nth-child(3n + 1),
            .campus-scope-card:nth-last-child(2):nth-child(3n + 1) + .campus-scope-card {
                display: flex;
                grid-column: span 1;
                min-height: 150px;
                height: auto;
                padding: 18px 16px;
                align-items: center;
                justify-content: center;
                flex-direction: column;
            }

            .campus-scope-card strong {
                margin-top: 0;
                margin-bottom: 8px;
            }

            .campus-filter-actions {
                width: 100%;
                margin-left: 0;
            }
        }
    </style>
@endpush
