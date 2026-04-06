@extends('layouts.theme')

@php use Illuminate\Support\Str; @endphp

@section('title', 'All Leads')

@section('content')
	@php
		$statusLabels = [
			'pending' => 'Pending',
			'registered' => 'Registered',
			'enrolled' => 'Enrolled',
			'not_interesting' => 'Not Interested',
		];
	@endphp

	<div class="lead-status-shell">
		<div id="lead-status-loader" class="follow-loader">
			<div class="follow-spinner">
				<div class="dot"></div>
				<div class="dot"></div>
				<div class="dot"></div>
			</div>
			<p>Loading leads...</p>
		</div>

		<div id="lead-status-content" class="follow-content">
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
						<div class="d-flex" style="gap:0.5rem;align-items: center;">
							<label class="">Show</label>
							<select class="form-select form-select-sm">
								<option>10</option>
								<option>25</option>
								<option>50</option>
							</select>
							<label class="">Entries</label>
						</div>
						<div class="follow-search">
							<input type="text" id="lead-status-search" class="form-control form-control-sm" placeholder="Search...">
							<i class="fa fa-search"></i>
						</div>
					</div>

					<div class="table-responsive">
						<table class="table table-bordered follow-table" id="lead-status-table">
							<thead>
								<tr>
									<th>Sr</th>
									<th>Name</th>
									<th>Contact No</th>
									<th>Status</th>
									<th>Interested Course</th>
									<th class="text-center">Action</th>
								</tr>
							</thead>
							<tbody>
								@forelse ($leads as $idx => $row)
									@php
										$actionId = 'action-' . Str::slug($row->name ?? 'lead') . '-' . $loop->iteration;
										$statusKey = $row->status ?? 'pending';
										$statusLabel = $statusLabels[$statusKey] ?? ucfirst(str_replace('_', ' ', $statusKey));
									@endphp
									<tr data-status="{{ $statusKey }}">
										<td class="text-center">{{ $idx + 1 }}</td>
										<td>{{ $row->name ?? 'N/A' }}</td>
										<td>{{ $row->phone ?? 'N/A' }}</td>
										<td>
											@php
												$labelClass = match ($statusKey) {
													'pending' => 'label-primary',
													'registered' => 'label-info',
													'enrolled' => 'label-warning',
													'not_interesting' => 'label-danger',
													default => 'label-default',
												};
											@endphp
											<span class="label {{ $labelClass }}">
												{{ $statusLabel }}
											</span>
										</td>
										<td>{{ $row->program->title ?? $row->program->name ?? 'N/A' }}</td>
										<td class="text-center action-cell">
											@include('lead.partials.action', ['actionId' => $actionId, 'lead' => $row])
										</td>
									</tr>
								@empty
									<tr>
										<td colspan="6" class="text-center text-muted">No training leads found.</td>
									</tr>
								@endforelse
							</tbody>
						</table>
					</div>

					<div class="follow-footer">
						<div id="lead-status-count">Showing {{ $leads->isNotEmpty() ? 1 : 0 }} to {{ $leads->count() }} of {{ $leads->count() }} entries</div>
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

	<div class="lead-modal" id="lead-form-modal" aria-hidden="true">
		<div class="modal-card" role="dialog" aria-modal="true">
			<div class="modal-header">
				<h5 class="modal-title" id="lead-form-modal-title">Form</h5>
				<button type="button" class="modal-close" id="lead-form-modal-close" aria-label="Close">&times;</button>
			</div>
			<iframe id="lead-form-modal-frame" title="Lead Form"></iframe>
		</div>
	</div>
@endsection

@push('styles')
	<style>
		
.bootstrap-table .table a, .fixed-table-body .table a, .table a {
    border-bottom: none;
    position: relative;
    top: -1px;
}

		.lead-status-shell {
			position: relative;
			min-height: 100vh;
			width: 100%;
			overflow: hidden;
		}

		/* Reuse loader + base follow styles from follow page */
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

		body.leads-ready .follow-content {
			opacity: 1;
			visibility: visible;
		}

		body.leads-ready #lead-status-loader {
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
		.follow-tab{
			padding:3px 14px !important;
		}

		.follow-tab-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    padding: 7px 18px 6px;
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
			text-align: center !important;

			border-bottom: 3px solid transparent;
		}

		.follow-tab.active {
			color: #0f3c6e;
			background-color:white;	
			border-radius: 5px;
			border-bottom: 2px solid #008efb;
		}

		.follow-tab .badge {
			padding: 6px 10px;
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
			margin-bottom: 7px;
			border: 1px solid #dbe4ed;
			text-align: center !important;

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
			font-size: 13px !;
			color: #54667a;
			padding: 4px 4px 0;
		}

		.follow-table .action-cell {
			min-width: 110px;
			white-space: nowrap;
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
	min-width: 180px;
	position: absolute !important;
	top: 0 !important;
	right: 100% !important;
	margin-right: 0 !important;
	left: auto !important;
	transform: none !important;
	z-index: 9999;
}

.follow-action-dropdown .dropdown-menu.dropdown-menu-upward {
	top: 0 !important;
	left: auto !important;
	right: 100% !important;
	transform: none !important;
}

		body.lead-modal-open {
			overflow: hidden;
		}

		.lead-modal {
			position: fixed;
			inset: 0;
			background: rgba(15, 23, 42, 0.6);
			backdrop-filter: blur(4px);
			display: none;
			align-items: center;
			justify-content: center;
			z-index: 1055;
			padding: 18px;
		}

		.lead-modal.show {
			display: flex;
		}

		.lead-modal .modal-card {
			background: #fff;
			width: min(1320px, 98vw);
			height: min(900px, 94vh);
			border-radius: 20px;
			border: 1px solid rgba(255, 255, 255, 0.72);
			box-shadow: 0 28px 80px rgba(15, 23, 42, 0.35);
			display: flex;
			flex-direction: column;
			overflow: hidden;
		}

		.lead-modal .modal-header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 14px 20px;
			border-bottom: 1px solid #e2e8f0;
			background: linear-gradient(180deg, #fbfdff 0%, #f3f8ff 100%);
		}

		.lead-modal .modal-title {
			font-weight: 800;
			color: #0f3c6e;
			margin: 0;
			font-size: 18px;
		}

		.lead-modal .modal-close {
			border: 0;
			background: transparent;
			font-size: 28px;
			line-height: 1;
			color: #5b6b80;
			cursor: pointer;
		}

		.lead-modal iframe {
			flex: 1;
			border: 0;
			width: 100%;
			background: #f3f8fd;
		}
		.table td{
			padding:2px 2px;
			height:38px;
		}
		.select2-container--arrow .select2-selection--single .select2-selection__rendered, .select2-container--default .select2-selection--single .select2-selection__rendered, .select2-container--white .select2-selection--single .select2-selection__rendered {
    border: solid 1px #d8e2e7;
    -webkit-border-radius: .25rem;
    border-radius: .25rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #343434;
    padding: .375rem 25px .375rem 1rem;
    min-height: 21px !important;
    background: #fff
}
		
		
	</style>
@endpush

@push('scripts')
	<script>
		(function () {
			function showAlert(title, text, type) {
				if (window.swal) {
					swal({ title: title, text: text, type: type });
					return;
				}

				alert(text);
			}

			function openLeadModal(url, title) {
				var modal = document.getElementById('lead-form-modal');
				var frame = document.getElementById('lead-form-modal-frame');
				var titleNode = document.getElementById('lead-form-modal-title');

				if (!modal || !frame) {
					window.location.href = url;
					return;
				}

				frame.src = url;
				modal.classList.add('show');
				modal.setAttribute('aria-hidden', 'false');
				document.body.classList.add('lead-modal-open');
				if (titleNode) titleNode.textContent = title || 'Form';
			}

			function closeLeadModal() {
				var modal = document.getElementById('lead-form-modal');
				var frame = document.getElementById('lead-form-modal-frame');

				if (!modal || !frame) return;

				frame.src = 'about:blank';
				modal.classList.remove('show');
				modal.setAttribute('aria-hidden', 'true');
				document.body.classList.remove('lead-modal-open');
			}

			function initLeadModal() {
				var modal = document.getElementById('lead-form-modal');
				var closeButton = document.getElementById('lead-form-modal-close');

				if (!modal) return;

				if (closeButton) {
					closeButton.addEventListener('click', closeLeadModal);
				}

				modal.addEventListener('click', function (event) {
					if (event.target === modal) {
						closeLeadModal();
					}
				});

				document.addEventListener('keydown', function (event) {
					if (event.key === 'Escape' && modal.classList.contains('show')) {
						closeLeadModal();
					}
				});

				document.addEventListener('click', function (event) {
					var trigger = event.target.closest('.js-lead-modal-link');
					if (!trigger) return;

					var url = trigger.getAttribute('data-lead-modal-url');
					if (!url) return;

					event.preventDefault();
					openLeadModal(url, trigger.getAttribute('data-lead-modal-title') || 'Form');
				});

				window.addEventListener('message', function (event) {
					if (event.data && event.data.type === 'lead-modal-close') {
						closeLeadModal();

						if (event.data.status) {
							showAlert('Success', event.data.status, 'success');
						}

						if (event.data.reload) {
							setTimeout(function () {
								window.location.reload();
							}, 500);
						}
					}
				});
			}

			function revealLeadPage() {
				setTimeout(function () {
					document.body.classList.add('leads-ready');
				}, 150);
			}

			function filterByStatus(status) {
				var rows = document.querySelectorAll('#lead-status-table tbody tr');
				var searchVal = (document.getElementById('lead-status-search').value || '').toLowerCase();
				var visible = 0;
				rows.forEach(function (row) {
					var matchesStatus = status === 'all' || row.getAttribute('data-status') === status;
					var matchesSearch = row.innerText.toLowerCase().indexOf(searchVal) !== -1;
					var show = matchesStatus && matchesSearch;
					row.style.display = show ? '' : 'none';
					if (show) visible++;
				});
				document.getElementById('lead-status-count').textContent = 'Showing ' + (visible ? 1 : 0) + ' to ' + visible + ' of ' + visible + ' entries';
			}

			document.addEventListener('DOMContentLoaded', function () {
				initLeadModal();
				revealLeadPage();

				var tabs = document.querySelectorAll('.follow-tab');
				tabs.forEach(function (tab) {
					tab.addEventListener('click', function () {
						tabs.forEach(function (t) { t.classList.remove('active'); });
						this.classList.add('active');
						filterByStatus(this.getAttribute('data-status'));
					});
				});

				document.getElementById('lead-status-search').addEventListener('input', function () {
					var activeTab = document.querySelector('.follow-tab.active');
					var status = activeTab ? activeTab.getAttribute('data-status') : 'all';
					filterByStatus(status);
				});

				var dropdownButtons = document.querySelectorAll('.follow-action-dropdown .dropdown-toggle');
				dropdownButtons.forEach(function (button) {
					button.addEventListener('click', function () {
						var wrapper = this.closest('.follow-action-dropdown');
						if (!wrapper) return;
						var menu = wrapper.querySelector('.dropdown-menu');
						if (!menu) return;

						menu.classList.remove('dropdown-menu-upward');
						var rect = wrapper.getBoundingClientRect();
						var approxMenuHeight = 220;
						var needsUpward = (window.innerHeight - rect.bottom) < approxMenuHeight;
						if (needsUpward) {
							menu.classList.add('dropdown-menu-upward');
						}
					});
				});

				filterByStatus('all');
			});
		})();
	</script>
@endpush
