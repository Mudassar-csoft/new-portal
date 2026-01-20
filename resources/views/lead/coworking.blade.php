<div class="lead-form" data-type="coworking">
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
		<!-- <div class="form-group col-md-4">
			<label class="required">Gender</label>
			<div class="radio-group">
				<label><input type="radio" name="gender_cowork" checked> Male</label>
				<label><input type="radio" name="gender_cowork"> Female</label>
				<label><input type="radio" name="gender_cowork"> Other</label>
			</div>
		</div> -->
		<div class="form-group form-group-radios">
    <label class="form-label" id="coworking-gender">
        Gender <span class="color-red">*</span>
    </label>

    <div class="radio">
        <input id="coworking-gender-male"
               name="coworking[gender]"
               data-validation="[NOTEMPTY]"
               data-validation-group="coworking-gender"
               data-validation-message="You must select a gender"
               type="radio"
               value="male">
        <label for="coworking-gender-male">Male</label>
    </div>

    <div class="radio">
        <input id="coworking-gender-female"
               name="coworking[gender]"
               data-validation-group="coworking-gender"
               type="radio"
               value="female">
        <label for="coworking-gender-female">Female</label>
    </div>

    <div class="radio">
        <input id="coworking-gender-other"
               name="coworking[gender]"
               data-validation-group="coworking-gender"
               type="radio"
               value="other">
        <label for="coworking-gender-other">Other</label>
    </div>
</div>

		<div class="form-group col-md-4">
			<label class="required">Business Name</label>
			<input type="text" class="form-control" placeholder="Business Name">
		</div>
		<div class="form-group col-md-4">
			<label class="required">Space Required</label>
			<select class="form-control">
				<option>Dedicated Desk</option>
				<option>Shared Office</option>
				<option>Private Office</option>
				<option>Studio Space</option>
				<option>Meeting Room</option>
				<option>Event Hall</option>
				<option>Virtual Office</option>
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
			<label class="required">Country</label>
			<input type="text" class="form-control" value="Pakistan">
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col-md-4">
			<label class="required">City</label>
			<select class="form-control">
				<option value="">Select a city</option>
			</select>
		</div>
		<div class="form-group col-md-4">
			<label>Area</label>
			<input type="text" class="form-control" placeholder="Enter Area">
		</div>
		<div class="form-group col-md-4">
			<label>Preferred Location</label>
			<input type="text" class="form-control" placeholder="Preferred Location">
		</div>
	</div>
	<div class="form-row align-items-center">
		<div class="form-group col-md-4">
			<label class="required">Next Follow-up</label>
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
