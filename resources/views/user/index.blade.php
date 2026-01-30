@extends('layouts.theme')

@section('title', 'Users')

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="box-typical box-typical-dashboard panel panel-default">
					<header class="box-typical-header panel-heading d-flex align-items-center justify-content-between">
						<div>
							<h3 class="panel-title mb-0">User Directory</h3>
							<small class="text-muted">Manage users, campuses, roles, and access.</small>
						</div>
						<div class="d-flex gap-2">
							<a href="{{ route('users.create') }}" class="btn btn-primary">New User</a>
						</div>
					</header>
					<div class="box-typical-body panel-body">
						<div class="table-responsive">
							<table class="table table-hover">
								<thead>
									<tr>
										<th>Name</th>
										<th>Email</th>
										<th>Campus</th>
										<th>Roles</th>
										<th>Status</th>
										<th class="text-right">Actions</th>
									</tr>
								</thead>
								<tbody>
									@forelse($users as $user)
										<tr>
											<td>
												<div class="fw-600">{{ $user->name }}</div>
												<small class="text-muted">Created {{ optional($user->created_at)->format('M d, Y') }}</small>
											</td>
											<td>{{ $user->email }}</td>
											<td>{{ $user->campus->name ?? '—' }}</td>
											<td>
												@forelse($user->roles as $role)
													<span class="label label-primary">{{ $role->name }}</span>
												@empty
													<span class="text-muted">No roles</span>
												@endforelse
											</td>
											<td><span class="label label-success">Active</span></td>
											<td class="text-right">
												<a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-secondary">Edit</a>
												<form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
													@csrf
													@method('DELETE')
													<button type="submit" class="btn btn-sm btn-danger-outline" onclick="return confirm('Delete this user?')">Delete</button>
												</form>
											</td>
										</tr>
									@empty
										<tr>
											<td colspan="6" class="text-center text-muted">No users found.</td>
										</tr>
									@endforelse
								</tbody>
							</table>
						</div>

						@if(method_exists($users, 'links'))
							<div class="mt-3">
								{{ $users->links() }}
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
		.gap-2 { gap: 8px; }
		.btn-danger-outline {
			border: 1px solid #e74c3c;
			color: #e74c3c;
			background: transparent;
		}
	</style>
@endpush
