@php
	$actionId = $actionId ?? ('adm-action-' . uniqid());
@endphp

<div class="dropdown admission-action-dropdown">
	<button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="{{ $actionId }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		Actions
	</button>
	<div class="dropdown-menu dropdown-menu-right" aria-labelledby="{{ $actionId }}">
		<a class="dropdown-item" href="#"><i class="fa fa-eye mr-2 text-muted"></i>View</a>
		<a class="dropdown-item" href="#"><i class="fa fa-pencil mr-2 text-info"></i>Edit</a>
		<a class="dropdown-item" href="#"><i class="fa fa-file-pdf-o mr-2 text-danger"></i>Invoice</a>
	</div>
</div>
