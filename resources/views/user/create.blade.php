@extends('layouts.theme')

@section('title', 'Create User')
@section('body_class', 'user-create-page')

@section('content')
    @php
        $selectedRoleId = old('role_id');
        if ($selectedRoleId === null) {
            $selectedRoleId = collect(old('roles', []))->filter()->first();
        }
    @endphp

    <div class="user-shell">
        <div class="box-typical box-typical-dashboard panel panel-default user-card">
            <header class="box-typical-header panel-heading user-card-header">
                <h3 class="panel-title lead-title mb-0">Create User</h3>
            </header>

            <div class="box-typical-body panel-body user-body">
                <form method="POST" action="{{ route('users.store') }}" class="user-form">
                    @csrf

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
                                value="{{ old('name') }}"
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
                                placeholder="admin@example.com"
                                value="{{ old('email') }}"
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
                                    class="form-control user-input user-input-password @error('password') is-invalid @enderror"
                                    placeholder="********"
                                    autocomplete="new-password"
                                    required
                                >
                                <div class="input-shell-actions">
                                    <button class="inline-chip generate-password" type="button" aria-label="Generate password">
                                        <i class="fa fa-refresh" aria-hidden="true"></i>
                                        Auto
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
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
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
                                    required
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
                                class="form-control select2 @error('campus_id') is-invalid @enderror"
                                style="width: 100%; background:white; border: 1px solid #d2dee9 !important;"
                                data-placeholder="- Select Campus -"
                            >
                                <option value="">- Select Campus -</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected((string) old('campus_id') === (string) $campus->id)>{{ $campus->name }}</option>
                                @endforeach
                            </select>
                            @error('campus_id')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        </div>
                        <div class="user-form-row">
                            <div class="user-form-label">
                                <label for="role_id" class="form-label">Roles</label>
                            </div>
                            <div class="user-form-field">
                                <select
                                    name="role_id"
                                    id="role_id"
                                    class="form-control select2  @error('role_id') is-invalid @enderror"
                                    style="width: 100%;"
                                    data-placeholder="- Select Role -"
                                >
                                    <option value="">Select role</option>
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
                    <div class="text-right" style="padding-right: 0px !important;">
                        <button type="submit" class="btn btn-inline btn-primary-outline ">Create User</button>
                        <a href="{{ route('users.index') }}" class="btn btn-inline btn-danger-outline ">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .user-create-page,
        .user-create-page .page-content {
            overflow-x: hidden;
            /* background: #f8fbff; */
        }
        .user-create-page .page-content > .container-fluid {
            max-width: 100% !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            overflow: visible !important;
        }
        .user-create-page .user-shell {
            min-height: auto;
            padding: 0px 0px 0px 7px;
            overflow-x: hidden;
        }
        .user-create-page .user-card {
            margin: 0 auto;
    border: 1px solid #d9e4f0;
    border-radius: 0px;
    overflow: hidden;
        }
        .user-create-page .user-card-header {
            padding: 22px 34px 18px;
            border-bottom: 1px solid #e8eef6;
            background: linear-gradient(180deg, rgba(248, 251, 255, 0.96), rgba(255, 255, 255, 0.98));
        }
        .user-create-page .user-card .panel-title {
            font-size: 32px !important;
            line-height: 1.15;
            font-weight: 400 !important;
            color: #1d2f40;
            letter-spacing: 0;
        }
        .user-create-page .user-body {
            padding: 26px 34px 24px;
        }
        .user-create-page .user-form {
            display: flex;
            flex-direction: column;
            gap: 14px;
            width: 100%;
            max-width: 960px;
            margin: 0 auto;
        }
        .user-create-page .user-form-row {
            display: grid;
            grid-template-columns: 230px minmax(0, 1fr);
            gap: 41px;
            align-items: start;
            width: 100%;
        }
        .user-create-page .user-form-label {
            padding-top: 11px;
            padding-left: 41px;
        }
        /* .user-create-page .form-label {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #15283a;
            letter-spacing: 0;
            text-transform: uppercase;
        } */
        .user-create-page .required::after {
            content: '*';
            color: #f00;
            margin-left: 4px;
        }
        .user-create-page .user-form-field,
        .user-create-page .input-shell {
            min-width: 0;
            width: 100%;
            max-width: 100%;
        }
        .user-create-page .user-input,
        .user-create-page .select2-container--white .select2-selection--single {
            min-height: 50px;
            border: 1px solid #d2dee9 !important;
            border-radius: 5px;
            background: #fff;
            font-size: 20px;
            color: #071526;
            box-shadow: none;
            transition: border-color 0.2s ease, background 0.2s ease;
            max-width: 100%;
            box-sizing: border-box;
        }
        .user-create-page .user-input {
            width: 100%;
            padding: 0 14px;
        }
        .user-create-page .user-input::placeholder {
            color: #8ca0b7;
        }
        .user-create-page .user-input:focus,
        .user-create-page .select2-container--white.select2-container--focus .select2-selection--single,
        .user-create-page .select2-container--white.select2-container--open .select2-selection--single {
            border-color: #bcd3e8;
            background: #e8f1ff;
            box-shadow: none;
        }
        .user-create-page .input-shell {
            position: relative;
        }
        .user-create-page .input-shell-actions {
            position: absolute;
            top: 0;
            right: 0;
            height: 37px;
            display: inline-flex;
            align-items: center;
            gap: 0;
            z-index: 3;
        }
        .user-create-page .user-input-password {
            padding-right: 70px;
            background: #e8f1ff;
        }
        .user-create-page .user-input-confirm {
            padding-right: 70px;
        }
        .user-create-page .inline-chip {
            display: none;
        }
        .user-create-page .inline-icon {
            width: 40px;
            height: 50px;
            border: 0;
            border-radius: 0;
            outline: 0;
            background: #0ea5f4;
            color: #fff;
            font-size: 20px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s ease;
        }
        .user-create-page .inline-icon:hover,
        .user-create-page .inline-icon:focus {
            background: #0086d8;
            color: #fff;
        }
        .user-create-page .inline-icon .fa-eye,
        .user-create-page .inline-icon .fa-eye-slash {
            background: transparent !important;
            padding: 0 !important;
            color: #fff !important;
        }
        .user-create-page .field-error {
            margin-top: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #d93048;
        }
        .user-create-page .select2-container {
            width: 100% !important;
            max-width: 100% !important;
            /* border: 1px solid #d2dee9 !important; */
        }
        .user-create-page .select2-container--default .select2-selection--single,
        .user-create-page .select2-container--white .select2-selection--single {
            display: flex !important;
            align-items: center !important;
            height: 39px !important;
            min-height: 39px !important;
            padding: 0 58px 0 14px !important;
            border: 1px solid #d2dee9 !important;
            border-radius: 5px !important;
            background: #fff !important;
            box-shadow: none !important;
        }
        .user-create-page .select2-container--default .select2-selection--single .select2-selection__rendered,
        .user-create-page .select2-container--white .select2-selection--single .select2-selection__rendered {
            width: 100% !important;
            color: #15283a;
            line-height: 1.2 !important;
            padding: 9px 0px 0px !important;
            margin: 0 !important;
            font-size: 20px;
			border: none !important;
        }
        .user-create-page .select2-container--default .select2-selection--single .select2-selection__arrow,
        .user-create-page .select2-container--white .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            top: 0 !important;
            right: 0 !important;
            width: 31px !important;
            border-radius: 0 5px 5px 0;
            background: #d8e3eb !important;
        }
        .user-create-page .select2-container--default .select2-selection--single .select2-selection__arrow b,
        .user-create-page .select2-container--white .select2-selection--single .select2-selection__arrow b {
            border-color: #778796 transparent transparent transparent !important;
        }
        .user-create-page .select2-dropdown {
            border-color: #d2dee9;
        }
        .user-create-page .account-activation {
            margin-top: 18px;
        }
        .user-create-page .account-activation-title {
            margin: 0 0 11px;
            color: #15283a;
            font-size: 19px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .user-create-page .account-activation-note {
            display: flex;
            align-items: center;
            gap: 8px;
            min-height: 48px;
            padding: 8px 16px;
            border: 1px solid #bfd8fb;
            border-radius: 8px;
            background: #eaf3ff;
            color: #0067d8;
            font-size: 18px;
            line-height: 1.35;
        }
        .user-create-page .account-activation-note i {
            color: #0d73da;
            font-size: 21px;
            flex: 0 0 auto;
        }
        .user-create-page .user-actions {
            display: flex;
            justify-content: flex-end;
            gap: 16px;
            margin-top: 10px;
            padding-top: 0;
        }
        .user-create-page .user-action-primary,
        .user-create-page .user-action-secondary {
            width: auto;
            min-width: 112px;
            height: 51px;
            border-radius: 5px;
            font-size: 20px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 22px;
        }
        .user-create-page .user-action-primary {
            border-color: #0098ff !important;
            color: #0098ff !important;
            background: #fff !important;
        }
        .user-create-page .user-action-primary:hover,
        .user-create-page .user-action-primary:focus {
            background: #0098ff !important;
            color: #fff !important;
        }
        .user-create-page .user-action-secondary {
            border-color: #ff3347 !important;
            color: #ff3347 !important;
            background: #fff !important;
        }
        .user-create-page .user-action-secondary:hover,
        .user-create-page .user-action-secondary:focus {
            background: #ff3347 !important;
            color: #fff !important;
        }
        @media (max-width: 900px) {
            .user-create-page .user-body {
                padding: 26px 20px;
            }
            .user-create-page .user-form-row {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            .user-create-page .user-form-label {
                padding-top: 0;
                padding-left: 0;
            }
        }
        @media (max-width: 640px) {
            .user-create-page .user-shell {
                padding: 14px;
            }
            .user-create-page .user-card-header,
            .user-create-page .user-body {
                padding-left: 16px;
                padding-right: 16px;
            }
            .user-create-page .user-card .panel-title {
                font-size: 26px !important;
            }
            .user-create-page .account-activation-note {
                align-items: flex-start;
                font-size: 16px;
            }
            .user-create-page .user-actions {
                flex-direction: column;
            }
            .user-create-page .user-action-primary,
            .user-create-page .user-action-secondary {
                width: 100%;
            }
            .user-create-page .user-input-password {
                padding-right: 70px;
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
                        minimumResultsForSearch: 0,
                    });

                    $('#role_id').select2({
                        width: '100%',
                        dropdownParent: $('.user-card'),
                        dropdownAutoWidth: false,
                        allowClear: true,
                        minimumResultsForSearch: 0,
                    });
                }

                if (!document.getElementById('password')?.value && !document.getElementById('password_confirmation')?.value) {
                    fillPasswordFields();
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
