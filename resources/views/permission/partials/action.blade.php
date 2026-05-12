@php
    $actionId = $actionId ?? ('permission-action-' . $permission->id);
    $isDeleted = !is_null($permission->at_deleted ?? null);
@endphp

<div class="dropdown follow-action-dropdown permission-action-dropdown">
    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="{{ $actionId }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-right lead-action-menu" aria-labelledby="{{ $actionId }}">
        @if(!$isDeleted)
            <a class="dropdown-item lead-action-item" href="{{ route('permissions.edit', $permission) }}">
                <span class="lead-action-icon lead-icon-blue"><i class="fa fa-pencil"></i></span>
                <span class="lead-action-label">Edit</span>
            </a>
            <form action="{{ route('permissions.destroy', $permission) }}" method="POST" onsubmit="return confirm('Delete this permission?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="dropdown-item lead-action-item">
                    <span class="lead-action-icon lead-icon-red"><i class="fa fa-trash"></i></span>
                    <span class="lead-action-label">Delete</span>
                </button>
            </form>
        @else
            <form action="{{ route('permissions.restore', $permission->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="dropdown-item lead-action-item">
                    <span class="lead-action-icon lead-icon-green"><i class="fa fa-undo"></i></span>
                    <span class="lead-action-label">Restore</span>
                </button>
            </form>
        @endif
    </div>
</div>
