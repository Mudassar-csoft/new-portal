@php
    $actionId = $actionId ?? ('program-action-' . $program->id);
    $isActive = ($program->status ?? 'active') === 'active';
@endphp

@once
    @push('styles')
        <style>
        :root {
            --dimension-program-partials-action-1: 24px;
            --space-program-partials-action-1: 0 !important;
            --color-program-partials-action-1: #303740;
            --typo-program-partials-action-font-size-1: 17px;
            --typo-program-partials-action-font-weight-2: 500;
        }

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
                color: var(--color-program-partials-action-1) !important;
                font-size: var(--typo-program-partials-action-font-size-1) !important;
                font-weight: var(--typo-program-partials-action-font-weight-2);
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
                width: var(--dimension-program-partials-action-1);
                min-width: var(--dimension-program-partials-action-1);
                height: var(--dimension-program-partials-action-1);
                font-size: 1.125rem !important;
                line-height: 1;
                text-align: center;
                margin-right: var(--space-program-partials-action-1);
                padding: var(--space-program-partials-action-1);
            }

            .follow-action-dropdown .lead-action-label {
                display: inline-block;
                font-size: var(--typo-program-partials-action-font-size-1) !important;
                font-weight: var(--typo-program-partials-action-font-weight-2);
                letter-spacing: 0.01em;
            }

            .follow-action-dropdown .lead-action-icon svg {
                display: block;
                width: var(--dimension-program-partials-action-1);
                height: var(--dimension-program-partials-action-1);
            }

            .follow-action-dropdown .lead-action-icon.lead-icon-blue { color: #19b6e6; }
            .follow-action-dropdown .lead-action-icon.lead-icon-black { color: var(--color-program-partials-action-1); }
            .follow-action-dropdown .lead-action-icon.lead-icon-green { color: #2db853; }
            .follow-action-dropdown .lead-action-icon.lead-icon-yellow { color: #f5b400; }
            .follow-action-dropdown .lead-action-icon.lead-icon-red { color: #ef4e4e; }

            .follow-action-dropdown form { margin: 0; }
        </style>
    @endpush
@endonce

<div class="dropdown follow-action-dropdown">
    @include('partials.action-dropdown-toggle')
    <div class="dropdown-menu dropdown-menu-right lead-action-menu" aria-labelledby="{{ $actionId }}">
        @if(auth()->user()?->isAdmin())
            <a class="dropdown-item lead-action-item" href="{{ route('program.edit', $program) }}">
                @include('partials.action-edit-content')
            </a>
        @endif
        <a class="dropdown-item lead-action-item" href="{{ route('batch.index', ['program_id' => $program->id]) }}">
            <span class="lead-action-icon lead-icon-black" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 20v-1a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v1"/>
                    <circle cx="9.5" cy="7.5" r="3.5"/>
                    <path d="M18 8h4"/>
                    <path d="M20 6v4"/>
                </svg>
            </span>
            <span class="lead-action-label">View Batches</span>
        </a>
        <a class="dropdown-item lead-action-item" href="{{ route('batch.timetable.index', ['program_id' => $program->id]) }}">
            @include('partials.action-calendar-icon')
            <span class="lead-action-label">View Time Table</span>
        </a>
        @if($program->outline_path)
            <a class="dropdown-item lead-action-item" href="{{ route('program.outline', $program) }}">
                <span class="lead-action-icon lead-icon-yellow" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3.5v12"/>
                        <path d="m6.5 10 5.5 5.5L17.5 10"/>
                        <path d="M4.5 19.5h15"/>
                    </svg>
                </span>
                <span class="lead-action-label">Download Outline</span>
            </a>
        @endif

        <form method="POST" action="{{ route('program.toggle-status', $program) }}">
            @csrf
            @method('PATCH')
            <button type="submit" class="dropdown-item lead-action-item">
                @if($isActive)
                    @include('partials.action-suspend-content')
                @else
                    @include('partials.action-activate-content')
                @endif
            </button>
        </form>

        <form method="POST" action="{{ route('program.destroy', $program) }}" onsubmit="return confirm('Delete this programme permanently? This cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="dropdown-item lead-action-item">
                @include('partials.action-delete-content')
            </button>
        </form>
    </div>
</div>
