@php
	$actionId = $actionId ?? ('action-' . uniqid());
@endphp

<div class="dropdown follow-action-dropdown">
	<button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="{{ $actionId }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		Actions
	</button>
	<div class="dropdown-menu dropdown-menu-right" aria-labelledby="{{ $actionId }}">
		@if(!empty($leadId))
			<a class="dropdown-item" href="{{ route('leads.show', $leadId) }}">
				<i class="fa fa-list-ul mr-2 text-primary"></i>Follow-Up
			</a>
			<a class="dropdown-item" href="{{ route('leads.transfer.form', $leadId) }}">
				<i class="fa fa-exchange mr-2 text-warning"></i>Transfer Lead
			</a>
			<div class="dropdown-divider"></div>
		@endif
		<a class="dropdown-item" href="#"><i class="fa fa-file-text-o mr-2 text-primary"></i>Register Now</a>
		<a class="dropdown-item" href="#"><i class="fa fa-phone mr-2 text-info"></i>Follow-Up Call</a>
		<a class="dropdown-item" href="#"><i class="fa fa-commenting-o mr-2 text-info"></i>Send SMS</a>
		<a class="dropdown-item" href="#"><i class="fa fa-envelope-o mr-2 text-muted"></i>Send Email</a>
		<a class="dropdown-item" href="#"><i class="fa fa-whatsapp mr-2 text-success"></i>Whatsapp</a>
		<a class="dropdown-item" href="#"><i class="fa fa-male mr-2 text-secondary"></i>Walk-In Status</a>
		<a class="dropdown-item" href="#"><i class="fa fa-hourglass-start mr-2 text-warning"></i>Start Trail</a>
		<a class="dropdown-item" href="#"><i class="fa fa-exchange mr-2 text-warning"></i>Transfer Lead</a>
		<a class="dropdown-item" href="#"><i class="fa fa-times-circle-o mr-2 text-danger"></i>Not Interested</a>
		<div class="dropdown-divider"></div>
		<a class="dropdown-item" href="#"><i class="fa fa-pencil-square-o mr-2 text-muted"></i>Edit</a>
	</div>
</div>
