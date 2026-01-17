@extends('layouts.theme')

@section('title', 'Transfer Lead')

@section('content')
	<div class="box-typical box-typical-dashboard panel panel-default">
		<div class="box-typical-header panel-heading">
			<h3 class="panel-title">Transfer Lead: {{ $lead->name ?? 'Lead' }}</h3>
		</div>
		<div class="box-typical-body panel-body">
			@if(empty($lead))
				<p class="text-danger mb-0">Lead not found.</p>
			@else
			<form method="POST" action="{{ route('leads.transfer.store', $lead) }}">
				@csrf
				<div class="form-group">
					<label>Current Campus</label>
					<input type="text" class="form-control" value="{{ $lead->campus->name ?? 'N/A' }}" disabled>
				</div>
				<div class="form-group">
					<label class="required">Transfer To</label>
					<select class="form-control" name="to_campus_id" required>
						<option value="">- Select campus -</option>
						@foreach($campuses as $campus)
							<option value="{{ $campus->id }}" {{ $campus->id === $lead->campus_id ? 'disabled' : '' }}>
								{{ $campus->name }} ({{ $campus->code }})
							</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label>Reason / Note</label>
					<textarea class="form-control" name="reason" rows="3" placeholder="Why is this lead being transferred?"></textarea>
				</div>
				<div class="text-right">
					<a href="{{ route('leads.show', $lead) }}" class="btn btn-default">Cancel</a>
					<button type="submit" class="btn btn-primary">Submit Transfer</button>
				</div>
			</form>
			@endif
		</div>
	</div>
@endsection
