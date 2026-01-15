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
		<div class="form-group col-md-4">
			<label class="required">Teaching Method</label>
			<div class="radio-group">
				<label><input type="radio" name="details[teaching_method]" value="online" checked> Online</label>
				<label><input type="radio" name="details[teaching_method]" value="on-campus"> On-Campus</label>
				<label><input type="radio" name="details[teaching_method]" value="hybrid"> Hybrid</label>

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
		<div class="form-group col-md-4">
			<label class="required">Gender</label>
			<div class="radio-group">
				<label><input type="radio" name="details[gender]" value="male" checked> Male</label>
				<label><input type="radio" name="details[gender]" value="female"> Female</label>
				<label><input type="radio" name="details[gender]" value="other"> Other</label>

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
