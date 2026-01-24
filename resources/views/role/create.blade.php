@extends('layouts.theme')

@section('title', 'Create Role')

@section('content')
	<div class="user-shell">
		<div class="box-typical box-typical-dashboard panel panel-default user-card">
			<header class="box-typical-header panel-heading d-flex align-items-center justify-content-between">
				<div>
					<h3 class="panel-title mb-0">Create Role</h3>
					<small class="text-muted">Define a role and attach permissions.</small>
				</div>
				<a href="{{ route('roles.index') }}" class="btn btn-default">Back</a>
			</header>
			<div class="box-typical-body panel-body user-body">
				<form method="POST" action="{{ route('roles.store') }}">
					@csrf
					<div class="form-section">
						<div class="section-title">Role Details</div>
						<div class="form-row">
							<div class="form-group col-md-6">
								<label class="required">Name</label>
								<input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Admin">
								@error('name')
									<div class="field-error">{{ $message }}</div>
								@enderror
							</div>
							<div class="form-group col-md-6">
								<label>Slug</label>
								<input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="admin">
								<small class="text-muted">Auto from name if left blank.</small>
								@error('slug')
									<div class="field-error">{{ $message }}</div>
								@enderror
							</div>
						</div>
						<div class="form-group">
							<label>Description</label>
							<textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2" placeholder="Optional description">{{ old('description') }}</textarea>
							@error('description')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>

					<div class="form-section">
						<div class="section-title">Permissions</div>
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
						@error('permissions')
							<div class="field-error">{{ $message }}</div>
						@enderror
						@error('permissions.*')
							<div class="field-error">{{ $message }}</div>
						@enderror
					</div>
					<!-- <div class="form-group row">
						<label for="inputPassword" class="col-sm-2 form-control-label">Password</label>
						<div class="col-sm-10">
							<input type="password" class="form-control" id="inputPassword" placeholder="Password">
						</div>
					</div> -->

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
		/* Tablet */
        @media (max-width: 992px) {
    .role-card {
        max-width: 95%;
        margin-left: auto;
        margin-right: auto;
    }
}

/* Mobile */
@media (max-width: 576px) {
    .role-card {
        max-width: 100%;
        margin-left: 12px;
        margin-right: 12px;
    }
}


	</style>
@endpush
