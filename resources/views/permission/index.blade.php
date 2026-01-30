@extends('layouts.theme')

@section('title', 'Permissions')

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="box-typical box-typical-dashboard panel panel-default">
					<header class="box-typical-header panel-heading d-flex align-items-center justify-content-between">
						<div>
							<h3 class="panel-title mb-0">Permissions</h3>
							<small class="text-muted">Manage permission definitions.</small>
						</div>
						<a href="{{ route('permissions.create') }}" class="btn btn-primary">New Permission</a>
					</header>
					<div class="box-typical-body panel-body">
						<div class="table-responsive">
							<table class="table table-hover">
								<thead>
									<tr>
										<th>Resource</th>
										<th>Action</th>
										<th class="text-right">Actions</th>
									</tr>
								</thead>
								<tbody>
									@forelse($permissions as $permission)
										<tr>
											<td>{{ $permission->resource }}</td>
											<td>{{ $permission->action }}</td>
											<td class="text-right">
												<a href="{{ route('permissions.edit', $permission) }}" class="btn btn-sm btn-secondary">Edit</a>
												<form action="{{ route('permissions.destroy', $permission) }}" method="POST" class="d-inline">
													@csrf
													@method('DELETE')
													<button type="submit" class="btn btn-sm btn-danger-outline" onclick="return confirm('Delete this permission?')">Delete</button>
												</form>
											</td>
										</tr>
									@empty
										<tr>
											<td colspan="4" class="text-center text-muted">No permissions found.</td>
										</tr>
									@endforelse
								</tbody>
							</table>
						</div>
						@if(method_exists($permissions, 'links'))
							<div class="mt-3">
								{{ $permissions->links() }}
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
		.btn-danger-outline { border: 1px solid #e74c3c; color: #e74c3c; background: transparent; }
	</style>
@endpush
