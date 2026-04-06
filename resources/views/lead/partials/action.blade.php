@php
	$actionId = $actionId ?? ('action-' . uniqid());
	$lead = $lead ?? null;
	$leadId = $lead?->id ?? ($leadId ?? null);
	$leadStatus = strtolower((string) ($lead?->status ?? 'pending'));
	$canRegister = !in_array($leadStatus, ['registered', 'enrolled', 'not_interesting'], true);
	$canEnroll = $leadStatus === 'registered';
	$canTransfer = !in_array($leadStatus, ['registered', 'enrolled'], true);
@endphp

<div class="dropdown follow-action-dropdown">
	<button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="{{ $actionId }}"   data-display="static" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		Actions
	</button>
	<div class="dropdown-menu dropdown-menu-right" aria-labelledby="{{ $actionId }}">
		@if(!empty($leadId))
			<a class="dropdown-item" href="{{ route('leads.show', $leadId) }}">
				<i class="fa fa-list-ul mr-2 text-primary p-1"></i>Follow-Up
			</a>
			@if($canRegister)
				<a class="dropdown-item" href="{{ route('registration.create', ['lead_id' => $leadId]) }}">
					<i class="fa fa-file-text-o mr-2 text-primary p-1"></i>Register Now
				</a>
			@elseif($canEnroll)
				<a class="dropdown-item" href="{{ route('admission.create', ['lead_id' => $leadId]) }}">
					<i class="fa fa-graduation-cap mr-2 text-primary p-1"></i>Enroll Now
				</a>
			@endif
			@if($canTransfer)
				<a class="dropdown-item js-lead-modal-link"
					href="{{ route('leads.transfer.form', $leadId) }}"
					data-lead-modal-url="{{ route('leads.transfer.form', ['lead' => $leadId, 'embed' => 1]) }}"
					data-lead-modal-title="Transfer Lead">
					<i class="fa fa-exchange mr-2 text-warning p-1"></i>Transfer Lead
				</a>
			@endif
			<div class="dropdown-divider"></div>
		@endif
		<a class="dropdown-item" href="#"><i class="fa fa-phone mr-2 text-info p-1"></i>Follow-Up Call</a>
		<a class="dropdown-item" href="#"><i class="fa fa-commenting-o mr-2 text-info p-1"></i>Send SMS</a>
		<a class="dropdown-item" href="#"><i class="fa fa-envelope-o mr-2 text-muted p-1"></i>Send Email</a>
		<a class="dropdown-item" href="#">
			<svg class="mr-2 p-1" width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" style="fill:#28a745;vertical-align:middle;">
				<path d="M20.52 3.48A11.8 11.8 0 0 0 12.12 0C5.58 0 .24 5.34.24 11.88c0 2.1.54 4.14 1.62 5.94L0 24l6.36-1.8a11.86 11.86 0 0 0 5.76 1.5H12.12c6.54 0 11.88-5.34 11.88-11.88 0-3.18-1.26-6.18-3.48-8.34zm-8.4 18.24h-.06a9.8 9.8 0 0 1-4.98-1.38l-.36-.24-3.78 1.08 1.02-3.66-.24-.36a9.88 9.88 0 0 1-1.56-5.28c0-5.46 4.44-9.9 9.9-9.9 2.64 0 5.1 1.02 6.96 2.88a9.8 9.8 0 0 1 2.88 7.02c0 5.46-4.44 9.9-9.84 9.9zm5.46-7.38c-.3-.18-1.8-.9-2.1-1.02-.24-.06-.48-.12-.72.18-.18.3-.72 1.02-.9 1.2-.18.18-.36.24-.66.06a8.1 8.1 0 0 1-2.4-1.5 8.98 8.98 0 0 1-1.68-2.1c-.18-.3 0-.42.12-.6.12-.12.3-.36.42-.54.12-.18.18-.3.3-.48.06-.18 0-.36 0-.54 0-.12-.72-1.74-.96-2.34-.24-.6-.54-.48-.72-.48h-.6c-.24 0-.54.06-.84.36-.3.3-1.08 1.08-1.08 2.64s1.14 3.06 1.26 3.24c.18.24 2.22 3.42 5.4 4.74.72.3 1.32.48 1.8.6.72.24 1.38.18 1.92.12.6-.06 1.8-.72 2.1-1.44.24-.66.24-1.32.18-1.44-.12-.12-.3-.18-.6-.36z"/>
			</svg>
			Whatsapp
		</a>
		<a class="dropdown-item" href="#"><i class="fa fa-male mr-2 text-secondary p-1"></i>Walk-In Status</a>
		<a class="dropdown-item" href="#"><i class="fa fa-hourglass-start mr-2 text-warning p-1"></i>Start Trail</a>
		<a class="dropdown-item" href="#"><i class="fa fa-times-circle-o mr-2 text-danger p-1"></i>Not Interested</a>
		<!-- <div class="dropdown-divider"></div> -->
		<a class="dropdown-item" href="#"><i class="fa fa-pencil-square-o mr-2 pl-2 text-muted"></i>Edit</a>
	</div>
</div>
