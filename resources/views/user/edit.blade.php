@extends('layouts.theme')

@section('title', 'Edit User')
@section('body_class', 'user-edit-page')

@section('content')
    @php
        $selectedRoleId = old('role_id');
        if ($selectedRoleId === null) {
            $selectedRoleId = collect(old('roles', $user->roles->modelKeys()))->filter()->first();
        }
        $selectedPermissionIds = collect(old('permissions', $user->permissions->modelKeys()))->map(fn ($id) => (int) $id);
        $selectedCampusId = old('campus_id', $user->campus_id);
        $selectedRole = $roles->first(fn ($role) => (string) $role->id === (string) $selectedRoleId);
        $rolePermissionIds = collect($selectedRole?->permissions?->modelKeys() ?? [])->map(fn ($id) => (int) $id);
    @endphp

    <div class="user-shell">
        <div class="box-typical box-typical-dashboard panel panel-default user-card">
            <header class="box-typical-header panel-heading user-card-header">
                <div>
                    <!-- <p class="user-kicker mb-1">User Management</p> -->
                    <h3 class="panel-title lead-title mb-0">Edit User</h3>
                </div>
            </header>

            <div class="box-typical-body panel-body user-body">
                <form method="POST" action="{{ route('users.update', $user) }}" class="user-form">
                    @csrf
                    @method('PUT')

                     <div class="user-form-row">
                        <div class="user-form-label">
                            <label for="name" class="form-label-role required">Full Name</label>
                        </div>
                        <div class="user-form-field">
                            <input
                                type="text"
                                name="name"
                                id="name"
                                class="form-control user-input @error('name') is-invalid @enderror"
                                placeholder="Alex Morgan"
                                value="{{ old('name', $user->name) }}"
                                required
                            >
                            @error('name')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="user-form-row">
                        <div class="user-form-label">
                            <label for="email_local" class="form-label-role required">Email Address</label>
                        </div>
                        <div class="user-form-field">
                            <div class="email-shell @error('email_local') has-error @enderror">
                                <input
                                    type="text"
                                    name="email_local"
                                    id="email_local"
                                    class="form-control user-input email-local-input @error('email_local') is-invalid @enderror"
                                    placeholder="admin"
                                    value="{{ old('email_local', $emailLocal) }}"
                                    inputmode="email"
                                    autocapitalize="none"
                                    spellcheck="false"
                                    required
                                >
                                <span class="email-domain">{{ '@' . $emailDomain }}</span>
                            </div>
                            @error('email_local')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="user-form-row">
                        <div class="user-form-label">
                            <label for="password" class="form-label">Password</label>
                        </div>
                        <div class="user-form-field">
                            <div class="input-shell">
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    class="form-control user-input user-input-password @error('password') is-invalid @enderror"
                                    placeholder="********"
                                    autocomplete="new-password"
                                >
                                <div class="input-shell-actions">
                                    <button class="inline-icon generate-password" type="button" aria-label="Generate password" title="Generate password">
                                        <i class="fa fa-refresh" aria-hidden="true"></i>
                                    </button>
                                    <button class="inline-icon toggle-visibility" type="button" data-target="#password" aria-label="Show password">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            @error('password')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="user-form-row">
                        <div class="user-form-label">
                            <label for="password_confirmation" class="form-label-role">Confirm Password</label>
                        </div>
                        <div class="user-form-field">
                            <div class="input-shell">
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    id="password_confirmation"
                                    class="form-control user-input user-input-confirm @error('password_confirmation') is-invalid @enderror"
                                    placeholder="********"
                                    autocomplete="new-password"
                                >
                                <div class="input-shell-actions">
                                    <button class="inline-icon toggle-visibility" type="button" data-target="#password_confirmation" aria-label="Show password confirmation">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            @error('password_confirmation')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="user-form-row">
                        <div class="user-form-label">
                            <label for="campus_id" class="form-label-role">Campus</label>
                        </div>
                        <div class="user-form-field">
                            <select
                                name="campus_id"
                                id="campus_id"
                                class="form-control select2  @error('campus_id') is-invalid @enderror"
                                style="width: 100%;"
                                data-placeholder="- Select Campus -"
                            >
                                <option value="">- Select Campus -</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected((string) $selectedCampusId === (string) $campus->id)>{{ $campus->code ?: $campus->name }}</option>
                                @endforeach
                            </select>
                            @error('campus_id')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="user-form-row">
                        <div class="user-form-label">
                            <label for="role_id" class="form-label-role">Roles</label>
                        </div>
                        <div class="user-form-field">
                            <select
                                name="role_id"
                                id="role_id"
                                class="form-control select2  @error('role_id') is-invalid @enderror"
                                style="width: 100%;"
                                data-placeholder="Select role"
                            >
                                <option value="">- Select role -</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" @selected((string) $selectedRoleId === (string) $role->id)>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                            @error('roles')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                            @error('roles.*')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    @include('user.partials.direct-permissions', [
                        'permissionGroups' => $permissionGroups,
                        'selectedPermissionIds' => $selectedPermissionIds,
                        'rolePermissionIds' => $rolePermissionIds,
                        'helperText' => 'Direct permissions can be edited here. Permissions inherited from the selected role are shown as checked and locked.',
                    ])

                    <div class="text-right">
                        <button type="submit" class="btn btn-inline btn-primary-outline ">Save Changes</button>
                        <a href="{{ route('users.index') }}" class="btn btn-inline btn-danger-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --dimension-user-edit-1: 100%;
            --dimension-user-edit-2: 100%;
            --dimension-user-edit-3: 30px;
            --dimension-user-edit-4: 39px;
            --dimension-user-edit-5: 50px;
            --space-user-edit-1: 0 !important;
            --space-user-edit-2: 0 auto;
            --space-user-edit-3: 14px;
            --space-user-edit-4: 16px;
            --space-user-edit-5: 41px;
            --space-user-edit-6: 6px;
            --space-user-edit-7: 8px;
            --color-user-edit-1: #bcd3e8;
            --color-user-edit-2: #d2dee9;
            --color-user-edit-3: #d93048;
            --color-user-edit-4: #e8f1ff;
            --color-user-edit-5: #fff;
        }

        :root {
            --dimension-user-edit-1: 100%;
            --dimension-user-edit-2: 100%;
            --dimension-user-edit-3: 30px;
            --dimension-user-edit-4: 39px;
            --dimension-user-edit-5: 50px;
            --space-user-edit-1: 0 !important;
            --space-user-edit-2: 0 auto;
            --space-user-edit-3: 14px;
            --space-user-edit-4: 16px;
            --space-user-edit-5: 41px;
            --space-user-edit-6: 6px;
            --space-user-edit-7: 8px;
            --typo-user-edit-font-weight-1: 600;
            --typo-user-edit-font-weight-2: 700;
            --typo-user-edit-font-size-3: 20px;
            --typo-user-edit-font-size-4: 13px;
        }0___

        .form-label-role{
            font-size: 14px !important;
            margin-bottom: var(--space-user-edit-6);
            margin-top: var(--space-user-edit-6);
            
            color: #343a40 !important;
            text-transform: uppercase;
            font-weight: var(--typo-user-edit-font-weight-1);

        }
        .user-edit-page {
            overflow-x: hidden;
        }
        .user-edit-page .page-content {
            overflow-x: hidden;
        }
        .user-edit-page .page-content > .container-fluid {
            max-width: var(--dimension-user-edit-2) !important;
            padding-left: var(--space-user-edit-1);
            padding-right: var(--space-user-edit-1);
            overflow: visible !important;
        }
        .user-edit-page .user-shell {
            min-height: auto;
                padding: 0px 0px 0px 22px;
           
            overflow-x: hidden;
        }
        .user-edit-page .user-card {
            
            margin: var(--space-user-edit-2);
            border: 1px solid #d9e4f0;
            border-radius: 0px;
            overflow: hidden;
           
            background: var(--color-user-edit-5);
        }
        .user-edit-page .user-card-header {
            padding: 22px 34px 18px;
            border-bottom: 1px solid #e8eef6;
            /* background: linear-gradient(180deg, rgba(248, 251, 255, 0.96), rgba(255, 255, 255, 0.98)); */
        }
        .user-edit-page .user-kicker {
            font-size: 12px;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #7f93ac;
            font-weight: var(--typo-user-edit-font-weight-2);
        }
        .user-edit-page .user-card .panel-title {
            font-size: 32px !important;
            line-height: 1.15;
            font-weight: 400 !important;
            color: #1d2f40;
            letter-spacing: 0;
        }
        .user-edit-page .user-body {
            padding: 26px 34px 24px;
        }
        .user-edit-page .user-form {
            display: flex;
            flex-direction: column;
            gap: var(--space-user-edit-3);
            width: var(--dimension-user-edit-1);
            max-width: 960px;
            margin: var(--space-user-edit-2);
        }
        .user-edit-page .user-form-row {
            display: grid;
            grid-template-columns: 230px minmax(0, 1fr);
            gap: var(--space-user-edit-5);
            align-items: start;
            width: var(--dimension-user-edit-1);
        }
        .user-edit-page .user-form-label {
            padding-top: 11px;
            padding-left: var(--space-user-edit-5);
        }
        .user-edit-page .required::after {
            content: '*';
            color: #f00;
            margin-left: 4px;
        }
        .user-edit-page .user-form-field,
        .user-edit-page .input-shell {
            min-width: 0;
            width: var(--dimension-user-edit-1);
            max-width: var(--dimension-user-edit-1);
        }
        .user-edit-page .user-input,
        .user-edit-page .select2-container--white .select2-selection--single,
        .user-edit-page .select2-container--white .select2-selection--multiple {
            min-height: var(--dimension-user-edit-5);
            border: 1px solid var(--color-user-edit-2) !important;
            border-radius: 5px;
            background: var(--color-user-edit-5);
            font-size: var(--typo-user-edit-font-size-3);
            color: #071526;
            box-shadow: none;
            transition: border-color 0.2s ease, background 0.2s ease;
            max-width: var(--dimension-user-edit-1);
            box-sizing: border-box;
        }
        .user-edit-page .user-input {
            width: var(--dimension-user-edit-1);
            padding: 0 14px;
        }
        .user-edit-page .user-input::placeholder {
            color: #8ca0b7;
        }
        .user-edit-page .user-input:focus,
        .user-edit-page .select2-container--white.select2-container--focus .select2-selection--single,
        .user-edit-page .select2-container--white.select2-container--open .select2-selection--single,
        .user-edit-page .select2-container--white.select2-container--focus .select2-selection--multiple {
            border-color: var(--color-user-edit-1);
            background: var(--color-user-edit-4);
            box-shadow: none;
        }
        .user-edit-page .input-shell {
            position: relative;
        }
        .user-edit-page .email-shell {
            display: flex;
            align-items: stretch;
            width: var(--dimension-user-edit-1);
        }
        .user-edit-page .email-local-input {
            border-right: 0 !important;
            border-radius: 5px 0 0 5px;
        }
        .user-edit-page .email-domain {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: var(--dimension-user-edit-5);
            padding: 0 16px;
            border: 1px solid var(--color-user-edit-2);
            border-left: 0;
            border-radius: 0 5px 5px 0;
            background: #eef4fb;
            color: #4e6278;
            font-size: 17px;
            font-weight: var(--typo-user-edit-font-weight-1);
            white-space: nowrap;
        }
        .user-edit-page .email-shell:focus-within .email-domain {
            border-color: var(--color-user-edit-1);
            background: var(--color-user-edit-4);
        }
        .user-edit-page .email-shell.has-error .email-domain {
            border-color: var(--color-user-edit-3);
        }
        .user-edit-page .input-shell-actions {
            position: absolute;
            top: 0;
            right: 0;
            height: 37px;
            display: inline-flex;
            align-items: center;
            gap: 0;
            z-index: 3;
        }
        .user-edit-page .user-input-password {
            padding-right: 96px;
            background: var(--color-user-edit-4);
        }
        .user-edit-page .user-input-confirm {
            padding-right: 70px;
        }
        .user-edit-page .inline-icon {
            width: 40px;
            height: var(--dimension-user-edit-5);
            border: 0;
            border-radius: 0;
            outline: 0;
            background: #0ea5f4;
            color: var(--color-user-edit-5);
            font-size: var(--typo-user-edit-font-size-3);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s ease;
        }
        .user-edit-page .inline-icon:hover,
        .user-edit-page .inline-icon:focus {
            background: #0086d8;
            color: var(--color-user-edit-5);
        }
        .user-edit-page .input-shell-actions .inline-icon + .inline-icon {
            border-left: 1px solid rgba(255, 255, 255, 0.18);
        }
        .user-edit-page .inline-icon .fa-eye,
        .user-edit-page .inline-icon .fa-eye-slash,
        .user-edit-page .inline-icon .fa-refresh {
            background: transparent !important;
            padding: var(--space-user-edit-1);
            color: var(--color-user-edit-5) !important;
        }
        .user-edit-page .field-error {
            margin-top: var(--space-user-edit-7);
            font-size: var(--typo-user-edit-font-size-4);
            font-weight: var(--typo-user-edit-font-weight-1);
            color: var(--color-user-edit-3);
        }
        .user-edit-page .select2-container {
            width: var(--dimension-user-edit-2) !important;
            max-width: var(--dimension-user-edit-2) !important;
        }
        .user-edit-page .select2-container--default .select2-selection--single,
        .user-edit-page .select2-container--white .select2-selection--single {
            display: flex !important;
            align-items: center !important;
            height: var(--dimension-user-edit-4) !important;
            min-height: var(--dimension-user-edit-4) !important;
            padding: 0 58px 0 14px !important;
            border-radius: 5px !important;
            background: var(--color-user-edit-5) !important;
            box-shadow: none !important;
            border: 1px solid var(--color-user-edit-2) !important;
        }
        .user-edit-page .select2-container--default .select2-selection--single .select2-selection__rendered,
        .user-edit-page .select2-container--white .select2-selection--single .select2-selection__rendered {
            width: var(--dimension-user-edit-2) !important;
            color: #15283a;
            line-height: 1.2 !important;
            padding: 9px 0px 0px !important;
            margin: var(--space-user-edit-1);
            font-size: var(--typo-user-edit-font-size-3);
            border: none !important;
        }
        .user-edit-page .select2-container--default .select2-selection--single .select2-selection__arrow,
        .user-edit-page .select2-container--white .select2-selection--single .select2-selection__arrow {
            height: var(--dimension-user-edit-2) !important;
            top: 0 !important;
            right: 0 !important;
            width: 31px !important;
            border-radius: 0 5px 5px 0;
            background: #d8e3eb !important;
        }
        .user-edit-page .select2-container--default .select2-selection--single .select2-selection__arrow b,
        .user-edit-page .select2-container--white .select2-selection--single .select2-selection__arrow b {
            border-color: #778796 transparent transparent transparent !important;
        }
        .user-edit-page .select2-container--white .select2-selection--multiple {
            height: auto;
            padding: 8px 12px;
        }
        .user-edit-page .select2-container--white .select2-selection--multiple .select2-selection__rendered {
            display: flex;
            flex-wrap: wrap;
            gap: var(--space-user-edit-7);
            margin: 0;
            padding: 0;
        }
        .user-edit-page .select2-container--white .select2-selection--multiple .select2-search--inline {
            margin: 0;
        }
        .user-edit-page .select2-container--white .select2-selection--multiple .select2-search--inline .select2-search__field {
            height: var(--dimension-user-edit-3);
            margin: 0;
            line-height: var(--dimension-user-edit-3);
        }
        .user-edit-page .select2-container--white .select2-selection--multiple .select2-selection__choice {
            margin: 0;
            padding: 6px 12px;
            border-radius: 999px;
            border: 1px solid #cfe0ff;
            background: #edf4ff;
            color: #1b4880;
            font-size: var(--typo-user-edit-font-size-4);
            font-weight: var(--typo-user-edit-font-weight-1);
            position: relative;
        }
        .form-label{
`           font-size:16px !important; 
        }
        .user-edit-page .select2-container--white .select2-selection--multiple .select2-selection__choice__remove {
            position: static;
            width: auto;
            height: auto;
            margin-right: var(--space-user-edit-6);
            border: 0;
            background: transparent;
            color: #7990ac;
            font-size: var(--typo-user-edit-font-size-4);
            line-height: 1;
        }
        .user-edit-page .select2-container--white .select2-selection--multiple .select2-selection__choice__remove:hover {
            background: transparent;
            color: var(--color-user-edit-3);
        }
        .user-edit-page .user-actions {
            display: flex;
            justify-content: center;
            gap: var(--space-user-edit-3);
            margin-top: 18px;
            padding-top: var(--space-user-edit-7);
        }
        .user-edit-page .user-action-primary,
        .user-edit-page .user-action-secondary {
            width: 176px;
            height: 48px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: var(--typo-user-edit-font-weight-2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        @media (max-width: 900px) {
            .user-edit-page .user-body {
                padding: 26px 20px;
            }
            .user-edit-page .user-form-row {
                grid-template-columns: 1fr;
                gap: var(--space-user-edit-7);
            }
            .user-edit-page .user-form-label {
                padding-top: 0;
                padding-left: 0;
            }
        }
        @media (max-width: 640px) {
            .user-edit-page .user-shell {
                padding: var(--space-user-edit-3);
            }
            .user-edit-page .user-card-header,
            .user-edit-page .user-body {
                padding-left: var(--space-user-edit-4);
                padding-right: var(--space-user-edit-4);
            }
            .user-edit-page .user-card .panel-title {
                font-size: 26px !important;
            }
            .user-edit-page .user-actions {
                flex-direction: column;
            }
            .user-edit-page .user-action-primary,
            .user-edit-page .user-action-secondary {
                width: var(--dimension-user-edit-1);
            }
            .user-edit-page .email-shell {
                flex-direction: column;
            }
            .user-edit-page .email-local-input {
                border-right: 1px solid var(--color-user-edit-2) !important;
                border-radius: 5px 5px 0 0;
            }
            .user-edit-page .email-domain {
                border-left: 1px solid var(--color-user-edit-2);
                border-top: 0;
                border-radius: 0 0 5px 5px;
                justify-content: flex-start;
                padding: 12px 14px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            function toggleVisibility(button) {
                const input = document.querySelector(button.getAttribute('data-target'));
                if (!input) return;

                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';

                const icon = button.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-eye', !isHidden);
                    icon.classList.toggle('fa-eye-slash', isHidden);
                }
            }

            function generatePassword(length) {
                const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^&*';
                let result = '';

                for (let index = 0; index < length; index += 1) {
                    result += chars.charAt(Math.floor(Math.random() * chars.length));
                }

                return result;
            }

            function fillPasswordFields() {
                const password = document.getElementById('password');
                const confirmation = document.getElementById('password_confirmation');
                if (!password || !confirmation) return;

                const generated = generatePassword(14);
                password.value = generated;
                confirmation.value = generated;
            }

            document.addEventListener('DOMContentLoaded', function () {
                if (window.jQuery && $.fn.select2) {
                    $('#campus_id').select2({
                        width: '100%',
                        dropdownParent: $('.user-card'),
                        dropdownAutoWidth: false,
                    });

                    $('#role_id').select2({
                        width: '100%',
                        dropdownParent: $('.user-card'),
                        dropdownAutoWidth: false,
                        allowClear: true,
                        minimumResultsForSearch: 0,
                    });
                }

                document.querySelectorAll('.toggle-visibility').forEach(function (button) {
                    button.addEventListener('click', function () {
                        toggleVisibility(this);
                    });
                });

                document.querySelector('.generate-password')?.addEventListener('click', function () {
                    fillPasswordFields();
                });
            });
        })();
    </script>
@endpush
