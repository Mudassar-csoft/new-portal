@extends('layouts.theme')

@php
	use App\Models\WebLead;
	use Illuminate\Support\Str;
@endphp

@section('title', 'Web Leads')

@section('content')
	<div class="follow-shell">
		<div id="web-lead-loader" class="follow-loader">
			<div class="follow-spinner">
				<div class="dot"></div>
				<div class="dot"></div>
				<div class="dot"></div>
			</div>
			<p>Loading web leads...</p>
		</div>

		<div id="web-lead-content" class="follow-content p-0 m-0">
			<div class="follow-card box-typical box-typical-dashboard panel panel-default">
				<div class="follow-tab-bar m-0 pt-3 small" style="gap:2px;">
					@foreach ($tabs as $key => $label)
						<div class="follow-tab {{ $activeTab === $key ? 'active' : '' }}" data-tab="{{ $key }}" style="display: flex; align-items: center; gap: 3px;">
							<span class="label-text">{{ $label }}</span>
							<span class="badge {{ $badgeColors[$key] ?? 'badge-secondary' }}">{{ $tabCounts[$key] ?? 0 }}</span>
						</div>
					@endforeach
				</div>

				<div class="box-typical-body panel-body follow-body">
					<div class="follow-controls">
						<div class="d-flex" style="gap:0.5rem;align-items: center;">
							<label>Show</label>
							<select class="form-select form-select-sm">
								<option>10</option>
								<option>25</option>
								<option>50</option>
							</select>
							<label>Entries</label>
						</div>
						<div class="follow-search">
							<input type="text" id="web-lead-search" class="form-control form-control-sm" placeholder="Search...">
							<i class="fa fa-search"></i>
						</div>
					</div>

					<div class="table-responsive">
						<table class="table table-bordered follow-table" id="web-lead-table">
							<thead>
								<tr>
									<th>Sr</th>
									<th>Lead Type</th>
									<th>Name</th>
									<th>Contact No</th>
									<th>Email</th>
									<th>City</th>
									<th>Interested Program</th>
									<th>Submitted</th>
									<th class="text-left">Action</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($webLeads as $idx => $webLead)
									@php
										$actionId = 'web-lead-action-' . Str::slug($webLead->full_name ?: 'web-lead') . '-' . $loop->iteration;
										$rowTab = $webLead->status === WebLead::STATUS_NOT_INTERESTED ? 'web_not_interest' : $webLead->source_type;
									@endphp
									<tr data-tab="{{ $rowTab }}">
										<td class="text-start">{{ $idx + 1 }}</td>
										<td>{{ $webLead->source_label }}</td>
										<td>
											<a href="{{ route('web-leads.show', $webLead) }}" class="lead-link">
												{{ $webLead->full_name }}
											</a>
										</td>
										<td>{{ $webLead->phone ?: 'N/A' }}</td>
										<td>{{ $webLead->email ?: 'N/A' }}</td>
										<td>{{ $webLead->city ?: 'N/A' }}</td>
										<td>{{ $webLead->interested_program ?: 'N/A' }}</td>
										<td>{{ optional($webLead->submitted_at ?? $webLead->created_at)->format('d-M-Y h:i A') ?? 'N/A' }}</td>
										<td class="text-center action-cell">
											@include('web_leads.action', ['actionId' => $actionId, 'webLead' => $webLead])
										</td>
									</tr>
								@endforeach
								<tr id="web-lead-empty-row" @if ($webLeads->isNotEmpty()) style="display:none;" @endif>
									<td colspan="9" class="text-center text-muted"></td>
								</tr>
							</tbody>
						</table>
					</div>

					<div class="follow-footer">
						<div id="web-lead-count">Showing 0 to 0 of 0 entries</div>
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
		* {
			font-family: 'Proxima Nova', sans-serif;
			font-size: 12px !important;
			margin: 0;
			padding: 0;
		}

		.form-label {
			font-size: 11px;
			font-weight: 600;
			color: #343434;
			text-transform: uppercase;
			margin-bottom: 3px;
		}

		body, button, html, input, select, textarea {
			color: #343434;
			height: 32px;
			font-family: 'Proxima Nova', sans-serif;
			line-height: 1.4;
			text-rendering: optimizeLegibility;
			-moz-osx-font-smoothing: grayscale;
			-webkit-font-smoothing: antialiased;
			-moz-font-smoothing: antialiased;
			-o-font-smoothing: antialiased;
		}

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

		body.web-leads-ready .follow-content {
			opacity: 1;
			visibility: visible;
		}

		body.web-leads-ready #web-lead-loader {
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
			padding: 14px 18px 10px;
			border-bottom: 3px solid #008efb;
			background: #f6f8fb;
			border-radius: 10px 10px 0 0;
		}

		.follow-tab {
			display: inline-flex;
			align-items: center;
			gap: 4px;
			padding: 8px 6px;
			font-weight: 600;
			color: #5f6f7f;
			cursor: pointer;
			position: relative;
			border-bottom: 3px solid transparent;
		}

		.follow-tab.active {
			color: #0f3c6e;
			background-color: white;
			border-radius: 5px;
			border-bottom: 2px solid #008efb;
		}

		.follow-tab .badge {
			border-radius: 999px;
			font-size: 11px;
			line-height: 1;
		}

		.follow-body {
			padding: 16px;
		}

		.follow-controls {
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 0px 2px 12px 2px;
			/* margin-left: 4px; */
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
			text-align: center;
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

		.lead-link {
			color: #0099f8;
			font-weight: 700;
			text-decoration: none !important;
		}

		.lead-link:hover {
			color: #0086d8;
			text-decoration: none !important;
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

		.table a {
			border-bottom: none !important;
		}

		.table-responsive {
			overflow: visible !important;
		}

		.follow-card, .follow-body {
			overflow: visible !important;
		}

		.follow-action-dropdown {
			position: relative;
		}

		.follow-action-dropdown .dropdown-menu {
			font-size: 12px !important;
			min-width: 180px;
			position: absolute !important;
			top: 0 !important;
			left: auto !important;
			right: 100% !important;
			margin: 0 !important;
			transform: none !important;
			z-index: 9999;
		}

		.follow-action-dropdown .dropdown-item {
			border: 0 !important;
			background: transparent;
		}

		.follow-action-dropdown form {
			margin: 0;
		}
	</style>
@endpush

@push('scripts')
	<script>
		(function () {
			function revealWebLeadPage() {
				setTimeout(function () {
					document.body.classList.add('web-leads-ready');
				}, 150);
			}

			function filterByStatus(status) {
				var rows = document.querySelectorAll('#web-lead-table tbody tr[data-tab]');
				var emptyRow = document.getElementById('web-lead-empty-row');
				var searchVal = (document.getElementById('web-lead-search').value || '').toLowerCase();
				var visible = 0;

				rows.forEach(function (row) {
					var matchesStatus = row.getAttribute('data-tab') === status;
					var matchesSearch = row.innerText.toLowerCase().indexOf(searchVal) !== -1;
					var show = matchesStatus && matchesSearch;
					row.style.display = show ? '' : 'none';
					if (show) visible++;
				});

				if (emptyRow) {
					emptyRow.style.display = visible ? 'none' : '';
				}

				document.getElementById('web-lead-count').textContent = 'Showing ' + (visible ? 1 : 0) + ' to ' + visible + ' of ' + visible + ' entries';
			}

			document.addEventListener('DOMContentLoaded', function () {
				revealWebLeadPage();

				var tabs = document.querySelectorAll('.follow-tab');
				tabs.forEach(function (tab) {
					tab.addEventListener('click', function () {
						tabs.forEach(function (t) { t.classList.remove('active'); });
						this.classList.add('active');
						filterByStatus(this.getAttribute('data-tab'));
					});
				});

				document.getElementById('web-lead-search').addEventListener('input', function () {
					var activeTab = document.querySelector('.follow-tab.active');
					var status = activeTab ? activeTab.getAttribute('data-tab') : '';
					if (status) {
						filterByStatus(status);
					}
				});

				var activeTab = document.querySelector('.follow-tab.active');
				if (activeTab) {
					filterByStatus(activeTab.getAttribute('data-tab'));
				}
			});
		})();
	</script>
	@if(session('status'))
		<script>
			(function () {
				if (!window.swal) return;
				swal({
					title: 'Success',
					text: @json(session('status')),
					type: 'success'
				});
			})();
		</script>
	@endif
@endpush
