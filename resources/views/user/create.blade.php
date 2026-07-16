@extends('layouts.theme')

@section('title', 'Create User')
@section('body_class', 'user-create-page')

@section('content')
    @php
        $selectedEmployeeId = old('employee_id');
        $selectedCampusId = old('campus_id');
        if ($selectedCampusId === null) {
            $selectedCampusId = optional($campuses->first())->id;
        }
        $selectedRoleId = old('role_id');
        if ($selectedRoleId === null) {
            $selectedRoleId = collect(old('roles', []))->filter()->first();
        }
        $employeeDirectory = $portalEmployees->mapWithKeys(function ($employee) {
            return [
                (string) $employee->id => [
                    'email' => (string) ($employee->email ?? ''),
                    'campus' => (string) ($employee->campus?->code ?: ($employee->campus?->name ?: '')),
                ],
            ];
        })->all();
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
                            <label for="employee_id" class="form-label-role">Full Name</label>
                        </div>
                        <div class="user-form-field">
                            <select
                                name="employee_id"
                                id="employee_id"
                                class="form-control select2 @error('employee_id') is-invalid @enderror"
                                style="width: 100%; background:white; border: 1px solid #d2dee9 !important;"
                                data-placeholder="- Select Full Name -"
                            >
                                <option value="">- Select Full Name -</option>
                                @foreach($portalEmployees as $employee)
                                    <option
                                        value="{{ $employee->id }}"
                                        data-email="{{ $employee->email }}"
                                        data-campus-label="{{ $employee->campus?->code ?: ($employee->campus?->name ?: '') }}"
                                        @selected((string) $selectedEmployeeId === (string) $employee->id)
                                    >
                                        {{ $employee->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                            @if($portalEmployees->isEmpty())
                                <div class="field-error" style="color: #64748b;">No portal-user employees are available.</div>
                            @endif
                        </div>
                    </div>
                    <div class="user-form-row">
                        <div class="user-form-label">
                            <label for="employee_email" class="form-label-role">Email Address</label>
                        </div>
                        <div class="user-form-field">
                            <div class="email-shell">
                                <input
                                    type="text"
                                    id="employee_email"
                                    class="form-control user-input"
                                    placeholder="Select employee first"
                                    value=""
                                    inputmode="email"
                                    autocapitalize="none"
                                    spellcheck="false"
                                    readonly
                                >
                            </div>
                        </div>
                    </div>
                    <div class="user-form-row">
                        <div class="user-form-label">
                            <label for="employee_campus" class="form-label-role">Employee Campus</label>
                        </div>
                        <div class="user-form-field">
                            <input
                                type="text"
                                id="employee_campus"
                                class="form-control user-input"
                                placeholder="Select employee first"
                                value=""
                                readonly
                            >
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
                                class="form-control select2 @error('campus_id') is-invalid @enderror"
                                style="width: 100%;"
                                data-placeholder="- Select Campus -"
                            >
                                <option value="">All Campuses</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected((string) $selectedCampusId === (string) $campus->id)>
                                        {{ $campus->code ? $campus->code . ' - ' . $campus->name : $campus->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('campus_id')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="user-form-row">
                        <div class="user-form-label">
                            <label for="password" class="form-label-role">Password</label>
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
                                <label for="role_id" class="form-label-role">Roles</label>
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
        :root {
            --dimension-user-create-1: 100%;
            --dimension-user-create-2: 100%;
            --dimension-user-create-3: 39px;
            --dimension-user-create-4: 50px;
            --space-user-create-1: 0 !important;
            --space-user-create-2: 0 auto;
            --space-user-create-3: 14px;
            --space-user-create-4: 16px;
            --space-user-create-5: 41px;
            --space-user-create-6: 6px;
            --space-user-create-7: 8px;
            --space-user-create-8: 96px;
            --color-user-create-1: #0098ff;
            --color-user-create-2: #15283a;
            --color-user-create-3: #bcd3e8;
            --color-user-create-4: #d2dee9;
            --color-user-create-5: #d93048;
            --color-user-create-6: #e8f1ff;
            --color-user-create-7: #ff3347;
            --color-user-create-8: #fff;
        }

        :root {
            --dimension-user-create-1: 100%;
            --dimension-user-create-2: 100%;
            --dimension-user-create-3: 39px;
            --dimension-user-create-4: 50px;
            --space-user-create-1: 0 !important;
            --space-user-create-2: 0 auto;
            --space-user-create-3: 14px;
            --space-user-create-4: 16px;
            --space-user-create-5: 41px;
            --space-user-create-6: 6px;
            --space-user-create-7: 8px;
            --space-user-create-8: 96px;
            --typo-user-create-font-weight-1: 600;
            --typo-user-create-font-size-2: 18px;
            --typo-user-create-font-weight-3: 700;
            --typo-user-create-font-size-4: 20px;
        }0___

        .user-create-page,
        .user-create-page .page-content {
            overflow-x: hidden;
            /* background: #f8fbff; */
        }
        .form-label-role{
            font-size: 0.875rem !important;
            margin-bottom: var(--space-user-create-6);
            margin-top: var(--space-user-create-6);
            
            color: #343a40 !important;
            text-transform: uppercase;
            font-weight: var(--typo-user-create-font-weight-1);

        }
        .user-create-page .page-content > .container-fluid {
            max-width: var(--dimension-user-create-2) !important;
            padding-left: var(--space-user-create-1);
            padding-right: var(--space-user-create-1);
            overflow: visible !important;
        }
        .user-create-page .user-shell {
            min-height: auto;
            padding: 0px 0px 0px 7px;
            overflow-x: hidden;
        }
        .user-create-page .user-card {
            margin: var(--space-user-create-2);
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
            font-size: 2rem !important;
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
            gap: var(--space-user-create-3);
            width: var(--dimension-user-create-1);
            max-width: 960px;
            margin: var(--space-user-create-2);
        }
        .user-create-page .user-form-row {
            display: grid;
            grid-template-columns: 230px minmax(0, 1fr);
            gap: var(--space-user-create-5);
            align-items: start;
            width: var(--dimension-user-create-1);
        }
        .user-create-page .user-form-label {
            padding-top: 11px;
            padding-left: var(--space-user-create-5);
        }
        /* .user-create-page .form-label {
            margin: 0;
            font-size: var(--typo-user-create-font-size-2);
            font-weight: var(--typo-user-create-font-weight-3);
            color: var(--color-user-create-2);
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
            width: var(--dimension-user-create-1);
            max-width: var(--dimension-user-create-1);
        }
        .user-create-page .user-input,
        .user-create-page .select2-container--white .select2-selection--single {
            min-height: var(--dimension-user-create-4);
            border: 1px solid var(--color-user-create-4) !important;
            border-radius: 5px;
            background: var(--color-user-create-8);
            font-size: var(--typo-user-create-font-size-4);
            color: #071526;
            box-shadow: none;
            transition: border-color 0.2s ease, background 0.2s ease;
            max-width: var(--dimension-user-create-1);
            box-sizing: border-box;
        }
        .user-create-page .user-input {
            width: var(--dimension-user-create-1);
            padding: 0 14px;
        }
        .user-create-page .user-input::placeholder {
            color: #8ca0b7;
        }
        .user-create-page .user-input:focus,
        .user-create-page .select2-container--white.select2-container--focus .select2-selection--single,
        .user-create-page .select2-container--white.select2-container--open .select2-selection--single {
            border-color: var(--color-user-create-3);
            background: var(--color-user-create-6);
            box-shadow: none;
        }
        .user-create-page .input-shell {
            position: relative;
        }
        .user-create-page .email-shell {
            display: flex;
            align-items: stretch;
            width: var(--dimension-user-create-1);
        }
        .user-create-page .email-local-input {
            border-right: 0 !important;
            border-radius: 5px 0 0 5px;
        }
        .user-create-page .email-domain {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: var(--dimension-user-create-4);
            padding: 0 16px;
            border: 1px solid var(--color-user-create-4);
            border-left: 0;
            border-radius: 0 5px 5px 0;
            background: #eef4fb;
            color: #4e6278;
            font-size: 1.0625rem;
            font-weight: var(--typo-user-create-font-weight-1);
            white-space: nowrap;
        }
        .user-create-page .email-shell:focus-within .email-domain {
            border-color: var(--color-user-create-3);
            background: var(--color-user-create-6);
        }
        .user-create-page .email-shell.has-error .email-domain {
            border-color: var(--color-user-create-5);
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
            padding-right: var(--space-user-create-8);
            background: var(--color-user-create-6);
        }
        .user-create-page .user-input-confirm {
            padding-right: 70px;
        }
        .user-create-page .inline-icon {
            width: 40px;
            height: var(--dimension-user-create-4);
            border: 0;
            border-radius: 0;
            outline: 0;
            background: #0ea5f4;
            color: var(--color-user-create-8);
            font-size: var(--typo-user-create-font-size-4);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s ease;
        }
        .user-create-page .inline-icon:hover,
        .user-create-page .inline-icon:focus {
            background: #0086d8;
            color: var(--color-user-create-8);
        }
        .user-create-page .input-shell-actions .inline-icon + .inline-icon {
            border-left: 1px solid rgba(255, 255, 255, 0.18);
        }
        .user-create-page .inline-icon .fa-eye,
        .user-create-page .inline-icon .fa-eye-slash,
        .user-create-page .inline-icon .fa-refresh {
            background: transparent !important;
            padding: var(--space-user-create-1);
            color: var(--color-user-create-8) !important;
        }
        .user-create-page .field-error {
            margin-top: var(--space-user-create-7);
            font-size: 0.8125rem;
            font-weight: var(--typo-user-create-font-weight-1);
            color: var(--color-user-create-5);
        }
        .user-create-page .select2-container {
            width: var(--dimension-user-create-2) !important;
            max-width: var(--dimension-user-create-2) !important;
            /* border: 1px solid var(--color-user-create-4) !important; */
        }
        .user-create-page .select2-container--default .select2-selection--single,
        .user-create-page .select2-container--white .select2-selection--single {
            display: flex !important;
            align-items: center !important;
            height: var(--dimension-user-create-3) !important;
            min-height: var(--dimension-user-create-3) !important;
            padding: 0 58px 0 14px !important;
            border: 1px solid var(--color-user-create-4) !important;
            border-radius: 5px !important;
            background: var(--color-user-create-8) !important;
            box-shadow: none !important;
        }
        .user-create-page .select2-container--default .select2-selection--single .select2-selection__rendered,
        .user-create-page .select2-container--white .select2-selection--single .select2-selection__rendered {
            width: var(--dimension-user-create-2) !important;
            color: var(--color-user-create-2);
            line-height: 1.2 !important;
            padding: 9px 0px 0px !important;
            margin: var(--space-user-create-1);
            font-size: var(--typo-user-create-font-size-4);
			border: none !important;
        }
        .user-create-page .select2-container--default .select2-selection--single .select2-selection__arrow,
        .user-create-page .select2-container--white .select2-selection--single .select2-selection__arrow {
            height: var(--dimension-user-create-2) !important;
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
            border-color: var(--color-user-create-4);
        }
        .user-create-page .account-activation {
            margin-top: 18px;
        }
        .user-create-page .account-activation-title {
            margin: 0 0 11px;
            color: var(--color-user-create-2);
            font-size: 1.1875rem;
            font-weight: var(--typo-user-create-font-weight-3);
            text-transform: uppercase;
        }
        .user-create-page .account-activation-note {
            display: flex;
            align-items: center;
            gap: var(--space-user-create-7);
            min-height: 48px;
            padding: 8px 16px;
            border: 1px solid #bfd8fb;
            border-radius: 8px;
            background: #eaf3ff;
            color: #0067d8;
            font-size: var(--typo-user-create-font-size-2);
            line-height: 1.35;
        }
        .user-create-page .account-activation-note i {
            color: #0d73da;
            font-size: 1.3125rem;
            flex: 0 0 auto;
        }
        .user-create-page .user-actions {
            display: flex;
            justify-content: flex-end;
            gap: var(--space-user-create-4);
            margin-top: 10px;
            padding-top: 0;
        }
        .user-create-page .user-action-primary,
        .user-create-page .user-action-secondary {
            width: auto;
            min-width: 112px;
            height: 51px;
            border-radius: 5px;
            font-size: var(--typo-user-create-font-size-4);
            font-weight: var(--typo-user-create-font-weight-3);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 22px;
        }
        .user-create-page .user-action-primary {
            border-color: var(--color-user-create-1) !important;
            color: var(--color-user-create-1) !important;
            background: var(--color-user-create-8) !important;
        }
        .user-create-page .user-action-primary:hover,
        .user-create-page .user-action-primary:focus {
            background: var(--color-user-create-1) !important;
            color: var(--color-user-create-8) !important;
        }
        .user-create-page .user-action-secondary {
            border-color: var(--color-user-create-7) !important;
            color: var(--color-user-create-7) !important;
            background: var(--color-user-create-8) !important;
        }
        .user-create-page .user-action-secondary:hover,
        .user-create-page .user-action-secondary:focus {
            background: var(--color-user-create-7) !important;
            color: var(--color-user-create-8) !important;
        }
        @media (max-width: 900px) {
            .user-create-page .user-body {
                padding: 26px 20px;
            }
            .user-create-page .user-form-row {
                grid-template-columns: 1fr;
                gap: var(--space-user-create-7);
            }
            .user-create-page .user-form-label {
                padding-top: 0;
                padding-left: 0;
            }
        }
        @media (max-width: 640px) {
            .user-create-page .user-shell {
                padding: var(--space-user-create-3);
            }
            .user-create-page .user-card-header,
            .user-create-page .user-body {
                padding-left: var(--space-user-create-4);
                padding-right: var(--space-user-create-4);
            }
            .user-create-page .user-card .panel-title {
                font-size: 1.625rem !important;
            }
            .user-create-page .account-activation-note {
                align-items: flex-start;
                font-size: 1rem;
            }
            .user-create-page .user-actions {
                flex-direction: column;
            }
            .user-create-page .user-action-primary,
            .user-create-page .user-action-secondary {
                width: var(--dimension-user-create-1);
            }
            .user-create-page .email-shell {
                flex-direction: column;
            }
            .user-create-page .email-local-input {
                border-right: 1px solid var(--color-user-create-4) !important;
                border-radius: 5px 5px 0 0;
            }
            .user-create-page .email-domain {
                border-left: 1px solid var(--color-user-create-4);
                border-top: 0;
                border-radius: 0 0 5px 5px;
                justify-content: flex-start;
                padding: 12px 14px;
            }
            .user-create-page .user-input-password {
                padding-right: var(--space-user-create-8);
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            const employeeDirectory = @json($employeeDirectory);

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

            function syncEmployeeSelection() {
                const employeeSelect = document.getElementById('employee_id');
                const emailInput = document.getElementById('employee_email');
                const campusInput = document.getElementById('employee_campus');

                if (!employeeSelect || !emailInput || !campusInput) return;

                const selectedOption = employeeSelect.options[employeeSelect.selectedIndex] || null;
                const selectedEmployeeId = String(employeeSelect.value || '');
                const selectedEmployee = employeeDirectory[selectedEmployeeId] || {};
                const selectedEmail = selectedOption ? (selectedOption.getAttribute('data-email') || '') : '';
                const selectedCampus = selectedOption ? (selectedOption.getAttribute('data-campus-label') || '') : '';

                emailInput.value = selectedEmail || selectedEmployee.email || '';
                campusInput.value = selectedCampus || selectedEmployee.campus || '';
            }

            document.addEventListener('DOMContentLoaded', function () {
                if (window.jQuery && $.fn.select2) {
                    const $employeeSelect = $('#employee_id');

                    $employeeSelect.select2({
                        width: '100%',
                        dropdownParent: $('.user-card'),
                        dropdownAutoWidth: false,
                        minimumResultsForSearch: 0,
                    });

                    $employeeSelect.on('change select2:select select2:clear', syncEmployeeSelection);

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

                const passwordInput = document.getElementById('password');
                const confirmationInput = document.getElementById('password_confirmation');

                if (passwordInput && confirmationInput && !passwordInput.value && !confirmationInput.value) {
                    fillPasswordFields();
                }

                document.querySelectorAll('.toggle-visibility').forEach(function (button) {
                    button.addEventListener('click', function () {
                        toggleVisibility(this);
                    });
                });

                const employeeSelect = document.getElementById('employee_id');
                if (employeeSelect) {
                    employeeSelect.addEventListener('change', syncEmployeeSelection);
                }
                syncEmployeeSelection();
                window.setTimeout(syncEmployeeSelection, 0);

                const generatePasswordButton = document.querySelector('.generate-password');
                if (generatePasswordButton) {
                    generatePasswordButton.addEventListener('click', function () {
                        fillPasswordFields();
                    });
                }
            });
        })();
    </script>
@endpush
