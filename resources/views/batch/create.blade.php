@extends('layouts.theme')

@section('title', 'Create New Batch')

@section('content')
	<div class="batch-shell">
		<div class="batch-card box-typical box-typical-dashboard panel panel-default">
			<div class="card-body">
				<h3 class="batch-title">Create New Batch <small class="text-muted">(All fields marked with * are required)</small></h3>
				<form method="POST" action="{{ route('batch.store') }}">
					@csrf
					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="required">Select Campus</label>
							<select class="form-control" name="campus_id" required>
								<option value="">- Select -</option>
								@foreach($campuses as $campus)
									<option value="{{ $campus->id }}">{{ $campus->name }} ({{ $campus->code ?? '' }})</option>
								@endforeach
							</select>
						</div>
						<div class="form-group col-md-3">
							<label class="required">Select Program</label>
							<select class="form-control" name="program_id" id="program-select" required>
								<option value="">- Select -</option>
								@foreach($programs as $program)
									<option value="{{ $program->id }}" data-code="{{ $program->code }}">{{ $program->title ?? $program->name }}</option>
								@endforeach
							</select>
						</div>
						<div class="form-group col-md-3">
							<label class="required">Batch Code</label>
							<input type="text" class="form-control" id="batch-code" placeholder="Auto generated" readonly>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="required">Instructor/Teacher</label>
							<input type="text" class="form-control" name="instructor" placeholder="Enter instructor" required>
						</div>
						<div class="form-group col-md-3">
							<label class="required">Batch Starting Date</label>
							<input type="date" class="form-control" name="start_date" id="start-date" required>
						</div>
						<div class="form-group col-md-3">
							<label class="required">Expected Batch Ending Date</label>
							<input type="date" class="form-control" name="end_date">
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="required">Batch Session</label>
							<div class="mt-1 session-options">
								<label class="mr-3"><input type="radio" name="session" value="morning" checked> Morning</label>
								<label class="mr-3"><input type="radio" name="session" value="evening"> Evening</label>
								<label><input type="radio" name="session" value="weekend"> Weekend</label>
							</div>
						</div>
						<div class="form-group col-md-3">
							<label class="required">Batch Start Time</label>
							<input type="time" class="form-control" name="start_time" required>
						</div>
						<div class="form-group col-md-3">
							<label class="required">Batch End Time</label>
							<input type="time" class="form-control" name="end_time" required>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-4">
							<label class="required">Select Lab</label>
							<input type="text" class="form-control" name="lab" placeholder="Enter lab" required>
						</div>
					</div>

					<div class="form-group">
						<label>Remarks</label>
						<textarea class="form-control" name="remarks" rows="2" placeholder="Enter remarks"></textarea>
					</div>

					<div class="text-right">
						<button type="submit" class="btn btn-primary">Create Batch</button>
						<a href="{{ url()->previous() }}" class="btn btn-outline-danger ml-2">Cancel</a>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection

@push('styles')
	<style>
		.batch-shell {
			padding: 8px 0 16px;
		}

		.batch-card {
			border: 1px solid #e6ecf2;
			border-radius: 8px;
			box-shadow: 0 6px 18px rgba(17, 24, 39, 0.06);
		}

		.batch-title {
			margin-bottom: 16px;
			font-weight: 700;
			color: #2f3b52;
		}

		.required::after {
			content: ' *';
			color: #e53935;
		}

		.session-options input {
			margin-right: 6px;
		}
	</style>
@endpush

@push('scripts')
	<script>
		(function () {
			function updateBatchCode() {
				const programSelect = document.getElementById('program-select');
				const startDate = document.getElementById('start-date');
				const codeField = document.getElementById('batch-code');
				if (!programSelect || !startDate || !codeField) return;

				const programCode = programSelect.selectedOptions[0]?.getAttribute('data-code') || '';
				const dateVal = startDate.value;
				if (!programCode) {
					codeField.value = '';
					return;
				}
				let dt = dateVal ? new Date(dateVal) : new Date();
				if (isNaN(dt)) {
					codeField.value = '';
					return;
				}
				const month = String(dt.getMonth() + 1).padStart(2, '0');
				const year = String(dt.getFullYear()).slice(-2);
				codeField.value = `${programCode.toUpperCase()}${month}-${year}`;
			}

			document.addEventListener('DOMContentLoaded', function () {
				const programSelect = document.getElementById('program-select');
				const startDate = document.getElementById('start-date');
				if (programSelect) programSelect.addEventListener('change', updateBatchCode);
				if (startDate) startDate.addEventListener('change', updateBatchCode);
				updateBatchCode();
			});
		})();
	</script>
@endpush
