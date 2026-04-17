@extends('layouts.theme')

@section('title', 'Registration Status')

@section('content')
	@php
		$registrations = $registrations ?? collect();

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

		$tabCounts = [];
		foreach ($tabs as $key => $label) {
			$tabCounts[$key] = match ($key) {
				'today' => $registrations->filter(fn($r) => optional($r->registered_at)->isToday())->count(),
				'month' => $registrations->filter(fn($r) => optional($r->registered_at)->isSameMonth(now()))->count(),
				'year' => $registrations->filter(fn($r) => optional($r->registered_at)->isSameYear(now()))->count(),
				default => $registrations->count(),
			};
		}
	@endphp

	<div class="reg-status-shell">
		<div class="follow-card box-typical box-typical-dashboard panel panel-default">
			<div class="follow-tab-bar">
				@foreach ($tabs as $key => $label)
					<div class="follow-tab {{ $loop->first ? 'active' : '' }}" data-status="{{ $key }}">
						<span class="label-text">{{ $label }}</span>
						<span class="badge {{ $badgeColors[$key] ?? 'badge-secondary' }}">{{ $tabCounts[$key] ?? 0 }}</span>
					</div>
				@endforeach
			</div>

			<div class="box-typical-body panel-body follow-body">
				<div class="follow-controls">
					<div class="d-flex" style="gap:0.5rem;align-items: baseline;">
						<label class="mr-2 mb-0">Show</label>
						<select class="form-control form-control-sm">
							<option>10</option>
							<option>25</option>
							<option>50</option>
						</select>
						<label class="ml-2 mb-0">Entries</label>
					</div>
					<div class="follow-search">
						<input type="text" id="reg-search" class="form-control form-control-sm" placeholder="Search...">
						<i class="fa fa-search"></i>
					</div>
				</div>

				<div class="table-responsive">
					<table class="table table-bordered follow-table" id="reg-table">
						<thead>
							<tr>
								<th>Sr</th>
								<th>Name</th>
								<th>Program</th>
								<th>Reg. No</th>
								<th>Date</th>
								<th>Contact</th>
								<th>Fee (Rs.)</th>
								<th>Receipt</th>
								<th class="text-center">Actions</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($registrations as $idx => $row)
								@php
									$regDate = optional($row->registered_at ?? $row->created_at)->format('Y-m-d');
								@endphp
								<tr data-date="{{ $regDate }}">
									<td class="text-center">{{ $idx + 1 }}</td>
									<td>{{ $row->student_name }}</td>
									<td>{{ $row->program->title ?? $row->program->name ?? '' }}</td>
									<td>{{ $row->registration_number }}</td>
									<td>{{ $regDate }}</td>
									<td>{{ $row->phone }}</td>
									<td>{{ $row->fee }}</td>
									<td>{{ $row->receipt_number }}</td>
									<td class=" action-cell">
										@include('registration.partials.action', ['actionId' => 'reg-action-' . $idx, 'registration' => $row, 'leadId' => $row->lead_id])
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>

				<div class="follow-footer">
					<div id="reg-count">Showing 1 to {{ count($registrations) }} of {{ count($registrations) }} entries</div>
					<ul class="pagination pagination-sm mb-0">
						<li class="page-item disabled"><span class="page-link">Previous</span></li>
						<li class="page-item active"><span class="page-link">1</span></li>
						<li class="page-item disabled"><span class="page-link">Next</span></li>
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
		.table-responsive {
			overflow: visible !important;
		}
		.follow-card, .follow-body {
    overflow: visible !important;
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
	<script>
		(function () {
			function isSameMonth(dateStr) {
				var d = new Date(dateStr);
				var n = new Date();
				return d.getMonth() === n.getMonth() && d.getFullYear() === n.getFullYear();
			}

			function isSameYear(dateStr) {
				var d = new Date(dateStr);
				var n = new Date();
				return d.getFullYear() === n.getFullYear();
			}

			function filterByStatus(status) {
				var rows = document.querySelectorAll('#reg-table tbody tr');
				var searchVal = (document.getElementById('reg-search').value || '').toLowerCase();
				var visible = 0;
				rows.forEach(function (row) {
					var date = row.getAttribute('data-date');
					var matchesStatus = true;
					if (status === 'today') {
						var today = new Date().toISOString().slice(0, 10);
						matchesStatus = date === today;
					} else if (status === 'month') {
						matchesStatus = isSameMonth(date);
					} else if (status === 'year') {
						matchesStatus = isSameYear(date);
					}
					var matchesSearch = row.innerText.toLowerCase().indexOf(searchVal) !== -1;
					var show = matchesStatus && matchesSearch;
					row.style.display = show ? '' : 'none';
					if (show) visible++;
				});
				document.getElementById('reg-count').textContent = 'Showing ' + (visible ? 1 : 0) + ' to ' + visible + ' of ' + visible + ' entries';
			}

			document.addEventListener('DOMContentLoaded', function () {
				var tabs = document.querySelectorAll('.follow-tab');
				tabs.forEach(function (tab) {
					tab.addEventListener('click', function () {
						tabs.forEach(function (t) { t.classList.remove('active'); });
						this.classList.add('active');
						filterByStatus(this.getAttribute('data-status'));
					});
				});

				document.getElementById('reg-search').addEventListener('input', function () {
					var activeTab = document.querySelector('.follow-tab.active');
					var status = activeTab ? activeTab.getAttribute('data-status') : 'all';
					filterByStatus(status);
				});

				filterByStatus('all');
			});
		})();
	</script>
@endpush
