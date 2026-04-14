@php
	$actionId = $actionId ?? ('action-' . uniqid());
	$lead = $lead ?? null;
	$leadId = $lead?->id ?? ($leadId ?? null);
	$leadStatus = strtolower((string) ($lead?->status ?? 'pending'));
	$canRegister = !in_array($leadStatus, ['registered', 'enrolled', 'not_interesting'], true);
	$canEnroll = $leadStatus === 'registered';
	$canTransfer = !in_array($leadStatus, ['registered', 'enrolled'], true);
@endphp

@once
	@push('styles')
		<style>
			.follow-action-dropdown .dropdown-menu.lead-action-menu {
				text-align: left !important;
			}

			.follow-action-dropdown .dropdown-item.lead-action-item {
				display: flex !important;
				align-items: center;
				justify-content: flex-start;
				gap: 10px;
				text-align: left !important;
				padding-right: 14px !important;
			}

			.follow-action-dropdown .lead-action-icon {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				width: 20px;
				min-width: 20px;
				font-size: 16px !important;
				line-height: 1;
				text-align: center;
				margin-right: 0 !important;
				padding: 0 !important;
			}

			.follow-action-dropdown .lead-action-icon svg {
				display: block;
				width: 18px;
				height: 18px;
			}
		</style>
	@endpush
@endonce

<div class="dropdown follow-action-dropdown">
	<button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="{{ $actionId }}"   data-display="static" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		Actions
	</button>
	<div class="dropdown-menu dropdown-menu-right lead-action-menu" aria-labelledby="{{ $actionId }}">
		@if(!empty($leadId))
			<a class="dropdown-item lead-action-item" href="{{ route('leads.show', $leadId) }}">
				<i class="fa fa-list-ul lead-action-icon text-primary" aria-hidden="true"></i><span>Follow-Up</span>
			</a>
			@if($canRegister)
				<a class="dropdown-item lead-action-item" href="{{ route('registration.create', ['lead_id' => $leadId]) }}">
					<i class="fa fa-file-text-o lead-action-icon text-primary" aria-hidden="true"></i><span>Register Now</span>
				</a>
			@elseif($canEnroll)
				<a class="dropdown-item lead-action-item" href="{{ route('admission.create', ['lead_id' => $leadId]) }}">
					<i class="fa fa-graduation-cap lead-action-icon text-primary" aria-hidden="true"></i><span>Enroll Now</span>
				</a>
			@endif
			@if($canTransfer)
				<a class="dropdown-item lead-action-item js-lead-modal-link"
					href="{{ route('leads.transfer.form', $leadId) }}"
					data-lead-modal-url="{{ route('leads.transfer.form', ['lead' => $leadId, 'embed' => 1]) }}"
					data-lead-modal-title="Transfer Lead">
					<i class="fa fa-exchange lead-action-icon text-warning" aria-hidden="true"></i><span>Transfer Lead</span>
				</a>
			@endif
			<div class="dropdown-divider"></div>
		@endif
		<a class="dropdown-item lead-action-item" href="#"><i class="fa fa-phone lead-action-icon text-info" aria-hidden="true"></i><span>Follow-Up Call</span></a>
		<a class="dropdown-item lead-action-item" href="#"><i class="fa fa-commenting-o lead-action-icon text-info" aria-hidden="true"></i><span>Send SMS</span></a>
		<a class="dropdown-item lead-action-item" href="#"><i class="fa fa-envelope-o lead-action-icon text-muted" aria-hidden="true"></i><span>Send Email</span></a>
		<a class="dropdown-item lead-action-item" href="#">
			<span class="lead-action-icon" aria-hidden="true">
				<svg viewBox="0 0 24 24" style="fill:#28a745;vertical-align:middle;">
				<path d="M20.52 3.48A11.8 11.8 0 0 0 12.12 0C5.58 0 .24 5.34.24 11.88c0 2.1.54 4.14 1.62 5.94L0 24l6.36-1.8a11.86 11.86 0 0 0 5.76 1.5H12.12c6.54 0 11.88-5.34 11.88-11.88 0-3.18-1.26-6.18-3.48-8.34zm-8.4 18.24h-.06a9.8 9.8 0 0 1-4.98-1.38l-.36-.24-3.78 1.08 1.02-3.66-.24-.36a9.88 9.88 0 0 1-1.56-5.28c0-5.46 4.44-9.9 9.9-9.9 2.64 0 5.1 1.02 6.96 2.88a9.8 9.8 0 0 1 2.88 7.02c0 5.46-4.44 9.9-9.84 9.9zm5.46-7.38c-.3-.18-1.8-.9-2.1-1.02-.24-.06-.48-.12-.72.18-.18.3-.72 1.02-.9 1.2-.18.18-.36.24-.66.06a8.1 8.1 0 0 1-2.4-1.5 8.98 8.98 0 0 1-1.68-2.1c-.18-.3 0-.42.12-.6.12-.12.3-.36.42-.54.12-.18.18-.3.3-.48.06-.18 0-.36 0-.54 0-.12-.72-1.74-.96-2.34-.24-.6-.54-.48-.72-.48h-.6c-.24 0-.54.06-.84.36-.3.3-1.08 1.08-1.08 2.64s1.14 3.06 1.26 3.24c.18.24 2.22 3.42 5.4 4.74.72.3 1.32.48 1.8.6.72.24 1.38.18 1.92.12.6-.06 1.8-.72 2.1-1.44.24-.66.24-1.32.18-1.44-.12-.12-.3-.18-.6-.36z"/>
				</svg>
			</span>
			<span>Whatsapp</span>
		</a>
		<a class="dropdown-item lead-action-item" href="#"><i class="fa fa-male lead-action-icon text-secondary" aria-hidden="true"></i><span>Walk-In Status</span></a>
		<a class="dropdown-item lead-action-item" href="#"><i class="fa fa-hourglass-start lead-action-icon text-warning" aria-hidden="true"></i><span>Start Trail</span></a>
		<a class="dropdown-item lead-action-item" href="#"><i class="fa fa-times-circle-o lead-action-icon text-danger" aria-hidden="true"></i><span>Not Interested</span></a>
		<!-- <div class="dropdown-divider"></div> -->
		<a class="dropdown-item lead-action-item" href="#"><i class="fa fa-pencil-square-o lead-action-icon text-muted" aria-hidden="true"></i><span>Edit</span></a>
	</div>
</div>
