@extends('layouts.theme')

@section('title', 'Edit User')

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-9">
				<div class="box-typical box-typical-dashboard panel panel-default">
					<header class="box-typical-header panel-heading d-flex align-items-center justify-content-between">
						<div>
							<h3 class="panel-title mb-0">Edit User</h3>
							<small class="text-muted">Update campus, roles, and credentials.</small>
						</div>
						<a href="{{ route('users.index') }}" class="btn btn-default">Back to Users</a>
					</header>
					<div class="box-typical-body panel-body">
						<form method="POST" action="{{ route('users.update', $user) }}">
							@csrf
							@method('PUT')
							<div class="form-row">
								<div class="form-group col-md-6">
									<label class="required">Campus</label>
									<select name="campus_id" class="form-control">
										<option value="">Select campus</option>
										@foreach($campuses as $campus)
											<option value="{{ $campus->id }}" @selected(old('campus_id', $user->campus_id) == $campus->id)>{{ $campus->name }}</option>
										@endforeach
									</select>
								</div>
								<div class="form-group col-md-6">
									<label class="required">Roles</label>
									<select name="roles[]" class="form-control" multiple>
										@foreach($roles as $role)
											<option value="{{ $role->id }}" @selected(collect(old('roles', $user->roles->pluck('id')->all()))->contains($role->id))>{{ $role->name }}</option>
										@endforeach
									</select>
									<small class="text-muted">Hold Ctrl/Cmd to select multiple roles.</small>
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-6">
									<label class="required">Full Name</label>
									<input type="text" name="name" class="form-control" placeholder="Full name" value="{{ old('name', $user->name) }}">
								</div>
								<div class="form-group col-md-6">
									<label class="required">Email</label>
									<input type="email" name="email" class="form-control" placeholder="user@example.com" value="{{ old('email', $user->email) }}">
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-6">
									<label class="d-flex align-items-center justify-content-between">
										<span>Password <small class="text-muted">(leave blank to keep current)</small></span>
										<button type="button" class="btn btn-link btn-sm p-0" id="generate-password">Generate strong password</button>
									</label>
									<div class="input-group">
										<input type="password" name="password" id="password" class="form-control" placeholder="********">
										<span class="input-group-btn">
											<button class="btn btn-default toggle-visibility" type="button" data-target="#password">👁</button>
										</span>
									</div>
								</div>
								<div class="form-group col-md-6">
									<label>Confirm Password</label>
									<div class="input-group">
										<input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="********">
										<span class="input-group-btn">
											<button class="btn btn-default toggle-visibility" type="button" data-target="#password_confirmation">👁</button>
										</span>
									</div>
								</div>
							</div>

							<div class="text-right">
								<a href="{{ route('users.index') }}" class="btn btn-default mr-2">Cancel</a>
								<button type="submit" class="btn btn-primary">Save Changes</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="box-typical box-typical-dashboard panel panel-default">
					<header class="box-typical-header panel-heading">
						<h6 class="panel-title mb-0">Access Preview</h6>
					</header>
					<div class="box-typical-body panel-body">
						<ul class="list-unstyled mb-3">
							<li><strong>Campus:</strong> {{ optional($user->campus)->name ?? 'None' }}</li>
							<li><strong>Roles:</strong> {{ $user->roles->pluck('name')->join(', ') ?: 'None' }}</li>
						</ul>
						<h6 class="text-muted">What they can do</h6>
						<ul class="list-unstyled mb-0">
							<li>• Access scoped to selected campus</li>
							<li>• Permissions derived from assigned roles</li>
							<li>• Extend with scoped module permissions later</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('styles')
	<style>
		.required::after { content: '*'; color: #e74c3c; margin-left: 4px; }
		.checkbox { display: block; margin-bottom: 6px; font-weight: 500; }
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
