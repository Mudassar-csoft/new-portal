@extends('layouts.theme')

@section('title', 'Create Role')

@section('content')
	<div class="role-shell">
		<div class="box-typical box-typical-dashboard panel panel-default role-card">
			<header class="box-typical-header panel-heading d-flex align-items-center justify-content-between">
				<div>
					<h3 class="panel-title mb-0">Create Role</h3>
					<small class="text-muted">Define a role and attach permissions.</small>
				</div>
				<a href="{{ route('roles.index') }}" class="btn btn-default">Back</a>
			</header>
			<div class="box-typical-body panel-body role-body">
				<form method="POST" action="{{ route('roles.store') }}">
					@csrf
					<div class="form-row">
						<div class="form-group col-md-6">
							<label class="required">Name</label>
							<input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Admin">
						</div>
						<div class="form-group col-md-6">
							<label>Slug</label>
							<input type="text" name="slug" class="form-control" value="{{ old('slug') }}" placeholder="admin">
							<small class="text-muted">Auto from name if left blank.</small>
						</div>
					</div>
					<div class="form-group">
						<label>Description</label>
						<textarea name="description" class="form-control" rows="2" placeholder="Optional description">{{ old('description') }}</textarea>
					</div>

					<div class="form-group mt-3">
						<label class="required d-block mb-2">Permissions</label>
						<div class="permission-wrapper">
							<div class="permission-grid">
								@foreach($permissions as $resource => $perms)
									<div class="perm-column">
										<h6 class="text-muted text-uppercase perm-heading">{{ $resource }}</h6>
										@foreach($perms as $perm)
											<label class="perm-item">
												<input type="checkbox" name="permissions[]" value="{{ $perm->id }}" @checked(collect(old('permissions', []))->contains($perm->id))>
												<span>{{ $perm->action }}</span>
											</label>
										@endforeach
									</div>
								@endforeach
							</div>
						</div>
					</div>

					<div class="text-right mt-3">
						<a href="{{ route('roles.index') }}" class="btn btn-default mr-2">Cancel</a>
						<button type="submit" class="btn btn-primary">Create Role</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection

@push('styles')
	<style>
		.role-shell {
			min-height: 100vh;
			padding: 10px;
		}
		.role-card {
			max-width: 100%;
			margin: 0 auto;
		}
		.role-body {
			padding: 20px;
		}
		.required::after { content: '*'; color: #e74c3c; margin-left: 4px; }
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
			min-height: 200px;
		}
		.perm-heading {
			margin-bottom: 8px;
			font-weight: 700;
			letter-spacing: 0.5px;
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
		.permission-wrapper {
			max-height: 500px;
			overflow-y: auto;
			overflow-x: hidden;
			padding-right: 8px;
			border: 1px solid #e6e9ed;
			border-radius: 6px;
			background: #fff;
			padding: 12px;
		}
	</style>
@endpush
