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
				<h2 class="lead-name form-label " style="font-size:14px !important; color:black;">{{ $lead->name ?? 'Lead' }}</h2>
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
			<div class="d-flex justify-content-end align-items-center mb-2 p-1">
				
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
					<form method="POST" action="{{ route('leads.followups.store', $lead) }}" id="followup-form"
						data-registration-url="{{ route('registration.create', ['lead_id' => $lead->id, 'embed' => 1]) }}"
						data-admission-url="{{ route('admission.create', ['lead_id' => $lead->id, 'embed' => 1]) }}">
						@csrf
						<fieldset {{ $isClosed ? 'disabled' : '' }}>
							<div class="form-row" style = "gap:18px;padding-left:15px">
								<div class="form-group custom-col-3 followup-toggle">
									<label>Follow-Up Method</label>
									<select class="form-control" name="method">
										<option value="">- Select -</option>
										@foreach (['call', 'sms', 'email', 'whatsapp', 'walk-in'] as $method)
											<option value="{{ $method }}">{{ ucfirst($method) }}</option>
										@endforeach
									</select>
								</div>
								<div class="form-group custom-col-3">
									<label>Stage *</label>
									@php
										$hideRegistered = $lead->status === 'not_interesting';
										$hideNotInteresting = in_array($lead->status, ['registered', 'enrolled'], true);
									@endphp
									<select class="form-control" name="stage" id="followup-stage" required onchange="window.handleFollowupStageChange && window.handleFollowupStageChange(this)">
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
								<div class="form-group custom-col-3 followup-toggle followup-hide-on-close" id="next-followup-wrap">
									<label>Next Follow Up</label>
									<input type="datetime-local" class="form-control" name="next_action_date" id="next_action_date">
								</div>
								<div class="form-group custom-col-3 followup-toggle followup-hide-on-close" id="campus-wrap">
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
							<div class="form-row align-items-center" style = "gap:18px;padding-left:15px">

    <!-- Probability -->
    <div class="form-group custom-col-3  followup-toggle followup-hide-on-close" id="probability-wrap">
        <label class="form-label small fw-semibold text-dark required">Probability</label>

        <input type="range"
               min="0"
               max="100"
               step="5"
               id="probabilitySlider"
               name="details[probability]"
               value="{{ old('details.probability', 10) }}"
               class="custom-range">

        <div class="range-numbers pt-0 mt-1">
            <span>0</span>
            <span>10</span>
            <span>20</span>
            <span>40</span>
            <span>60</span>
            <span>80</span>
            <span>100</span>
        </div>

        <div class="small semibold">
            Selected: <span id="probabilityValue">
                {{ old('details.probability', 10) }}%
            </span>
        </div>
    </div>

    <!-- Remarks -->
    <div class="form-group col-md-9 followup-toggle">
        <label>Remarks</label>
        <textarea class="form-control" name="note" rows="2"
                  placeholder="Add remarks for this follow-up"
				  style="width:92%"></textarea>
    </div>

</div>
							<div class="alert alert-info d-none" id="registration-link">
								Selecting <strong>Registered</strong>? Complete the registration form first.
								<a href="{{ route('registration.create', ['lead_id' => $lead->id, 'embed' => 1]) }}" class="btn btn-sm btn-primary ml-2">Open Registration Form</a>
							</div>
							<div class="alert alert-info d-none" id="admission-link">
								Selecting <strong>Enrolled</strong>? Complete the admission form first.
								<a href="{{ route('admission.create', ['lead_id' => $lead->id, 'embed' => 1]) }}" class="btn btn-sm btn-primary ml-2">Open Admission Form</a>
							</div>
</div>
							<div class="text-right p-1">
								<button type="submit" class="btn btn-primary-outline">Save Follow-Up</button>
								<button type="button" id="cancel-followup-btn" class="btn btn-danger-outline">Cancel Follow-Up</button>
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
									<th>Interested Program</th>
									<td>{{ $lead->program->title ?? $lead->program->name ?? '—' }}</td>
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
								<td>{{ ucfirst(str_replace('_', ' ', $lead->status ?? 'pending')) }}</td>
								<th>Next Follow-Up</th>
								<td>{{ $nextFollowup?->next_action_date ? \Illuminate\Support\Carbon::parse($nextFollowup->next_action_date)->format('Y-m-d H:i') : '—' }}</td>
								<th>Campus Code</th>
								<td>{{ $lead->campus->code ?? '—' }}</td>
								
							</tr>
							
							<tr>
								
								<th>Campus Name</th>
								<td>{{ $lead->campus->name ?? '—' }}</td>
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
		*{
			font-family: 'Proxima Nova', sans-serif;
			font-size: 12px !important; 
			margin: 0;
			padding: 0;
        }
   .lead-show-shell {
    max-width: 1400px;
    margin: 0 auto;
    padding: 7px;
    background: #fff;
    border: 1px solid #dbe4ed;
    border-radius: 10px;
	height:85vh !important
}
		 .followup-table-wrapper {
	max-width: 1400px;
    border: 1px solid #dbe4ed;
    border-radius: 6px;
    overflow-x: auto;
    overflow-y: scroll;
    height: 20vh !important;
}
		.box-typical.box-typical-dashboard .box-typical-body {
   			 overflow: hidden;
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
		.form-label{
			font-size: 11px;
			font-weight: 600 ;
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
        .col-md-3 {
			flex: 0 0 200px !important;
			max-width: 200px !important;
			height: 62px;
			margin-bottom: 2px ;
			/* margin-top: 2px;    */
		}
		.lead-show-shell {
			max-width: 1400px;
			margin: 0 auto;	
			padding: 7px;
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
			width: 14%;
			height:auto;
			background: #f6f8fb;
			color: #4c5a6a;
		}

		.info-table td {
			background: #fff;
			color: #334155;
		}

		.lead-modal {
			position: fixed;
			inset: 0;
			background: rgba(15, 23, 42, 0.55);
			display: none;
			align-items: center;
			justify-content: center;
			z-index: 1055;
			padding: 16px;
		}

		.lead-modal.show {
			display: flex;
		}

		.lead-modal .modal-card {
			background: #fff;
			width: min(1100px, 96vw);
			height: min(720px, 90vh);
			border-radius: 12px;
			box-shadow: 0 20px 60px rgba(15, 23, 42, 0.35);
			display: flex;
			flex-direction: column;
			overflow: hidden;
		}

		.lead-modal .modal-header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 10px 16px;
			border-bottom: 1px solid #e2e8f0;
			background: #f8fbff;
		}

		.lead-modal .modal-title {
			font-weight: 700;
			color: #0f3c6e;
			margin: 0;
			font-size: 16px;
		}

		.lead-modal .modal-close {
			border: 0;
			background: transparent;
			font-size: 22px;
			line-height: 1;
			color: #5b6b80;
			cursor: pointer;
		}

		.lead-modal iframe {
			flex: 1;
			border: 0;
			width: 100%;
		}

		.custom-range {
    -webkit-appearance: none;
    width: 100%;
    height: 6px;
    border-radius: 4px;
    background: #ddd;
    outline: none;
}

/* Webkit Track */
.custom-range::-webkit-slider-runnable-track {
    height: 6px;
    border-radius: 4px;
}

/* Webkit Thumb */
.custom-range::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 22px;
    height: 22px;
    background: #1e88e5;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 4px rgba(0,0,0,0.3);
    cursor: pointer;
    margin-top: -8px;
}

/* Firefox Thumb */
.custom-range::-moz-range-thumb {
    width: 22px;
    height: 22px;
    background: #1e88e5;
    border-radius: 50%;
    border: 3px solid #fff;
    cursor: pointer;
}
	.range-numbers {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #666;
    margin-top: 8px;
}
.range-numbers span{
    font-size:10px;
    margin-bottom: 3px ;
}


input[name="details[probability]"] + .small {
    margin-top: 0px;
    font-size: 12px;
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

    /* ===============================
       Helpers
    =============================== */

    const $ = (selector, parent = document) => parent.querySelector(selector);
    const $$ = (selector, parent = document) => parent.querySelectorAll(selector);

    function normalize(value) {
        return (value || '')
            .toString()
            .trim()
            .toLowerCase()
            .replace(/\s+/g, '_');
    }

    /* ===============================
       Tabs
    =============================== */

    function initTabs() {
        const tabs = $$('.lead-tabs .tab');
        const contents = $$('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.style.display = 'none');

                this.classList.add('active');
                const target = this.dataset.target;
                if (target) $(target).style.display = 'block';
            });
        });
    }

    /* ===============================
       Probability Slider
    =============================== */

    function initProbability() {
        const range = $('#probability-range');
        const label = $('#probability-value');
        if (!range || !label) return;

        const update = () => {
            label.textContent = `Selected: ${range.value}%`;
        };

        range.addEventListener('input', update);
        update();
    }

    /* ===============================
       Followup Toggle
    =============================== */

    function initFollowupToggle() {
        const btn = $('#toggle-followup-form');
        const card = $('#followup-form-card');
        if (!btn || !card) return;

        btn.addEventListener('click', () => {
            const isOpen = card.style.display === 'block';
            card.style.display = isOpen ? 'none' : 'block';
            btn.textContent = isOpen ? 'Add Follow-Up' : 'Hide Follow-Up';
        });
    }

    /* ===============================
       Followup Submit (AJAX)
    =============================== */

    function initFollowupSubmit() {
        const form = $('#followup-form');
        if (!form) return;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const btn = form.querySelector('button[type="submit"]');
            if (btn) btn.disabled = true;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': form.querySelector('[name="_token"]')?.value || ''
                    },
                    credentials: 'same-origin',
                    body: new FormData(form)
                });

                if (response.status === 422) {
                    const data = await response.json();
                    throw new Error(data.message || 'Validation error.');
                }

                if (!response.ok) throw new Error();

                showAlert('Success', 'Follow-up saved successfully.', 'success');
                form.reset();
                initProbability();
                refreshFollowupHistory();

            } catch (err) {
                showAlert('Error', err.message || 'Unable to save follow-up.', 'error');
            } finally {
                if (btn) btn.disabled = false;
            }
        });
    }

    function showAlert(title, text, type) {
        if (window.swal) {
            swal({ title, text, type });
        } else {
            alert(text);
        }
    }

    /* ===============================
       Cancel Followup
    =============================== */

   function initFollowupCancel() {
    const btn = document.getElementById('cancel-followup-btn');
    const form = document.getElementById('followup-form');
    const card = document.getElementById('followup-form-card');

    if (!btn || !form) return;

    btn.addEventListener('click', function () {

        form.reset();

        // probability update
        const range = document.getElementById('probability-range');
        const label = document.getElementById('probability-value');
        if (range && label) {
            label.textContent = 'Selected: ' + range.value + '%';
        }

        if (card) {
            card.style.display = 'none';
        }

        if (window.swal) {
            swal({
                title: 'Cancelled',
                text: 'Follow-up cancelled successfully.',
                type: 'success'
            });
        } else {
            alert('Follow-up cancelled successfully.');
        }

    });
}

    /* ===============================
       Refresh History
    =============================== */

    async function refreshFollowupHistory() {
        const table = $('.followup-table');
        if (!table) return;

        try {
            const response = await fetch(window.location.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });

            const html = await response.text();
            const doc = document.implementation.createHTMLDocument('');
            doc.documentElement.innerHTML = html;

            const newBody = doc.querySelector('.followup-table tbody');
            const oldBody = table.querySelector('tbody');

            if (newBody && oldBody) {
                oldBody.innerHTML = newBody.innerHTML;
            }
        } catch (_) { }
    }

    /* ===============================
       Stage Progress Bar
    =============================== */

    function updateStageProgress() {
        const bar = $('.stage-bar');
        const progress = $('.stage-progress');
        if (!bar || !progress) return;

        const bullets = $$('.stage .bullet', bar);
        if (!bullets.length) return;

        const current =
            $('.stage.current .bullet', bar) ||
            $('.stage.active:last-child .bullet', bar) ||
            bullets[0];

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
        window.addEventListener('resize', () => {
            setTimeout(updateStageProgress, 100);
        });
    }

    /* ===============================
       INIT ALL
    =============================== */

    document.addEventListener('DOMContentLoaded', () => {
        initTabs();
        initProbability();
        initFollowupToggle();
        initFollowupSubmit();
        initFollowupCancel();
        initStageProgress();
    });

})();
</script>
	<script>
document.addEventListener("DOMContentLoaded", function () {

    const slider = document.getElementById("probabilitySlider");
    const output = document.getElementById("probabilityValue");

    function updateSlider() {
        const value = slider.value;
        const percent = (value - slider.min) / (slider.max - slider.min) * 100;

        slider.style.background =
            `linear-gradient(to right, #1e88e5 0%, #1e88e5 ${percent}%, #ddd ${percent}%, #ddd 100%)`;

        output.textContent = value + "%";
    }

    updateSlider();
    slider.addEventListener("input", updateSlider);
});
</script>
@endpush
