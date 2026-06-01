@extends('layouts.theme')

@section('title', 'Edit User')
@section('body_class', 'user-edit-page')

@section('content')
    @php
        $selectedRoleIds = collect(old('roles', $user->roles->modelKeys()))->map(fn ($id) => (int) $id);
        $selectedPermissionIds = collect(old('permissions', $user->permissions->modelKeys()))->map(fn ($id) => (int) $id);
        $selectedCampusId = old('campus_id', $user->campus_id);
    @endphp

    <div class="user-shell">
        <div class="box-typical box-typical-dashboard panel panel-default user-card">
            <header class="box-typical-header panel-heading user-card-header">
                <div>
                    <p class="user-kicker mb-1">User Management</p>
                    <h3 class="panel-title form-label mb-0">Edit User</h3>
                </div>
            </header>

            <div class="box-typical-body panel-body user-body">
                <form method="POST" action="{{ route('users.update', $user) }}" class="user-form">
                    @csrf
                    @method('PUT')

                    <div class="user-form-row">
                        <div class="user-form-label">
                            <label for="name" class="form-label required">Full Name</label>
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
                            <label for="email" class="form-label required">Email Address</label>
                        </div>
                        <div class="user-form-field">
                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-control user-input @error('email') is-invalid @enderror"
                                placeholder="alex@example.com"
                                value="{{ old('email', $user->email) }}"
                                required
                            >
                            @error('email')
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
                                    class="form-control user-input user-input-confirm @error('password') is-invalid @enderror"
                                    placeholder="Leave blank to keep current password"
                                    autocomplete="new-password"
                                >
                                <div class="input-shell-actions">
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
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                        </div>
                        <div class="user-form-field">
                            <div class="input-shell">
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    id="password_confirmation"
                                    class="form-control user-input user-input-confirm @error('password_confirmation') is-invalid @enderror"
                                    placeholder="Repeat new password"
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
                            <label for="campus_id" class="form-label">Campus</label>
                        </div>
                        <div class="user-form-field">
                            <select
                                name="campus_id"
                                id="campus_id"
                                class="form-control select2 select2-white @error('campus_id') is-invalid @enderror"
                                style="width: 100%;"
                                data-placeholder="Select campus"
                            >
                                <option value="">All Campuses</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected((string) $selectedCampusId === (string) $campus->id)>{{ $campus->name }}</option>
                                @endforeach
                            </select>
                            @error('campus_id')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="user-form-row">
                        <div class="user-form-label">
                            <label for="roles" class="form-label">Roles</label>
                        </div>
                        <div class="user-form-field">
                            <select
                                name="roles[]"
                                id="roles"
                                class="form-control select2 select2-white select2-roles @error('roles') is-invalid @enderror"
                                multiple
                                style="width: 100%;"
                                data-placeholder="Select roles"
                            >
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" data-slug="{{ $role->slug }}" @selected($selectedRoleIds->contains($role->id))>{{ $role->name }}</option>
                                @endforeach
                            </select>
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
                    ])

                    <div class="user-actions">
                        <button type="submit" class="btn btn-inline btn-primary-outline user-action-primary">Save Changes</button>
                        <a href="{{ route('users.index') }}" class="btn btn-inline btn-danger-outline user-action-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .user-edit-page {
            overflow-x: hidden;
        }
        .user-edit-page .page-content {
            overflow-x: hidden;
        }
        .user-edit-page .page-content > .container-fluid {
            max-width: 100% !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            overflow: visible !important;
        }
        .user-edit-page .user-shell {
            min-height: 100vh;
            padding: 24px;
            background:
                radial-gradient(circle at top left, rgba(45, 120, 255, 0.09), transparent 26%),
                linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
            overflow-x: hidden;
        }
        .user-edit-page .user-card {
            width: min(100%, 1080px);
            margin: 0 auto;
            border: 1px solid #d9e4f0;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 22px 50px rgba(20, 53, 93, 0.12);
            background: #ffffff;
        }
        .user-edit-page .user-card-header {
            padding: 28px 34px 22px;
            border-bottom: 1px solid #e8eef6;
            background: linear-gradient(180deg, rgba(248, 251, 255, 0.96), rgba(255, 255, 255, 0.98));
        }
        .user-edit-page .user-kicker {
            font-size: 12px;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #7f93ac;
            font-weight: 700;
        }
        .user-edit-page .user-card .panel-title {
            font-size: 32px !important;
            line-height: 1.05;
            font-weight: 700 !important;
            color: #16324f;
        }
        .user-edit-page .user-body {
            padding: 34px;
        }
        .user-edit-page .user-form {
            display: flex;
            flex-direction: column;
            gap: 22px;
            width: 100%;
            max-width: 960px;
            margin: 0 auto;
        }
        .user-edit-page .user-form-row {
            display: grid;
            grid-template-columns: 200px minmax(0, 1fr);
            gap: 24px;
            align-items: start;
            width: 100%;
        }
        .user-edit-page .user-form-label {
            padding-top: 16px;
        }
        .user-edit-page .form-label {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            color: #17324b;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }
        .user-edit-page .required::after {
            content: '*';
            color: #f24f61;
            margin-left: 4px;
        }
        .user-edit-page .user-form-field,
        .user-edit-page .input-shell {
            min-width: 0;
            width: 100%;
            max-width: 100%;
        }
        .user-edit-page .user-input,
        .user-edit-page .select2-container--white .select2-selection--single,
        .user-edit-page .select2-container--white .select2-selection--multiple {
            min-height: 54px;
            border: 1px solid #cfe0f1;
            border-radius: 14px;
            background: #fff;
            font-size: 15px;
            color: #16324f;
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.8);
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
            max-width: 100%;
            box-sizing: border-box;
        }
        .user-edit-page .user-input {
            width: 100%;
            padding: 0 18px;
        }
        .user-edit-page .user-input::placeholder {
            color: #8ca0b7;
        }
        .user-edit-page .user-input:focus,
        .user-edit-page .select2-container--white.select2-container--focus .select2-selection--single,
        .user-edit-page .select2-container--white.select2-container--open .select2-selection--single,
        .user-edit-page .select2-container--white.select2-container--focus .select2-selection--multiple {
            border-color: #2b78ff;
            box-shadow: 0 0 0 4px rgba(43, 120, 255, 0.13);
        }
        .user-edit-page .input-shell {
            position: relative;
        }
        .user-edit-page .input-shell-actions {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            z-index: 3;
        }
        .user-edit-page .user-input-confirm {
            padding-right: 54px;
        }
        .user-edit-page .inline-icon {
            width: 34px;
            height: 34px;
            border: 0;
            outline: 0;
            cursor: pointer;
            border-radius: 50%;
            background: #f2f7fc;
            color: #6d849f;
            font-size: 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }
        .user-edit-page .inline-icon:hover,
        .user-edit-page .inline-icon:focus {
            background: #e5f0ff;
            color: #236be7;
            transform: translateY(-1px);
        }
        .user-edit-page .field-error {
            margin-top: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #d93048;
        }
        .user-edit-page .select2-container {
            width: 100% !important;
            max-width: 100% !important;
        }
        .user-edit-page .select2-container--default .select2-selection--single,
        .user-edit-page .select2-container--white .select2-selection--single {
            display: flex !important;
            align-items: center !important;
            height: 54px !important;
            min-height: 54px !important;
            padding: 0 48px 0 16px !important;
            border: 1px solid #cfe0f1 !important;
            border-radius: 14px !important;
            background: #fff !important;
            box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.8) !important;
        }
        .user-edit-page .select2-container--default .select2-selection--single .select2-selection__rendered,
        .user-edit-page .select2-container--white .select2-selection--single .select2-selection__rendered {
            width: 100% !important;
            color: #16324f;
            line-height: 1.2 !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .user-edit-page .select2-container--default .select2-selection--single .select2-selection__arrow,
        .user-edit-page .select2-container--white .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            right: 14px !important;
            width: 20px !important;
        }
        .user-edit-page .select2-container--white .select2-selection--multiple {
            height: auto;
            padding: 8px 12px;
        }
        .user-edit-page .select2-container--white .select2-selection--multiple .select2-selection__rendered {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 0;
            padding: 0;
        }
        .user-edit-page .select2-container--white .select2-selection--multiple .select2-search--inline {
            margin: 0;
        }
        .user-edit-page .select2-container--white .select2-selection--multiple .select2-search--inline .select2-search__field {
            height: 30px;
            margin: 0;
            line-height: 30px;
        }
        .user-edit-page .select2-container--white .select2-selection--multiple .select2-selection__choice {
            margin: 0;
            padding: 6px 12px;
            border-radius: 999px;
            border: 1px solid #cfe0ff;
            background: #edf4ff;
            color: #1b4880;
            font-size: 13px;
            font-weight: 600;
            position: relative;
        }
        .user-edit-page .select2-container--white .select2-selection--multiple .select2-selection__choice__remove {
            position: static;
            width: auto;
            height: auto;
            margin-right: 6px;
            border: 0;
            background: transparent;
            color: #7990ac;
            font-size: 13px;
            line-height: 1;
        }
        .user-edit-page .select2-container--white .select2-selection--multiple .select2-selection__choice__remove:hover {
            background: transparent;
            color: #d93048;
        }
        .user-edit-page .user-actions {
            display: flex;
            justify-content: center;
            gap: 14px;
            margin-top: 18px;
            padding-top: 8px;
        }
        .user-edit-page .user-action-primary,
        .user-edit-page .user-action-secondary {
            width: 176px;
            height: 48px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
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
                gap: 10px;
            }
            .user-edit-page .user-form-label {
                padding-top: 0;
            }
        }
        @media (max-width: 640px) {
            .user-edit-page .user-shell {
                padding: 14px;
            }
            .user-edit-page .user-card-header,
            .user-edit-page .user-body {
                padding-left: 16px;
                padding-right: 16px;
            }
            .user-edit-page .user-card .panel-title {
                font-size: 26px !important;
            }
            .user-edit-page .user-actions {
                flex-direction: column;
            }
            .user-edit-page .user-action-primary,
            .user-edit-page .user-action-secondary {
                width: 100%;
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

            document.addEventListener('DOMContentLoaded', function () {
                if (window.jQuery && $.fn.select2) {
                    $('#campus_id').select2({
                        width: '100%',
                        dropdownParent: $('.user-card'),
                        dropdownAutoWidth: false,
                    });

                    $('#roles').select2({
                        width: '100%',
                        dropdownParent: $('.user-card'),
                        dropdownAutoWidth: false,
                        closeOnSelect: false,
                    });
                }

                document.querySelectorAll('.toggle-visibility').forEach(function (button) {
                    button.addEventListener('click', function () {
                        toggleVisibility(this);
                    });
                });
            });
        })();
    </script>
@endpush
