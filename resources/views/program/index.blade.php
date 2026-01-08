@extends('layouts.theme')

@section('title', 'Programs')

@section('content')
	<div class="program-shell">
		<div class="box-typical box-typical-dashboard panel panel-default program-card">
			<header class="box-typical-header panel-heading d-flex align-items-center justify-content-between">
				<div>
					<h3 class="panel-title mb-0">Programs</h3>
					<small class="text-muted">List of programs with discounts.</small>
				</div>
				<a href="{{ route('program.create') }}" class="btn btn-primary">New Program</a>
			</header>
			<div class="box-typical-body panel-body">
				<div class="table-responsive">
					<table class="table table-hover">
						<thead>
							<tr>
								<th>Title</th>
								<th>Code</th>
								<th>Type</th>
								<th>Fee</th>
								<th>Duration (weeks)</th>
								<th>Discounts</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							@forelse($programs as $program)
								<tr>
									<td>{{ $program->title ?? $program->name }}</td>
									<td>{{ $program->code }}</td>
									<td>{{ ucfirst($program->program_type ?? 'n/a') }}</td>
									<td>{{ number_format($program->fee ?? 0, 2) }}</td>
									<td>{{ $program->duration_weeks ?? '—' }}</td>
									<td>
										@if($program->campusDiscounts->isEmpty())
											<span class="text-muted">None</span>
										@else
											@foreach($program->campusDiscounts as $discount)
												<div>
													{{ $discount->discount_percent }}%
													<span class="text-muted">
														({{ $discount->campus?->name ?? 'All campuses' }})
													</span>
												</div>
											@endforeach
										@endif
									</td>
									<td>{{ ucfirst($program->status ?? 'active') }}</td>
								</tr>
							@empty
								<tr>
									<td colspan="7" class="text-center text-muted">No programs found.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
				@if(method_exists($programs, 'links'))
					<div class="mt-3">
						{{ $programs->links() }}
					</div>
				@endif
			</div>
		</div>
	</div>
@endsection

@push('styles')
	<style>
		.program-shell { padding: 10px; }
		.program-card { max-width: 1200px; margin: 0 auto; }
	</style>
@endpush
