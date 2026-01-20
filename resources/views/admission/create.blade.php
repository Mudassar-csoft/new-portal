@extends('layouts.theme')

@section('title', 'Create New Admission')

@section('content')
	<div class="admission-shell">
		<div class="admission-card box-typical box-typical-dashboard panel panel-default">
			<div class="card-body">
				<h3 class="adm-title">Create New Admission <small class="text-muted">(All fields marked with * are required)</small></h3>
				<form>
					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="required">Select Course</label>
							<select class="form-control">
								<option>- Select -</option>
								<option>Full Stack Developer</option>
								<option>Data Science</option>
								<option>DevOps</option>
								<option>Cyber Security</option>
							</select>
						</div>
						<div class="form-group col-md-3">
							<label class="required">Select Batch</label>
							<select class="form-control">
								<option>- Select -</option>
								<option>Batch A</option>
								<option>Batch B</option>
								<option>Batch C</option>
							</select>
						</div>
						<div class="form-group col-md-3">
							<label class="required">Student Name (As per CNIC)</label>
							<input type="text" class="form-control" placeholder="Enter full name">
						</div>
						<div class="form-group col-md-3">
							<label class="required">Primary Contact Number</label>
							<input type="text" class="form-control" placeholder="03XXXXXXXXX">
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="required">Guardian Name</label>
							<input type="text" class="form-control" placeholder="Enter guardian name">
						</div>
						<div class="form-group col-md-3">
							<label class="required">Guardian Contact Number</label>
							<input type="text" class="form-control" placeholder="03XXXXXXXXX">
						</div>
						<div class="form-group col-md-3">
							<label class="required">National Identity Card (CNIC)</label>
							<input type="text" class="form-control" placeholder="Numbers only">
						</div>
						<div class="form-group col-md-3">
							<label>Passport Number (Optional)</label>
							<input type="text" class="form-control" placeholder="Enter passport number">
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="required">Date of Birth</label>
							<input type="date" class="form-control" placeholder="dd/mm/yyyy">
						</div>
						<div class="form-group col-md-3">
							<label class="required">Email</label>
							<input type="email" class="form-control" placeholder="Enter email">
						</div>
						<!-- <div class="form-group col-md-3">
							<label class="required">Gender</label>
							<div class="mt-1 gender-options">
								<label class="mr-3"><input type="radio" name="gender" checked> Male</label>
								<label class="mr-3"><input type="radio" name="gender"> Female</label>
								<label><input type="radio" name="gender"> Other</label>
							</div>
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
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="required">Country</label>
							<select class="form-control" id="adm-country-select"></select>
						</div>
						<div class="form-group col-md-3">
							<label class="required">City</label>
							<select class="form-control" id="adm-city-select">
								<option>Loading...</option>
							</select>
						</div>
						<div class="form-group col-md-3">
							<label class="required">Area</label>
							<input type="text" class="form-control" placeholder="Enter area">
						</div>
						<div class="form-group col-md-3">
							<label class="required">Postal Address</label>
							<input type="text" class="form-control" placeholder="Enter complete postal address">
						</div>
					</div>

					<div class="form-group">
						<label class="required">Remarks</label>
						<textarea class="form-control" rows="2" placeholder="Remarks"></textarea>
					</div>

					<div class="text-right">
						<button type="button" class="btn btn-primary">Create Admission</button>
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
