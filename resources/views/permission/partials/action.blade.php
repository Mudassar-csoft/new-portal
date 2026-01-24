@php
	$actionId = $actionId ?? ('permission-action-' . $permission->id);
@endphp

<div class="dropdown permission-action-dropdown">
	<button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="{{ $actionId }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		Actions
	</button>
	<div class="dropdown-menu dropdown-menu-right" aria-labelledby="{{ $actionId }}">
		<a class="dropdown-item" href="{{ route('permissions.edit', $permission) }}">
			<i class="fa fa-pencil mr-2 text-info"></i>Edit
		</a>
		<form action="{{ route('permissions.destroy', $permission) }}" method="POST" onsubmit="return confirm('Delete this permission?')">
			@csrf
			@method('DELETE')
			<button type="submit" class="dropdown-item text-danger">
				<i class="fa fa-trash mr-2"></i>Delete
			</button>
		</form>
	</div>
</div>
