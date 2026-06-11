@extends('layouts.theme')

@section('title', 'Lead Detail')

@section('content')
	@php
		$stageKeys = array_keys($stages);
		$currentIndex = array_search($currentStage, $stageKeys);
		$progress = $currentIndex !== false && count($stageKeys) > 1 ? ($currentIndex / (count($stageKeys) - 1)) * 100 : 0;
		$showLeadCompletionFields = $currentStage === 'new';
		$leadDetails = $lead->details ?? [];
		$isCoworkingLead = $isCoworkingLead ?? false;
		$usesTrainingConversionFlow = $usesTrainingConversionFlow ?? true;
		$interestLabel = $isCoworkingLead ? 'Space Type' : 'Interested Program';
		$interestValue = $isCoworkingLead
			? (data_get($leadDetails, 'space_required') ?: 'N/A')
			: ($lead->program?->title ?? $lead->program?->name ?? 'N/A');
		$locationSelectLabel = $isCoworkingLead ? 'Preferred Branch' : 'Preferred Campus';
		$locationCodeLabel = $isCoworkingLead ? 'Branch Code' : 'Campus Code';
		$locationNameLabel = $isCoworkingLead ? 'Branch Name' : 'Campus Name';
		$registrationFormUrl = $isCoworkingLead
			? route('coworking-registrations.create', ['lead_id' => $lead->id])
			: route('registration.create', ['lead_id' => $lead->id]);
		$registrationFormModalUrl = $isCoworkingLead
			? route('coworking-registrations.create', ['lead_id' => $lead->id, 'embed' => 1])
			: route('registration.create', ['lead_id' => $lead->id, 'embed' => 1]);
		$registrationPrompt = $isCoworkingLead
			? 'Complete the coworking registration form first.'
			: 'Complete the registration form first.';
		$registrationButtonLabel = $isCoworkingLead ? 'Open Coworking Registration' : 'Open Registration Form';
		$registrationStageLabel = $isCoworkingLead ? 'Registered' : 'Register';
		$leadLocationDisplay = $leadLocationCode ?? $leadLocationName ?? 'N/A';
		$statusLabel = match ($lead->status) {
			'registered' => $stages['registered'] ?? 'Registered',
			'enrolled' => $isCoworkingLead ? ($stages['registered'] ?? 'Registered') : ($stages['enroll'] ?? 'Enrolled'),
			'not_interesting' => $stages['not_interesting'] ?? 'Not Interesting',
			default => ucfirst(str_replace('_', ' ', $lead->status ?? 'pending')),
		};
		$defaultProbability = old('probability', $latestFollowup?->probability ?? 0);
	@endphp

	<div class="lead-show-shell">
		<div class="lead-header">
			<div>
				<h2 class="lead-name form-label " style="font-size:14px !important; color:black;">{{ $lead->name ?? 'Lead' }}</h2>
				<div class="lead-sub">
					<span>{{ $interestValue }}</span>
					@if($leadLocationDisplay !== 'N/A')
						<span class="divider">•</span>
						<span>{{ $leadLocationDisplay }}</span>
					@endif
				</div>
			</div>
				<div class="lead-actions">
					@include('lead.partials.action', ['actionId' => 'lead-action-' . $lead->id, 'lead' => $lead])
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
			<div class="tab active" data-tab-target="#tab-followups">
				<i class="fa fa-list-ul"></i> Follow-Up History
			</div>
			<div class="tab" data-tab-target="#tab-personal">
				<i class="fa fa-user-o"></i> Personal Information
			</div>
		</div>

		<div class="lead-pane" id="tab-followups" style="display: block;">
			<div class="d-flex justify-content-end align-items-center p-1">
				
				@php $isClosed = $isFollowupClosed ?? in_array($lead->status, ['registered', 'not_interesting', 'enrolled'], true); @endphp
				@if($isClosed)
					<div class="alert alert-warning mb-0 followup-closed-banner">
						This lead is marked as <strong>{{ $statusLabel }}</strong>. No further follow-ups can be added.
					</div>
				@else
					<button id="toggle-followup-form" class="btn btn-primary btn-sm">
						Add Follow-Up
					</button>
				@endif
			</div>
			@if(!$isClosed)
			<div class="card followup-form card-elevated" id="followup-form-card" style="display: none;">
				<div class="card-body">
					<form method="POST" action="{{ route('leads.followups.store', $lead) }}" id="followup-form"
						data-uses-training-conversion-flow="{{ $usesTrainingConversionFlow ? '1' : '0' }}"
						data-registration-url="{{ $registrationFormModalUrl }}"
						@if($usesTrainingConversionFlow)
							data-admission-url="{{ route('admission.create', ['lead_id' => $lead->id, 'embed' => 1]) }}"
						@endif>
						@csrf
							<div class="form-row" >
								<div class="form-group col-lg-3 col-md-6 followup-toggle">
									<label class="form-label required">Follow-Up Method </label>
									<select class="form-control" name="method" required>
										<option value="">- Select -</option>
										@foreach (['call', 'sms', 'email', 'whatsapp', 'walk-in'] as $method)
											<option value="{{ $method }}" @selected(old('method') === $method)>{{ ucfirst($method) }}</option>
										@endforeach
									</select>
									<div class="field-error" data-error-for="method"></div>
								</div>
								<div class="form-group col-lg-3 col-md-6">
									<label class="form-label">Stage</label>
									@php
										$hideRegistered = $lead->status === 'not_interesting';
										$hideNotInteresting = $usesTrainingConversionFlow
											? in_array($lead->status, ['registered', 'enrolled'], true)
											: $lead->status === 'enrolled';
									@endphp
									<select class="form-control" name="stage" id="followup-stage" required onchange="window.handleFollowupStageChange && window.handleFollowupStageChange(this)">
										@foreach ($stages as $key => $label)
											@if ($key === 'new') @continue @endif
											@if ($hideRegistered && $key === 'registered') @continue @endif
											@if ($hideNotInteresting && $key === 'not_interesting') @continue @endif
											<option value="{{ $key }}" @selected(old('stage', $currentStage === 'new' ? 'contacted' : $currentStage) === $key)>
												{{ $usesTrainingConversionFlow && $key === 'registered' ? 'Register' : ($usesTrainingConversionFlow && $key === 'enroll' ? 'Enroll' : $label) }}
											</option>
										@endforeach
									</select>
									<div class="field-error" data-error-for="stage"></div>
								</div>
								<div class="form-group col-lg-3 col-md-6 followup-toggle followup-hide-on-close" id="next-followup-wrap">
									<label class="form-label required">Next Follow Up </label>
									<input type="datetime-local" class="form-control" name="next_action_date" id="next_action_date" value="{{ old('next_action_date') }}" required>
									<div class="field-error" data-error-for="next_action_date"></div>
								</div>
								<div class="form-group col-lg-3 col-md-6 followup-toggle followup-hide-on-close" id="campus-wrap">
									<label class="form-label ">{{ $locationSelectLabel }}</label>
									<select class="form-control" name="campus_id" id="campus_id">
										<option value="">Same as lead ({{ $leadLocationDisplay }})</option>
										@foreach ($campuses as $campus)
											<option value="{{ $campus->id }}" @selected((string) old('campus_id', $defaultFollowupCampusId ?? $lead->campus_id) === (string) $campus->id)>
												{{ $isCoworkingLead ? (($campus->code ?? 'N/A') . ' - ' . $campus->name) : ($campus->name . ' (' . ($campus->code ?? $campus->city ?? $campus->country) . ')') }}
											</option>
										@endforeach
									</select>
									<div class="field-error" data-error-for="campus_id"></div>
								</div>
							</div>

							@if ($showLeadCompletionFields)
								<div class="followup-extra-fields" id="lead-completion-fields">
									<div class="followup-extra-title">Complete Lead Details</div>
									<!-- <p class="followup-extra-copy">This lead is still new. Fill the missing profile details before the first proper follow-up.</p> -->
									<div class="form-row" >
										<div class="form-group col-lg-4 col-md-6 followup-toggle">
											<label class="form-label required">Email Address</label>
											<input type="email" class="form-control" name="email" value="{{ old('email', $lead->email) }}" placeholder="Enter email address">
											<div class="field-error" data-error-for="email"></div>
										</div>
										<div class="form-group col-lg-4 col-md-6 followup-toggle">
											<label class="form-label required">Area</label>
											<input type="text" class="form-control" name="lead_details[area]" value="{{ old('lead_details.area', data_get($leadDetails, 'area')) }}" placeholder="Enter area" required>
											<div class="field-error" data-error-for="lead_details.area"></div>
										</div>
										<div class="form-group col-lg-4 col-md-6 followup-toggle">
											<label class="form-label text-dark fw-semibold small">Gender</label>
											<div class="row mt-2 choice-group">
												<div class="col-4 d-flex justify-content-center mb-1">
													<div class="form-check d-flex align-items-center mt-0">
														<input class="form-check-input mt-0 mr-1"
															type="radio"
															id="lead-details-gender-male"
															name="lead_details[gender]"
															value="male"
															@checked(old('lead_details.gender', data_get($leadDetails, 'gender', 'male')) === 'male')>
														<label class="form-check-label small mb-0" for="lead-details-gender-male">
															Male
														</label>
													</div>
												</div>
												<div class="col-4 d-flex justify-content-center">
													<div class="form-check d-flex align-items-center">
														<input class="form-check-input mt-0 mr-1"
															type="radio"
															id="lead-details-gender-female"
															name="lead_details[gender]"
															value="female"
															@checked(old('lead_details.gender', data_get($leadDetails, 'gender')) === 'female')>
														<label class="form-check-label small mb-0" for="lead-details-gender-female">
															Female
														</label>
													</div>
												</div>
												<div class="col-4 d-flex justify-content-center ">
													<div class="form-check d-flex align-items-center">
														<input class="form-check-input mt-0 mr-1"
															type="radio"
															id="lead-details-gender-other"
															name="lead_details[gender]"
															value="other"
															@checked(old('lead_details.gender', data_get($leadDetails, 'gender')) === 'other')>
														<label class="form-check-label small mb-0" for="lead-details-gender-other">
															Other
														</label>
													</div>
												</div>
											</div>
											<div class="field-error" data-error-for="lead_details.gender"></div>
										</div>
									</div>
								</div>
							@endif

							<div class="form-row align-items-center">
								<div class="form-group col-lg-3 col-md-6 followup-toggle followup-hide-on-close" id="probability-wrap">
									<label class="form-label small text-dark fw-semibold">Probability: Selected <span id="probabilityValue">{{ (int) $defaultProbability }}%</span></label>
									@include('lead.partials.probability_slider', [
										'inputName' => 'probability',
										'inputId' => 'probabilitySlider',
										'displayId' => 'probabilityValue',
										'value' => $defaultProbability,
										'required' => true,
										'showDisplay' => false,
									])
									<div class="field-error" data-error-for="probability"></div>
								</div>

								<div class="form-group col-md-9 followup-toggle">
									<label class="form-label required">Remarks </label>
									<textarea class="form-control" name="note" rows="2"
										placeholder="Add remarks for this follow-up"
										style="width:100%; height:80px !important;"
										required>{{ old('note') }}</textarea>
									<div class="field-error" data-error-for="note"></div>
								</div>
							</div>

							<div class="alert alert-info d-none" id="registration-link">
								Selecting <strong>{{ $registrationStageLabel }}</strong>? {{ $registrationPrompt }}
								<a href="{{ $registrationFormUrl }}"
									class="btn btn-sm btn-primary ml-2 {{ $isCoworkingLead ? 'js-lead-modal-link' : '' }}"
									@if($isCoworkingLead)
										data-lead-modal-url="{{ $registrationFormModalUrl }}"
										data-lead-modal-title="Create New Coworking Space Registration (All fields marked with * are required)"
									@endif>{{ $registrationButtonLabel }}</a>
							</div>
							@if($usesTrainingConversionFlow)
								<div class="alert alert-info d-none" id="admission-link">
									Selecting <strong>Enroll</strong>? Complete the admission form first.
									<a href="{{ route('admission.create', ['lead_id' => $lead->id]) }}" class="btn btn-sm btn-primary ml-2">Open Admission Form</a>
								</div>
							@endif

							<div class="text-right p-1">
								<button type="submit" class="btn btn-primary-outline">Save Follow-Up</button>
								<button type="button" id="cancel-followup-btn" class="btn btn-danger-outline">Cancel</button>
							</div>
					</form>
				</div>
			</div>
			@endif

			<div class="table-responsive followup-table-wrapper">
				<table class="table table-bordered followup-table">
					<thead>
						<tr>
							<th>Sr</th>
							<th>Follower</th>
							<th>Method</th>
							<th>Probability</th>
							<th>Status</th>
							<th>Created At</th>
							<th>Next Follow-Up</th>
							<th>{{ $locationCodeLabel }}</th>
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
								<td>{{ $row->user?->name ?? 'System' }}</td>
								<td>{{ $row->method ? ucfirst($row->method) : '—' }}</td>
								<td>{{ !is_null($row->probability) ? $row->probability . '%' : '—' }}</td>
								<td>{{ $label }}</td>
								<td>{{ optional($row->created_at)->format('Y-m-d H:i') }}</td>
								<td>{{ $row->next_action_date ? \Illuminate\Support\Carbon::parse($row->next_action_date)->format('Y-m-d H:i') : '—' }}</td>
								<td>{{ $row->campus?->code ?? $row->campus?->name ?? '—' }}</td>
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
		</div>{{-- /lead-pane #tab-followups --}}

			<div class="lead-pane" id="tab-personal" style="display: none;">
				<div class="card card-elevated">
					<div class="card-body">
						<table class="table table-bordered info-table mb-0">
							<tbody>
								<tr>
									<th>Contact No</th>
									<td>{{ $lead->phone ?? '—' }}</td>
									<th>Email Address</th>
									<td>{{ $lead->email ?? '—' }}</td>
									<th>{{ $interestLabel }}</th>
									<td>{{ $lead->program?->title ?? $lead->program?->name ?? '—' }}</td>
								</tr>
								<tr>
									
									<th>Origin</th>
									<td>{{ $lead->origin ?? '—' }}</td>
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
									<th>Probability</th>
									<td>{{ !is_null($latestFollowup?->probability) ? $latestFollowup->probability . '%' : '—' }}</td>
								</tr>
								<tr>
									
									
								<th>Status</th>
								<td>{{ $statusLabel }}</td>
								<th>Next Follow-Up</th>
								<td>{{ $nextFollowup?->next_action_date ? \Illuminate\Support\Carbon::parse($nextFollowup->next_action_date)->format('Y-m-d H:i') : '—' }}</td>
								<th>{{ $locationCodeLabel }}</th>
								<td>{{ $lead->campus?->code ?? '—' }}</td>

							</tr>

							<tr>

								<th>{{ $locationNameLabel }}</th>
								<td>{{ $lead->campus?->name ?? '—' }}</td>
								<th>Remarks</th>
								<td colspan="3">{{ $latestFollowup?->note ?? data_get($lead->details, 'remarks', '—') }}</td>
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
										<td>{{ $transfer->fromCampus?->name ?? '—' }}</td>
										<td>{{ $transfer->toCampus?->name ?? '—' }}</td>
										<td>{{ ucfirst($transfer->status) }}</td>
										<td>{{ $transfer->requester?->name ?? '—' }}</td>
										<td>{{ $transfer->approver?->name ?? '—' }}</td>
										<td>{{ optional($transfer->approved_at)->format('Y-m-d H:i') ?? '—' }}</td>
										<td>{{ $transfer->reason ?? '—' }}</td>
									</tr>
								@endforeach
							</tbody>
						</table>
						@endif
					</div>{{-- /card-body --}}
				</div>{{-- /card-elevated --}}
			</div>{{-- /lead-pane #tab-personal --}}

	</div>{{-- /lead-show-shell --}}
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
	@include('lead.partials.probability_slider_assets')
	<style>
		*{
			font-family: 'Proxima Nova', sans-serif;
			font-size: 12px; 
			margin: 0;
			padding: 0;
        }
		.control-panel .page-content {
    padding-right: 57px !important;
}
   .lead-show-shell {
    /* max-width: 1400px; */
    margin: 0 auto;
    padding: 2%;
    background: #fff;
    border: 1px solid #dbe4ed;
    border-radius: 10px;
	height:auto;
}
		 .followup-table-wrapper {
	/* max-width: 1400px; */
    border: 1px solid #dbe4ed;
    border-radius: 6px;
    overflow-x: auto;
    overflow-y: scroll;
    height: 20vh !important;
}
		.box-typical.box-typical-dashboard .box-typical-body {
   			 overflow: hidden;
			 /* padding: 1px; */
			 margin: 5px;
		}
		.box-typical.box-typical-dashboard{
   			 margin:0px 0px 5px !important;
    
		}
		.box-typical.box-typical-dashboard .box-typical-header{
    		display:flex;

		}
    

		.select2-container--arrow .select2-selection--single .select2-selection__rendered,
		.select2-container--default .select2-selection--single .select2-selection__rendered,
		.select2-container--white .select2-selection--single .select2-selection__rendered {
			border: solid 1px #d8e2e7;
			-webkit-border-radius: .25rem;
			border-radius: .25rem;
			font-size: 1rem;
			line-height: 1.5;
			color: #343434;
			padding: .375rem 25px .375rem 1rem;
			min-height: 32px;
			background: #fff
		}

		.choice-group.is-invalid {
			border: 1px solid #e53935;
			border-radius: 6px;
			margin-left: 0;
			margin-right: 0;
			padding: 4px 0;
		}

		.form-check-input[type="radio"] {
			-webkit-appearance: none;
			-moz-appearance: none;
			appearance: none;
			width: 14px;
			height: 14px !important;
			border: 2px solid grey;
			border-radius: 50%;
			outline: none;
			cursor: pointer;
			position: relative;
			background-color: #fff;
			transition: background 0.2s, box-shadow 0.2s;
		}

		.form-check-input[type="radio"]:checked {
			border-color: #00a8ff;
		}

		.form-check-input[type="radio"]:checked::before {
			content: '';
			position: absolute;
			top: 2px;
			left: 2px;
			width: 7px;
			height: 7px;
			border-radius: 50%;
			background-color: #00a8ff;
		}

		.form-check-label {
			font-size: 14px;
			margin-bottom: 0;
			cursor: pointer;
		}
		/* .form-label{
			font-size: 11px;
			font-weight: 600 ;
			color: #343434;
			text-transform: uppercase;
			margin-bottom: 3px;
			
		} */

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

        .col-md-3 {
			flex: 0 0 200px !important;
			max-width: 200px !important;
			height: 62px;
			margin-bottom: 2px ;
			/* margin-top: 2px;    */
		}
		.lead-show-shell {
			/* max-width: 1400px; */
			margin: 0 auto;	
			padding: 2%;
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
			grid-template-columns: repeat(8, 1fr);
			gap: 10px;
			padding: 13px 16px 7px;
			border: 1px solid #d6e6f7;
			border-radius: 10px;
			background: linear-gradient(180deg, #f4f9ff 0%, #ffffff 100%);
			overflow: hidden;
			margin-bottom: 8px;
		}

		.stage-bar::before {
			content: '';
			position: absolute;
			top: 25px;
			left: 66px;
			right: 75px;
			height: 4px;
			background: #dbeafe;
			border-radius: 999px;
			z-index: 1;
		}

		.stage-progress {
			position: absolute;
			top: 25px;
			height: 4px;
			background: linear-gradient(90deg, #0099f8, #00c2ff);
			border-radius: 999px;
			transition: width 0.4s ease;
			z-index: 2;
		}

		.stage {
			position: relative;
			display: flex;
			flex-direction: column;
			align-items: center;
			gap: 8px;
			z-index: 2;
		}

		.stage .bullet {
			width: 15px;
			height: 15px;
			border-radius: 50%;
			background: #cdd9e6;
			margin-top:5px;
			grid-template-columns: repeat(8, 1fr);
			gap: 10px;
			box-shadow: 0 0 0 4px #f4f9ff;
			transition: background 0.3s ease, transform 0.3s ease;
		}




	.dropdown-menu.dropdown-menu-right {
		top: calc(100% + -30px) !important;
		margin-right: 74px !important;
	}
	.dropdown-item{
		padding: 6px 12px !important;
	}

	.stage .label {
			font-size: 12px;
			text-align: center;
			color: white !important;
			font-weight: 700;
			min-height: 30px;
			padding: 6px 10px;
			border-radius: 999px;
   			background: #00a8ff !important;
    		line-height: 1.5;
			border: 1px solid #d6e6f7;
			white-space: nowrap;
			grid-template-columns: repeat(8, 1fr);
			gap: 10px;
			margin-top:3px;
		
		}

		.stage.active .bullet {
			background: #0099f8;
			transform: scale(1.08);
		}

		.stage.current .bullet {
			background: #00c2ff;
			box-shadow: 0 0 0 6px rgba(0, 153, 248, 0.2);
		}

		.stage.active .label {
			background: #dff0ff;
			border-color: #b8ddfb;
			color: #0f3c6e;
		}

		.stage.current .label {
			background: #00a8ff;
			color:white;
			margin-top: 3px;
			line-height: 1.5;
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
		.card-body{
			padding:0.50rem;
		}

		.followup-form {
			margin-bottom: 5px;
		}

		.followup-closed-banner {
			max-width: 460px;
			text-align: left;
			font-size: 13px;
		}

		.followup-extra-fields {
			margin: 8px 15px 12px;
			padding: 12px 14px 2px;
			border: 1px solid #d6e6f7;
			border-radius: 8px;
			background: #f8fbff;
		}

		.followup-extra-title {
			font-weight: 700;
			color: #0f3c6e;
			margin-bottom: 4px;
		}

		.followup-extra-copy {
			margin: 0 0 10px;
			color: #5f6f7f;
			line-height: 1.5;
		}

		.field-error {
			display: none;
			margin-top: 4px;
			color: #e53935;
			font-weight: 600;
			font-size: 11px !important;
		}

		.field-error.has-error {
			display: block;
		}

		.form-control.is-invalid,
		.probability-input.is-invalid {
			border-color: #e53935 !important;
			box-shadow: 0 0 0 2px rgba(229, 57, 53, 0.12);
		}

		.tab {
   			 padding: 2px 16px !important;
		}


		.followup-form.hide-all .followup-toggle {
			display: none !important;
		}

		.followup-form.hide-closed .followup-hide-on-close {
			display: none !important;
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
			width: auto;
			height: 35px !important;
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
		.table td{
			height:20px !important;
			padding:2px
		}

		.info-table th {
			height:auto;
			background: #f6f8fb;
			color: #4c5a6a;
		}

		.info-table td {
			background: #fff;
			color: #334155;
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
		font-size: 26px !important;
  font-weight: 500 !important;
      text-wrap: auto;
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

		#probability-wrap .probability-field {
			max-width: 100%;
		}

		#probability-wrap .probability-shell {
			padding-top: 4px;
		}

		#probability-wrap .probability-field .probability-input {
			height: 16px !important;
			min-height: 16px !important;
		}

		#probability-wrap .probability-field .probability-input::-webkit-slider-runnable-track {
			height: 7px;
		}

		#probability-wrap .probability-field .probability-input::-webkit-slider-thumb {
			width: 20px;
			height: 20px;
			margin-top: -6.5px;
			border-width: 4px;
		}

		#probability-wrap .probability-field .probability-input::-moz-range-track,
		#probability-wrap .probability-field .probability-input::-moz-range-progress {
			height: 7px;
		}

		#probability-wrap .probability-field .probability-input::-moz-range-thumb {
			width: 12px;
			height: 12px;
			border-width: 4px;
		}

		#probability-wrap .probability-ticks {
			width: calc(100% - 8px);
			margin: 0 4px;
		}

		#probability-wrap .probability-ticks span {
			height: 3px;
		}

		#probability-wrap .probability-ticks span.is-major {
			height: 6px;
		}

		#probability-wrap .probability-scale {
			margin-top: 4px;
			font-size: 8px;
			font-weight: 600;
			letter-spacing: -0.15px;
		}

		#probability-wrap .probability-display {
			margin-top: 6px;
			padding: 3px 8px;
			font-size: 9px;
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
		const $ = (selector, parent = document) => parent.querySelector(selector);
		const $$ = (selector, parent = document) => parent.querySelectorAll(selector);

		function showAlert(title, text, type) {
			if (window.swal) {
				swal({ title, text, type });
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

		function errorKeyToInputName(key) {
			return key.replace(/\.(\w+)/g, '[$1]');
		}

		function findField(form, key) {
			const inputName = errorKeyToInputName(key);

			return Array.from(form.elements).find(function (element) {
				return element.name === inputName;
			}) || null;
		}

		function clearFollowupErrors(form) {
			form.querySelectorAll('.field-error[data-error-for]').forEach(function (node) {
				node.textContent = '';
				node.classList.remove('has-error');
			});

			form.querySelectorAll('.is-invalid').forEach(function (element) {
				element.classList.remove('is-invalid');
			});
		}

		function renderFollowupErrors(form, errors) {
			clearFollowupErrors(form);

			Object.entries(errors || {}).forEach(function (entry) {
				const key = entry[0];
				const messages = entry[1];
				const message = Array.isArray(messages) && messages.length ? messages[0] : 'Invalid value.';
				const errorNode = form.querySelector('[data-error-for="' + key + '"]');
				const field = findField(form, key);

				if (errorNode) {
					errorNode.textContent = message;
					errorNode.classList.add('has-error');
				}

				if (field) {
					field.classList.add('is-invalid');
				}
			});

			const card = $('#followup-form-card');
			const toggleButton = $('#toggle-followup-form');

			if (card) {
				card.style.display = 'block';
			}

			if (toggleButton) {
				toggleButton.textContent = 'Hide Follow-Up';
			}
		}

		function renderFollowupClientErrors(form) {
			const errors = {};
			const fields = Array.from(form.elements).filter(function (field) {
				return field.willValidate && !field.disabled;
			});

			fields.forEach(function (field) {
				if (field.checkValidity()) {
					return;
				}

				const key = field.name;
				if (!key || errors[key]) {
					return;
				}

				if (field.validity.valueMissing) {
					errors[key] = ['This field is required.'];
					return;
				}

				if (field.validity.typeMismatch) {
					errors[key] = ['Please enter a valid value.'];
					return;
				}

				errors[key] = [field.validationMessage || 'Please review this field.'];
			});

			if (!Object.keys(errors).length) {
				return true;
			}

			renderFollowupErrors(form, errors);
			showAlert('Error', 'Please fill the required fields.', 'error');
			return false;
		}

		function initTabs() {
			const tabs = $$('.lead-tabs .tab');
			const contents = $$('.lead-pane');

			tabs.forEach(function (tab) {
				tab.addEventListener('click', function (event) {
					event.preventDefault();
					event.stopPropagation();

					const targetSelector = this.dataset.tabTarget || this.dataset.target;
					if (!targetSelector) return;

					const targetEl = document.querySelector(targetSelector);
					if (!targetEl) return;

					tabs.forEach(function (item) {
						item.classList.remove('active');
					});

					contents.forEach(function (content) {
						content.style.setProperty('display', 'none', 'important');
					});

					this.classList.add('active');
					targetEl.style.setProperty('display', 'block', 'important');
				});
			});
		}

		function initProbability() {
			const range = $('#probabilitySlider');
			const label = $('#probabilityValue');
			const field = range ? range.closest('.probability-field') : null;

			if (!range || !label) {
				return;
			}

			const update = function () {
				const min = parseFloat(range.min || 0);
				const max = parseFloat(range.max || 100);
				const value = parseFloat(range.value || 0);
				const progress = max > min ? ((value - min) / (max - min)) * 100 : 0;

				if (field) {
					field.style.setProperty('--probability-progress', Math.max(0, Math.min(100, progress)) + '%');
				}
				label.textContent = value + '%';
			};

			range.addEventListener('input', update);
			window.addEventListener('resize', update);
			update();
		}

		function openLeadModal(url, title) {
			const modal = $('#lead-form-modal');
			const frame = $('#lead-form-modal-frame');
			const titleNode = $('#lead-form-modal-title');

			if (!modal || !frame) {
				window.location.href = url;
				return;
			}

			frame.src = url;
			modal.classList.add('show');
			modal.setAttribute('aria-hidden', 'false');
			document.body.classList.add('lead-modal-open');

			if (titleNode) {
				titleNode.textContent = title;
			}
		}

		function closeLeadModal() {
			const modal = $('#lead-form-modal');
			const frame = $('#lead-form-modal-frame');

			if (!modal || !frame) {
				return;
			}

			frame.src = 'about:blank';
			modal.classList.remove('show');
			modal.setAttribute('aria-hidden', 'true');
			document.body.classList.remove('lead-modal-open');
		}

		function initLeadModal() {
			const modal = $('#lead-form-modal');
			const closeButton = $('#lead-form-modal-close');

			if (!modal) {
				return;
			}

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

		function initActionModalLinks() {
			document.addEventListener('click', function (event) {
				const trigger = event.target.closest('.js-lead-modal-link');

				if (!trigger) {
					return;
				}

				const modalUrl = trigger.getAttribute('data-lead-modal-url');
				const modalTitle = trigger.getAttribute('data-lead-modal-title') || 'Form';

				if (!modalUrl) {
					return;
				}

				event.preventDefault();
				openLeadModal(modalUrl, modalTitle);
			});
		}

		function syncRequiredState(field, shouldRequire) {
			if (!field || field.type === 'hidden') {
				return;
			}

			if (field.dataset.originalRequired === undefined) {
				field.dataset.originalRequired = field.required ? '1' : '0';
			}

			field.required = shouldRequire && field.dataset.originalRequired === '1';
		}

		function syncContainerAvailability(container, enabled) {
			if (!container) {
				return;
			}

			container.querySelectorAll('input, select, textarea').forEach(function (field) {
				if (field.type === 'hidden') {
					return;
				}

				syncRequiredState(field, enabled);
				field.disabled = !enabled;
			});
		}

		window.handleFollowupStageChange = function (element) {
			const stageField = $('#followup-stage');
			const nextStage = String(element ? element.value : (stageField ? stageField.value : '')).trim().toLowerCase();
			const form = $('#followup-form');
			const usesTrainingConversionFlow = form && form.dataset.usesTrainingConversionFlow === '1';

			updateStageSpecificUI(nextStage);

			if (!form) {
				return;
			}

			if (!usesTrainingConversionFlow && nextStage === 'registered' && form.dataset.registrationUrl) {
				openLeadModal(form.dataset.registrationUrl, 'Create New Coworking Space Registration (All fields marked with * are required)');
			}

			if (usesTrainingConversionFlow && nextStage === 'enroll' && form.dataset.admissionUrl) {
				openLeadModal(form.dataset.admissionUrl, 'Create New Admission (All fields marked with * are required)');
			}
		};

		function updateStageSpecificUI(stageValue) {
			const normalizedStage = String(stageValue || '').trim().toLowerCase();
			const isNotInteresting = normalizedStage === 'not_interesting';
			const form = $('#followup-form');
			const usesTrainingConversionFlow = form && form.dataset.usesTrainingConversionFlow === '1';
			const isRegistrationStage = normalizedStage === 'registered';
			const isAdmissionStage = usesTrainingConversionFlow && normalizedStage === 'enroll';
			const isModalStage = isRegistrationStage || isAdmissionStage;
			const useMinimalFields = isNotInteresting || isModalStage;
			const registrationLink = $('#registration-link');
			const admissionLink = $('#admission-link');
			const completionFields = $('#lead-completion-fields');
			const noteField = form ? form.querySelector('[name="note"]') : null;

			if (registrationLink) {
				registrationLink.classList.toggle('d-none', !isRegistrationStage);
			}

			if (admissionLink) {
				admissionLink.classList.toggle('d-none', !isAdmissionStage);
			}

			$$('.followup-hide-on-close').forEach(function (element) {
				element.style.display = useMinimalFields ? 'none' : '';
				syncContainerAvailability(element, !useMinimalFields);
			});

			if (completionFields) {
				completionFields.style.display = useMinimalFields ? 'none' : '';
				syncContainerAvailability(completionFields, !useMinimalFields);
			}

			if (noteField) {
				syncRequiredState(noteField, !isModalStage);
				noteField.disabled = false;
			}
		}

		function initFollowupStage() {
			const stageField = $('#followup-stage');

			if (!stageField) {
				return;
			}

			updateStageSpecificUI(stageField.value);
		}

		function initFollowupToggle() {
			const button = $('#toggle-followup-form');
			const card = $('#followup-form-card');

			if (!button || !card) {
				return;
			}

			button.addEventListener('click', function () {
				const isOpen = card.style.display === 'block';

				card.style.display = isOpen ? 'none' : 'block';
				button.textContent = isOpen ? 'Add Follow-Up' : 'Hide Follow-Up';
			});
		}

		function initFollowupSubmit() {
			const form = $('#followup-form');

			if (!form) {
				return;
			}

			form.addEventListener('submit', async function (event) {
				event.preventDefault();
				clearFollowupErrors(form);

				if (!renderFollowupClientErrors(form)) {
					return;
				}

				const button = form.querySelector('button[type="submit"]');

				if (button) {
					button.disabled = true;
				}

				try {
					const response = await fetch(form.action, {
						method: 'POST',
						headers: {
							'Accept': 'application/json',
							'X-Requested-With': 'XMLHttpRequest',
							'X-CSRF-TOKEN': form.querySelector('[name="_token"]')?.value || ''
						},
						credentials: 'same-origin',
						body: new FormData(form)
					});

					if (response.status === 422) {
						const data = await response.json();

						renderFollowupErrors(form, data.errors || {});
						showAlert('Error', data.message || 'Please fix the highlighted fields and try again.', 'error');
						return;
					}

					if (!response.ok) {
						throw new Error('Unable to save follow-up.');
					}

					showAlert('Success', 'Follow-up saved successfully.', 'success');
					setTimeout(function () {
						window.location.reload();
					}, 900);
				} catch (error) {
					showAlert('Error', error.message || 'Unable to save follow-up.', 'error');
				} finally {
					if (button) {
						button.disabled = false;
					}
				}
			});
		}

		function initFollowupCancel() {
			const button = $('#cancel-followup-btn');
			const form = $('#followup-form');
			const card = $('#followup-form-card');
			const toggleButton = $('#toggle-followup-form');

			if (!button || !form) {
				return;
			}

			button.addEventListener('click', function () {
				form.reset();
				clearFollowupErrors(form);
				initProbability();
				initFollowupStage();

				if (card) {
					card.style.display = 'none';
				}

				if (toggleButton) {
					toggleButton.textContent = 'Add Follow-Up';
				}
			});
		}

		function updateStageProgress() {
			const bar = $('.stage-bar');
			const progress = $('.stage-progress');

			if (!bar || !progress) {
				return;
			}

			const bullets = $$('.stage .bullet', bar);

			if (!bullets.length) {
				return;
			}

			const current = $('.stage.current .bullet', bar) || $('.stage.active:last-child .bullet', bar) || bullets[0];
			const barRect = bar.getBoundingClientRect();
			const startRect = bullets[0].getBoundingClientRect();
			const currentRect = current.getBoundingClientRect();
			const start = startRect.left + startRect.width / 2 - barRect.left;
			const end = currentRect.left + currentRect.width / 2 - barRect.left;

			progress.style.left = start + 'px';
			progress.style.width = Math.max(0, end - start) + 'px';
		}

		function initStageProgress() {
			updateStageProgress();

			window.addEventListener('resize', function () {
				setTimeout(updateStageProgress, 100);
			});
		}

		document.addEventListener('DOMContentLoaded', function () {
			initTabs();
			initProbability();
			initLeadModal();
			initActionModalLinks();
			initFollowupStage();
			initFollowupToggle();
			initFollowupSubmit();
			initFollowupCancel();
			initStageProgress();
		});
	})();
	</script>
@endpush
