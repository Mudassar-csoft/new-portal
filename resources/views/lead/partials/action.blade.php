@php
	$actionId = $actionId ?? ('action-' . uniqid());
	$lead = $lead ?? null;
	$leadId = $lead?->id ?? ($leadId ?? null);
	$leadStatus = strtolower((string) ($lead?->status ?? 'pending'));
	$leadType = (string) ($lead?->type ?? 'training');
	$isCoworkingLead = $leadType === 'coworking';
	$supportsRegistration = in_array($leadType, ['training', 'coworking'], true);
	$supportsAdmission = $leadType === 'training';
	$editOnly = (bool) ($editOnly ?? false);
	$canRegister = $supportsRegistration && !in_array($leadStatus, ['registered', 'enrolled', 'not_interesting'], true);
	$canEnroll = $supportsAdmission && $leadStatus === 'registered';
	$canTransfer = !in_array($leadStatus, ['registered', 'enrolled', 'not_interesting'], true);
	$registrationRoute = $isCoworkingLead
		? route('coworking-registrations.create', ['lead_id' => $leadId])
		: route('registration.create', ['lead_id' => $leadId]);
	$registrationModalRoute = $isCoworkingLead
		? route('coworking-registrations.create', ['lead_id' => $leadId, 'embed' => 1])
		: route('registration.create', ['lead_id' => $leadId, 'embed' => 1]);
	$registrationModalTitle = $isCoworkingLead
		? 'Create New Coworking Space Registration (All fields marked with * are required)'
		: 'Create New Registration (All fields marked with * are required)';
	$canMarkNotInterested = !in_array($leadStatus, ['registered', 'enrolled', 'not_interesting'], true);
	$canEditLead = auth()->user()?->hasAnyPermission(['lead.update']) ?? false;
@endphp

@once
	@push('styles')
		<style>
        :root {
            --dimension-lead-partials-action-1: 22px;
            --dimension-lead-partials-action-2: 24px;
            --space-lead-partials-action-1: 0 !important;
            --color-lead-partials-action-1: #303740;
            --typo-lead-partials-action-font-size-1: 17px;
            --typo-lead-partials-action-font-weight-2: 500;
        }

			.follow-action-dropdown .dropdown-menu.lead-action-menu {
				min-width: 220px;
				padding: 1px 0;
				border: 1px solid #dfe5eb;
				border-radius: 6px;
				background: #fff;
				box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
				text-align: left !important;
				z-index: 9999;
			}

			.follow-action-dropdown .dropdown-toggle,
			.follow-action-dropdown .dropdown-toggle:hover,
			.follow-action-dropdown .dropdown-toggle:focus {
				z-index: auto !important;
			}

			.follow-action-dropdown .dropdown-item.lead-action-item,
			.follow-action-dropdown form button.dropdown-item.lead-action-item {
				display: flex !important;
				align-items: center;
				justify-content: flex-start;
				gap: 8px;
				width: 100%;
				text-align: left !important;
				padding: 5px 18px !important;
				color: var(--color-lead-partials-action-1) !important;
				font-size: var(--typo-lead-partials-action-font-size-1) !important;
				font-weight: var(--typo-lead-partials-action-font-weight-2);
				line-height: 1.35;
				background: transparent !important;
				border: 0;
				transition: background-color 0.18s ease, color 0.18s ease;
			}

			.follow-action-dropdown .dropdown-item.lead-action-item:hover,
			.follow-action-dropdown .dropdown-item.lead-action-item:focus,
			.follow-action-dropdown form button.dropdown-item.lead-action-item:hover,
			.follow-action-dropdown form button.dropdown-item.lead-action-item:focus {
				background: #f7fafc !important;
				color: #222b33 !important;
				text-decoration: none;
			}

			.follow-action-dropdown .lead-action-icon {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				width: var(--dimension-lead-partials-action-2);
				min-width: var(--dimension-lead-partials-action-2);
				height: var(--dimension-lead-partials-action-2);
				font-size: 1.125rem !important;
				line-height: 1;
				text-align: center;
				margin-right: var(--space-lead-partials-action-1);
				padding: var(--space-lead-partials-action-1);
			}

			.follow-action-dropdown .lead-action-label {
				display: inline-block;
				font-size: var(--typo-lead-partials-action-font-size-1) !important;
				font-weight: var(--typo-lead-partials-action-font-weight-2);
				letter-spacing: 0.01em;
			}

			.follow-action-dropdown .lead-action-icon svg {
				display: block;
				width: var(--dimension-lead-partials-action-2);
				height: var(--dimension-lead-partials-action-2);
			}

			.follow-action-dropdown .lead-action-icon--whatsapp svg {
				width: var(--dimension-lead-partials-action-1);
				height: var(--dimension-lead-partials-action-1);
			}

			.follow-action-dropdown .lead-action-icon.lead-icon-blue {
				color: #19b6e6;
			}

			.follow-action-dropdown .lead-action-icon.lead-icon-black {
				color: var(--color-lead-partials-action-1);
			}

			.follow-action-dropdown .lead-action-icon.lead-icon-green {
				color: #2db853;
			}

			.follow-action-dropdown .lead-action-icon.lead-icon-yellow {
				color: #f5b400;
			}

			.follow-action-dropdown .lead-action-icon.lead-icon-red {
				color: #ef4e4e;
			}

			.follow-action-dropdown .lead-action-item.lead-action-danger .lead-action-label {
				color: var(--color-lead-partials-action-1) !important;
			}

			.follow-action-dropdown form {
				margin: 0;
			}
		</style>
	@endpush
@endonce

<div class="dropdown follow-action-dropdown">
	<button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="{{ $actionId }}"   data-display="static" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		Actions
	</button>
	<div class="dropdown-menu dropdown-menu-right lead-action-menu" aria-labelledby="{{ $actionId }}">

			@if(!$editOnly)
				@if(!empty($leadId))
					@if($canRegister)
						<a class="dropdown-item lead-action-item {{ $isCoworkingLead ? 'js-lead-modal-link' : '' }}"
							href="{{ $registrationRoute }}"
							@if($isCoworkingLead)
								data-lead-modal-url="{{ $registrationModalRoute }}"
								data-lead-modal-title="{{ $registrationModalTitle }}"
							@endif>
							<span class="lead-action-icon lead-icon-blue" aria-hidden="true">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
									<path d="M8 3.75h5.5L18.25 8.5V19a1.25 1.25 0 0 1-1.25 1.25h-9.5A1.25 1.25 0 0 1 6.25 19V5A1.25 1.25 0 0 1 7.5 3.75Z"/>
								<path d="M13.5 3.75V8.5h4.75"/>
								<path d="M9 12h6"/>
								<path d="M9 15.5h6"/>
							</svg>
						</span><span class="lead-action-label">Register Now</span>
					</a>
				@elseif($canEnroll)
					<a class="dropdown-item lead-action-item" href="{{ route('admission.create', ['lead_id' => $leadId]) }}">
						<span class="lead-action-icon lead-icon-blue" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
								<path d="m3 8.5 9-4 9 4-9 4-9-4Z"/>
								<path d="M7 10.5V15"/>
								<path d="M17 10.5V15"/>
								<path d="M7 15c1.6 1.6 8.4 1.6 10 0"/>
							</svg>
						</span><span class="lead-action-label">Enroll Now</span>
					</a>
				@endif
			@endif
			<a class="dropdown-item lead-action-item" href="{{ !empty($leadId) ? route('leads.show', $leadId) : '#' }}">
				<span class="lead-action-icon lead-icon-black" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
						<path d="M22 16.92v2.58a1.5 1.5 0 0 1-1.64 1.5A18.35 18.35 0 0 1 3 3.64 1.5 1.5 0 0 1 4.49 2h2.59a1.5 1.5 0 0 1 1.49 1.28l.35 2.3a1.5 1.5 0 0 1-.43 1.32l-1.68 1.68a14.5 14.5 0 0 0 8.61 8.61l1.68-1.68a1.5 1.5 0 0 1 1.32-.43l2.3.35A1.5 1.5 0 0 1 22 16.92Z"/>
						<path d="M15.5 2.5h6v6"/>
						<path d="M14 10 21.5 2.5"/>
					</svg>
				</span><span class="lead-action-label">Follow-Up Call</span>
			</a>
			<a class="dropdown-item lead-action-item" href="#">
				@include('partials.action-send-sms-content')
			</a>
			<a class="dropdown-item lead-action-item" href="#">
				@include('partials.action-send-email-content')
			</a>
			<a class="dropdown-item lead-action-item" href="#">
				@include('partials.action-whatsapp-content')
			</a>
			<a class="dropdown-item lead-action-item" href="#">
				<span class="lead-action-icon lead-icon-blue" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round">
						<path d="M16 20v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1"/>
						<circle cx="9.5" cy="7.5" r="3.5"/>
						<path d="M18 8h4"/>
						<path d="M20 6v4"/>
					</svg>
				</span><span class="lead-action-label">Walk-In Status</span>
			</a>
			<a class="dropdown-item lead-action-item" href="#">
				<span class="lead-action-icon lead-icon-black" aria-hidden="true">
					<i class="bi bi-hourglass-split"></i>
				</span><span class="lead-action-label">Start Trail</span>
			</a>
			@if(!empty($leadId) && $canTransfer)
				<a class="dropdown-item lead-action-item js-lead-modal-link"
					href="{{ route('leads.transfer.form', $leadId) }}"
					data-lead-modal-url="{{ route('leads.transfer.form', ['lead' => $leadId, 'embed' => 1]) }}"
					data-lead-modal-title="Transfer Lead">
					<span class="lead-action-icon lead-icon-yellow" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
							<path d="M4 7h14"/>
							<path d="m14 3 4 4-4 4"/>
							<path d="M20 17H6"/>
							<path d="m10 13-4 4 4 4"/>
						</svg>
					</span><span class="lead-action-label">Transfer Lead</span>
				</a>
			@endif
			@if($canMarkNotInterested && !empty($leadId))
				<form method="POST" action="{{ route('leads.not-interested', $leadId) }}" onsubmit="return confirm('Mark this lead as not interested?');">
					@csrf
					<button type="submit" class="dropdown-item lead-action-item lead-action-danger">
						<span class="lead-action-icon lead-icon-red" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round">
								<path d="M9.4 2.75h5.2l6.65 6.65v5.2l-6.65 6.65H9.4l-6.65-6.65V9.4z"/>
								<path d="m9 9 6 6"/>
								<path d="m15 9-6 6"/>
							</svg>
						</span><span class="lead-action-label">Not Interested</span>
					</button>
				</form>
			@endif
		@endif
		@if($canEditLead)
			<a class="dropdown-item lead-action-item" href="{{ !empty($leadId) ? route('leads.edit', $leadId) : '#' }}">
				@include('partials.action-edit-black-content')
			</a>
		@endif
	</div>
</div>

<style>
        :root {
            --space-lead-partials-action-1: 0 !important;
            --color-lead-partials-action-1: #303740;
        }

	.bi-hourglass-split{
		font-size:1.25rem !important;
	}
</style>
