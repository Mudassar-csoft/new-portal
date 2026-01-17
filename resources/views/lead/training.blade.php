<div class="lead-form active" data-type="training">
	<div class="form-row">
		<div class="form-group col-md-4">
			<label class="required">Full Name (As Per CNIC)</label>
			<input type="text" name="name" class="form-control" placeholder="Enter Full Name">
		</div>
		<div class="form-group col-md-4">
			<label class="required">Primary Contact Number</label>
			<input type="tel" name="phone" class="form-control" placeholder="0300 0000000">
		</div>
		<div class="form-group col-md-4">
			<label class="required">Course Interested</label>
			<select class="form-control" name="program_id" required>
				<option value="">-Select-</option>
				@foreach($programs as $program)
					<option value="{{ $program->id }}">
						{{ $program->title ?? $program->name }}
						@if(!is_null($program->fee)) - Fee: {{ number_format($program->fee, 0) }} @endif
						@if(!is_null($program->duration_weeks)) (Duration: {{ $program->duration_weeks }} weeks) @endif
					</option>
				@endforeach
			</select>
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col-md-4">
			<label>Email Address</label>
			<input type="email" name="email" class="form-control" placeholder="Enter Email Address">
		</div>
		<div class="form-group col-md-4">
			<label class="required">Country</label>
			<select class="form-control" id="lead-country-select" name="details[country]"></select>
		</div>
		<div class="form-group col-md-4">
			<label class="required">City</label>
			<select class="form-control" id="lead-city-select" name="city">
				<option>Loading...</option>
			</select>
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col-md-4">
			<label class="required">Marketing Source</label>
			<select class="form-control" name="marketing_source">
				<option value="">- Select -</option>
				@foreach($marketingSources as $source)
					<option value="{{ $source }}">{{ $source }}</option>
				@endforeach
			</select>
		</div>
		<div class="form-group col-md-4">
			<label class="required">Origin</label>
			<select class="form-control" name="origin">
				<option value="">- Select -</option>
				@foreach($origins as $origin)
					<option value="{{ $origin }}">{{ $origin }}</option>
				@endforeach
			</select>
		</div>
		<div class="form-group col-md-4">
			<label class="required">Area</label>
			<input type="text" class="form-control" name="details[area]" placeholder="Enter Area">
		</div>
	</div>
	<div class="form-row">
		<!-- <div class="form-group col-md-4">
			<label class="required">Teaching Method</label>
			<div class="radio-group">
				<label><input type="radio" name="details[teaching_method]" value="online" checked> Online</label>
				<label><input type="radio" name="details[teaching_method]" value="on-campus"> On-Campus</label>
				<label><input type="radio" name="details[teaching_method]" value="hybrid"> Hybrid</label>

			</div>
		</div> -->
		<div class="form-group form-group-radios">
    <label class="form-label">
        Teaching Method <span class="color-red">*</span>
    </label>

    <div class="radio">
        <input id="teaching-method-online"
               name="details[teaching_method]"
               data-validation="[NOTEMPTY]"
               data-validation-group="teaching-method"
               data-validation-message="You must select a teaching method"
               type="radio"
               value="online"
               checked>
        <label for="teaching-method-online">Online</label>
    </div>

    <div class="radio">
        <input id="teaching-method-oncampus"
               name="details[teaching_method]"
               data-validation-group="teaching-method"
               type="radio"
               value="on-campus">
        <label for="teaching-method-oncampus">On-Campus</label>
    </div>

    <div class="radio">
        <input id="teaching-method-hybrid"
               name="details[teaching_method]"
               data-validation-group="teaching-method"
               type="radio"
               value="hybrid">
        <label for="teaching-method-hybrid">Hybrid</label>
    </div>
</div>

		<div class="form-group col-md-4">
			<label class="required">Preferred Campus</label>
			<select class="form-control" name="campus_id">
				<option value="">-Select-</option>
				@foreach($campuses as $campus)
					<option value="{{ $campus->id }}">{{ $campus->name }} ({{ $campus->city }})</option>
				@endforeach
			</select>
		</div>
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
	<div class="form-row align-items-center">
		<div class="form-group col-md-4">
			<label class="required">Next Follow Up</label>
			<input type="datetime-local" class="form-control" name="details[next_followup_at]">
		</div>
		<div class="form-group col-md-4">
			<label class="required">Probability</label>
			<input type="range" min="0" max="100" step="5" class="form-control-range probability-range" name="details[probability]" value="0">
			<div class="probability-display">Selected: <span>0%</span></div>
		</div>
	</div>
	<div class="form-group">
		<label class="required">Remarks</label>
		<textarea class="form-control" name="details[remarks]" rows="3" placeholder="Remarks"></textarea>
	</div>
</div>
