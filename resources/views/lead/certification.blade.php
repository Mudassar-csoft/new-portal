<div class="lead-form" data-type="certification">
	<div class="form-row">
		<div class="form-group col-md-4">
			<label class="required">Full Name (As Per CNIC)</label>
			<input type="text" class="form-control" placeholder="Enter Full Name">
		</div>
		<div class="form-group col-md-4">
			<label class="required">Personal Contact Number</label>
			<input type="tel" class="form-control" placeholder="0300 0000000">
		</div>
		<div class="form-group col-md-4">
			<label>Email</label>
			<input type="email" class="form-control" placeholder="Enter Email">
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col-md-4">
			<label class="required">Gender</label>
			<div class="radio-group">
				<label><input type="radio" name="gender_cert" checked> Male</label>
				<label><input type="radio" name="gender_cert"> Female</label>
				<label><input type="radio" name="gender_cert"> Other</label>

			</div>
		</div>
		<div class="form-group col-md-4">
			<label class="required">Organization/Vender</label>
			<input type="text" class="form-control" placeholder="Organization">
		</div>
		<div class="form-group col-md-4">
			<label class="required">Certification Title</label>
			<input type="text" class="form-control" placeholder="Title">
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col-md-4">
			<label>Exam Code</label>
			<input type="text" class="form-control" placeholder="Exam Code">
		</div>
		<div class="form-group col-md-4">
			<label class="required">Training &amp; Exam Booking</label>
			<div class="radio-group">
				<label><input type="radio" name="booking_cert" checked> Training</label>
				<label><input type="radio" name="booking_cert"> Exam</label>
				<label><input type="radio" name="booking_cert"> Both</label>
			</div>
		</div>
		<div class="form-group col-md-4">
			<label class="required">Marketing Source</label>
			<select class="form-control">
				<option value="">- Select -</option>
			</select>
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col-md-4">
			<label class="required">Origin</label>
			<select class="form-control">
				<option value="">- Select -</option>
			</select>
		</div>
		<div class="form-group col-md-4">
			<label class="required">Country</label>
			<input type="text" class="form-control" value="Pakistan">
		</div>
		<div class="form-group col-md-4">
			<label class="required">City</label>
			<select class="form-control">
				<option value="">Select a city</option>
			</select>
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col-md-4">
			<label>Area</label>
			<input type="text" class="form-control" placeholder="Enter Area">
		</div>
		<div class="form-group col-md-4">
			<label>Preferred Campus</label>
			<select class="form-control">
				<option value="">-Select-</option>
			</select>
		</div>
		<div class="form-group col-md-4">
			<label class="required">Next Follow-up</label>
			<input type="datetime-local" class="form-control">
		</div>
	</div>
	<div class="form-row align-items-center">
		<div class="form-group col-md-4">
			<label class="required">Probability</label>
			<input type="range" min="0" max="100" step="5" class="form-control-range probability-range">
			<div class="probability-display">Selected: <span>0%</span></div>
		</div>
	</div>
	<div class="form-group">
		<label class="required">Remarks</label>
		<textarea class="form-control" rows="3" placeholder="Remarks"></textarea>
	</div>
</div>
