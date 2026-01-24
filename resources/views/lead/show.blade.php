@extends('layouts.theme')

@section('title', 'Lead Detail')

@section('content')
	@php
		$stageKeys = array_keys($stages);
		$currentIndex = array_search($currentStage, $stageKeys);
		$progress = $currentIndex !== false && count($stageKeys) > 1 ? ($currentIndex / (count($stageKeys) - 1)) * 100 : 0;
	@endphp

	<div class="lead-show-shell">
		<div class="lead-header">
			<div>
				<h2 class="lead-name">{{ $lead->name ?? 'Lead' }}</h2>
				<div class="lead-sub">
					<span>{{ $lead->program->title ?? $lead->program->name ?? 'N/A' }}</span>
					@if($lead->campus)
						<span class="divider">•</span>
						<span>{{ $lead->campus->code ?? $lead->campus->name }}</span>
					@endif
				</div>
			</div>
			<div class="lead-actions">
				@include('lead.partials.action', ['actionId' => 'lead-action-' . $lead->id, 'leadId' => $lead->id])
			</div>
		</div>

		<div class="stage-bar">
			@foreach ($stages as $key => $label)
				@php
					$idx = array_search($key, $stageKeys);
					$isActive = $currentIndex !== false && $idx <= $currentIndex;
					$isCurrent = $key === $currentStage;
				@endphp
				<div class="stage {{ $isActive ? 'active' : '' }} {{ $isCurrent ? 'current' : '' }}">
					<div class="bullet"></div>
					<div class="label">{{ $label }}</div>
				</div>
			@endforeach
			<div class="stage-progress" style="width: {{ $progress }}%;"></div>
		</div>

		<div class="lead-tabs">
			<div class="tab active" data-target="#tab-followups">
				<i class="fa fa-list-ul"></i> Follow-Up History
			</div>
			<div class="tab" data-target="#tab-personal">
				<i class="fa fa-user-o"></i> Personal Information
			</div>
		</div>

		<div class="tab-content" id="tab-followups" style="display: block;">
			<div class="d-flex justify-content-between align-items-center mb-2">
				<h4 class="mb-0">Follow-Ups</h4>
				@php $isClosed = in_array($lead->status, ['registered', 'not_interesting', 'enrolled'], true); @endphp
				<button id="toggle-followup-form" class="btn btn-primary btn-sm" {{ $isClosed ? 'disabled' : '' }}>
					Add Follow-Up
				</button>
			</div>
			<div class="card followup-form card-elevated" id="followup-form-card" style="display: none;">
				<div class="card-body">
					@if($isClosed)
						<div class="alert alert-warning mb-3">
							This lead is marked as <strong>{{ ucfirst(str_replace('_', ' ', $lead->status)) }}</strong>. No further follow-ups can be added.
						</div>
					@endif
					<form method="POST" action="{{ route('leads.followups.store', $lead) }}">
						@csrf
						<fieldset {{ $isClosed ? 'disabled' : '' }}>
							<div class="form-row">
								<div class="form-group col-md-3 followup-toggle">
									<label>Follow-Up Method</label>
									<select class="form-control" name="method">
										<option value="">- Select -</option>
										@foreach (['call', 'sms', 'email', 'whatsapp', 'walk-in'] as $method)
											<option value="{{ $method }}">{{ ucfirst($method) }}</option>
										@endforeach
									</select>
								</div>
								<div class="form-group col-md-3">
									<label>Stage *</label>
									@php
										$hideRegistered = $lead->status === 'not_interesting';
										$hideNotInteresting = in_array($lead->status, ['registered', 'enrolled'], true);
									@endphp
									<select class="form-control" name="stage" id="followup-stage" required>
										@foreach ($stages as $key => $label)
											@if ($key === 'new') @continue @endif
											@if ($hideRegistered && $key === 'registered') @continue @endif
											@if ($hideNotInteresting && $key === 'not_interesting') @continue @endif
											<option value="{{ $key }}" {{ $key === ($currentStage === 'new' ? 'contacted' : $currentStage) ? 'selected' : '' }}>
												{{ $label }}
											</option>
										@endforeach
									</select>
								</div>
								<div class="form-group col-md-3 followup-toggle" id="next-followup-wrap">
									<label>Next Follow Up</label>
									<input type="datetime-local" class="form-control" name="next_action_date" id="next_action_date">
								</div>
								<div class="form-group col-md-3 followup-toggle" id="campus-wrap">
									<label>Preferred Campus</label>
									<select class="form-control" name="campus_id" id="campus_id">
										<option value="">Same as lead ({{ $lead->campus->name ?? 'N/A' }})</option>
										@foreach ($campuses as $campus)
											<option value="{{ $campus->id }}" {{ $campus->id === $lead->campus_id ? 'selected' : '' }}>
												{{ $campus->name }} ({{ $campus->code ?? $campus->city ?? $campus->country }})
											</option>
										@endforeach
									</select>
								</div>
							</div>
							<div class="form-row align-items-center">
								<div class="form-group col-md-4 followup-toggle">
									<label>Probability</label>
									@php $prob = $latestFollowup->probability ?? 0; @endphp
									<input type="range" min="0" max="100" step="5" class="form-control-range" name="probability" id="probability-range" value="{{ $prob }}">
									<div id="probability-value" class="probability-value">Selected: {{ $prob }}%</div>
								</div>
								<div class="form-group col-md-8 followup-toggle">
									<label>Remarks</label>
									<textarea class="form-control" name="note" rows="2" placeholder="Add remarks for this follow-up"></textarea>
								</div>
							</div>
							<div class="alert alert-info d-none" id="registration-link">
								Selecting <strong>Registered</strong>? Complete the registration form first.
								<a href="{{ route('registration.create', ['lead_id' => $lead->id]) }}" class="btn btn-sm btn-primary ml-2">Open Registration Form</a>
							</div>
							<div class="alert alert-info d-none" id="admission-link">
								Selecting <strong>Enrolled</strong>? Complete the admission form first.
								<a href="{{ route('admission.create', ['lead_id' => $lead->id]) }}" class="btn btn-sm btn-primary ml-2">Open Admission Form</a>
							</div>
							<div class="text-right">
								<button type="submit" class="btn btn-primary">Save Follow-Up</button>
							</div>
						</fieldset>
					</form>
				</div>
			</div>

			<div class="table-responsive followup-table-wrapper">
				<table class="table table-bordered followup-table">
					<thead>
						<tr>
							<th style="width: 40px;">Sr</th>
							<th>Follower</th>
							<th>Method</th>
							<th>Probability</th>
							<th>Status</th>
							<th>Created At</th>
							<th>Next Follow-Up</th>
							<th>Campus Code</th>
							<th>Remarks</th>
						</tr>
					</thead>
					<tbody>
						@forelse ($followups as $idx => $row)
							@php
								$label = $stages[$row->stage] ?? ucfirst(str_replace('_', ' ', $row->stage));
								$rowHighlight = $row->stage === 'not_interesting';
							@endphp
							<tr class="{{ $rowHighlight ? 'row-highlight' : '' }}">
								<td class="text-center">{{ $idx + 1 }}</td>
								<td>{{ $row->user->name ?? 'System' }}</td>
								<td>{{ $row->method ? ucfirst($row->method) : '—' }}</td>
								<td>{{ !is_null($row->probability) ? $row->probability . '%' : '—' }}</td>
								<td>{{ $label }}</td>
								<td>{{ optional($row->created_at)->format('Y-m-d H:i') }}</td>
								<td>{{ $row->next_action_date ? \Illuminate\Support\Carbon::parse($row->next_action_date)->format('Y-m-d H:i') : '—' }}</td>
								<td>{{ $row->campus->code ?? $row->campus->name ?? '—' }}</td>
								<td>{{ $row->note ?? '—' }}</td>
							</tr>
						@empty
							<tr>
								<td colspan="9" class="text-center text-muted">No follow-ups yet.</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>
		</div>

		<div class="tab-content" id="tab-personal" style="display: none;">
			<div class="card card-elevated">
				<div class="card-body">
					<table class="table table-bordered info-table mb-0">
						<tbody>
							<tr>
								<th>Contact No</th>
								<td>{{ $lead->phone ?? '—' }}</td>
								<th>Email Address</th>
								<td>{{ $lead->email ?? '—' }}</td>
							</tr>
							<tr>
								<th>Interested Program</th>
								<td>{{ $lead->program->title ?? $lead->program->name ?? '—' }}</td>
								<th>Origin</th>
								<td>{{ $lead->origin ?? '—' }}</td>
							</tr>
							<tr>
								<th>Country</th>
								<td>{{ data_get($lead->details, 'country', '—') }}</td>
								<th>City</th>
								<td>{{ $lead->city ?? '—' }}</td>
							</tr>
							<tr>
								<th>Marketing Source</th>
								<td>{{ $lead->marketing_source ?? '—' }}</td>
								<th>Gender</th>
								<td>{{ ucfirst(data_get($lead->details, 'gender', '—')) }}</td>
							</tr>
							<tr>
								<th>Teaching Method</th>
								<td>{{ ucfirst(data_get($lead->details, 'teaching_method', '—')) }}</td>
								<th>Probability</th>
								<td>{{ !is_null($latestFollowup?->probability) ? $latestFollowup->probability . '%' : '—' }}</td>
							</tr>
							<tr>
								<th>Status</th>
								<td>{{ ucfirst(str_replace('_', ' ', $lead->status ?? 'pending')) }}</td>
								<th>Next Follow-Up</th>
								<td>{{ $nextFollowup?->next_action_date ? \Illuminate\Support\Carbon::parse($nextFollowup->next_action_date)->format('Y-m-d H:i') : '—' }}</td>
							</tr>
							<tr>
								<th>Campus Code</th>
								<td>{{ $lead->campus->code ?? '—' }}</td>
								<th>Campus Name</th>
								<td>{{ $lead->campus->name ?? '—' }}</td>
							</tr>
							<tr>
								<th>Remarks</th>
								<td colspan="3">{{ $latestFollowup->note ?? data_get($lead->details, 'remarks', '—') }}</td>
							</tr>
						</tbody>
					</table>
					@if(isset($transfers) && $transfers->count())
						<hr>
						<h5 class="mb-2">Campus Transfer History</h5>
						<table class="table table-bordered info-table mb-0">
							<thead>
								<tr>
									<th>#</th>
									<th>From</th>
									<th>To</th>
									<th>Status</th>
									<th>Requested By</th>
									<th>Approved By</th>
									<th>Approved At</th>
									<th>Reason</th>
								</tr>
							</thead>
							<tbody>
								@foreach($transfers as $idx => $transfer)
									<tr>
										<td>{{ $idx + 1 }}</td>
										<td>{{ $transfer->fromCampus->name ?? '—' }}</td>
										<td>{{ $transfer->toCampus->name ?? '—' }}</td>
										<td>{{ ucfirst($transfer->status) }}</td>
										<td>{{ $transfer->requester->name ?? '—' }}</td>
										<td>{{ $transfer->approver->name ?? '—' }}</td>
										<td>{{ optional($transfer->approved_at)->format('Y-m-d H:i') ?? '—' }}</td>
										<td>{{ $transfer->reason ?? '—' }}</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					@endif
				</div>
			</div>
		</div>
	</div>
@endsection

@push('styles')
	<style>
		.lead-show-shell {
			max-width: 1400px;
			margin: 0 auto;
			padding: 16px;
			background: #fff;
			border: 1px solid #dbe4ed;
			border-radius: 10px;
		}

		.lead-header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			margin-bottom: 12px;
		}

		.lead-name {
			font-size: 32px;
			font-weight: 700;
			margin: 0;
			color: #2f3b52;
		}

		.lead-sub {
			color: #5f6f7f;
			font-weight: 600;
			display: flex;
			align-items: center;
			gap: 6px;
		}

		.lead-sub .divider {
			color: #b0b8c2;
		}

		.stage-bar {
			position: relative;
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
			gap: 6px;
			padding: 16px 10px 12px;
			border: 1px solid #dbe4ed;
			border-radius: 10px;
			background: #f6f8fb;
			overflow: hidden;
			margin-bottom: 14px;
		}

		.stage-progress {
			position: absolute;
			top: 26px;
			left: 20px;
			height: 3px;
			background: linear-gradient(90deg, #00a8ff, #00d1ff);
			border-radius: 999px;
			transition: width 0.4s ease;
		}

		.stage {
			position: relative;
			display: flex;
			flex-direction: column;
			align-items: center;
			gap: 6px;
			z-index: 2;
		}

		.stage .bullet {
			width: 16px;
			height: 16px;
			border-radius: 50%;
			background: #d3dce6;
			box-shadow: 0 0 0 2px #f6f8fb;
			transition: background 0.3s ease, transform 0.3s ease;
		}

		.stage .label {
			font-size: 12px;
			text-align: center;
			color: #5f6f7f;
			font-weight: 700;
			min-height: 30px;
		}

		.stage.active .bullet {
			background: #00a8ff;
			transform: scale(1.05);
		}

		.stage.current .bullet {
			background: #00d1ff;
			box-shadow: 0 0 0 4px rgba(0, 209, 255, 0.2);
		}

		.lead-tabs {
			display: flex;
			align-items: center;
			border-bottom: 2px solid #e6ecf2;
			margin-bottom: 12px;
		}

		.lead-tabs .tab {
			padding: 10px 16px;
			cursor: pointer;
			font-weight: 700;
			color: #5f6f7f;
			border-bottom: 3px solid transparent;
			display: inline-flex;
			align-items: center;
			gap: 8px;
		}

		.lead-tabs .tab.active {
			color: #0f3c6e;
			border-bottom-color: #00a8ff;
		}

		.card-elevated {
			border: 1px solid #dbe4ed;
			box-shadow: 0 4px 12px rgba(17, 24, 39, 0.08);
		}

		.followup-form {
			margin-bottom: 14px;
		}

		.probability-value {
			margin-top: 6px;
			font-weight: 700;
			color: #334155;
		}

		.followup-table-wrapper {
			border: 1px solid #dbe4ed;
			border-radius: 6px;
			overflow-x: auto;
		}

		.followup-table thead th {
			background: #0099f8;
			color: #fff;
			font-weight: 700;
			border-color: #0086d8;
			vertical-align: middle;
		}

		.followup-table tbody td {
			vertical-align: middle;
			color: #334155;
			background: #fdfefe;
			border-color: #e6ecf2;
			min-width: 110px;
		}

		.followup-table tbody tr:nth-child(even) td {
			background: #f8fbff;
		}

		.followup-table tbody tr:hover td {
			background: #eef5ff;
		}

		.followup-table .row-highlight td {
			background: #ffeded;
			color: #b00020;
		}

		.info-table th {
			width: 22%;
			background: #f6f8fb;
			color: #4c5a6a;
		}

		.info-table td {
			background: #fff;
			color: #334155;
		}

		@media (max-width: 991px) {
			.stage-bar {
				grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
			}
		}
	</style>
@endpush

@push('scripts')
	<script>
		(function () {
			function toggleTabs() {
				var tabs = document.querySelectorAll('.lead-tabs .tab');
				tabs.forEach(function (tab) {
					tab.addEventListener('click', function () {
						tabs.forEach(function (t) { t.classList.remove('active'); });
						document.querySelectorAll('.tab-content').forEach(function (c) { c.style.display = 'none'; });
						this.classList.add('active');
						var target = this.getAttribute('data-target');
						document.querySelector(target).style.display = 'block';
					});
				});
			}

			function bindProbability() {
				var range = document.getElementById('probability-range');
				var label = document.getElementById('probability-value');
				if (!range || !label) return;
				var update = function () { label.textContent = 'Selected: ' + range.value + '%'; };
				range.addEventListener('input', update);
				update();
			}

			function bindFollowupToggle() {
				var btn = document.getElementById('toggle-followup-form');
				var card = document.getElementById('followup-form-card');
				if (!btn || !card) return;
				btn.addEventListener('click', function () {
					var isOpen = card.style.display === 'block';
					card.style.display = isOpen ? 'none' : 'block';
					btn.textContent = isOpen ? 'Add Follow-Up' : 'Hide Follow-Up';
				});
			}

			function bindStageFields() {
				var stage = document.getElementById('followup-stage');
				var nextWrap = document.getElementById('next-followup-wrap');
				var campusWrap = document.getElementById('campus-wrap');
				var nextInput = document.getElementById('next_action_date');
				var campusInput = document.getElementById('campus_id');
				var regLink = document.getElementById('registration-link');
				var admLink = document.getElementById('admission-link');
				var toggleables = document.querySelectorAll('.followup-toggle');
				if (!stage || !nextWrap || !campusWrap) return;

				var toggle = function () {
					var val = stage.value;
					var hide = (val === 'registered' || val === 'not_interesting' || val === 'enroll');
					toggleables.forEach(function (el) {
						el.style.display = hide ? 'none' : '';
					});
					if (nextInput) nextInput.disabled = hide;
					if (campusInput) campusInput.disabled = hide;
					if (regLink) regLink.classList.toggle('d-none', val !== 'registered');
					if (admLink) admLink.classList.toggle('d-none', val !== 'enroll');
				};
				stage.addEventListener('change', toggle);
				toggle();
			}

			document.addEventListener('DOMContentLoaded', function () {
				toggleTabs();
				bindProbability();
				bindFollowupToggle();
				bindStageFields();
			});
		})();
	</script>
@endpush
