@extends('layouts.theme')

@section('title', $pageTitle ?? 'Lead Follow-ups')

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

		<div id="follow-content" class="follow-content p-0 m-0 ">
			<div class="follow-card box-typical box-typical-dashboard panel panel-default">
				<div class="panel-heading p-3">
					<h3 class="panel-title">Lead Management | <span class="text-muted">{{ $moduleTitle ?? 'Lead Follow-ups' }}</span></h3>
				</div>
				<div class="follow-tab-bar m-0 pt-3 small" style = "gap:2px;">
    @foreach ($tabs as $key => $label)
        <div class="follow-tab {{ $loop->first ? 'active' : '' }}" data-status="{{ $key }}" style="display: flex; align-items: center; gap: 3px;">
            <span class="label-text">{{ $label }}</span>
            <span class="badge {{ $badgeColors[$key] ?? 'badge-secondary' }}">{{ $tabCounts[$key] ?? 0 }}</span>
        </div>
    @endforeach
</div>

				<div class="box-typical-body panel-body follow-body">
					<div class="follow-controls">
						<div class="d-flex control-flow-show-bar" style="gap:0.5rem;align-items: center;">
							<label class="">Show</label>
							<select class="form-select form-select-sm " >
								<option>10</option>
								<option>25</option>
								<option>50</option>
							</select>
							<label class="">Entries</label>
							</div> 
						<div class= "follow-search">
							<input type="text" id="follow-search" class="form-control form-control-sm" placeholder="Search...">
							<i class="fa fa-search"></i>
						</div>
					</div>

					<div class="table-responsive">
						<table class="table table-bordered follow-table" id="follow-table">
							<thead>
								<tr>
									<th>Sr</th>
									<th>Name</th>
									<th>Contact No</th>
									<th>Status</th>
									<th>{{ $interestHeading ?? 'Interested Course' }}</th>
									<th>{{ ($type ?? 'training') === 'coworking' ? 'Branch' : 'Campus' }}</th>
									<th class="text-left">Action</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($followups as $idx => $row)
									@php
										$actionId = 'action-' . \Illuminate\Support\Str::slug($row->lead->name ?? 'lead') . '-' . $loop->iteration;
										$nameUrl = !empty($row->lead?->id)
											? route('leads.show', $row->lead->id)
											: null;

										if (($type ?? 'training') === 'coworking' && $row->lead?->coworkingRegistration) {
											$nameUrl = route('coworking-registrations.show', $row->lead->coworkingRegistration);
										}
									@endphp
									<tr data-status="{{ $row->stage_label }}">
										<td class="text-start">{{ $idx + 1 }}</td>
										<td>
											@if($nameUrl)
												<a href="{{ $nameUrl }}" class="lead-link">
													{{ $row->lead->name ?? '—' }}
												</a>
											@else
												{{ $row->lead->name ?? '—' }}
											@endif
										</td>
										<td>{{ $row->lead->phone ?? '—' }}</td>
										<td>
											@php
												$labelClass = match ($row->stage_label) {
													'New' => 'label-primary',
													'Contacted' => 'label-success',
													'Need Analysis' => 'label-warning',
													'Branch Visited' => 'label-default',
													'Proposal & Negotiation', 'Proposal or Negotiation' => 'label-info',
													'Not Interesting' => 'label-default',
													'Registered' => 'label-success',
													default => 'label-default',
												};
											@endphp
											<span class="label {{ $labelClass }}">
												{{ $row->stage_label }}
											</span>
										</td>
										<td>
											@if(($type ?? 'training') === 'coworking')
												{{ data_get($row->lead?->details, 'space_required') ?? '—' }}
											@else
												{{ $row->lead->program->title ?? $row->lead->program->name ?? '—' }}
											@endif
										</td>
										<td>
											@if(($type ?? 'training') === 'coworking')
												{{ $row->branch_code ?? '—' }}
											@else
												{{ $row->lead->campus->code ?? $row->campus->code ?? $row->campus->name ?? '—' }}
											@endif
										</td>
										<td class=" action-cell">
											@include('lead.partials.action', ['actionId' => $actionId, 'lead' => $row->lead])
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>

					<div class="follow-footer">
						<div id="follow-count">Showing 1 to {{ count($followups) }} of {{ count($followups) }} Entries</div>
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

			function openUrls(urls) {
				(urls || []).forEach(function (url) {
					if (!url) {
						return;
					}

					try {
						window.open(url, '_blank');
					} catch (error) {
						console.error('Unable to open voucher url', error);
					}
				});
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

						if (event.data.openUrls) {
							openUrls(event.data.openUrls);
						}

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
				initLeadModal();
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
