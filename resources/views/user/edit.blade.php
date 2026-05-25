@extends('layouts.theme')

@section('title', 'Edit User')

@section('content')
    @php
        $selectedRoleIds = collect(old('roles', $user->roles->modelKeys()))->map(fn ($id) => (int) $id);
        $selectedPermissionIds = collect(old('permissions', $user->permissions->modelKeys()))->map(fn ($id) => (int) $id);
        $selectedCampusId = old('campus_id', $user->campus_id);
    @endphp

    <div class="user-shell">
        <div class="box-typical box-typical-dashboard panel panel-default user-card">
            <header class="box-typical-header panel-heading d-flex justify-content-between">
                <div>
                    <h3 class="panel-title mb-0 form-label">Edit User</h3>
                </div>
            </header>
            <div class="box-typical-body panel-body user-body">
                <form method="POST" action="{{ route('users.update', $user) }}">
                    @csrf
                    @method('PUT')

                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label class="form-label required">Full Name</label>
                        </div>
                        <div class="form-group col-md-10">
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Alex Morgan" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label class="form-label required">Email Address</label>
                        </div>
                        <div class="form-group col-md-10">
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="alex@example.com" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label class="form-label">Password</label>
                        </div>
                        <div class="form-group col-md-10">
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="********">
                                <span class="input-group-btn">
                                    <button class="btn btn-default toggle-visibility" type="button" data-target="#password" aria-label="Show password">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </span>
                            </div>
                            @error('password')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label class="form-label">Confirm Password</label>
                        </div>
                        <div class="form-group col-md-10">
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" placeholder="********">
                                <span class="input-group-btn">
                                    <button class="btn btn-default toggle-visibility" type="button" data-target="#password_confirmation" aria-label="Show password confirmation">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </span>
                            </div>
                            @error('password_confirmation')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label class="form-label">Campus</label>
                        </div>
                        <div class="form-group col-md-10">
                            <select name="campus_id" class="form-control select2 select2-white select2-user @error('campus_id') is-invalid @enderror" style="width: 100%;" data-placeholder="Select campus">
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

                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label class="form-label">Roles</label>
                        </div>
                        <div class="form-group col-md-10">
                            <select name="roles[]" class="form-control select2 select2-white select2-user select2-roles @error('roles') is-invalid @enderror" multiple style="width: 100%;" data-placeholder="Select roles">
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

                    <div class="text-right mt-3 mr-5 mb-4">
                        <button type="submit" class="btn btn-primary-outline">Save Changes</button>
                        <a href="{{ route('users.index') }}" class="btn btn-danger-outline mr-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .user-shell {
            min-height: 100vh;
            padding: 20px;
            background: linear-gradient(160deg, #f6f8fc 0%, #eef3fb 100%);
        }
        .user-card {
            margin: 0 auto;
            border-radius: 14px;
            box-shadow: 0 18px 40px rgba(25, 45, 85, 0.12);
        }
        .user-body {
            padding: 24px 24px 10px;
        }
        .required::after {
            content: '*';
            color: #e74c3c;
            margin-left: 4px;
        }
        .form-section {
            background: #fff;
            border: 1px solid #e6edf5;
            border-radius: 12px;
            padding: 18px 18px 6px;
            margin-bottom: 18px;
        }
        .section-title {
            font-weight: 600;
            color: #1f2d3d;
            margin-bottom: 12px;
        }
        .input-group .btn {
            border-left: 0;
        }
        .form-control:focus {
            border-color: #2b78ff;
            box-shadow: 0 0 0 3px rgba(43, 120, 255, 0.12);
        }
        .select2-container {
            width: 100% !important;
        }
        .select2-container--white .select2-selection--single,
        .select2-container--white .select2-selection--multiple {
            min-height: 38px;
            border: 1px solid #dbe5f1;
            border-radius: 6px;
            background: #fff;
            box-sizing: border-box;
        }
        .select2-container--white .select2-selection--single {
            height: 38px;
            display: flex;
            align-items: center;
            width: 100%;
        }
        .select2-container--white .select2-selection--multiple {
            min-height: 38px;
            display: flex;
            align-items: center;
            padding: 6px 10px;
        }
        .select2-container--white .select2-selection--multiple .select2-selection__rendered {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin: 0;
            padding: 0;
            align-items: center;
        }
        .select2-container--white .select2-selection--multiple .select2-selection__choice {
            border-radius: 12px;
            border: 1px solid #2b78ff;
            background: #e9f2ff;
            color: #1f2d3d;
            padding: 0 10px 6px 10px;
            margin: -8px 6px 0 0;
            font-size: 13px;
            position: relative;
        }
        .select2-container--white .select2-selection--multiple .select2-selection__choice__remove {
            position: absolute;
            top: -6px;
            right: -6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #e74c3c;
            color: #fff;
            font-size: 11px;
            line-height: 1;
            border: 2px solid #fff;
        }
        .select2-container--white .select2-selection--multiple .select2-selection__choice__remove:hover {
            background: #c0392b;
            color: #fff;
        }
        .select2-container--white .select2-selection--single .select2-selection__arrow {
            height: 28px;
            right: 8px;
            width: 18px;
        }
        .select2-container--white .select2-dropdown {
            border: 1px solid #dbe5f1;
            border-radius: 8px;
            box-shadow: 0 12px 24px rgba(25, 45, 85, 0.12);
            overflow: hidden;
            margin-top: 4px;
            z-index: 1060;
        }
        .select2-container--white .select2-search--dropdown {
            padding: 10px 12px;
            border-bottom: 1px solid #eef2f7;
        }
        .select2-container--white .select2-search--dropdown .select2-search__field {
            border: 1px solid #dbe5f1;
            border-radius: 6px;
            padding: 6px 10px;
            outline: none;
            width: 100%;
            box-sizing: border-box;
        }
        .select2-results__option .role-check {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .select2-results__option .role-check .box {
            width: 14px;
            height: 14px;
            border: 1px solid #9fb3c8;
            border-radius: 3px;
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #fff;
        }
        .select2-results__option[aria-selected=true] .role-check .box {
            background: #2b78ff;
            border-color: #2b78ff;
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            function toggleVisibility(button) {
                const input = document.querySelector(button.getAttribute('data-target'));
                if (!input) return;
                input.type = input.type === 'password' ? 'text' : 'password';
            }

            function syncAdminPermissions() {
                const roleSelect = document.querySelector('.select2-roles');
                if (!roleSelect) return;

                const selectedOptions = Array.from(roleSelect.selectedOptions || []);
                const adminSelected = selectedOptions.some(function (option) {
                    return ['admin', 'owner'].includes(option.dataset.slug || '');
                });

                document.querySelectorAll('input[name="permissions[]"]').forEach(function (checkbox) {
                    if (adminSelected) {
                        checkbox.checked = true;
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                if (window.jQuery && $.fn.select2) {
                    $('.select2-user').select2({
                        width: '100%',
                        dropdownParent: $('.user-card'),
                        dropdownAutoWidth: true,
                    });
                    $('.select2-roles').select2({
                        width: '100%',
                        dropdownParent: $('.user-card'),
                        dropdownAutoWidth: true,
                        closeOnSelect: false,
                        templateResult: function (state) {
                            if (!state.id) return state.text;
                            return $('<span class="role-check"><span class="box">&#10003;</span>' + state.text + '</span>');
                        },
                        templateSelection: function (state) {
                            return state.text;
                        },
                    });
                }

                syncAdminPermissions();
                document.querySelector('.select2-roles')?.addEventListener('change', syncAdminPermissions);
                document.querySelectorAll('.toggle-visibility').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        toggleVisibility(this);
                    });
                });
            });
        })();
    </script>
@endpush
