@extends('layouts.theme')

@php
	use App\Models\WebLead;
	use Illuminate\Support\Str;
@endphp

@section('title', 'Web Leads')

@section('content')
	<div class="follow-shell">
		@include('partials.status-loader', ['id' => 'web-lead-loader', 'message' => 'Loading web leads...'])

		<div id="web-lead-content" class="follow-content p-0 m-0">
			<div class="follow-card box-typical box-typical-dashboard panel panel-default">
				<div class="follow-tab-bar m-0 pt-3 small" style="gap:2px;">
					@foreach (($sourceTabs ?? []) as $key => $tabLabel)
						<a
							class="follow-tab notification-link-tab {{ $activeTab === $key ? 'active' : '' }}"
							href="{{ route('web-leads.index', array_filter([
								'tab' => $key !== 'all' ? $key : null,
								'search' => $search !== '' ? $search : null,
								'per_page' => $perPage !== 25 ? $perPage : null,
							], static fn ($value) => $value !== null && $value !== '')) }}"
							style="display: flex; align-items: center; gap: 3px;"
						>
							<span class="label-text">{{ $tabLabel }}</span>
							<span class="badge {{ $badgeColors[$key] ?? 'badge-secondary' }}">{{ $tabCounts[$key] ?? 0 }}</span>
						</a>
					@endforeach
				</div>

				<div class="box-typical-body panel-body follow-body">
					<form method="GET" action="{{ route('web-leads.index') }}" class="follow-controls">
						<input type="hidden" id="web-lead-tab-input" name="tab" value="{{ $activeTab !== 'all' ? $activeTab : '' }}">
						<div class="d-flex" style="gap:0.5rem;">
							<label>Show</label>
							<select name="per_page" id="web-lead-per-page" class="form-control form-control-sm" style="width: 86px;">
								@foreach ([10, 25, 50, 100] as $option)
									<option value="{{ $option }}" @selected($perPage === $option)>{{ $option }}</option>
								@endforeach
							</select>
							<label>Entries</label>

							<!-- <a href="{{ route('web-leads.index', $activeTab !== 'all' ? ['tab' => $activeTab] : []) }}" class="btn btn-default btn-sm">Reset</a> -->
						</div>
						<div class="follow-search">
							<input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search name, phone, email, city...">
							<!-- <button type="submit" class="btn btn-primary btn-sm">Search</button> -->
						</div>
					</form>

					<div class="table-responsive">
						<table class="table table-bordered follow-table" id="web-lead-table">
							<thead>
								<tr>
									<th>Sr</th>
									<!-- <th>Lead Type</th> -->
									<th>Name</th>
									<th>Program</th>
									<th>Contact No</th>
									<!-- <th>Email</th> -->
									<!-- <th>City</th> -->
									<th>Date</th>
									<th>Time</th>
									<th>Campus Code</th>
									<th class="text-left">Action</th>
								</tr>
							</thead>
							<tbody>
								@forelse ($webLeads as $webLead)
									@php
										$actionId = 'web-lead-action-' . Str::slug($webLead->full_name ?: 'web-lead') . '-' . $loop->iteration;
									@endphp
									<tr>
										<td class="text-start">{{ ($webLeads->firstItem() ?? 1) + $loop->index }}</td>
										<!-- <td>{{ $webLead->source_label }}</td> -->
										<td>
											@if(!empty($webLead->is_placeholder))
												<span class="lead-link">{{ $webLead->full_name }}</span>
											@else
												<a href="{{ route('web-leads.show', $webLead) }}" class="lead-link">
													{{ $webLead->full_name }}
												</a>
											@endif
										</td>
										<td>{{ $webLead->interested_program ?: 'N/A' }}</td>
										<td>{{ $webLead->phone ?: 'N/A' }}</td>
										<!-- <td>{{ $webLead->email ?: 'N/A' }}</td> -->
										<!-- <td>{{ $webLead->city ?: 'N/A' }}</td> -->
										<td>{{ optional($webLead->submitted_at ?? $webLead->created_at)->format('d-M-Y') ?? 'N/A' }}</td>
										<td>{{ optional($webLead->submitted_at ?? $webLead->created_at)->format('h:i A') ?? 'N/A' }}</td>
										<td>{{ $webLead->campus_id ?: 'N/A' }}</td>
										<td class=" action-cell">
											@if(!empty($webLead->is_placeholder))
												<span class="badge badge-info">{{ $webLead->source_label ?? 'Sample' }}</span>
											@else
												@include('web_leads.action', ['actionId' => $actionId, 'webLead' => $webLead])
											@endif
										</td>
									</tr>
								@empty
									<tr id="web-lead-empty-row">
										<td colspan="10" class="text-center text-muted">No pending web leads found for the current filters.</td>
									</tr>
								@endforelse
							</tbody>
						</table>
					</div>

					@php
						$currentPage = $webLeads->currentPage();
						$lastPage = $webLeads->lastPage();
						$startPage = max(1, $currentPage - 2);
						$endPage = min($lastPage, $currentPage + 2);
					@endphp
					<div class="follow-footer">
						@include('partials.follow-pagination', ['paginator' => $webLeads, 'countId' => 'web-lead-count'])
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('styles')
	<style>
        :root {
            --dimension-web-leads-index-1: 100vh;
            --dimension-web-leads-index-2: 12px;
            --space-web-leads-index-1: 8px;
            --color-web-leads-index-1: #343434;
            --typo-web-leads-index-font-family-1: 'Proxima Nova', sans-serif;
            --typo-web-leads-index-font-weight-2: 600;
        }

		* {
			font-family: var(--typo-web-leads-index-font-family-1);
			font-size: 12px;
			margin: 0;
			padding: 0;
		}

		.form-label {
			font-size: 11px;
			font-weight: var(--typo-web-leads-index-font-weight-2);
			color: var(--color-web-leads-index-1);
			text-transform: uppercase;
			margin-bottom: 3px;
		}

		body, button, html, input, select, textarea {
			color: var(--color-web-leads-index-1);
			height: 32px;
			font-family: var(--typo-web-leads-index-font-family-1);
			line-height: 1.4;
			text-rendering: optimizeLegibility;
			-moz-osx-font-smoothing: grayscale;
			-webkit-font-smoothing: antialiased;
			-moz-font-smoothing: antialiased;
			-o-font-smoothing: antialiased;
		}

		.follow-shell {
			position: relative;
			min-height: var(--dimension-web-leads-index-1);
			width: 100%;
			overflow: hidden;
		}

		.follow-loader {
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			height: var(--dimension-web-leads-index-1);
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
			gap: var(--space-web-leads-index-1);
		}

		.follow-spinner .dot {
			width: var(--dimension-web-leads-index-2);
			height: var(--dimension-web-leads-index-2);
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
			font-weight: var(--typo-web-leads-index-font-weight-2);
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

		.lead-link {
			color: #0099f8;
			font-weight: 700;
			text-decoration: none !important;
		}

		.lead-link:hover {
			color: #0086d8;
			text-decoration: none !important;
		}

		.follow-table .action-cell {
			min-width: 110px;
			white-space: nowrap;
		}

		.notification-link-tab,
		.notification-link-tab:hover,
		.notification-link-tab:focus {
			color: inherit;
			text-decoration: none !important;
		}

		.table a {
			border-bottom: none !important;
		}

		.table-responsive {
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

		.follow-search {
			display: flex;
			align-items: center;
			gap: var(--space-web-leads-index-1);
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

			document.addEventListener('DOMContentLoaded', function () {
				revealWebLeadPage();
				var perPage = document.getElementById('web-lead-per-page');

				if (perPage) {
					perPage.addEventListener('change', function () {
						this.form.submit();
					});
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
