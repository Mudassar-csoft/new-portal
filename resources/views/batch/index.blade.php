@extends('layouts.theme')

@section('title', $pageTitle)

@section('content')

	<div class="batch-shell">
		<div class="batch-card box-typical box-typical-dashboard panel panel-default">
			<header class="box-typical-header panel-heading d-flex justify-content-between">
				<div>
					<h3 class="panel-title mb-0">Batches</h3>
					<!-- <small class="text-muted">List of batches by campus and program.</small> -->
				</div>
				<a href="{{ route('batch.create') }}" class="btn btn-primary">New Batch</a>
			</header>
			<div class="box-typical-body panel-body">
				<div class="table-responsive">
					<table class="table table-hover">
						<thead>
							<tr>
								<th>Code</th>
								<th>Program</th>
								<th>Campus</th>
								<th>Session</th>
								<th>Time</th>
								<th>Dates</th>
								<th>Instructor</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							@forelse($batches as $batch)
								<tr>
									<td>{{ $batch->code }}</td>
									<td>{{ $batch->program->title ?? $batch->program->name ?? '—' }}</td>
									<td>{{ $batch->campus->name ?? '—' }}</td>
									<td>{{ ucfirst($batch->session ?? 'n/a') }}</td>
									<td>
										@if($batch->start_time && $batch->end_time)
											{{ \Carbon\Carbon::parse($batch->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($batch->end_time)->format('h:i A') }}
										@else
											—
										@endif
									</td>
									<td>
										@if($batch->start_date)
											{{ \Carbon\Carbon::parse($batch->start_date)->format('d M Y') }}
										@endif
										@if($batch->end_date)
											<br>to {{ \Carbon\Carbon::parse($batch->end_date)->format('d M Y') }}
										@endif
									</td>
									<td>{{ $batch->instructor ?? '—' }}</td>
									<td>{{ ucfirst($batch->status ?? 'active') }}</td>
								</tr>
							@empty
								<tr>
									<td colspan="8" class="text-center text-muted">No batches found.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
				@if(method_exists($batches, 'links'))
					<div class="mt-3">
						{{ $batches->links() }}
					</div>
				@endif
			</div>
		</div>
	</div>
    @php
        $filters = $filters ?? ['scope' => 'all', 'campus_id' => null, 'program_id' => null, 'session' => null, 'status' => null, 'search' => null];
        $scopeCards = $scopeCards ?? [];
        $badgeClasses = [
            'active' => 'label-success',
            'inactive' => 'label-default',
            'completed' => 'label-primary',
            'cancelled' => 'label-danger',
        ];
    @endphp

    <div class="batch-index-shell">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="box-typical box-typical-dashboard panel panel-default batch-index-card">
            <header class="box-typical-header panel-heading d-flex justify-content-between">
                <div>
                    <h3 class="panel-title mb-0">{{ $pageTitle }}</h3>
                    <small class="text-muted">{{ $pageDescription }}</small>
                </div>
                <div class="d-flex" style="gap:10px;">
                    <a href="{{ route('batch.create') }}" class="btn btn-primary">Create Batch</a>
                    <a href="{{ route('batch.timetable.index') }}" class="btn btn-default">Manage Time Table</a>
                </div>
            </header>
            <div class="box-typical-body panel-body">
                <div class="batch-scope-grid p-2 ">
                    @foreach($scopeCards as $card)
                        <a href="{{ route('batch.index', array_filter(array_merge(request()->except('page', 'scope'), ['scope' => $card['scope'] !== 'all' ? $card['scope'] : null]))) }}"
                           class="batch-scope-card {{ ($filters['scope'] ?? 'all') === $card['scope'] ? 'is-active' : '' }}">
                           <strong>{{ number_format((int) $card['count']) }}</strong>
                           <span class="batch-scope-label">{{ $card['label'] }}</span>
                        </a>
                    @endforeach
                </div>

                <form method="GET" action="{{ route('batch.index') }}" class="batch-filter-form ">
                    <input type="hidden" name="scope" value="{{ $filters['scope'] ?? 'all' }}">
                    <div class="form-row batch-filter-row ">
                        <div class="form-group col-lg-4 col-md-6">
                            <label class="form-label">Campus</label>
                            <select class="form-control" name="campus_id">
                                <option value="">All Campuses</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected(($filters['campus_id'] ?? null) == $campus->id)>
                                        {{ $campus->code }} - {{ $campus->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-4 col-md-6">
                            <label class="form-label">Programme</label>
                            <select class="form-control" name="program_id">
                                <option value="">All Programmes</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" @selected(($filters['program_id'] ?? null) == $program->id)>
                                        {{ $program->code }} - {{ $program->title ?? $program->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-4 col-md-6">
                            <label class="form-label">Session</label>
                            <select class="form-control" name="session">
                                <option value="">All Sessions</option>
                                @foreach(['morning' => 'Morning', 'evening' => 'Evening', 'weekend' => 'Weekend'] as $key => $label)
                                    <option value="{{ $key }}" @selected(($filters['session'] ?? '') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-4 col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status">
                                <option value="">All Statuses</option>
                                @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $key => $label)
                                    <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-lg-4 col-md-6 batch-filter-col-wide">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Batch code, name, campus, instructor, or lab">
                        </div>
                        <div class="form-group batch-filter-actions">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('batch.index', array_filter(['scope' => ($filters['scope'] ?? 'all') !== 'all' ? $filters['scope'] : null])) }}" class="btn btn-danger">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered batch-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Batch Name</th>
                                <th>Programme</th>
                                <th>Campus</th>
                                <th>Session</th>
                                <th>Time</th>
                                <th>Duration</th>
                                <th>Instructor</th>
                                <th>Lab</th>
                                <th>Students</th>
                                <th>Time Table</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($batches as $batch)
                                <tr>
                                    <td>{{ $batch->code }}</td>
                                    <td>{{ $batch->name }}</td>
                                    <td>{{ $batch->program?->title ?? $batch->program?->name ?? 'N/A' }}</td>
                                    <td>{{ $batch->campus?->code ?? $batch->campus?->name ?? 'N/A' }}</td>
                                    <td>{{ ucfirst($batch->session ?? 'n/a') }}</td>
                                    <td>
                                        @if($batch->start_time && $batch->end_time)
                                            {{ \Carbon\Carbon::parse($batch->start_time)->format('h:i A') }}
                                            <br>
                                            <span class="text-muted">to {{ \Carbon\Carbon::parse($batch->end_time)->format('h:i A') }}</span>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        {{ optional($batch->start_date)->format('d M Y') ?: 'N/A' }}
                                        @if($batch->end_date)
                                            <br>
                                            <span class="text-muted">to {{ optional($batch->end_date)->format('d M Y') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $batch->instructor ?: 'N/A' }}</td>
                                    <td>{{ $batch->lab ?: 'N/A' }}</td>
                                    <td>{{ number_format((int) ($batch->admissions_count ?? 0)) }}</td>
                                    <td>{{ number_format((int) ($batch->timetables_count ?? 0)) }} slots</td>
                                    <td><span class="label {{ $badgeClasses[$batch->status] ?? 'label-default' }}">{{ ucfirst($batch->status ?? 'active') }}</span></td>
                                    <td class="text-right">
                                        @include('batch.partials.action', ['actionId' => 'batch-action-' . $batch->id])
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center text-muted">No batches found for the selected filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $batches->links() }}
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        

        .batch-index-card {
            max-width: 1450px;
            margin: 0 auto;
        }
.form-group.batch-filter-actions

 {
    margin: 3.8%;
}
        .batch-scope-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(31%, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .batch-scope-card {
            display: block;
            border: 1px solid #dbe5f1;
            border-radius: 12px;
            padding: 14px 16px;
            padding-right: 10px;
            background: #f8fbff;
            color: #334155;
            text-decoration: none;
            text-align: center;
            transition: all .18s ease;
        }

        .batch-scope-card:hover,
        .batch-scope-card:focus {
            text-decoration: none;
            border-color: #3ba8ff;
            box-shadow: 0 8px 18px rgba(59, 168, 255, 0.12);
        }

        .batch-scope-card.is-active {
            background: #e9f5ff;
            border-color: #1fb2ff;
        }

        .batch-scope-card strong {
            display: block;
            font-size: 22px;
            margin-top: 8px;
        }

        .batch-scope-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #64748b;
        }

        .batch-filter-form {
            margin-bottom: 18px;
        }

        .batch-filter-row {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            align-items: end;
        }

        .batch-filter-col {
            flex: 1 1 180px;
            min-width: 180px;
        }

        .batch-filter-col-wide {
            flex: 2 1 280px;
            min-width: 260px;
        }

        .batch-filter-actions {
            display: flex;
            gap: 10px;
            margin-left: auto;
        }

        .batch-table thead th {
            background: #1fb2ff;
            color: #fff;
            border-color: #1aa4ea;
        }

        .batch-table td,
        .batch-table th {
            vertical-align: middle;
        }

        .batch-action-dropdown .dropdown-menu {
            z-index: 1050;
        }

        @media (max-width: 767px) {
            .batch-filter-actions {
                width: 100%;
                margin-left: 0;
            }
        }
    </style>
@endpush
