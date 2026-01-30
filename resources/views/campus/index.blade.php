@extends('layouts.theme')

@section('title', 'Campuses')

@section('content')
	<div class="campus-shell">
		<div class="box-typical box-typical-dashboard panel panel-default campus-card">
			<header class="box-typical-header panel-heading d-flex align-items-center justify-content-between">
				<div>
					<h3 class="panel-title mb-0">Campuses</h3>
					<small class="text-muted">List of campuses.</small>
				</div>
				<a href="{{ route('campus.create') }}" class="btn btn-primary">New Campus</a>
			</header>
			<div class="box-typical-body panel-body">
				<div class="table-responsive">
					<table class="table table-hover">
						<thead>
							<tr>
								<th>Code</th>
								<th>Title</th>
								<th>City</th>
								<th>Type</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							@forelse($campuses as $campus)
								<tr>
									<td>{{ $campus->code }}</td>
									<td>{{ $campus->name }}</td>
									<td>{{ $campus->city }}</td>
									<td>{{ ucfirst($campus->campus_type) }}</td>
									<td>{{ ucfirst($campus->status) }}</td>
								</tr>
							@empty
								<tr>
									<td colspan="5" class="text-center text-muted">No campuses found.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
				@if(method_exists($campuses, 'links'))
					<div class="mt-3">
						{{ $campuses->links() }}
					</div>
				@endif
			</div>
		</div>
	</div>
@endsection

@push('styles')
	<style>
		.campus-shell { padding: 10px; }
		.campus-card { max-width: 1200px; margin: 0 auto; }
	</style>
@endpush
