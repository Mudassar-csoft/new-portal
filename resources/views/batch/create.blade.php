@extends('layouts.theme')

@section('title', 'Create Batch')

@section('content')

	<div class="batch-shell">
		<div class="batch-card box-typical box-typical-dashboard panel panel-default">
			<div class="card-body">
				<h3 class="batch-title">Create New Batch <small class="text-muted">(All fields marked with * are required)</small></h3>
				<form method="POST" action="{{ route('batch.store') }}">
					@csrf
					<div class="form-row">
						<div class="form-group col-lg-3 col-md-6">
							<label class="required">Select Campus</label>
							<select class="form-control" name="campus_id" required>
								<option value="">- Select -</option>
								@foreach($campuses as $campus)
									<option value="{{ $campus->id }}">{{ $campus->name }} ({{ $campus->code ?? '' }})</option>
								@endforeach
							</select>
						</div>
						<div class="form-group col-lg-3 col-md-6">
							<label class="required">Select Program</label>
							<select class="form-control" name="program_id" id="program-select" required>
								<option value="">- Select -</option>
								@foreach($programs as $program)
									<option value="{{ $program->id }}" data-code="{{ $program->code }}">{{ $program->title ?? $program->name }}</option>
								@endforeach
							</select>
						</div>
						<div class="form-group col-lg-3 col-md-6">
							<label class="required">Batch Code</label>
							<input type="text" class="form-control" id="batch-code" placeholder="Auto generated" readonly>
						</div>
						<div class="form-group col-lg-3 col-md-6">
							<label class="required">Instructor/Teacher</label>
							<input type="text" class="form-control" name="instructor" placeholder="Enter instructor" required>
</div>
					</div>

					<div class="form-row">
						
						<div class="form-group col-lg-3 col-md-6">
							<label class="required">Batch Starting Date</label>
							<input type="date" class="form-control" name="start_date" id="start-date" required>
						</div>
						<div class="form-group col-lg-3 col-md-6">
							<label class="required">Expected Batch Ending Date</label>
							<input type="date" class="form-control" name="end_date">
						</div>
						<div class="form-group col-lg-3 col-md-6">
							<label class="required">Batch Start Time</label>
							<input type="time" class="form-control" name="start_time" required>
						</div>
						<div class="form-group col-lg-3 col-md-6">
							<label class="required">Batch End Time</label>
							<input type="time" class="form-control" name="end_time" required>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-lg-3 col-md-6">
							<!-- <label class="required">Batch Session</label>
							<div class="mt-1 session-options">
								<label class="mr-3"><input type="radio" name="session" value="morning" checked> Morning</label>
								<label class="mr-3"><input type="radio" name="session" value="evening"> Evening</label>
								<label><input type="radio" name="session" value="weekend"> Weekend</label>
							</div> -->
							<div class="form-group form-group-radios ">
							<label class="form-label" id="signup_v2-session">
								Batch Session <span class="color-red">*</span>
							</label>
<div class="d-flex row">
    <div class="radio col-sm-4 g-1">
        <input id="signup_v2-session-morning"
               name="signup_v2[session]"
               data-validation="[NOTEMPTY]"
               data-validation-group="signup_v2-session"
               data-validation-message="You must select a session"
               type="radio"
               value="morning"
               checked>
        <label for="signup_v2-session-morning">Morning</label>
    </div>

    <div class="radio  col-sm-4 g-1">
        <input id="signup_v2-session-evening"
               name="signup_v2[session]"
               data-validation-group="signup_v2-session"
               type="radio"
               value="evening">
        <label for="signup_v2-session-evening">Evening</label>
    </div>

    <div class="radio  col-sm-4 g-1">
        <input id="signup_v2-session-weekend"
               name="signup_v2[session]"
               data-validation-group="signup_v2-session"
               type="radio"
               value="weekend">
        <label for="signup_v2-session-weekend">Weekend</label>
    </div>
</div>
</div>

						</div>
						
						<div class="form-group col-md-9">
							<label class="required">Select Lab</label>
							<input type="text" class="form-control" name="lab" placeholder="Enter lab" required>
						</div>
					</div>

					<div class="form-row">
						
						
						<div class="form-group col-12">
							<label>Remarks</label>
							<textarea class="form-control" name="remarks" rows="2" placeholder="Enter remarks"></textarea>
						</div>
					</div>
						
					<div class="text-right">
						<button type="submit" class="btn btn-inline btn-primary-outline">Create Batch</button>
						<a href="{{ url()->previous() }}" class="btn btn-inline btn-danger-outline btn-outline-danger ml-2">Cancel</a>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection

@push('styles')
	<style>


/* input[type="radio"]:checked {
    border-color: #00a8ff;
}
input[type="radio"]{
	    min-height: 14px !important;
    height: auto !important;
} */
	.checkbox input+label:before, .radio input+label:before{
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    width: 14px;
    height: 14px;
    border: 2px solid grey;
    border-radius: 50%;
    outline: none;
    cursor: pointer;
	
    background-color: #fff;
    transition: background 0.2s, box-shadow 0.2s;
}
.radio input[type=radio]:checked+label:after {
    width: 6px;
    height: 6px;
    background: #00a8ff;
    -webkit-border-radius: 50%;
    border-radius: 50%;
    left: 4px;
    top: 4px;
}
input[type=checkbox], input[type=radio] {
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
    padding: 0;
}


		.batch-shell {
			padding: 8px 0 16px;
		}

		.batch-card {
			border: 1px solid #e6ecf2;
			border-radius: 8px;
			box-shadow: 0 6px 18px rgba(17, 24, 39, 0.06);
		}
		.radio input+label{
			padding: 0px 0 0 18px !important;
		}
		.batch-title {
			margin-bottom: 16px;
			font-size:22px;
			font-weight: 500;
			color: #2f3b52;
		}
    <div class="batch-form-shell">
        <div class="box-typical box-typical-dashboard panel panel-default batch-form-card">
            <header class="box-typical-header panel-heading d-flex justify-content-between">
                <div>
                    <h3 class="panel-title mb-0">Create Batch</h3>
                    <small class="text-muted">Create a new batch and keep the code, timing, and instructor aligned with your layout.</small>
                </div>
                <div class="d-flex" style="gap:10px;">
                    <a href="{{ route('batch.index') }}" class="btn btn-default">Back to Batches</a>
                    <a href="{{ route('batch.timetable.index') }}" class="btn btn-primary">Manage Time Table</a>
                </div>
            </header>
            <div class="box-typical-body panel-body">
                <form method="POST" action="{{ route('batch.store') }}">
                    @csrf
                    @include('batch.partials.form')

                    <div class="text-right mt-3">
                        <button type="submit" class="btn btn-primary">Create Batch</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .batch-form-shell {
            padding: 10px;
        }

        .batch-form-card {
            max-width: 1250px;
            margin: 0 auto;
        }

        .required::after {
            content: ' *';
            color: #e53935;
        }


        .session-radio-group {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 8px;
        }

        .session-radio {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border: 1px solid #d9e2ef;
            border-radius: 8px;
            background: #f8fbff;
            cursor: pointer;
        }

        .session-radio input {
            margin: 0;
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            function updateBatchCode() {
                var programSelect = document.getElementById('batch-program');
                var startDate = document.getElementById('batch-start-date');
                var previewField = document.getElementById('batch-code-preview');

                if (!programSelect || !startDate || !previewField) {
                    return;
                }

                var selectedOption = programSelect.selectedOptions[0];
                var programCode = selectedOption ? selectedOption.getAttribute('data-code') : '';
                var dateValue = startDate.value;

                if (!programCode || !dateValue) {
                    previewField.value = '';
                    return;
                }

                var date = new Date(dateValue);
                if (Number.isNaN(date.getTime())) {
                    previewField.value = '';
                    return;
                }

                var month = String(date.getMonth() + 1).padStart(2, '0');
                var year = String(date.getFullYear()).slice(-2);

                previewField.value = programCode.toUpperCase() + month + '-' + year;
            }

            document.addEventListener('DOMContentLoaded', function () {
                var programSelect = document.getElementById('batch-program');
                var startDate = document.getElementById('batch-start-date');

                if (programSelect) {
                    programSelect.addEventListener('change', updateBatchCode);
                }

                if (startDate) {
                    startDate.addEventListener('change', updateBatchCode);
                }

                updateBatchCode();
            });
        })();
    </script>
@endpush
