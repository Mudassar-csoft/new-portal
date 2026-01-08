@extends('layouts.theme')

@section('title', 'Roles')

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="box-typical box-typical-dashboard panel panel-default">
					<header class="box-typical-header panel-heading d-flex align-items-center justify-content-between">
						<div>
							<h3 class="panel-title mb-0">Roles</h3>
							<small class="text-muted">Manage role definitions and attached permissions.</small>
						</div>
						<a href="{{ route('roles.create') }}" class="btn btn-primary">New Role</a>
					</header>
					<div class="box-typical-body panel-body">
						<div class="table-responsive">
							<table class="table table-hover">
								<thead>
									<tr>
										<th>Name</th>
										<th>Permissions</th>
										<th class="text-right">Actions</th>
									</tr>
								</thead>
								<tbody>
									@forelse($roles as $role)
										<tr>
											<td>
												<div class="fw-600">{{ $role->name }}</div>
												<small class="text-muted">{{ $role->slug }}</small>
											</td>
											<td>
												@if($role->permissions->isEmpty())
													<span class="text-muted">No permissions</span>
												@else
													<span class="label label-default">{{ $role->permissions->count() }}</span>
												@endif
											</td>
											<td class="text-right">
												<a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-secondary">Edit</a>
												<form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline">
													@csrf
													@method('DELETE')
													<button type="submit" class="btn btn-sm btn-danger-outline" onclick="return confirm('Delete this role?')">Delete</button>
												</form>
											</td>
										</tr>
									@empty
										<tr>
											<td colspan="4" class="text-center text-muted">No roles found.</td>
										</tr>
									@endforelse
								</tbody>
							</table>
						</div>
						@if(method_exists($roles, 'links'))
							<div class="mt-3">
								{{ $roles->links() }}
							</div>
						@endif
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('styles')
	<style>
		.fw-600 { font-weight: 600; }
		.btn-danger-outline { border: 1px solid #e74c3c; color: #e74c3c; background: transparent; }
	</style>
@endpush
