@php
    $actionId = $actionId ?? ('campus-action-' . $campus->id);
    $isActive = ($campus->status ?? 'active') === 'active';
@endphp

@once
    @push('styles')
        <style>
            .follow-action-dropdown .dropdown-menu.lead-action-menu {
                min-width: 220px;
                padding: 1px 0;
                border: 1px solid #dfe5eb;
                border-radius: 6px;
                background: #fff;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
                text-align: left !important;
            }

            .follow-action-dropdown .dropdown-item.lead-action-item,
            .follow-action-dropdown form button.dropdown-item.lead-action-item {
                display: flex !important;
                align-items: center;
                justify-content: flex-start;
                gap: 8px;
                width: 100%;
                text-align: left !important;
                padding: 5px 18px !important;
                color: #303740 !important;
                font-size: 16px !important;
                font-weight: 500;
                line-height: 1.35;
                background: transparent !important;
                border: 0;
                transition: background-color 0.18s ease, color 0.18s ease;
            }

            .follow-action-dropdown .dropdown-item.lead-action-item:hover,
            .follow-action-dropdown .dropdown-item.lead-action-item:focus,
            .follow-action-dropdown form button.dropdown-item.lead-action-item:hover,
            .follow-action-dropdown form button.dropdown-item.lead-action-item:focus {
                background: #f7fafc !important;
                color: #222b33 !important;
                text-decoration: none;
            }

            .follow-action-dropdown .lead-action-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 22px;
                min-width: 22px;
                height: 22px;
                font-size: 16px !important;
                line-height: 1;
                text-align: center;
                margin-right: 0 !important;
                padding: 0 !important;
            }

            .follow-action-dropdown .lead-action-label {
                display: inline-block;
                font-size: 16px !important;
                font-weight: 500;
                letter-spacing: 0.01em;
            }

            .follow-action-dropdown .lead-action-icon svg {
                display: block;
                width: 22px;
                height: 22px;
            }

            .follow-action-dropdown .lead-action-icon.lead-icon-blue { color: #19b6e6; }
            .follow-action-dropdown .lead-action-icon.lead-icon-black { color: #303740; }
            .follow-action-dropdown .lead-action-icon.lead-icon-green { color: #2db853; }
            .follow-action-dropdown .lead-action-icon.lead-icon-yellow { color: #f5b400; }
            .follow-action-dropdown .lead-action-icon.lead-icon-red { color: #ef4e4e; }

            .follow-action-dropdown form { margin: 0; }
        </style>
    @endpush
@endonce

<div class="dropdown follow-action-dropdown campus-action-dropdown">
    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="{{ $actionId }}" data-display="static" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-right lead-action-menu" aria-labelledby="{{ $actionId }}">
        @if(auth()->user()?->isAdmin())
            <a class="dropdown-item lead-action-item" href="{{ route('campus.edit', $campus) }}">
                <span class="lead-action-icon lead-icon-blue" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3.75 20.25h4.5l11-11a1.6 1.6 0 0 0 0-2.25l-2.25-2.25a1.6 1.6 0 0 0-2.25 0l-11 11v4.5Z"/>
                        <path d="m13.5 6.5 4 4"/>
                    </svg>
                </span>
                <span class="lead-action-label">Edit</span>
            </a>
        @endif

        <a class="dropdown-item lead-action-item" href="{{ route('inventory.index', ['campus_id' => $campus->id]) }}">
            <span class="lead-action-icon lead-icon-black" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3.5" y="6" width="17" height="14" rx="1.75"/>
                    <path d="M8 3.5h8"/>
                    <path d="M8 11h8"/>
                    <path d="M8 15h5"/>
                </svg>
            </span>
            <span class="lead-action-label">View Inventory</span>
        </a>

        <form method="POST" action="{{ route('campus.toggle-status', $campus) }}">
            @csrf
            @method('PATCH')
            <button type="submit" class="dropdown-item lead-action-item">
                @if($isActive)
                    <span class="lead-action-icon lead-icon-yellow" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="6" y="5" width="4" height="14" rx="1"/>
                            <rect x="14" y="5" width="4" height="14" rx="1"/>
                        </svg>
                    </span>
                    <span class="lead-action-label">Suspend</span>
                @else
                    <span class="lead-action-icon lead-icon-green" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="6,4 20,12 6,20" fill="currentColor" stroke="currentColor"/>
                        </svg>
                    </span>
                    <span class="lead-action-label">Activate</span>
                @endif
            </button>
        </form>

        <form method="POST" action="{{ route('campus.destroy', $campus) }}" onsubmit="return confirm('Delete this campus permanently? This cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="dropdown-item lead-action-item">
                <span class="lead-action-icon lead-icon-red" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 7h16"/>
                        <path d="M9 7V4h6v3"/>
                        <path d="M6 7l1 13h10l1-13"/>
                        <path d="M10 11v6"/>
                        <path d="M14 11v6"/>
                    </svg>
                </span>
                <span class="lead-action-label">Delete</span>
            </button>
        </form>
    </div>
</div>
