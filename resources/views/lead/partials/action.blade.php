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
		<a class="dropdown-item" href="#"><i class="fa fa-whatsapp mr-2 text-success p-1"></i>Whatsapp</a>
		<a class="dropdown-item" href="#"><i class="fa fa-male mr-2 text-secondary p-1"></i>Walk-In Status</a>
		<a class="dropdown-item" href="#"><i class="fa fa-hourglass-start mr-2 text-warning p-1"></i>Start Trail</a>
		<a class="dropdown-item" href="#"><i class="fa fa-times-circle-o mr-2 text-danger p-1"></i>Not Interested</a>
		<!-- <div class="dropdown-divider"></div> -->
		<a class="dropdown-item" href="#"><i class="fa fa-pencil-square-o mr-2 pl-2 text-muted"></i>Edit</a>
	</div>
</div>
