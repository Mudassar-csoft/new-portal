@php
    $actionId = $actionId ?? ('news-action-' . $news->id);
    $isActive = ($news->status ?? 'active') === 'active';
@endphp

@once
    @push('styles')
        <style>
            :root {
                --dimension-news-partials-action-1: 24px;
                --space-news-partials-action-1: 0 !important;
                --color-news-partials-action-1: #303740;
                --typo-news-partials-action-font-size-1: 17px;
                --typo-news-partials-action-font-weight-2: 500;
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
                color: var(--color-news-partials-action-1) !important;
                font-size: var(--typo-news-partials-action-font-size-1) !important;
                font-weight: var(--typo-news-partials-action-font-weight-2);
                line-height: 1.35;
                background: transparent !important;
                border: 0;
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
                width: var(--dimension-news-partials-action-1);
                min-width: var(--dimension-news-partials-action-1);
                height: var(--dimension-news-partials-action-1);
                font-size: 1.125rem !important;
                line-height: 1;
                text-align: center;
                margin-right: var(--space-news-partials-action-1);
                padding: var(--space-news-partials-action-1);
            }

            .follow-action-dropdown .lead-action-label {
                display: inline-block;
                font-size: var(--typo-news-partials-action-font-size-1) !important;
                font-weight: var(--typo-news-partials-action-font-weight-2);
                letter-spacing: 0.01em;
            }

            .follow-action-dropdown form { margin: 0; }
        </style>
    @endpush
@endonce

<div class="dropdown follow-action-dropdown">
    @include('partials.action-dropdown-toggle')
    <div class="dropdown-menu dropdown-menu-right lead-action-menu" aria-labelledby="{{ $actionId }}">
        @if(auth()->user()?->isAdmin())
            <a class="dropdown-item lead-action-item" href="{{ route('news.edit', $news) }}">
                @include('partials.action-edit-content')
            </a>
        @endif

        <form method="POST" action="{{ route('news.toggle-status', $news) }}">
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

        <form method="POST" action="{{ route('news.destroy', $news) }}" onsubmit="return confirm('Delete this news permanently? This cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="dropdown-item lead-action-item">
                @include('partials.action-delete-content')
            </button>
        </form>
    </div>
</div>
