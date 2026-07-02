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
					<div class="d-flex" style="gap:0.5rem;align-items: baseline;">
						<label class="mr-2 mb-0">Show</label>
						<select name="per_page" class="form-control form-control-sm" onchange="this.form.submit()">
							@foreach ([10, 25, 50, 100] as $option)
								<option value="{{ $option }}" @selected((int) $perPage === $option)>{{ $option }}</option>
							@endforeach
						</select>
						<label class="ml-2 mb-0">Entries</label>
					</div>
					<div class="follow-search">
						<input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search...">
						<!-- <button type="submit" class="reg-search-submit" aria-label="Search">
							<i class="fa fa-search"></i>
						</button> -->
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
					@include('partials.follow-pagination', ['paginator' => $registrations, 'countId' => 'reg-count'])
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

		.reg-status-shell {
			max-width: 100%;
			overflow-x: hidden;
		}

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
			overflow-x: hidden !important;
			overflow-y: visible !important;
		}
		.follow-card, .follow-body {
    overflow: visible !important;
}
		.follow-tab-bar .follow-tab {
			text-decoration: none;
		}
		.reg-search-submit {
			border: 0;
			background: transparent;
			color: #8a97a8;
			line-height: 1;
			padding: 0;
		}
		.reg-search-submit:hover,
		.reg-search-submit:focus {
			color: #1593ff;
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
				overflow-x: hidden !important;
			}

			.reg-status-shell .follow-table {
				width: 100% !important;
				min-width: 0 !important;
			}

			.reg-status-shell .follow-table th,
			.reg-status-shell .follow-table td {
				white-space: normal;
				word-break: break-word;
			}
		}
	</style>
@endpush

@push('scripts')
@endpush
