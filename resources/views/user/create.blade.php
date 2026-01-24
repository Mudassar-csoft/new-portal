@extends('layouts.theme')

@section('title', 'Create User')

@section('content')
	<div class="user-shell">
		<div class="box-typical box-typical-dashboard panel panel-default user-card">
			<header class="box-typical-header panel-heading d-flex align-items-center justify-content-between">
				<div>
					<h3 class="panel-title mb-0">Create User</h3>
					<small class="text-muted">Assign campus, roles, and access.</small>
				</div>
				<a href="{{ route('users.index') }}" class="btn btn-default">Back to Users</a>
			</header>
			<div class="box-typical-body panel-body user-body">
				<form method="POST" action="{{ route('users.store') }}">
					@csrf
					<div class="form-section">
						<div class="section-title">Access &amp; Roles</div>
						<div class="form-row">
						<div class="form-group col-md-6">
							<label class="required">Campus</label>
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
						<div class="form-group col-md-6">
							<label class="required">Roles</label>
								<select name="roles[]" class="form-control select2 select2-white select2-user select2-roles @error('roles') is-invalid @enderror" multiple style="width: 100%;" data-placeholder="Select roles">
								@foreach($roles as $role)
									<option value="{{ $role->id }}" @selected(collect(old('roles', []))->contains($role->id))>{{ $role->name }}</option>
								@endforeach
							</select>
							<small class="text-muted">Hold Ctrl/Cmd to select multiple roles.</small>
							@error('roles')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>
					</div>
					<div class="form-section">
						<div class="section-title">User Details</div>
						<div class="form-row">
						<div class="form-group col-md-6">
							<label class="required">Full Name</label>
							<input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Alex Morgan" value="{{ old('name') }}">
							@error('name')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-6">
							<label class="required">Email</label>
							<input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="alex@example.com" value="{{ old('email') }}">
							@error('email')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>
					</div>
					<div class="form-section">
						<div class="section-title d-flex align-items-center justify-content-between">
							<span>Security</span>
							<button id="generate-password" type="button" class="btn btn-sm btn-primary" aria-label="Generate strong password" title="Generate strong password">
								<i class="fa fa-random"></i>
							</button>
						</div>
						<div class="form-row align-items-end">
						<div class="form-group col-md-6">
							<label class="required">
								<span>Password</span>
							</label>
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
						<div class="form-group col-md-6">
							<label class="required">Confirm Password</label>
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
					</div>

					<div class="text-right mt-3">
						<a href="{{ route('users.index') }}" class="btn btn-default mr-2">Cancel</a>
						<button type="submit" class="btn btn-primary">Create User</button>
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
			max-width: 1200px;
			margin: 0 auto;
			border-radius: 14px;
			box-shadow: 0 18px 40px rgba(25, 45, 85, 0.12);
		}
		.user-body {
			padding: 24px 24px 10px;
		}
		.required::after { content: '*'; color: #e74c3c; margin-left: 4px; }
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
		.select2-container--white .select2-selection--single .select2-selection__rendered {
			line-height: 36px;
			padding-left: 12px;
			padding-right: 28px;
			flex: 1;
			min-width: 0;
		}
		.select2-container--white .select2-selection--multiple {
			height: 44px;
			display: flex;
			align-items: center;
			padding: 6px 10px;
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
			align-items: center;
		}
		.select2-container--white .select2-selection--multiple .select2-selection__choice {
			border-radius: 12px;
			border: 1px solid #2b78ff;
			background: #e9f2ff;
			color: #1f2d3d;
			padding: 6px 10px 6px 10px;
			margin: 4px 6px 0 0;
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
			height: 36px;
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
			border-color: #2b78ff;
			box-shadow: 0 0 0 3px rgba(43, 120, 255, 0.12);
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

			function generatePassword() {
				const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^&*()';
				let pwd = '';
				for (let i = 0; i < 14; i++) {
					pwd += chars.charAt(Math.floor(Math.random() * chars.length));
				}
				const password = document.getElementById('password');
				const confirm = document.getElementById('password_confirmation');
				if (password) password.value = pwd;
				if (confirm) confirm.value = pwd;
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
							return $('<span class="role-check"><span class="box">✓</span>' + state.text + '</span>');
						},
						templateSelection: function (state) {
							return state.text;
						},
					});
				}
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
@endpush
