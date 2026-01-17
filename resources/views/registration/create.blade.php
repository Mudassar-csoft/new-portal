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
							<select class="form-control" name="campus_id" required>
								<option value="">- Select -</option>
								@foreach($campuses ?? [] as $campus)
									<option value="{{ $campus->id }}" {{ ($lead->campus_id ?? null) === $campus->id ? 'selected' : '' }}>
										{{ $campus->code ?? $campus->name }} - {{ $campus->name }}
									</option>
								@endforeach
							</select>
						</div>
						<div class="form-group col-md-4">
							<label class="required">Full Name (As Per CNIC)</label>
							<input type="text" class="form-control" name="student_name" placeholder="Enter full name" value="{{ $lead->name ?? '' }}" required>
						</div>
						<div class="form-group col-md-4">
							<label class="required">Primary Contact Number</label>
							<input type="text" class="form-control" name="phone" placeholder="03XXXXXXXXX" value="{{ $lead->phone ?? '' }}" required>
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
							<input type="email" class="form-control" name="email" placeholder="Enter email address" value="{{ $lead->email ?? '' }}">
						</div>
						<div class="form-group col-md-4">
							<label class="required">Date of Birth</label>
							<input type="text" class="form-control" placeholder="dd/mm/yyyy">
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-4">
							<label class="required">Gender</label>
							<div class="mt-1 gender-options">
								<label class="mr-3"><input type="radio" name="gender" checked> Male</label>
								<label class="mr-3"><input type="radio" name="gender"> Female</label>
								<label><input type="radio" name="gender"> Other</label>
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
							<select class="form-control" name="program_id" required>
								<option value="">- Select -</option>
								@foreach($programs ?? [] as $program)
									<option value="{{ $program->id }}" {{ ($lead->program_id ?? null) === $program->id ? 'selected' : '' }}>
										{{ $program->title ?? $program->name }}
									</option>
								@endforeach
							</select>
						</div>
						<div class="form-group col-md-6">
							<label>Mode / Shift</label>
							<input type="text" class="form-control" value="Aligned with lead selection" disabled>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-4">
							<label class="required">Admission/Registration Fee</label>
							<input type="number" step="0.01" class="form-control" name="fee" value="2000" readonly>
						</div>
						<div class="form-group col-md-4">
							<label>Discount</label>
							<input type="number" step="0.01" class="form-control" name="discount" value="0" readonly>
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

					<div class="text-right">
						<button type="submit" class="btn btn-primary">Register Now</button>
						<a href="{{ url()->previous() }}" class="btn btn-outline-danger ml-2">Cancel</a>
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

	</style>
@endpush

@push('scripts')
	@include('partials.country_city_script')
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			CountryCityLoader.init('reg-country-select', 'reg-city-select', { country: 'Pakistan', city: 'Faisalabad' });
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
@endpush
