@extends('layouts.theme')

@section('title', 'Create New Program')

@section('content')
	<div class="program-shell">
		<div class="program-card box-typical box-typical-dashboard panel panel-default">
			<div class="card-body">
				<h3 class="prog-title">Create New Program <small class="text-muted">(All fields marked with * are required)</small></h3>
				<form method="POST" action="{{ route('program.store') }}">
					@csrf
					<div class="form-row">
						<div class="form-group col-md-4">
							<label class="required">Program Type</label>
							<select class="form-control" name="program_type" required>
								<option value="">- Select -</option>
								<option value="certificate">Certificate</option>
								<option value="diploma">Diploma</option>
								<option value="bootcamp">Bootcamp</option>
								<option value="workshop">Workshop</option>
							</select>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-4">
							<label class="required">Title</label>
							<input type="text" name="title" class="form-control" placeholder="Enter title" required>
						</div>
						<div class="form-group col-md-4">
							<label class="required">Course Code</label>
							<input type="text" name="code" class="form-control" placeholder="Enter course code" required>
						</div>
						<div class="form-group col-md-4">
							<label class="required">Fee</label>
							<input type="number" step="0.01" name="fee" class="form-control" placeholder="Enter fee amount" required>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-4">
							<label class="required">Duration (in weeks)</label>
							<select class="form-control" name="duration_weeks" required>
								<option value="">- Select -</option>
								<option value="4">4</option>
								<option value="8">8</option>
								<option value="12">12</option>
								<option value="24">24</option>
							</select>
						</div>
						<div class="form-group col-md-4">
							<label class="required">No. of Installments</label>
							<select class="form-control" name="installments" required>
								<option value="">- Select -</option>
								<option value="1">1</option>
								<option value="2">2</option>
								<option value="3">3</option>
								<option value="4">4</option>
							</select>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-6">
							<label class="required">Upload Outline</label>
							<input type="file" class="form-control-file" disabled>
							<small class="text-muted d-block">File upload wiring not implemented yet.</small>
						</div>
					</div>

					<div class="form-group">
						<label>Prerequisite</label>
						<textarea class="form-control" name="prerequisite" rows="2" placeholder="Enter prerequisite"></textarea>
					</div>

					<div class="form-group">
						<label>Remarks</label>
						<textarea class="form-control" name="remarks" rows="2" placeholder="Enter remarks"></textarea>
					</div>

					<hr>
					<div class="form-row">
						<div class="form-group col-md-6">
							<label>Discount Campuses</label>
							<select class="form-control" name="discount_campuses[]" multiple>
								<option value="all">All campuses</option>
								@foreach($campuses as $campus)
									<option value="{{ $campus->id }}">{{ $campus->name }} ({{ $campus->city }})</option>
								@endforeach
							</select>
							<small class="text-muted">Select one or more campuses, or choose “All campuses” for a global discount.</small>
						</div>
						<div class="form-group col-md-4">
							<label>Discount %</label>
							<input type="number" step="0.01" name="discount_percent" class="form-control" placeholder="e.g., 10">
						</div>
					</div>

					<div class="form-group">
						<label class="required">Status</label>
						<select name="status" class="form-control" required>
							<option value="active">Active</option>
							<option value="inactive">Inactive</option>
						</select>
					</div>

					<div class="text-right">
						<button type="submit" class="btn btn-primary">Create Program</button>
						<a href="{{ url()->previous() }}" class="btn btn-outline-danger ml-2">Cancel</a>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection

@push('styles')
	<style>
		.program-shell {
			padding: 8px 0 16px;
		}

		.program-card {
			border: 1px solid #e6ecf2;
			border-radius: 8px;
			box-shadow: 0 6px 18px rgba(17, 24, 39, 0.06);
		}

		.prog-title {
			margin-bottom: 16px;
			font-weight: 700;
			color: #2f3b52;
		}

		.required::after {
			content: ' *';
			color: #e53935;
		}
		/* FIX LEFT SPACING ON RESPONSIVE */
@media (max-width: 992px) {
    .program-shell {
        padding-left: 15px;
        padding-right: 15px;
    }
}

@media (max-width: 576px) {
    .program-shell {
        padding-left: 20px;
        padding-right: 20px;
    }
}

	</style>
@endpush
