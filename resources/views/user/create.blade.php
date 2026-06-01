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
                <div>
                    <p class="user-kicker mb-1">User Management</p>
                    <h3 class="panel-title form-label mb-0">Create User</h3>
                </div>
            </header>

            <div class="box-typical-body panel-body user-body">
                <form method="POST" action="{{ route('users.store') }}" class="user-form">
                    @csrf
<<<<<<< HEAD
=======
								<input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Alex Morgan" value="{{ old('name') }}" required>
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
							<input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="alex@example.com" value="{{ old('email') }}" required>
							@error('email')
								<div class="field-error">{{ $message }}</div>
							@enderror
							</div>
						</div>
						<div class="form-row">
								<div class="form-group col-md-2">
								<label class="form-label">
									<span>Password</span>
									<!-- <small class="text-muted">(leave blank to keep current)</small> -->
								</label>
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
					<!-- </div> -->
					<!-- <div class="form-section"> -->
						<!-- <div class="section-title form-label">Access &amp; Roles</div> -->
						<div class="form-row" >
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
								<select name="campus_id" class="form-control select2  select2-user @error('campus_id') is-invalid @enderror" style="width: 100%;" >
									<option value="">- Select Campus -</option>
								@foreach($campuses as $campus)
									<option value="{{ $campus->id }}" @selected(old('campus_id') == $campus->id)>{{ $campus->name }}</option>
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
									<option value="{{ $role->id }}" @selected(collect(old('roles', []))->contains($role->id))>{{ $role->name }}</option>
								@endforeach
								</select>
							</div>
							<!-- <small class="text-muted">Hold Ctrl/Cmd to select multiple roles.</small> -->
							<!-- @error('roles')
								<div class="field-error">{{ $message }}</div>
							@enderror
							@error('roles.*')
								<div class="field-error">{{ $message }}</div>
							@enderror -->
						</div>
					</div>
					<!-- <div class="form-section">
						<div class="section-title form-label">Access &amp; Roles</div>
						<div class="form-row" >
							<div class="form-group col-md-4">
								<label class="form-label">Confirm Password</label>
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
						<div class="form-group col-md-4">
							<label class="form-label">Campus</label>
								<select name="campus_id" class="form-control select2 select2-white select2-user @error('campus_id') is-invalid @enderror" style="width: 100%;" data-placeholder="Select campus">
									<option value="">Select campus</option>
								@foreach($campuses as $campus)
									<option value="{{ $campus->id }}" @selected(old('campus_id') == $campus->id)>{{ $campus->name }}</option>
								@endforeach
							</select>
							@error('campus_id')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-4">
							<label class="form-label">Roles</label>
								<select name="roles[]" class="form-control select2 select2-white select2-user select2-roles @error('roles') is-invalid @enderror" multiple style="width: 100%;" data-placeholder="Select roles">
								@foreach($roles as $role)
									<option value="{{ $role->id }}" data-slug="{{ $role->slug }}" @selected(collect(old('roles', []))->contains($role->id))>{{ $role->name }}</option>
								@endforeach
							</select>
							<small class="text-muted">Hold Ctrl/Cmd to select multiple roles.</small>
							@error('roles')
								<div class="field-error">{{ $message }}</div>
							@enderror
							@error('roles.*')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>
					</div>

					</div>
					@php($selectedPermissionIds = collect(old('permissions', []))->map(fn ($id) => (int) $id))
					@include('user.partials.direct-permissions', ['permissionGroups' => $permissionGroups, 'selectedPermissionIds' => $selectedPermissionIds])

					</div> -->

					<div class="form-section">
						<div class="section-title form-label">Account Activation</div>
						<div class="alert alert-info mb-0" style="background:#eef5ff;border:1px solid #cfe0f5;color:#0a6fd1;border-radius:8px;padding:10px 12px;">
							<i class="fa fa-info-circle"></i>
							A setup link will be emailed to the new user. The link is valid for <strong>1 hour</strong> — the user clicks it to set their own password and activate the account.
						</div>
					</div>
>>>>>>> d5886aa41c564aa6d9c178e1302e7daf4b5b6adc

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
                                placeholder="alex@example.com"
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
                            <label for="password" class="form-label required">Password</label>
                        </div>
                        <div class="user-form-field">
                            <div class="input-shell">
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    class="form-control user-input user-input-password @error('password') is-invalid @enderror"
                                    placeholder="Auto-generated password"
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
                            <label for="password_confirmation" class="form-label required">Confirm Password</label>
                        </div>
                        <div class="user-form-field">
                            <div class="input-shell">
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    id="password_confirmation"
                                    class="form-control user-input user-input-confirm @error('password_confirmation') is-invalid @enderror"
                                    placeholder="Repeat password"
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
                                class="form-control select2 select2-white @error('campus_id') is-invalid @enderror"
                                style="width: 100%;"
                                data-placeholder="Select campus"
                            >
                                <option value="">All Campuses</option>
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
                            <label for="role_id" class="form-label">Role</label>
                        </div>
                        <div class="user-form-field">
                            <select
                                name="role_id"
                                id="role_id"
                                class="form-control select2 select2-white @error('role_id') is-invalid @enderror"
                                style="width: 100%;"
                                data-placeholder="Select role"
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

                    <div class="user-actions">
                        <button type="submit" class="btn btn-inline btn-primary-outline user-action-primary">Create User</button>
                        <a href="{{ route('users.index') }}" class="btn btn-inline btn-danger-outline user-action-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
	<style>
		.form-group {
    margin-bottom: 1px !important;
}
		.user-shell {
			min-height: 100vh;
			padding: 20px;
			background: linear-gradient(160deg, #f6f8fc 0%, #eef3fb 100%);
		}
		.user-card {
			/* max-width: 1200px; */
			margin: 0 auto;
			border-radius: 14px;
			box-shadow: 0 18px 40px rgba(25, 45, 85, 0.12);
		}
		.user-body {
			padding: 8px 24px 8px;
		}
		.required::after { content: '*'; color: #e74c3c; margin-left: 4px; }
		.form-section {
			background: #fff;
			/* border: 1px solid #e6edf5; */
			border-radius: 12px;
			padding: 12px 18px 6px;
    margin-bottom: 10px;
		}
		.section-title {
			font-weight: 600;
			color: #1f2d3d;
			margin-bottom: 5px;
		}
		.section-title .btn {
			height: 32px;
			width: 32px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			padding: 0;
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

.select2-results > .select2-results__options {
    max-height: 220px !important;
    overflow-y: auto !important;
}

/* Smooth scrollbar */

.select2-results__options::-webkit-scrollbar {
    width: 8px;
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
		.select2-container--white .select2-results__option--highlighted[aria-selected],
		.select2-container--white .select2-results__option--highlighted,
		.select2-container--white .select2-results__option:hover {
			/* background: #e8f4ff !important; */
			color: #00a8ff !important;
		}

		.select2-container--white .select2-selection--multiple {
			height: 36px;
			display: flex;
			align-items: center;
			/* padding: 6px 10px; */
			margin: 2px;
		}
		.select2-container--white .select2-selection--multiple .select2-selection__rendered {
			min-height: 44px;
			align-items: center;
		}
		.select2-container--white .select2-selection--multiple .select2-search--inline .select2-search__field {
			height: 32px;
			margin-top: 0;
			line-height: 32px;
		}
		.select2-container--white .select2-selection--multiple .select2-selection__rendered {
			display: flex;
			flex-wrap: wrap;
			gap: 4px;
			margin: 0;
			padding: 0;
			align-items: baseline;
		}
		.select2-container--white .select2-selection--multiple .select2-selection__choice {
			border-radius: 12px;
			border: 1px solid #2b78ff;
			background: #e9f2ff;
			color: #1f2d3d;
			    padding: 0px 10px 6px 10px;
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
		.select2-container--white .select2-dropdown--below {
			margin-top: 14px;
		}
		.select2-container--white.select2-container--open .select2-selection--single {
			border-bottom-left-radius: 0;
			border-bottom-right-radius: 0;
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
		.select2-container--white .role-dropdown-search {
			display: block;
			padding: 10px 12px;
			border-bottom: 1px solid #eef2f7;
			background: #fff;
		}
		.select2-container--white .role-dropdown-search-input {
			width: 100%;
			border: 1px solid #dbe5f1;
			border-radius: 6px;
			padding: 6px 10px;
			outline: none;
			box-sizing: border-box;
		}
		.select2-container--white .role-dropdown-search-input:focus {
			border-color: #aaa;
			/* box-shadow: 0 0 0 3px rgba(43, 120, 255, 0.12); */
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
		.select2-container--white.select2-container--focus .select2-selection--single,
		.select2-container--white.select2-container--open .select2-selection--single,
		.select2-container--white.select2-container--focus .select2-selection--multiple {
			border-color: #aaa;
			/* box-shadow: 0 0 0 3px rgba(43, 120, 255, 0.12); */
		}
	
	</style>
    <style>
        .user-create-page {
            overflow-x: hidden;
        }
        .user-create-page .page-content {
            overflow-x: hidden;
        }
        .user-create-page .page-content > .container-fluid {
            max-width: 100% !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            overflow: visible !important;
        }
        .user-create-page .user-shell {
            min-height: 100vh;
            padding: 24px;
            background:
                radial-gradient(circle at top left, rgba(45, 120, 255, 0.09), transparent 26%),
                linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
            overflow-x: hidden;
        }
        .user-create-page .user-card {
            width: min(100%, 1080px);
            margin: 0 auto;
            border: 1px solid #d9e4f0;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 22px 50px rgba(20, 53, 93, 0.12);
            background: #ffffff;
        }
        .user-create-page .user-card-header {
            padding: 28px 34px 22px;
            border-bottom: 1px solid #e8eef6;
            background: linear-gradient(180deg, rgba(248, 251, 255, 0.96), rgba(255, 255, 255, 0.98));
        }
        .user-create-page .user-kicker {
            font-size: 12px;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #7f93ac;
            font-weight: 700;
        }
        .user-create-page .user-card .panel-title {
            font-size: 32px !important;
            line-height: 1.05;
            font-weight: 700 !important;
            color: #16324f;
        }
        .user-create-page .user-body {
            padding: 34px;
        }
        .user-create-page .user-form {
            display: flex;
            flex-direction: column;
            gap: 22px;
            width: 100%;
            max-width: 960px;
            margin: 0 auto;
        }
        .user-create-page .user-form-row {
            display: grid;
            grid-template-columns: 200px minmax(0, 1fr);
            gap: 24px;
            align-items: start;
            width: 100%;
        }
        .user-create-page .user-form-label {
            padding-top: 16px;
        }
        .user-create-page .form-label {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            color: #17324b;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }
        .user-create-page .required::after {
            content: '*';
            color: #f24f61;
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
        .user-create-page .user-input {
            width: 100%;
            padding: 0 18px;
        }
        .user-create-page .user-input::placeholder {
            color: #8ca0b7;
        }
        .user-create-page .user-input:focus,
        .user-create-page .select2-container--white.select2-container--focus .select2-selection--single,
        .user-create-page .select2-container--white.select2-container--open .select2-selection--single {
            border-color: #2b78ff;
            box-shadow: 0 0 0 4px rgba(43, 120, 255, 0.13);
        }
        .user-create-page .input-shell {
            position: relative;
        }
        .user-create-page .input-shell-actions {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            z-index: 3;
        }
        .user-create-page .user-input-password {
            padding-right: 132px;
        }
        .user-create-page .user-input-confirm {
            padding-right: 54px;
        }
        .user-create-page .inline-chip,
        .user-create-page .inline-icon {
            border: 0;
            outline: 0;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }
        .user-create-page .inline-chip {
            height: 34px;
            padding: 0 14px;
            border-radius: 999px;
            background: #e7f0ff;
            color: #1a66e0;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .user-create-page .inline-chip:hover,
        .user-create-page .inline-chip:focus {
            background: #d8e7ff;
            transform: translateY(-1px);
        }
        .user-create-page .inline-icon {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #f2f7fc;
            color: #6d849f;
            font-size: 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .user-create-page .inline-icon:hover,
        .user-create-page .inline-icon:focus {
            background: #e5f0ff;
            color: #236be7;
            transform: translateY(-1px);
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
        }
        .user-create-page .select2-container--default .select2-selection--single,
        .user-create-page .select2-container--white .select2-selection--single {
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
        .user-create-page .select2-container--default .select2-selection--single .select2-selection__rendered,
        .user-create-page .select2-container--white .select2-selection--single .select2-selection__rendered {
            width: 100% !important;
            color: #16324f;
            line-height: 1.2 !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .user-create-page .select2-container--default .select2-selection--single .select2-selection__arrow,
        .user-create-page .select2-container--white .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            right: 14px !important;
            width: 20px !important;
        }
        .user-create-page .user-actions {
            display: flex;
            justify-content: center;
            gap: 14px;
            margin-top: 18px;
            padding-top: 8px;
        }
        .user-create-page .user-action-primary,
        .user-create-page .user-action-secondary {
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
            .user-create-page .user-body {
                padding: 26px 20px;
            }
            .user-create-page .user-form-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            .user-create-page .user-form-label {
                padding-top: 0;
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
            .user-create-page .user-actions {
                flex-direction: column;
            }
            .user-create-page .user-action-primary,
            .user-create-page .user-action-secondary {
                width: 100%;
            }
            .user-create-page .user-input-password {
                padding-right: 122px;
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

				if (window.jQuery && $.fn.select2) {
					$('.select2-user').not('.select2-roles').select2({
						width: '100%',
						dropdownParent: $('.user-card'),
						dropdownAutoWidth: true,
						minimumResultsForSearch: 0,
					});
					const $roleSelect = $('.select2-roles');

					$roleSelect.select2({
						width: '100%',
						dropdownParent: $('.user-card'),
						dropdownAutoWidth: true,
						closeOnSelect: false,
						templateResult: function (state) {
							if (!state.id) return state.text;
							return $('<span class="role-check"><span class="box">✓</span>' + state.text + '</span>');
						},
						templateSelection: function (state) {
							return state.text;
						},
					});

					$roleSelect.on('select2:open', function () {
						const $openContainer = $('.select2-container--open').last();
						const $dropdown = $openContainer.find('.select2-dropdown');
						if (!$dropdown.length || $dropdown.find('.role-dropdown-search').length) return;

						const $inlineSearch = $(this).next('.select2-container').find('.select2-search--inline .select2-search__field');
						const $searchWrap = $('<span class="role-dropdown-search"></span>');
						const $searchInput = $('<input class="role-dropdown-search-input" type="search" autocomplete="off" placeholder="Search roles">');

						$searchWrap.append($searchInput);
						$dropdown.prepend($searchWrap);

						$searchInput.on('keydown keyup input mousedown', function (event) {
							event.stopPropagation();
						});

						$searchInput.on('input', function () {
							$inlineSearch.val(this.value).trigger('input').trigger('keyup');
						});

						setTimeout(function () {
							$searchInput.trigger('focus');
						}, 0);
					});
				}
				syncAdminPermissions();
				document.querySelector('.select2-roles')?.addEventListener('change', syncAdminPermissions);
				document.querySelectorAll('.toggle-visibility').forEach(function (btn) {
					btn.addEventListener('click', function () {
						toggleVisibility(this);
					});
				});
				const gen = document.getElementById('generate-password');
				if (gen) gen.addEventListener('click', generatePassword);
			});
		})();
	</script>
                document.querySelector('.generate-password')?.addEventListener('click', function () {
                    fillPasswordFields();
                });
            });
        })();
    </script>
@endpush
