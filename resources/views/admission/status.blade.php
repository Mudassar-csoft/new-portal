@extends('layouts.theme')

@section('title', 'Admission Status')

@section('content')
	@php
		$admissions = $admissions ?? collect();

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
				'today' => $admissions->filter(fn($a) => optional($a->admission_date)->isToday())->count(),
				'month' => $admissions->filter(fn($a) => optional($a->admission_date)->isSameMonth(now()))->count(),
				'year' => $admissions->filter(fn($a) => optional($a->admission_date)->isSameYear(now()))->count(),
				default => $admissions->count(),
			};
		}
	@endphp

	<div class="adm-status-shell">
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
				<div class="follow-controls ">
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
						<input type="text" id="adm-search" class="form-control form-control-sm" placeholder="Search...">
						<i class="fa fa-search"></i>
					</div>
				</div>

				<div class="table-responsive">
					<table class="table table-bordered follow-table" id="adm-table">
						<thead>
							<tr>
								<th>Sr</th>
								<th>Name</th>
								<th>Course</th>
								<th>Batch</th>
								<th>Date</th>
								<th>Contact</th>
								<th>City</th>
								<th class="text-center">Actions</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($admissions as $idx => $row)
								@php
									$admDate = optional($row->admission_date ?? $row->created_at)->format('Y-m-d');
								@endphp
								<tr data-date="{{ $admDate }}">
									<td class="text-center">{{ $idx + 1 }}</td>
									<td>
										@if($row->registration_id)
											<a href="{{ route('student.show', $row->registration_id) }}" class="adm-name-link" title="View student detail">
												{{ $row->student_name }}
											</a>
										@else
											{{ $row->student_name }}
										@endif
									</td>
									<td>{{ $row->program->title ?? $row->program->name ?? '' }}</td>
									<td>{{ $row->batch->name ?? $row->batch->code ?? '' }}</td>
									<td>{{ $admDate }}</td>
									<td>{{ $row->phone }}</td>
									<td>{{ $row->city }}</td>
									<td class=" action-cell">
										@include('admission.partials.action', [
											'actionId' => 'adm-action-' . $idx,
											'admission' => $row,
											'leadId' => $row->lead_id ?? null,
										])
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>

				<div class="follow-footer">
					<div id="adm-count">Showing 1 to {{ count($admissions) }} of {{ count($admissions) }} entries</div>
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
		.adm-status-shell {
			/* padding: 0 6px 0 16px; */
		}

		.action-cell {
			min-width: 110px;
			white-space: nowrap;
			position: relative;
		}
		.adm-name-link {
			color: #0a6fd1;
			font-weight: 600;
			text-decoration: none;
			border-bottom: 1px dashed transparent;
			transition: color 0.15s ease, border-color 0.15s ease;
		}
		.adm-name-link:hover {
			color: #0958a8;
			border-bottom-color: #0a6fd1;
			text-decoration: none;
		}
		.table-responsive {
			overflow: visible !important;
		}
		.follow-card, .follow-body {
    overflow: visible !important;
}
		.admission-action-dropdown{
			position: relative;
		}

		.admission-action-dropdown .dropdown-menu {
			min-width: 292px;
			position: absolute !important;
			top: 100% !important;
			left: auto !important;
			right: 0 !important;
			margin-top: 6px !important;
			margin-right: 0 !important;
			transform: none !important;
			z-index: 9999;
		}

		@media (max-width: 768px) {
			.adm-status-shell .table-responsive {
				overflow-x: auto !important;
				-webkit-overflow-scrolling: touch;
			}

			.adm-status-shell .follow-table {
				width: max-content !important;
				min-width: 100% !important;
			}

			.adm-status-shell .follow-table th,
			.adm-status-shell .follow-table td {
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
				var rows = document.querySelectorAll('#adm-table tbody tr');
				var searchVal = (document.getElementById('adm-search').value || '').toLowerCase();
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
				document.getElementById('adm-count').textContent = 'Showing ' + (visible ? 1 : 0) + ' to ' + visible + ' of ' + visible + ' entries';
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

				document.getElementById('adm-search').addEventListener('input', function () {
					var activeTab = document.querySelector('.follow-tab.active');
					var status = activeTab ? activeTab.getAttribute('data-status') : 'all';
					filterByStatus(status);
				});

				filterByStatus('all');
			});
		})();
	</script>
@endpush
