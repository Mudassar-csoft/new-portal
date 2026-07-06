@php
    $actionId = $actionId ?? ('user-action-' . $user->id);
    $isDeleted = !is_null($user->at_deleted ?? null);
@endphp

@once
    @push('styles')
        <style>
        :root {
            --dimension-user-partials-action-1: 24px;
            --color-user-partials-action-1: #303740;
        }

        :root {
            --dimension-user-partials-action-1: 24px;
            --typo-user-partials-action-font-size-1: 16px;
            --typo-user-partials-action-font-weight-2: 500;
            --typo-user-partials-action-line-height-3: 1;
            --typo-user-partials-action-font-size-4: 18px;
        }0___

            .user-action-dropdown .dropdown-menu.lead-action-menu {
                min-width: 320px;
                padding: 8px 0;
                border: 1px solid #dfe5eb;
                border-radius: 6px;
                background: #fff;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
                text-align: left !important;
            }

            .user-action-dropdown .dropdown-item.lead-action-item,
            .user-action-dropdown form button.dropdown-item.lead-action-item {
                display: flex !important;
                align-items: center;
                justify-content: flex-start;
                gap: 12px;
                width: 100%;
                padding: 8px 18px !important;
                color: var(--color-user-partials-action-1) !important;
                font-size: var(--typo-user-partials-action-font-size-1) !important;
                font-weight: var(--typo-user-partials-action-font-weight-2);
                line-height: 1.35;
                text-align: left !important;
                background: transparent !important;
                border: 0;
                transition: background-color 0.18s ease, color 0.18s ease;
            }

            .user-action-dropdown .dropdown-item.lead-action-item:hover,
            .user-action-dropdown .dropdown-item.lead-action-item:focus,
            .user-action-dropdown form button.dropdown-item.lead-action-item:hover,
            .user-action-dropdown form button.dropdown-item.lead-action-item:focus {
                background: #f7fafc !important;
                color: #222b33 !important;
                text-decoration: none;
            }

            .user-action-dropdown .dropdown-item.lead-action-item.is-disabled {
                opacity: 0.5;
                pointer-events: none;
            }

            .user-action-dropdown .lead-action-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: var(--dimension-user-partials-action-1);
                min-width: var(--dimension-user-partials-action-1);
                height: var(--dimension-user-partials-action-1);
                line-height: var(--typo-user-partials-action-line-height-3);
                color: var(--color-user-partials-action-1);
                font-size: 1.3125rem;
            }

            .user-action-dropdown .lead-action-label {
                display: inline-block;
                font-size: var(--typo-user-partials-action-font-size-1) !important;
                font-weight: var(--typo-user-partials-action-font-weight-2);
                letter-spacing: 0.01em;
            }

            .user-action-dropdown .lead-action-label strong {
                font-weight: 700;
            }

            /* .user-action-dropdown .lead-action-label .bi-person {
                font-size: var(--typo-user-partials-action-font-size-4);
                line-height: var(--typo-user-partials-action-line-height-3);
                position: relative;
                top: 1px;
                margin-right: 6px;
                vertical-align: middle;
            } */
            .bi-person {
                ;
                font-weight: 700 !important;
            }
            .user-action-dropdown .lead-icon-blue { color: #1698ff; }
            .user-action-dropdown .lead-icon-black { color: var(--color-user-partials-action-1); }
            .user-action-dropdown .lead-icon-red { color: var(--color-user-partials-action-1); }
            .user-action-dropdown form { margin: 0; }
        </style>
    @endpush
@endonce

<div class="dropdown follow-action-dropdown user-action-dropdown">
    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="{{ $actionId }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-right lead-action-menu" aria-labelledby="{{ $actionId }}">
        @if(!$isDeleted)
            @if(auth()->user()?->isAdmin())
                <a class="dropdown-item lead-action-item" href="{{ route('users.edit', $user) }}">
                    <span class="lead-action-icon lead-icon-blue" aria-hidden="true">
                        <i class="fa fa-pencil"></i>
                    </span>
                    <span class="lead-action-label">Edit</span>
                </a>

                <a class="dropdown-item lead-action-item" href="{{ route('users.edit', $user) }}">
                    <span class="lead-action-icon lead-icon-black" aria-hidden="true">
                        <i class="fa fa-pencil"></i>
                    </span>
                    <span class="lead-action-label"> Assign/Update Permisison</span>
                </a>
            @endif

            @if(auth()->id() !== $user->id)
                <form action="{{ route('users.impersonate', $user) }}" method="POST" onsubmit="return confirm(@json('Login as ' . $user->name . '?'));">
                    @csrf
                    <button type="submit" class="dropdown-item lead-action-item">
                        <span class="lead-action-icon lead-icon-black" aria-hidden="true">
                            <i class="bi bi-person" style="font-size: var(--typo-user-partials-action-font-size-4); margin-left: -3px !important;"></i>
                        </span>
                        <span class="lead-action-label"> Login as <strong>{{ $user->name }}</strong></span>
                    </button>
                </form>
            @else
                <a class="dropdown-item lead-action-item is-disabled" href="#" aria-disabled="true" tabindex="-1">
                    <span class="lead-action-icon lead-icon-black" aria-hidden="true">
                        <i class="bi bi-person" style="font-size: var(--typo-user-partials-action-font-size-4); margin-left: -3px !important;"></i>
                    </span>
                    <span class="lead-action-label"> Current User</span>
                </a>
            @endif

            <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Deactivate this portal user?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="dropdown-item lead-action-item">
                    <span class="lead-action-icon lead-icon-red" aria-hidden="true">
                        <i class="fa fa-trash-o"></i>
                    </span>
                    <span class="lead-action-label">Deactivate Portal</span>
                </button>
            </form>
        @else
            <form action="{{ route('users.restore', $user->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="dropdown-item lead-action-item">
                    <span class="lead-action-icon lead-icon-blue" aria-hidden="true">
                        <i class="fa fa-undo"></i>
                    </span>
                    <span class="lead-action-label">Restore Portal</span>
                </button>
            </form>
        @endif
    </div>
</div>
