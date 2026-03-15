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

		<div id="follow-content" class="follow-content p-0 m-0 ">
			<div class="follow-card box-typical box-typical-dashboard panel panel-default">
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
						<div class="d-flex" style="gap:0.5rem;align-items: center;">
							<label class="">Show</label>
							<select class="form-select form-select-sm " >
								<option>10</option>
								<option>25</option>
								<option>50</option>
							</select>
							<label class="">Entries</label>
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
									<th class="text-left" style="width: 110px;">Action</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($followups as $idx => $row)
									@php $actionId = 'action-' . \Illuminate\Support\Str::slug($row->lead->name ?? 'lead') . '-' . $loop->iteration; @endphp
									<tr data-status="{{ $row->stage_label }}">
										<td class="text-start">{{ $idx + 1 }}</td>
										<td>
											@if(!empty($row->lead?->id))
												<a href="{{ route('leads.show', $row->lead->id) }}" class="lead-link">
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
