@extends('layouts.theme')

@section('title', 'Lead Follow-ups')

@section('content')
	<div class="follow-shell">
		<div id="follow-loader" class="follow-loader">
			<div class="follow-spinner">
				<div class="dot"></div>
				<div class="dot"></div>
				<div class="dot"></div>
			</div>
			<p>Loading follow-ups...</p>
		</div>

		<div id="follow-content" class="follow-content">
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
							<input type="text" id="follow-search" class="form-control form-control-sm" placeholder="Search...">
							<i class="fa fa-search"></i>
						</div>
					</div>

					<div class="table-responsive">
						<table class="table table-bordered follow-table" id="follow-table">
							<thead>
								<tr>
									<th style="width: 50px;">Sr</th>
									<th>Name</th>
									<th>Contact No</th>
									<th>Status</th>
									<th>Interested Course</th>
									<th>Campus</th>
									<th class="text-center" style="width: 110px;">Action</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($followups as $idx => $row)
									@php $actionId = 'action-' . \Illuminate\Support\Str::slug($row->lead->name ?? 'lead') . '-' . $loop->iteration; @endphp
									<tr data-status="{{ $row->stage_label }}">
										<td class="text-center">{{ $idx + 1 }}</td>
										<td>{{ $row->lead->name ?? '—' }}</td>
										<td>{{ $row->lead->phone ?? '—' }}</td>
										<td>
											@php
												$labelClass = match ($row->stage_label) {
													'New' => 'label-primary',
													'Contacted' => 'label-success',
													'Need Analysis' => 'label-warning',
													'Branch Visited' => 'label-default',
													'Proposal or Negotiation' => 'label-info',
													'Not Interesting' => 'label-default',
													'Registered' => 'label-success',
													default => 'label-default',
												};
											@endphp
											<span class="label {{ $labelClass }}">
												{{ $row->stage_label }}
											</span>
										</td>
										<td>{{ $row->lead->program->title ?? $row->lead->program->name ?? '—' }}</td>
										<td>{{ $row->campus->name ?? '—' }}</td>
										<td class="text-center action-cell">
											@include('lead.partials.action', ['actionId' => $actionId, 'leadId' => $row->lead->id ?? null])
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>

					<div class="follow-footer">
						<div id="follow-count">Showing 1 to {{ count($followups) }} of {{ count($followups) }} entries</div>
						<ul class="pagination pagination-sm mb-0">
							<li class="page-item disabled"><span class="page-link">Previous</span></li>
							<li class="page-item active"><span class="page-link">1</span></li>
							<li class="page-item disabled"><span class="page-link">Next</span></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('styles')
	<style>
		.follow-shell {
			position: relative;
			min-height: 100vh;
			width: 100%;
			overflow: hidden;
		}

		.follow-loader {
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			height: 100vh;
			background: rgba(245, 247, 251, 0.95);
			display: flex;
			align-items: center;
			justify-content: center;
			flex-direction: column;
			z-index: 10;
			gap: 12px;
		}

		.follow-spinner {
			display: inline-flex;
			align-items: center;
			gap: 8px;
		}

		.follow-spinner .dot {
			width: 12px;
			height: 12px;
			border-radius: 50%;
			background: #12a0ff;
			animation: bounce 0.9s ease-in-out infinite;
		}

		.follow-spinner .dot:nth-child(2) {
			animation-delay: 0.15s;
			background: #1f8ef1;
		}

		.follow-spinner .dot:nth-child(3) {
			animation-delay: 0.3s;
			background: #36b1ff;
		}

		.follow-loader p {
			margin: 0;
			color: #54667a;
			font-weight: 600;
		}

		.follow-content {
			opacity: 0;
			visibility: hidden;
			transition: opacity 0.4s ease;
			position: relative;
			min-height: 400px;
		}

		body.follow-ready .follow-content {
			opacity: 1;
			visibility: visible;
		}

		body.follow-ready #follow-loader {
			display: none;
		}

		@keyframes bounce {
			0%, 80%, 100% {
				transform: translateY(0);
				opacity: 0.6;
			}
			40% {
				transform: translateY(-12px);
				opacity: 1;
			}
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

		.follow-footer {
			display: flex;
			align-items: center;
			justify-content: space-between;
			font-size: 13px;
			color: #54667a;
			padding: 4px 4px 0;
		}

		.follow-table .action-cell {
			min-width: 110px;
			white-space: nowrap;
		}

		.follow-action-dropdown .dropdown-menu {
			min-width: 220px;
		}
	</style>
@endpush

@push('scripts')
	<script>
		(function () {
			function revealFollowPage() {
				setTimeout(function () {
					document.body.classList.add('follow-ready');
				}, 150);
			}

			function filterByStatus(status) {
				var rows = document.querySelectorAll('#follow-table tbody tr');
				var searchVal = (document.getElementById('follow-search').value || '').toLowerCase();
				var visible = 0;
				rows.forEach(function (row) {
					var matchesStatus = status === 'all' || row.getAttribute('data-status') === status;
					var matchesSearch = row.innerText.toLowerCase().indexOf(searchVal) !== -1;
					var show = matchesStatus && matchesSearch;
					row.style.display = show ? '' : 'none';
					if (show) visible++;
				});
				document.getElementById('follow-count').textContent = 'Showing ' + (visible ? 1 : 0) + ' to ' + visible + ' of ' + visible + ' entries';
			}

			document.addEventListener('DOMContentLoaded', function () {
				revealFollowPage();

				var tabs = document.querySelectorAll('.follow-tab');
				tabs.forEach(function (tab) {
					tab.addEventListener('click', function () {
						tabs.forEach(function (t) { t.classList.remove('active'); });
						this.classList.add('active');
						filterByStatus(this.getAttribute('data-status'));
					});
				});

				document.getElementById('follow-search').addEventListener('input', function () {
					var activeTab = document.querySelector('.follow-tab.active');
					var status = activeTab ? activeTab.getAttribute('data-status') : 'all';
					filterByStatus(status);
				});

				var activeTab = document.querySelector('.follow-tab.active');
				var initialStatus = activeTab ? activeTab.getAttribute('data-status') : 'all';
				filterByStatus(initialStatus);
			});
		})();
	</script>
@endpush
