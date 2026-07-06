@php
    $sectionTitle = $sectionTitle ?? 'Direct Permissions';
    $helperText = $helperText ?? 'Permissions are assigned manually by module. Role-based access stays separate and is not auto-selected here.';
    $selectedPermissionIds = collect($selectedPermissionIds ?? [])->map(fn ($id) => (int) $id);
    $rolePermissionIds = collect($rolePermissionIds ?? [])->map(fn ($id) => (int) $id);
    $inputName = $inputName ?? 'permissions[]';
@endphp

<div class="form-section">
    <div class="section-title form-label-role">{{ $sectionTitle }}</div>
    @if($helperText)
        <p class="permission-helper text-muted">{{ $helperText }}</p>
    @endif

    @if (!empty($permissionGroups))
        <div class="permission-wrapper">
            <div class="permission-grid">
                @foreach ($permissionGroups as $group)
                    <div class="perm-column">
                        <h6 class="text-muted text-uppercase perm-heading">{{ $group['label'] }}</h6>
                        @foreach (($group['sections'] ?? [['label' => null, 'permissions' => $group['permissions']]]) as $section)
                            <div class="perm-section">
                                @if(!empty($section['label']))
                                    <div class="perm-subheading">{{ $section['label'] }}</div>
                                @endif
                                @foreach ($section['permissions'] as $permission)
                                    @php
                                        $permissionId = (int) $permission['id'];
                                        $isDirectPermission = $selectedPermissionIds->contains($permissionId);
                                        $isRolePermission = $rolePermissionIds->contains($permissionId);
                                        $isLockedByRole = $isRolePermission && !$isDirectPermission;
                                    @endphp
                                    <label class="perm-item form-label {{ $isLockedByRole ? 'perm-item-locked' : '' }}">
                                        <input
                                            type="checkbox"
                                            name="{{ $inputName }}"
                                            value="{{ $permissionId }}"
                                            @checked($isDirectPermission || $isRolePermission)
                                            @disabled($isLockedByRole)
                                        >
                                        <span>{{ $permission['label'] }}</span>
                                        @if($isRolePermission)
                                            <span class="perm-badge">{{ $isDirectPermission ? 'Role + Direct' : 'Role' }}</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="alert alert-info mb-0">
            No permissions are available yet. Create or seed module permissions first.
        </div>
    @endif

    @error('permissions')
        <div class="field-error">{{ $message }}</div>
    @enderror
    @error('permissions.*')
        <div class="field-error">{{ $message }}</div>
    @enderror
</div>

@once
    @push('styles')
        <style>
        :root {
            --dimension-user-partials-direct-permissions-1: 16px;
            --space-user-partials-direct-permissions-1: 12px;
            --space-user-partials-direct-permissions-2: 8px;
            --color-user-partials-direct-permissions-1: #e6e9ed;
            --typo-user-partials-direct-permissions-font-weight-1: 700;
        }

            .permission-helper {
                margin-bottom: 10px;
                font-size: 0.8125rem;
            }
            .permission-wrapper {
                max-height: 300px;
                overflow-y: auto;
                overflow-x: hidden;
                padding: var(--space-user-partials-direct-permissions-1);
                border: 1px solid var(--color-user-partials-direct-permissions-1);
                border-radius: 6px;
                background: #fff;
            }
            .form-label{
                font-size: clamp(0.8rem, 1.5vw, 1rem) !important;
            }
            .permission-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: var(--space-user-partials-direct-permissions-1);
            }
            .perm-column {
                padding: var(--space-user-partials-direct-permissions-1);
                border: 1px solid var(--color-user-partials-direct-permissions-1);
                border-radius: 6px;
                background: #fafbfc;
                min-height: 120px;
            }
            .perm-heading {
                margin-bottom: var(--space-user-partials-direct-permissions-2);
                font-weight: var(--typo-user-partials-direct-permissions-font-weight-1);
                letter-spacing: 0.5px;
            }
            .perm-section + .perm-section {
                margin-top: 14px;
                padding-top: var(--space-user-partials-direct-permissions-1);
                border-top: 1px solid #e6edf5;
            }
            .perm-subheading {
                margin-bottom: 6px;
                font-size: 0.75rem;
                font-weight: var(--typo-user-partials-direct-permissions-font-weight-1);
                letter-spacing: 0.08em;
                /* text-transform: uppercase; */
                color: #7f93ac;
            }
            .perm-item {
                display: flex;
                align-items: center;
                gap: var(--space-user-partials-direct-permissions-2);
                padding: 6px 0;
                cursor: pointer;
                font-weight: 500 !important;
                flex-wrap: wrap;
            }
            .perm-item input[type="checkbox"] {
                margin: 0;
                width: var(--dimension-user-partials-direct-permissions-1);
                height: var(--dimension-user-partials-direct-permissions-1);
            }
            .perm-item-locked {
                cursor: default;
            }
            .perm-item input[type="checkbox"][disabled] {
                cursor: not-allowed;
                opacity: 0.9;
            }
            .perm-badge {
                display: inline-flex;
                align-items: center;
                padding: 2px 8px;
                border-radius: 999px;
                background: #e6eef9;
                color: #47627f;
                font-size: 0.6875rem;
                font-weight: var(--typo-user-partials-direct-permissions-font-weight-1);
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }
        </style>
    @endpush
@endonce
