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
							<select class="form-control @error('campus_id') is-invalid @enderror" name="campus_id" required>
								<option value="">- Select -</option>
								@foreach($campuses ?? [] as $campus)
									<option value="{{ $campus->id }}" {{ old('campus_id', $lead->campus_id ?? '') == $campus->id ? 'selected' : '' }}>
										{{ $campus->code ?? $campus->name }} - {{ $campus->name }}
									</option>
								@endforeach
							</select>
							@error('campus_id')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="required">Select Program</label>
							<select class="form-control @error('program_id') is-invalid @enderror" name="program_id" required>
								<option value="">- Select -</option>
								@foreach($programs ?? [] as $program)
									<option value="{{ $program->id }}" {{ old('program_id', $lead->program_id ?? '') == $program->id ? 'selected' : '' }}>
										{{ $program->title ?? $program->name }}
									</option>
								@endforeach
							</select>
							@error('program_id')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="required">Select Batch</label>
							<select class="form-control @error('batch_id') is-invalid @enderror" name="batch_id" required>
								<option value="">- Select -</option>
								@foreach($batches ?? [] as $batch)
									<option value="{{ $batch->id }}" {{ old('batch_id') == $batch->id ? 'selected' : '' }}>
										{{ $batch->name ?? $batch->code }}
									</option>
								@endforeach
							</select>
							@error('batch_id')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="required">Student Name (As per CNIC)</label>
							<input type="text" class="form-control @error('student_name') is-invalid @enderror" name="student_name" value="{{ old('student_name', $lead->name ?? '') }}" placeholder="Enter full name" required>
							@error('student_name')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="required">Primary Contact Number</label>
							<input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $lead->phone ?? '') }}" placeholder="03XXXXXXXXX" required>
							@error('phone')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="required">Guardian Name</label>
							<input type="text" class="form-control @error('guardian_name') is-invalid @enderror" name="guardian_name" value="{{ old('guardian_name') }}" placeholder="Enter guardian name" required>
							@error('guardian_name')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="required">Guardian Contact Number</label>
							<input type="text" class="form-control @error('guardian_phone') is-invalid @enderror" name="guardian_phone" value="{{ old('guardian_phone') }}" placeholder="03XXXXXXXXX" required>
							@error('guardian_phone')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="required">National Identity Card (CNIC)</label>
							<input type="text" class="form-control @error('cnic') is-invalid @enderror" name="cnic" value="{{ old('cnic') }}" placeholder="Numbers only" required>
							@error('cnic')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-3">
							<label>Email Address</label>
							<input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $lead->email ?? '') }}" placeholder="Enter email address" required>
							@error('email')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="required">Education</label>
							<input type="text" class="form-control @error('education') is-invalid @enderror" name="education" value="{{ old('education') }}" placeholder="Enter education" required>
							@error('education')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="required">Date of Birth</label>
							<input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" name="date_of_birth" value="{{ old('date_of_birth') }}" required>
							@error('date_of_birth')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="required">Gender</label>
							<div class="mt-1 gender-options @error('gender') is-invalid @enderror">
								<label class="mr-3"><input type="radio" name="gender" value="male" {{ old('gender', 'male') === 'male' ? 'checked' : '' }}> Male</label>
								<label class="mr-3"><input type="radio" name="gender" value="female" {{ old('gender') === 'female' ? 'checked' : '' }}> Female</label>
								<label><input type="radio" name="gender" value="other" {{ old('gender') === 'other' ? 'checked' : '' }}> Other</label>
							</div>
							@error('gender')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="required">Country</label>
							<select class="form-control @error('country') is-invalid @enderror" id="adm-country-select" name="country" required></select>
							@error('country')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="required">City</label>
							<select class="form-control @error('city') is-invalid @enderror" id="adm-city-select" name="city" required>
								<option>Loading...</option>
							</select>
							@error('city')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="required">Area</label>
							<input type="text" class="form-control @error('area') is-invalid @enderror" name="area" value="{{ old('area') }}" placeholder="Enter area" required>
							@error('area')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>

					<div class="form-group">
						<label class="required">Postal Address</label>
						<textarea class="form-control @error('postal_address') is-invalid @enderror" name="postal_address" rows="2" placeholder="Enter complete postal address..." required>{{ old('postal_address') }}</textarea>
						@error('postal_address')
							<div class="field-error">{{ $message }}</div>
						@enderror
					</div>

					<div class="form-row">
						<div class="form-group col-md-3">
							<label>Passport Number (Optional)</label>
							<input type="text" class="form-control @error('passport_number') is-invalid @enderror" name="passport_number" value="{{ old('passport_number') }}" placeholder="Enter passport number">
							@error('passport_number')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="required">Registration Number</label>
							<input type="text" class="form-control @error('registration_number') is-invalid @enderror" name="registration_number" value="{{ old('registration_number') }}" placeholder="Enter registration number" required>
							@error('registration_number')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="required">Roll Number</label>
							<input type="text" class="form-control @error('roll_number') is-invalid @enderror" name="roll_number" value="{{ old('roll_number') }}" placeholder="Enter roll number" required>
							@error('roll_number')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="required">Date of Admission</label>
							<input type="date" class="form-control @error('admission_date') is-invalid @enderror" name="admission_date" value="{{ old('admission_date') }}" required>
							@error('admission_date')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="required">Fee Package</label>
							<input type="number" step="0.01" class="form-control @error('fee_package') is-invalid @enderror" name="fee_package" value="{{ old('fee_package') }}" required>
							@error('fee_package')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="required">Discount Amount</label>
							<input type="number" step="0.01" class="form-control @error('discount_amount') is-invalid @enderror" name="discount_amount" value="{{ old('discount_amount') }}" required>
							@error('discount_amount')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="required">Discount %</label>
							<input type="number" step="0.01" class="form-control @error('discount_percent') is-invalid @enderror" name="discount_percent" value="{{ old('discount_percent') }}" required>
							@error('discount_percent')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-3">
							<label class="required">Discounted Fee</label>
							<input type="number" step="0.01" class="form-control @error('discounted_fee') is-invalid @enderror" name="discounted_fee" value="{{ old('discounted_fee') }}" required>
							@error('discounted_fee')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-3">
							<label class="required">Fee Type</label>
							<div class="mt-1 gender-options @error('fee_type') is-invalid @enderror">
								<label class="mr-3"><input type="radio" name="fee_type" value="full" {{ old('fee_type', 'full') === 'full' ? 'checked' : '' }}> Full Fee</label>
								<label><input type="radio" name="fee_type" value="installments" {{ old('fee_type') === 'installments' ? 'checked' : '' }}> Installments</label>
							</div>
							@error('fee_type')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
						<div class="form-group col-md-9">
							<label class="required">Remarks</label>
							<textarea class="form-control @error('remarks') is-invalid @enderror" name="remarks" rows="2" placeholder="Remarks" required>{{ old('remarks') }}</textarea>
							@error('remarks')
								<div class="field-error">{{ $message }}</div>
							@enderror
						</div>
					</div>

					<div class="form-group">
						<label class="required">Receipt Number</label>
						<input type="text" class="form-control @error('receipt_number') is-invalid @enderror" name="receipt_number" value="{{ old('receipt_number') }}" placeholder="Enter receipt number" required>
						@error('receipt_number')
							<div class="field-error">{{ $message }}</div>
						@enderror
					</div>

					<div class="text-right embed-actions">
						<button type="submit" class="btn btn-primary">Admission Now</button>
						<button type="button" class="btn btn-outline-danger ml-2 embed-cancel">Cancel</button>
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

		.admission-shell {
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
			CountryCityLoader.init('adm-country-select', 'adm-city-select', {
				country: @json(old('country', 'Pakistan')),
				city: @json(old('city', 'Faisalabad'))
			});
		});
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
