@php
	$actionId = $actionId ?? ('action-' . uniqid());
@endphp

<div class="dropdown follow-action-dropdown">
	<button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="{{ $actionId }}"   data-display="static" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		Actions
	</button>
	<div class="dropdown-menu dropdown-menu-right" aria-labelledby="{{ $actionId }}">
		@if(!empty($leadId))
			<a class="dropdown-item" href="{{ route('leads.show', $leadId) }}">
				<i class="fa fa-list-ul mr-2 text-primary p-2"></i>Follow-Up
			</a>
			<a class="dropdown-item" href="{{ route('leads.transfer.form', $leadId) }}">
				<i class="fa fa-exchange mr-2 text-warning p-2"></i>Transfer Lead
			</a>
			<div class="dropdown-divider"></div>
		@endif
		<a class="dropdown-item" href="#"><i class="fa fa-file-text-o mr-2 text-primary p-2"></i>Register Now</a>
		<a class="dropdown-item" href="#"><i class="fa fa-phone mr-2 text-info p-2"></i>Follow-Up Call</a>
		<a class="dropdown-item" href="#"><i class="fa fa-commenting-o mr-2 text-info p-2"></i>Send SMS</a>
		<a class="dropdown-item" href="#"><i class="fa fa-envelope-o mr-2 text-muted p-2"></i>Send Email</a>
		<a class="dropdown-item" href="#"><i class="fa fa-whatsapp mr-2 text-success p-2"></i>Whatsapp</a>
		<a class="dropdown-item" href="#"><i class="fa fa-male mr-2 text-secondary p-2"></i>Walk-In Status</a>
		<a class="dropdown-item" href="#"><i class="fa fa-hourglass-start mr-2 text-warning p-2"></i>Start Trail</a>
		<a class="dropdown-item" href="#"><i class="fa fa-exchange mr-2 text-warning p-2"></i>Transfer Lead</a>
		<a class="dropdown-item" href="#"><i class="fa fa-times-circle-o mr-2 text-danger p-2"></i>Not Interested</a>
		<div class="dropdown-divider"></div>
		<a class="dropdown-item" href="#"><i class="fa fa-pencil-square-o mr-2 text-muted"></i>Edit</a>
	</div>
</div>

