<div class="lead-form active" data-type="training">
	<div class="form-row">
		<div class="form-group col-md-4">
			<label class="required">Full Name (As Per CNIC)</label>
			<input type="text" class="form-control" placeholder="Enter Full Name">
		</div>
		<div class="form-group col-md-4">
			<label class="required">Primary Contact Number</label>
			<input type="tel" class="form-control" placeholder="0300 0000000">
		</div>
		<div class="form-group col-md-4">
			<label class="required">Course Interested</label>
			<select class="form-control">
				<option value="">-Select-</option>
			</select>
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col-md-4">
			<label>Email Address</label>
			<input type="email" class="form-control" placeholder="Enter Email Address">
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
			<label class="required">Marketing Source</label>
			<select class="form-control">
				<option value="">- Select -</option>
			</select>
		</div>
		<div class="form-group col-md-4">
			<label class="required">Origin</label>
			<select class="form-control">
				<option value="">- Select -</option>
			</select>
		</div>
		<div class="form-group col-md-4">
			<label class="required">Area</label>
			<input type="text" class="form-control" placeholder="Enter Area">
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col-md-4">
			<label class="required">Teaching Method</label>
			<div class="radio-group">
				<label><input type="radio" name="teaching_method" checked> Online</label>
				<label><input type="radio" name="teaching_method"> On-Campus</label>
			</div>
		</div>
		<div class="form-group col-md-4">
			<label class="required">Preferred Campus</label>
			<select class="form-control">
				<option value="">-Select-</option>
			</select>
		</div>
		<div class="form-group col-md-4">
			<label class="required">Gender</label>
			<div class="radio-group">
				<label><input type="radio" name="gender_training" checked> Male</label>
				<label><input type="radio" name="gender_training"> Female</label>
			</div>
		</div>
	</div>
	<div class="form-row align-items-center">
		<div class="form-group col-md-4">
			<label class="required">Next Follow Up</label>
			<input type="datetime-local" class="form-control">
		</div>
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
