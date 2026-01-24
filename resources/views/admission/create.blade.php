@extends('layouts.theme')

@section('title', 'Create New Admission')

@section('content')
	<div class="admission-shell">
		<div class="admission-card box-typical box-typical-dashboard panel panel-default">
			<div class="card-body">
				<h3 class="adm-title">Create New Admission <small class="text-muted">(All fields marked with * are required)</small></h3>
				<form method="POST" action="{{ route('admission.store') }}">
					@csrf
					@if(!empty($lead))
						<input type="hidden" name="lead_id" value="{{ $lead->id }}">
					@endif
					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="required">Select Campus</label>
							<select class="form-control" name="campus_id" required>
								<option value="">- Select -</option>
								@foreach($campuses ?? [] as $campus)
									<option value="{{ $campus->id }}" {{ old('campus_id', $lead->campus_id ?? '') == $campus->id ? 'selected' : '' }}>
										{{ $campus->code ?? $campus->name }} - {{ $campus->name }}
									</option>
								@endforeach
							</select>
						</div>
						<div class="form-group col-md-3">
							<label class="required">Select Program</label>
							<select class="form-control" name="program_id" required>
								<option value="">- Select -</option>
								@foreach($programs ?? [] as $program)
									<option value="{{ $program->id }}" {{ old('program_id', $lead->program_id ?? '') == $program->id ? 'selected' : '' }}>
										{{ $program->title ?? $program->name }}
									</option>
								@endforeach
							</select>
						</div>
						<div class="form-group col-md-3">
							<label class="required">Select Batch</label>
							<select class="form-control" name="batch_id" required>
								<option value="">- Select -</option>
								@foreach($batches ?? [] as $batch)
									<option value="{{ $batch->id }}" {{ old('batch_id') == $batch->id ? 'selected' : '' }}>
										{{ $batch->name ?? $batch->code }}
									</option>
								@endforeach
							</select>
						</div>
						<div class="form-group col-md-3">
							<label class="required">Student Name (As per CNIC)</label>
							<input type="text" class="form-control" name="student_name" value="{{ old('student_name', $lead->name ?? '') }}" placeholder="Enter full name" required>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="required">Primary Contact Number</label>
							<input type="text" class="form-control" name="phone" value="{{ old('phone', $lead->phone ?? '') }}" placeholder="03XXXXXXXXX" required>
						</div>
						<div class="form-group col-md-3">
							<label class="required">Guardian Name</label>
							<input type="text" class="form-control" name="guardian_name" value="{{ old('guardian_name') }}" placeholder="Enter guardian name" required>
						</div>
						<div class="form-group col-md-3">
							<label class="required">Guardian Contact Number</label>
							<input type="text" class="form-control" name="guardian_phone" value="{{ old('guardian_phone') }}" placeholder="03XXXXXXXXX" required>
						</div>
						<div class="form-group col-md-3">
							<label class="required">National Identity Card (CNIC)</label>
							<input type="text" class="form-control" name="cnic" value="{{ old('cnic') }}" placeholder="Numbers only" required>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-3">
<<<<<<< HEAD
							<label class="required">Date of Birth</label>
							<input type="date" class="form-control" placeholder="dd/mm/yyyy">
=======
							<label>Email Address</label>
							<input type="email" class="form-control" name="email" value="{{ old('email', $lead->email ?? '') }}" placeholder="Enter email address" required>
>>>>>>> bc2710b (new  updates)
						</div>
						<div class="form-group col-md-3">
							<label class="required">Education</label>
							<input type="text" class="form-control" name="education" value="{{ old('education') }}" placeholder="Enter education" required>
						</div>
						<div class="form-group col-md-3">
							<label class="required">Date of Birth</label>
							<input type="date" class="form-control" name="date_of_birth" value="{{ old('date_of_birth') }}" required>
						</div>
						<!-- <div class="form-group col-md-3">
							<label class="required">Gender</label>
							<div class="mt-1 gender-options">
								<label class="mr-3"><input type="radio" name="gender" value="male" {{ old('gender', 'male') === 'male' ? 'checked' : '' }}> Male</label>
								<label class="mr-3"><input type="radio" name="gender" value="female" {{ old('gender') === 'female' ? 'checked' : '' }}> Female</label>
								<label><input type="radio" name="gender" value="other" {{ old('gender') === 'other' ? 'checked' : '' }}> Other</label>
							</div>
<<<<<<< HEAD
						</div> -->
							<div class="form-group form-group-radios">
									<label class="form-label" id="signup_v2-gender">
										Gender <span class="color-red">*</span>
									</label>
									<div class="radio">
										<input id="signup_v2-gender-male"
											   name="signup_v2[gender]"
											   data-validation="[NOTEMPTY]"
											   data-validation-group="signup_v2-gender"
											   data-validation-message="You must select a gender"
											   type="radio"
											   value="male">
										<label for="signup_v2-gender-male">Male</label>
									</div>
									<div class="radio">
										<input id="signup_v2-gender-female"
											   name="signup_v2[gender]"
											   data-validation-group="signup_v2-gender"
											   type="radio"
											   value="female">
										<label for="signup_v2-gender-female">Female</label>
									</div>
									<div class="radio">
										<input id="signup_v2-gender-other"
											   name="signup_v2[gender]"
											   data-validation-group="signup_v2-gender"
											   type="radio"
											   value="other">
										<label for="signup_v2-gender-other">Other</label>
									</div>
								</div>
						<div class="form-group col-md-3">
							<label class="required">Current Education Level</label>
							<input type="text" class="form-control" placeholder="Enter recent completed degree">
=======
>>>>>>> bc2710b (new  updates)
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="required">Country</label>
							<select class="form-control" id="adm-country-select" name="country" required></select>
						</div>
						<div class="form-group col-md-3">
							<label class="required">City</label>
							<select class="form-control" id="adm-city-select" name="city" required>
								<option>Loading...</option>
							</select>
						</div>
						<div class="form-group col-md-3">
							<label class="required">Area</label>
							<input type="text" class="form-control" name="area" value="{{ old('area') }}" placeholder="Enter area" required>
						</div>
					</div>

					<div class="form-group">
						<label class="required">Postal Address</label>
						<textarea class="form-control" name="postal_address" rows="2" placeholder="Enter complete postal address..." required>{{ old('postal_address') }}</textarea>
					</div>

					<div class="form-row">
						<div class="form-group col-md-3">
							<label>Passport Number (Optional)</label>
							<input type="text" class="form-control" name="passport_number" value="{{ old('passport_number') }}" placeholder="Enter passport number">
						</div>
						<div class="form-group col-md-3">
							<label class="required">Registration Number</label>
							<input type="text" class="form-control" name="registration_number" value="{{ old('registration_number') }}" placeholder="Enter registration number" required>
						</div>
						<div class="form-group col-md-3">
							<label class="required">Roll Number</label>
							<input type="text" class="form-control" name="roll_number" value="{{ old('roll_number') }}" placeholder="Enter roll number" required>
						</div>
						<div class="form-group col-md-3">
							<label class="required">Date of Admission</label>
							<input type="date" class="form-control" name="admission_date" value="{{ old('admission_date') }}" required>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="required">Fee Package</label>
							<input type="number" step="0.01" class="form-control" name="fee_package" value="{{ old('fee_package') }}" required>
						</div>
						<div class="form-group col-md-3">
							<label class="required">Discount Amount</label>
							<input type="number" step="0.01" class="form-control" name="discount_amount" value="{{ old('discount_amount') }}" required>
						</div>
						<div class="form-group col-md-3">
							<label class="required">Discount %</label>
							<input type="number" step="0.01" class="form-control" name="discount_percent" value="{{ old('discount_percent') }}" required>
						</div>
						<div class="form-group col-md-3">
							<label class="required">Discounted Fee</label>
							<input type="number" step="0.01" class="form-control" name="discounted_fee" value="{{ old('discounted_fee') }}" required>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="required">Fee Type</label>
							<div class="mt-1 gender-options">
								<label class="mr-3"><input type="radio" name="fee_type" value="full" {{ old('fee_type', 'full') === 'full' ? 'checked' : '' }}> Full Fee</label>
								<label><input type="radio" name="fee_type" value="installments" {{ old('fee_type') === 'installments' ? 'checked' : '' }}> Installments</label>
							</div>
						</div>
						<div class="form-group col-md-9">
							<label class="required">Remarks</label>
							<textarea class="form-control" name="remarks" rows="2" placeholder="Remarks" required>{{ old('remarks') }}</textarea>
						</div>
					</div>

					<div class="form-group">
						<label class="required">Receipt Number</label>
						<input type="text" class="form-control" name="receipt_number" value="{{ old('receipt_number') }}" placeholder="Enter receipt number" required>
					</div>

					<div class="text-right">
						<button type="submit" class="btn btn-primary">Admission Now</button>
						<button type="button" class="btn btn-outline-danger ml-2">Cancel</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection

@push('styles')
	<style>
		.admission-shell {
			padding: 8px 0 16px;
		}

		.admission-card {
			border: 1px solid #e6ecf2;
			border-radius: 8px;
			box-shadow: 0 6px 18px rgba(17, 24, 39, 0.06);
		}

		.adm-title {
			margin-bottom: 16px;
			font-weight: 700;
			color: #2f3b52;
		}

		.required::after {
			content: ' *';
			color: #e53935;
		}

		.gender-options input {
			margin-right: 6px;
		}
		/* Equal height for all form groups */
.form-group {
    margin-bottom: 16px;
}

/* Fix radio alignment */
.gender-col .gender-box {
    height: 38px; /* same as input height */
    display: flex;
    align-items: center;
    gap: 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    padding: 0 10px;
}

/* Radio spacing */
.gender-box input {
    margin-right: 4px;
}

/* Mobile fix */
@media (max-width: 768px) {
    .gender-col .gender-box {
        height: auto;
        flex-wrap: wrap;
        padding: 8px;
    }
}

	</style>
@endpush

@push('scripts')
	@include('partials.country_city_script')
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			CountryCityLoader.init('adm-country-select', 'adm-city-select', { country: 'Pakistan', city: 'Faisalabad' });
		});
	</script>
@endpush
