@php
    $actionId = $actionId ?? ('role-action-' . $role->id);
    $isDeleted = !is_null($role->at_deleted ?? null);
    $isSystem = (bool) ($role->is_system ?? false);
@endphp

<div class="dropdown follow-action-dropdown role-action-dropdown">
    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="{{ $actionId }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-right lead-action-menu" aria-labelledby="{{ $actionId }}">
        @if(!$isDeleted)
            <a class="dropdown-item lead-action-item" href="{{ route('roles.edit', $role) }}">
                <span class="lead-action-icon lead-icon-blue"><i class="fa fa-pencil"></i></span>
                <span class="lead-action-label">Edit</span>
            </a>
            @if(!$isSystem)
                <form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Delete this role?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="dropdown-item lead-action-item">
                        <span class="lead-action-icon lead-icon-red"><i class="fa fa-trash"></i></span>
                        <span class="lead-action-label">Delete</span>
                    </button>
                </form>
            @endif
        @else
            <form action="{{ route('roles.restore', $role->id) }}" method="POST">
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
