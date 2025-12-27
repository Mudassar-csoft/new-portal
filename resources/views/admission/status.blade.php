@extends('layouts.theme')

@section('title', 'Admission Status')

@section('content')
	@php
		$admissions = [
			['name' => 'Ayesha Khan', 'course' => 'Full Stack Developer', 'batch' => 'Batch A', 'date' => now()->format('Y-m-d'), 'contact' => '03038251031', 'city' => 'Faisalabad'],
			['name' => 'Ravi Sharma', 'course' => 'Data Science', 'batch' => 'Batch B', 'date' => now()->subDays(1)->format('Y-m-d'), 'contact' => '9811122233', 'city' => 'Lahore'],
			['name' => 'Meera Patel', 'course' => 'AWS Solutions Architect', 'batch' => 'Batch A', 'date' => now()->subMonths(1)->format('Y-m-d'), 'contact' => '9898989898', 'city' => 'Karachi'],
			['name' => 'Adil Hussain', 'course' => 'DevOps', 'batch' => 'Batch C', 'date' => now()->subMonths(2)->format('Y-m-d'), 'contact' => '9123456780', 'city' => 'Islamabad'],
		];

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
				'today' => count(array_filter($admissions, fn($a) => $a['date'] === now()->format('Y-m-d'))),
				'month' => count(array_filter($admissions, fn($a) => \Illuminate\Support\Carbon::parse($a['date'])->isSameMonth(now()))),
				'year' => count(array_filter($admissions, fn($a) => \Illuminate\Support\Carbon::parse($a['date'])->isSameYear(now()))),
				default => count($admissions),
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
				<div class="follow-controls">
					<div class="form-inline">
						<label class="mr-2 mb-0">Show</label>
						<select class="form-control form-control-sm">
							<option>10</option>
							<option>25</option>
							<option>50</option>
						</select>
						<label class="ml-2 mb-0">entries</label>
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
								<th style="width: 50px;">Sr</th>
								<th>Name</th>
								<th>Course</th>
								<th>Batch</th>
								<th>Date</th>
								<th>Contact</th>
								<th>City</th>
								<th class="text-center" style="width: 110px;">Actions</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($admissions as $idx => $row)
								<tr data-date="{{ $row['date'] }}">
									<td class="text-center">{{ $idx + 1 }}</td>
									<td>{{ $row['name'] }}</td>
									<td>{{ $row['course'] }}</td>
									<td>{{ $row['batch'] }}</td>
									<td>{{ $row['date'] }}</td>
									<td>{{ $row['contact'] }}</td>
									<td>{{ $row['city'] }}</td>
									<td class="text-center action-cell">
										@include('admission.partials.action', ['actionId' => 'adm-action-' . $idx])
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
			padding: 8px 0 16px;
		}

		.follow-card {
			border: 1px solid #dbe4ed;
			border-radius: 10px;
			background: #fff;
			box-shadow: 0 6px 18px rgba(17, 24, 39, 0.06);
		}

		.follow-tab-bar {
			display: flex;
			flex-wrap: wrap;
			gap: 12px;
			padding: 14px 18px 10px;
			border-bottom: 3px solid #008efb;
			background: #f6f8fb;
			border-radius: 10px 10px 0 0;
		}

		.follow-tab {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			padding: 8px 14px;
			font-weight: 700;
			color: #5f6f7f;
			cursor: pointer;
			position: relative;
			border-bottom: 3px solid transparent;
		}

		.follow-tab.active {
			color: #0f3c6e;
			border-bottom-color: #00a8ff;
		}

		.follow-tab .badge {
			padding: 6px 10px;
			border-radius: 999px;
			font-size: 12px;
			line-height: 1;
		}

		.follow-body {
			padding: 16px;
		}

		.follow-controls {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 12px;
			margin-bottom: 12px;
		}

		.follow-search {
			position: relative;
			width: 240px;
		}

		.follow-search input {
			padding-right: 32px;
		}

		.follow-search i {
			position: absolute;
			right: 10px;
			top: 50%;
			transform: translateY(-50%);
			color: #9aa8b6;
		}

		.follow-table {
			margin-bottom: 12px;
			border: 1px solid #dbe4ed;
		}

		.follow-table thead th {
			background: #0099f8;
			color: #fff;
			font-weight: 700;
			border-color: #0086d8;
			vertical-align: middle;
		}

		.follow-table tbody td {
			vertical-align: middle;
			color: #334155;
			background: #fdfefe;
			border-color: #e6ecf2;
		}

		.follow-table tbody tr:nth-child(even) td {
			background: #f8fbff;
		}

		.follow-table tbody tr:hover td {
			background: #eef5ff;
		}

		.action-cell {
			min-width: 110px;
			white-space: nowrap;
		}

		.admission-action-dropdown .dropdown-menu {
			min-width: 160px;
		}

		.follow-footer {
			display: flex;
			align-items: center;
			justify-content: space-between;
			font-size: 13px;
			color: #54667a;
			padding: 4px 4px 0;
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
