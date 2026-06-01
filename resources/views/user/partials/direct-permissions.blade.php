@php
    $sectionTitle = $sectionTitle ?? 'Direct Permissions';
    $helperText = $helperText ?? 'Permissions are assigned manually by module. Role-based access stays separate and is not auto-selected here.';
    $selectedPermissionIds = collect($selectedPermissionIds ?? []);
    $inputName = $inputName ?? 'permissions[]';
@endphp

<div class="form-section">
    <div class="section-title form-label">{{ $sectionTitle }}</div>
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
                                    <label class="perm-item form-label">
                                        <input
                                            type="checkbox"
                                            name="{{ $inputName }}"
                                            value="{{ $permission['id'] }}"
                                            @checked($selectedPermissionIds->contains($permission['id']))
                                        >
                                        <span>{{ $permission['label'] }}</span>
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
            .permission-helper {
                margin-bottom: 10px;
                font-size: 13px;
            }
            .permission-wrapper {
                max-height: 300px;
                overflow-y: auto;
                overflow-x: hidden;
                padding: 12px;
                border: 1px solid #e6e9ed;
                border-radius: 6px;
                background: #fff;
            }
            .permission-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 12px;
            }
            .perm-column {
                padding: 12px;
                border: 1px solid #e6e9ed;
                border-radius: 6px;
                background: #fafbfc;
                min-height: 120px;
            }
            .perm-heading {
                margin-bottom: 8px;
                font-weight: 700;
                letter-spacing: 0.5px;
            }
            .perm-section + .perm-section {
                margin-top: 14px;
                padding-top: 12px;
                border-top: 1px solid #e6edf5;
            }
            .perm-subheading {
                margin-bottom: 6px;
                font-size: 12px;
                font-weight: 700;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: #7f93ac;
            }
            .perm-item {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 6px 0;
                cursor: pointer;
                font-weight: 500;
            }
            .perm-item input[type="checkbox"] {
                margin: 0;
                width: 16px;
                height: 16px;
            }
        </style>
    @endpush
@endonce
