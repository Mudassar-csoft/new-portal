@extends('layouts.theme')

@section('title', 'Create New Registration')

@section('content')
	<div class="registration-shell">
		<div class="registration-card box-typical box-typical-dashboard panel panel-default">
			<div class="card-body">
				<h3 class="reg-title">Create New Registration <small class="text-muted">(All fields marked with * are required)</small></h3>
				<form method="POST" action="{{ route('registration.store') }}">
					@csrf
					@if(!empty($lead))
						<input type="hidden" name="lead_id" value="{{ $lead->id }}">
					@endif
					<div class="form-row">
						<div class="form-group col-md-4">
							<label class="required">Select Campus</label>
							<select class="form-control @error('campus_id') is-invalid @enderror" name="campus_id" required>
								<option value="">- Select -</option>
								@foreach($campuses ?? [] as $campus)
									<option value="{{ $campus->id }}" {{ old('campus_id', $lead->campus_id ?? null) == $campus->id ? 'selected' : '' }}>
										{{ $campus->code ?? $campus->name }} - {{ $campus->name }}
									</option>
								@endforeach
							</select>
							@error('campus_id')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-4">
							<label class="required">Full Name (As Per CNIC)</label>
							<input type="text" class="form-control @error('student_name') is-invalid @enderror" name="student_name" placeholder="Enter full name" value="{{ old('student_name', $lead->name ?? '') }}" required>
							@error('student_name')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-4">
							<label class="required">Primary Contact Number</label>
							<input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" placeholder="03XXXXXXXXX" value="{{ old('phone', $lead->phone ?? '') }}" required>
							@error('phone')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-4">
							<label class="required">Guardian Name</label>
							<input type="text" class="form-control" placeholder="Enter guardian name">
						</div>
						<div class="form-group col-md-4">
							<label class="required">Guardian Contact Number</label>
							<input type="text" class="form-control" placeholder="03XXXXXXXXX">
						</div>
						<div class="form-group col-md-4">
							<label class="required">National Identity Card (CNIC)</label>
							<input type="text" class="form-control" placeholder="Numbers only">
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-4">
							<label>Passport Number (Optional)</label>
							<input type="text" class="form-control" placeholder="Enter passport number">
						</div>
						<div class="form-group col-md-4">
							<label class="required">Email Address</label>
							<input type="email" class="form-control @error('email') is-invalid @enderror" name="email" placeholder="Enter email address" value="{{ old('email', $lead->email ?? '') }}">
							@error('email')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-4">
							<label class="required">Date of Birth</label>
							<input type="date" class="form-control" placeholder="dd/mm/yyyy">
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-4">
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
						</div>
						<div class="form-group col-md-4">
							<label class="required">Current Education Level</label>
							<input type="text" class="form-control" placeholder="Enter recent completed degree">
						</div>
						<div class="form-group col-md-4">
							<label class="required">Country</label>
							<select class="form-control" id="reg-country-select"></select>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-4">
							<label class="required">City</label>
							<select class="form-control" id="reg-city-select">
								<option>Loading...</option>
							</select>
						</div>
						<div class="form-group col-md-4">
							<label class="required">Area</label>
							<input type="text" class="form-control" placeholder="Enter area">
						</div>
						<div class="form-group col-md-4">
							<label class="required">Personal Contact Number</label>
							<input type="text" class="form-control" placeholder="03XXXXXXXXX">
						</div>
					</div>

					<div class="form-group">
						<label class="required">Postal Address</label>
						<textarea class="form-control" rows="2" placeholder="Enter complete postal address..."></textarea>
					</div>

					<hr>

					<div class="form-row">
						<div class="form-group col-md-6">
							<label class="required">Program</label>
							<select class="form-control @error('program_id') is-invalid @enderror" name="program_id" required>
								<option value="">- Select -</option>
								@foreach($programs ?? [] as $program)
									<option value="{{ $program->id }}" {{ old('program_id', $lead->program_id ?? null) == $program->id ? 'selected' : '' }}>
										{{ $program->title ?? $program->name }}
									</option>
								@endforeach
							</select>
							@error('program_id')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-6">
							<label>Mode / Shift</label>
							<input type="text" class="form-control" value="Aligned with lead selection" disabled>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-4">
							<label class="required">Admission/Registration Fee</label>
							<input type="number" step="0.01" class="form-control @error('fee') is-invalid @enderror" name="fee" value="2000" readonly>
							@error('fee')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-4">
							<label>Discount</label>
							<input type="number" step="0.01" class="form-control @error('discount') is-invalid @enderror" name="discount" value="0" readonly>
							@error('discount')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-4">
							<label>Net Payable (After Discount)</label>
							<input type="text" class="form-control" id="net-payable" value="2000" readonly>
						</div>
					</div>

					<hr>

					<div class="form-row">
						<div class="form-group col-md-4">
							<label class="required">Registration Number</label>
							<input type="text" class="form-control" id="reg-number" value="{{ $preview['registration_number'] ?? '' }}" disabled>
						</div>
						<div class="form-group col-md-4">
							<label class="required">Date of Registration</label>
							<input type="text" class="form-control" value="{{ now()->format('d/m/Y') }}" disabled>
						</div>
						<div class="form-group col-md-4">
							<label class="required">Receipt Number</label>
							<input type="text" class="form-control" id="receipt-number" value="{{ $preview['receipt_number'] ?? '' }}" disabled>
						</div>
					</div>

					<div class="form-group">
						<label>Remarks</label>
						<textarea class="form-control" rows="2" placeholder="Remarks"></textarea>
					</div>

					<div class="text-right embed-actions">
						<button type="submit" class="btn btn-primary">Register Now</button>
						<a href="{{ url()->previous() }}" class="btn btn-outline-danger ml-2 embed-cancel">Cancel</a>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection

@push('styles')
	<style>
		.registration-shell {
			padding: 8px 0 16px;
		}

		.registration-card {
			border: 1px solid #e6ecf2;
			border-radius: 8px;
			box-shadow: 0 6px 18px rgba(17, 24, 39, 0.06);
		}

		.reg-title {
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

		.embed-actions .btn-primary {
			color: #0099f8 !important;
			background: transparent;
			border-color: #0099f8;
		}

		.embed-actions .btn-primary:hover,
		.embed-actions .btn-primary:focus {
			color: #fff !important;
			background: #0099f8;
			border-color: #0099f8;
		}

		.embed-actions .btn-outline-danger {
			color: #dc3545 !important;
			border-color: #dc3545;
			background: transparent;
		}

		.embed-actions .btn-outline-danger:hover,
		.embed-actions .btn-outline-danger:focus {
			color: #fff !important;
			background: #dc3545;
			border-color: #dc3545;
		}

		.btn-primary {
			background: #0099f8;
			border-color: #0099f8;
		}

		.btn-primary:hover,
		.btn-primary:focus {
			background: #0086d8;
			border-color: #0086d8;
		}

		.btn-outline-danger:hover,
		.btn-outline-danger:focus {
			background: #dc3545;
			border-color: #dc3545;
			color: #fff;
		}
		/* FIX LEFT SPACING ON RESPONSIVE */
@media (max-width: 992px) {
    .registration-shell {
        padding-left: 15px;
        padding-right: 15px;
    }
}

@media (max-width: 576px) {
    .registration-shell {
        padding-left: 20px;
        padding-right: 20px;
    }
}

@if(request()->boolean('embed'))
		.site-header,
		.side-menu,
		.taskbar,
		.control-panel-container {
			display: none !important;
		}

		.with-side-menu .page-content {
			margin-left: 0 !important;
			padding: 24px !important;
		}

		.page-content > .container-fluid {
			max-width: 100% !important;
			padding: 0 !important;
		}

		.registration-shell {
			padding: 0 !important;
		}

		.embed-cancel {
			display: inline-block;
		}
@endif
	</style>
@endpush

@push('scripts')
	@include('partials.country_city_script')
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			CountryCityLoader.init('reg-country-select', 'reg-city-select', {
				country: @json(old('country', 'Pakistan')),
				city: @json(old('city', 'Faisalabad'))
			});
		});
	</script>
	<script>
		(function() {
			var campusSelect = document.querySelector('select[name="campus_id"]');
			var regField = document.getElementById('reg-number');
			var receiptField = document.getElementById('receipt-number');
			var netField = document.getElementById('net-payable');

			function updateNumbers() {
				if (!campusSelect || !campusSelect.value) return;
				fetch('{{ route('registration.preview') }}?campus_id=' + campusSelect.value)
					.then(function (res) { return res.json(); })
					.then(function (data) {
						if (regField && data.registration_number) regField.value = data.registration_number;
						if (receiptField && data.receipt_number) receiptField.value = data.receipt_number;
					})
					.catch(function () {});
			}

			if (campusSelect) {
				campusSelect.addEventListener('change', updateNumbers);
				updateNumbers();
			}

			if (netField) {
				netField.value = '2000';
			}
		})();
	</script>
	@if(request()->boolean('embed'))
	<script>
		(function () {
			document.querySelectorAll('.embed-cancel').forEach(function (btn) {
				btn.addEventListener('click', function (event) {
					event.preventDefault();
					if (window.parent) {
						window.parent.postMessage({ type: 'lead-modal-close' }, '*');
					}
				});
			});
		})();
	</script>
	@endif
@endpush
