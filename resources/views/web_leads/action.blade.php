@php
	$actionId = $actionId ?? ('web-lead-action-' . uniqid());
@endphp

<div class="dropdown follow-action-dropdown">
	@if ($webLead->isActionable())
		<button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="{{ $actionId }}" data-display="static" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			Actions
		</button>
		<div class="dropdown-menu dropdown-menu-right" aria-labelledby="{{ $actionId }}">
			<a class="dropdown-item" href="{{ route('leads.create', ['web_lead' => $webLead->id]) }}">
				<i class="fa fa-plus-square-o mr-2 text-primary p-1"></i>Create Lead
			</a>
			<div class="dropdown-divider"></div>
			<form method="POST" action="{{ route('web-leads.not-interested', $webLead) }}">
				@csrf
				<button type="submit" class="dropdown-item text-danger">
					<i class="fa fa-times-circle-o mr-2 text-danger p-1"></i>Not Interested
				</button>
			</form>
		</div>
	@else
		<a href="{{ route('web-leads.show', $webLead) }}" class="btn btn-default btn-sm">View</a>
	@endif
</div>
