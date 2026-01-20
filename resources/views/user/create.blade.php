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
					<div class="form-row">
						<div class="form-group col-md-6">
							<label class="required">Campus</label>
							<select name="campus_id" class="form-control">
								<option value="">Select campus</option>
								@foreach($campuses as $campus)
									<option value="{{ $campus->id }}" @selected(old('campus_id') == $campus->id)>{{ $campus->name }}</option>
								@endforeach
							</select>
						</div>
						<div class="form-group col-md-6">
							<label class="required">Roles</label>
							<select name="roles[]" class="form-control" multiple>
								@foreach($roles as $role)
									<option value="{{ $role->id }}" @selected(collect(old('roles', []))->contains($role->id))>{{ $role->name }}</option>
								@endforeach
							</select>
							<small class="text-muted">Hold Ctrl/Cmd to select multiple roles.</small>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group col-md-6">
							<label class="required">Full Name</label>
							<input type="text" name="name" class="form-control" placeholder="Enter Full Name" value="{{ old('name') }}">
						</div>
						<div class="form-group col-md-6">
							<label class="required">Email</label>
							<input type="email" name="email" class="form-control" placeholder="Enter Email" value="{{ old('email') }}">
						</div>
					</div>
					<div class="form-row align-items-end">
						<div class="form-group col-md-6">
							<label class="required d-flex align-items-center justify-content-between">
								<span>Password</span>
							</label>
							<div class="input-group">
								<input type="password" name="password" id="password" class="form-control" placeholder="********">
								<span class="input-group-btn">
									<button class="btn btn-default toggle-visibility" type="button" data-target="#password">👁</button>
								</span>
							</div>
						</div>
						<div class="form-group col-md-6">
							<label class="required">Confirm Password</label>
							<div class="input-group">
								<input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="********">
								<span class="input-group-btn">
									<button class="btn btn-default toggle-visibility" type="button" data-target="#password_confirmation">👁</button>
								</span>
								
							</div>
						</div>
					</div>
                        <button class="context-menu-one btn btn-primary">Generate Strong Password</button>

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
			padding: 10px;
		}
		.user-card {
			max-width: 1200px;
			margin: 0 auto;
		}
		.user-body {
			padding: 20px;
		}
		.required::after { content: '*'; color: #e74c3c; margin-left: 4px; }
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
