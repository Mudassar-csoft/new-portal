@extends('layouts.theme')

@section('title', 'Edit Permission')

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8">
				<div class="box-typical box-typical-dashboard panel panel-default">
					<header class="box-typical-header panel-heading d-flex align-items-center justify-content-between">
						<div>
							<h3 class="panel-title mb-0">Edit Permission</h3>
							<small class="text-muted">Update resource and action.</small>
						</div>
						<a href="{{ route('permissions.index') }}" class="btn btn-default">Back</a>
					</header>
					<div class="box-typical-body panel-body">
						<form method="POST" action="{{ route('permissions.update', $permission) }}">
							@csrf
							@method('PUT')
							<div class="form-row">
								<div class="form-group col-md-6">
									<label class="required">Resource</label>
									<input type="text" name="resource" class="form-control" placeholder="lead" value="{{ old('resource', $permission->resource) }}">
								</div>
								<div class="form-group col-md-6">
									<label class="required">Action</label>
									<input type="text" name="action" class="form-control" placeholder="view" value="{{ old('action', $permission->action) }}">
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-12">
									<label>Description</label>
									<input type="text" name="description" class="form-control" placeholder="Optional description" value="{{ old('description', $permission->description) }}">
								</div>
							</div>
							<div class="text-right">
								<a href="{{ route('permissions.index') }}" class="btn btn-default mr-2">Cancel</a>
								<button type="submit" class="btn btn-primary">Save Changes</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('styles')
	<style>
		.required::after { content: '*'; color: #e74c3c; margin-left: 4px; }
	</style>
@endpush
