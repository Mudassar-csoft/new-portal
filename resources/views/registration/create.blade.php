@extends('layouts.theme')

@section('title', 'Create New Registration')

@section('content')
	<div class="registration-shell">
		<div class="registration-card box-typical box-typical-dashboard panel panel-default">
			<div class="card-body">
				<h3 class="reg-title">Create New Registration <small class="text-muted">(All fields marked with * are required)</small></h3>
				<form>
					<div class="form-row">
						<div class="form-group col-md-4">
							<label class="required">Select Campus</label>
							<select class="form-control">
								<option>- Select -</option>
								<option>CIVTL01</option>
								<option>CIFSD02</option>
								<option>CIFSD04</option>
								<option>CIFSD06</option>
							</select>
						</div>
						<div class="form-group col-md-4">
							<label class="required">Full Name (As Per CNIC)</label>
							<input type="text" class="form-control" placeholder="Enter full name">
						</div>
						<div class="form-group col-md-4">
							<label class="required">Primary Contact Number</label>
							<input type="text" class="form-control" placeholder="03XXXXXXXXX">
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
							<input type="email" class="form-control" placeholder="Enter email address">
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
							<input type="text" class="form-control" placeholder="Enter country">
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-4">
							<label class="required">City</label>
							<input type="text" class="form-control" placeholder="Enter city">
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
						<div class="form-group col-md-3">
							<label class="required">Registration Number</label>
							<input type="text" class="form-control" value="CIVTL01-1225-07">
						</div>
						<div class="form-group col-md-3">
							<label class="required">Date of Registration</label>
							<input type="text" class="form-control" value="27/12/2025">
						</div>
						<div class="form-group col-md-3">
							<label class="required">Registration Fee</label>
							<input type="text" class="form-control" value="2000">
						</div>
						<div class="form-group col-md-3">
							<label class="required">Receipt Number</label>
							<input type="text" class="form-control" value="CIVTL01-1225-000037">
						</div>
					</div>

					<div class="form-group">
						<label class="required">Remarks</label>
						<textarea class="form-control" rows="2" placeholder="Remarks"></textarea>
					</div>

					<div class="text-right">
						<button type="button" class="btn btn-primary">Register Now</button>
						<button type="button" class="btn btn-outline-danger ml-2">Cancel</button>
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
	</style>
@endpush
