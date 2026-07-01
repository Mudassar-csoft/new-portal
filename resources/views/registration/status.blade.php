@extends('layouts.theme')

@section('title', 'Registration Status')

@section('content')
	@php
		$registrations = $registrations ?? collect();
		$activePeriod = $activePeriod ?? 'all';
		$periodCounts = $periodCounts ?? [];
		$search = $search ?? '';
		$perPage = $perPage ?? 25;

		$tabs = [
			'all' => 'All',
			'today' => 'Today',
			'month' => 'Current Month',
			'year' => 'Current Year',
		];

		$badgeColors = [
			'all' => 'badge-secondary',
			'today' => 'badge-success',
			'month' => 'badge-info',
			'year' => 'badge-primary',
		];
	@endphp

	<div class="reg-status-shell">
		<div class="follow-card box-typical box-typical-dashboard panel panel-default">
			<div class="follow-tab-bar">
				@foreach ($tabs as $key => $label)
					<a
						class="follow-tab {{ $activePeriod === $key ? 'active' : '' }}"
						href="{{ route('registration.status', array_filter([
							'period' => $key !== 'all' ? $key : null,
							'search' => $search !== '' ? $search : null,
							'per_page' => $perPage !== 25 ? $perPage : null,
						], static fn ($value) => $value !== null && $value !== '')) }}"
					>
						<span class="label-text">{{ $label }}</span>
						<span class="badge {{ $badgeColors[$key] ?? 'badge-secondary' }}">{{ $periodCounts[$key] ?? 0 }}</span>
					</a>
				@endforeach
			</div>

			<div class="box-typical-body panel-body follow-body">
				<form method="GET" action="{{ route('registration.status') }}" class="follow-controls">
					<input type="hidden" name="period" value="{{ $activePeriod !== 'all' ? $activePeriod : '' }}">
					<div class="d-flex follow-status-meta" style="gap:0.5rem;align-items: center; flex-wrap: wrap;">
						<label class="mr-2 mb-0">Show</label>
						<select name="per_page" class="form-control form-control-sm follow-per-page" onchange="this.form.submit()">
							@foreach ([10, 25, 50, 100] as $option)
								<option value="{{ $option }}" @selected((int) $perPage === $option)>{{ $option }}</option>
							@endforeach
						</select>
						<label class="ml-2 mb-0">Entries</label>
						<a
							href="{{ route('registration.status', array_filter([
								'period' => $activePeriod !== 'all' ? $activePeriod : null,
							], static fn ($value) => $value !== null && $value !== '')) }}"
							class="btn btn-default btn-sm"
						>
							Reset
						</a>
					</div>
					<div class="follow-search">
						<input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search name, phone, course, campus...">
						<button type="submit" class="btn btn-primary btn-sm">Search</button>
					</div>
				</form>

				<div class="table-responsive">
					<table class="table table-bordered follow-table" id="reg-table">
						<thead>
							<tr>
								<th>Sr</th>
								<th>Name</th>
								<th>Primary Contact</th>
								<th>Campus Code</th>
								<th>Status</th>
								<th>Date</th>
								<!-- <th>Fee (Rs.)</th>
								<th>Receipt</th> -->
								<th class="text-center">Actions</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($registrations as $idx => $row)
								@php
									$statusLabel = $row->admission
										? 'Enrolled'
										: ucfirst((string) ($row->status ?: 'registered'));
									$statusClass = $row->admission
										? 'label-success'
										: match ((string) $row->status) {
											'registered' => 'label-info',
											'pending' => 'label-warning',
										'cancelled', 'cancelled_registration' => 'label-danger',
											default => 'label-default',
										};
								@endphp
								<tr>
									<td class="text-center">{{ ($registrations->firstItem() ?? 1) + $idx }}</td>
									<td>
										<a href="{{ route('student.show', $row) }}" class="student-name-link {{ $row->admission ? '' : 'student-name-link--pending' }}" title="View student detail">
											{{ $row->student_name }}
										</a>
									</td>
									<td>{{ $row->phone }}</td>
									<td>{{ $row->campus?->code ?? 'N/A' }}</td>
									<td>
										<span class="label {{ $statusClass }}">{{ $statusLabel }}</span>
									</td>
									<td>{{ optional($row->registered_at ?? $row->created_at)->format('d-M-Y') ?? 'N/A' }}</td>
									<td class="action-cell">
										@include('registration.partials.action', ['actionId' => 'reg-action-' . $idx, 'registration' => $row, 'leadId' => $row->lead_id])
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>

				<div class="follow-footer">
					<div id="reg-count">Showing {{ $registrations->firstItem() ?? 0 }} to {{ $registrations->lastItem() ?? 0 }} of {{ $registrations->total() ?? 0 }} entries</div>
					@php
						$currentPage = $registrations->currentPage();
						$lastPage = $registrations->lastPage();
						$startPage = max(1, $currentPage - 2);
						$endPage = min($lastPage, $currentPage + 2);
					@endphp
					<ul class="pagination pagination-sm mb-0">
						<li class="page-item {{ $registrations->onFirstPage() ? 'disabled' : '' }}">
							<a class="page-link" href="{{ $registrations->onFirstPage() ? '#' : $registrations->previousPageUrl() }}">Previous</a>
						</li>

						@if ($startPage > 1)
							<li class="page-item"><a class="page-link" href="{{ $registrations->url(1) }}">1</a></li>
							@if ($startPage > 2)
								<li class="page-item disabled"><span class="page-link">...</span></li>
							@endif
						@endif

						@for ($page = $startPage; $page <= $endPage; $page++)
							<li class="page-item {{ $page === $currentPage ? 'active' : '' }}">
								<a class="page-link" href="{{ $registrations->url($page) }}">{{ $page }}</a>
							</li>
						@endfor

						@if ($endPage < $lastPage)
							@if ($endPage < $lastPage - 1)
								<li class="page-item disabled"><span class="page-link">...</span></li>
							@endif
							<li class="page-item"><a class="page-link" href="{{ $registrations->url($lastPage) }}">{{ $lastPage }}</a></li>
						@endif

						<li class="page-item {{ $registrations->hasMorePages() ? '' : 'disabled' }}">
							<a class="page-link" href="{{ $registrations->hasMorePages() ? $registrations->nextPageUrl() : '#' }}">Next</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('styles')
	<style>
		/* .reg-status-shell {
			padding: 8px 0 16px;
		} */

		.action-cell {
			min-width: 110px;
			white-space: nowrap;
			/* position: relative; */
		}

		.student-name-link {
			color: #0a6fd1;
			font-weight: 600;
			text-decoration: none;
			border-bottom: 1px dashed transparent;
			transition: color 0.15s ease, border-color 0.15s ease;
		}
		.student-name-link:hover {
			color: #0958a8;
			border-bottom-color: #0a6fd1;
			text-decoration: none;
		}
		.student-name-link--pending {
			color: #0a6fd1;
		}
		.student-name-link--pending:hover {
			color: #0a6fd1;
			border-bottom-color: #54667a;
		}
		.table-responsive {
			overflow: visible !important;
		}
		.follow-card, .follow-body {
    overflow: visible !important;
}
		.follow-tab-bar .follow-tab {
			text-decoration: none;
		}
		.follow-status-meta {
			font-size: 13px;
			font-weight: 500;
			color: #64748b;
		}
		.follow-per-page {
			width: 84px;
		}
		.registration-action-dropdown{
			position: relative;
		}
		.registration-action-dropdown .dropdown-menu {
			min-width: 292px;
			position: absolute !important;
			top: 100% !important;
			right: 0 !important;
			margin-top: 6px !important;
			margin-right: 0 !important;
			left: auto !important;
			transform: none !important;
			z-index: 9999;
		}

		@media (max-width: 768px) {
			.reg-status-shell .table-responsive {
				overflow-x: auto !important;
				-webkit-overflow-scrolling: touch;
			}

			.reg-status-shell .follow-table {
				width: max-content !important;
				min-width: 100% !important;
			}

			.reg-status-shell .follow-table th,
			.reg-status-shell .follow-table td {
				white-space: nowrap;
			}
		}
	</style>
@endpush

@push('scripts')
@endpush
