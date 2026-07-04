@php
    $actionId = $actionId ?? ('batch-action-' . $batch->id);
    $isActive = ($batch->status ?? 'active') === 'active';
    $isTerminal = in_array(($batch->status ?? ''), ['completed', 'cancelled'], true);
@endphp

@once
    @push('styles')
        <style>
        :root {
            --dimension-batch-partials-action-1: 24px;
            --space-batch-partials-action-1: 0 !important;
            --color-batch-partials-action-1: #303740;
            --typo-batch-partials-action-font-size-1: 17px;
            --typo-batch-partials-action-font-weight-2: 500;
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
                color: var(--color-batch-partials-action-1) !important;
                font-size: var(--typo-batch-partials-action-font-size-1) !important;
                font-weight: var(--typo-batch-partials-action-font-weight-2);
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
                width: var(--dimension-batch-partials-action-1);
                min-width: var(--dimension-batch-partials-action-1);
                height: var(--dimension-batch-partials-action-1);
                font-size: 18px !important;
                line-height: 1;
                text-align: center;
                margin-right: var(--space-batch-partials-action-1);
                padding: var(--space-batch-partials-action-1);
            }

            .follow-action-dropdown .lead-action-label {
                display: inline-block;
                font-size: var(--typo-batch-partials-action-font-size-1) !important;
                font-weight: var(--typo-batch-partials-action-font-weight-2);
                letter-spacing: 0.01em;
            }

            .follow-action-dropdown .lead-action-icon svg {
                display: block;
                width: var(--dimension-batch-partials-action-1);
                height: var(--dimension-batch-partials-action-1);
            }

            .follow-action-dropdown .lead-action-icon.lead-icon-blue { color: #19b6e6; }
            .follow-action-dropdown .lead-action-icon.lead-icon-black { color: var(--color-batch-partials-action-1); }
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
            <a class="dropdown-item lead-action-item" href="{{ route('batch.edit', $batch) }}">
                @include('partials.action-edit-content')
            </a>
        @endif
        <a class="dropdown-item lead-action-item" href="{{ route('batch.timetable.index', ['batch_id' => $batch->id]) }}">
            @include('partials.action-calendar-icon')
            <span class="lead-action-label">Manage Time Table</span>
        </a>

        @unless($isTerminal)
            <form method="POST" action="{{ route('batch.toggle-status', $batch) }}">
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
        @endunless

        <form method="POST" action="{{ route('batch.destroy', $batch) }}" onsubmit="return confirm('Delete this batch permanently? This cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="dropdown-item lead-action-item">
                @include('partials.action-delete-content')
            </button>
        </form>
    </div>
</div>
