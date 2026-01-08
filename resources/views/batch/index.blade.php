@extends('layouts.theme')

@section('title', 'Batches')

@section('content')
	<div class="batch-shell">
		<div class="batch-card box-typical box-typical-dashboard panel panel-default">
			<header class="box-typical-header panel-heading d-flex align-items-center justify-content-between">
				<div>
					<h3 class="panel-title mb-0">Batches</h3>
					<small class="text-muted">List of batches by campus and program.</small>
				</div>
				<a href="{{ route('batch.create') }}" class="btn btn-primary">New Batch</a>
			</header>
			<div class="box-typical-body panel-body">
				<div class="table-responsive">
					<table class="table table-hover">
						<thead>
							<tr>
								<th>Code</th>
								<th>Program</th>
								<th>Campus</th>
								<th>Session</th>
								<th>Time</th>
								<th>Dates</th>
								<th>Instructor</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							@forelse($batches as $batch)
								<tr>
									<td>{{ $batch->code }}</td>
									<td>{{ $batch->program->title ?? $batch->program->name ?? '—' }}</td>
									<td>{{ $batch->campus->name ?? '—' }}</td>
									<td>{{ ucfirst($batch->session ?? 'n/a') }}</td>
									<td>
										@if($batch->start_time && $batch->end_time)
											{{ \Carbon\Carbon::parse($batch->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($batch->end_time)->format('h:i A') }}
										@else
											—
										@endif
									</td>
									<td>
										@if($batch->start_date)
											{{ \Carbon\Carbon::parse($batch->start_date)->format('d M Y') }}
										@endif
										@if($batch->end_date)
											<br>to {{ \Carbon\Carbon::parse($batch->end_date)->format('d M Y') }}
										@endif
									</td>
									<td>{{ $batch->instructor ?? '—' }}</td>
									<td>{{ ucfirst($batch->status ?? 'active') }}</td>
								</tr>
							@empty
								<tr>
									<td colspan="8" class="text-center text-muted">No batches found.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
				@if(method_exists($batches, 'links'))
					<div class="mt-3">
						{{ $batches->links() }}
					</div>
				@endif
			</div>
		</div>
	</div>
@endsection

@push('styles')
	<style>
		.batch-shell { padding: 10px; }
		.batch-card { max-width: 1200px; margin: 0 auto; }
	</style>
@endpush
