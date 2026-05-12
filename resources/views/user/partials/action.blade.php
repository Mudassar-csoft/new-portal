@php
    $actionId = $actionId ?? ('user-action-' . $user->id);
    $isDeleted = !is_null($user->at_deleted ?? null);
@endphp

@once
    @push('styles')
        <style>
            .follow-action-dropdown .dropdown-menu.lead-action-menu {
                min-width: 200px;
                padding: 1px 0;
                border: 1px solid #dfe5eb;
                border-radius: 6px;
                background: #fff;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
                text-align: left !important;
            }
            .follow-action-dropdown .dropdown-item.lead-action-item,
            .follow-action-dropdown form button.dropdown-item.lead-action-item {
                display: flex !important; align-items: center; gap: 8px; width: 100%;
                text-align: left !important; padding: 6px 18px !important;
                color: #303740 !important; font-size: 15px !important; font-weight: 500;
                background: transparent !important; border: 0;
                transition: background-color 0.18s ease;
            }
            .follow-action-dropdown .dropdown-item.lead-action-item:hover,
            .follow-action-dropdown form button.dropdown-item.lead-action-item:hover {
                background: #f7fafc !important; text-decoration: none;
            }
            .follow-action-dropdown .lead-action-icon { width: 20px; min-width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center; }
            .follow-action-dropdown .lead-action-icon.lead-icon-blue { color: #19b6e6; }
            .follow-action-dropdown .lead-action-icon.lead-icon-green { color: #2db853; }
            .follow-action-dropdown .lead-action-icon.lead-icon-red { color: #ef4e4e; }
            .follow-action-dropdown form { margin: 0; }
        </style>
    @endpush
@endonce

<div class="dropdown follow-action-dropdown user-action-dropdown">
    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="{{ $actionId }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-right lead-action-menu" aria-labelledby="{{ $actionId }}">
        @if(!$isDeleted)
            <a class="dropdown-item lead-action-item" href="{{ route('users.edit', $user) }}">
                <span class="lead-action-icon lead-icon-blue"><i class="fa fa-pencil"></i></span>
                <span class="lead-action-label">Edit</span>
            </a>
            <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete this user? They will be moved to the Deleted tab.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="dropdown-item lead-action-item">
                    <span class="lead-action-icon lead-icon-red"><i class="fa fa-trash"></i></span>
                    <span class="lead-action-label">Delete</span>
                </button>
            </form>
        @else
            <form action="{{ route('users.restore', $user->id) }}" method="POST">
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
