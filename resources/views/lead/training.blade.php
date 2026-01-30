<div class="lead-form active" data-type="training">
	<div class="form-row">
		<div class="form-group" style="flex-basis: 100%; text-align: right;">
			@include('lead.partials.action', ['actionId' => 'training-action-dropdown'])
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col-md-4">
			<label class="required">Full Name (As Per CNIC)</label>
			<input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter Full Name" value="{{ old('name') }}">
			@error('name')
				<div class="field-error">{{ $message }}</div>
			@enderror
		</div>
		<div class="form-group col-md-4">
			<label class="required">Primary Contact Number</label>
			<input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="0300 0000000" value="{{ old('phone') }}">
			@error('phone')
				<div class="field-error">{{ $message }}</div>
			@enderror
		</div>
		<div class="form-group col-md-4">
			<label class="form-label semibold text-dark" for="program-dropdown">
				COURSE INTERESTED <span class="required-feild_symbol">*</span>
			</label>
			<select class="form-control @error('program_id') is-invalid @enderror" id="program-dropdown" name="program_id" required>
				<option value="">-Select-</option>
				@foreach ($programs as $program)
					<option value="{{ $program->id }}"
						data-title="{{ $program->title ?? $program->name }}"
						data-fee="{{ number_format($program->fee) }}"
						data-duration="{{ $program->duration_weeks / 4 }}">
						{{ $program->title ?? $program->name }} - Fee: {{ number_format($program->fee) }}
						({{ $program->duration_weeks / 4 }} months)
					</option>
				@endforeach
			</select>
			@error('program_id')
				<div class="field-error">{{ $message }}</div>
			@enderror
			<div class="error-message" id="program_id-error"></div>
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col-md-4">
			<label>Email Address</label>
			<input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Enter Email Address" value="{{ old('email') }}">
			@error('email')
				<div class="field-error">{{ $message }}</div>
			@enderror
		</div>
		<div class="form-group col-md-4">
			<label class="required">Country</label>
			<select class="form-control @error('details.country') is-invalid @enderror" id="lead-country-select" name="details[country]"></select>
			@error('details.country')
				<div class="field-error">{{ $message }}</div>
			@enderror
		</div>
		<div class="form-group col-md-4">
			<label class="required">City</label>
			<select class="form-control @error('city') is-invalid @enderror" id="lead-city-select" name="city">
				<option>Loading...</option>
			</select>
			@error('city')
				<div class="field-error">{{ $message }}</div>
			@enderror
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col-md-4">
			<label class="required">Marketing Source</label>
			<select class="form-control @error('marketing_source') is-invalid @enderror" name="marketing_source">
				<option value="">- Select -</option>
				@foreach($marketingSources as $source)
					<option value="{{ $source }}" @selected(old('marketing_source') == $source)>{{ $source }}</option>
				@endforeach
			</select>
			@error('marketing_source')
				<div class="field-error">{{ $message }}</div>
			@enderror
		</div>
		<div class="form-group col-md-4">
			<label class="required">Origin</label>
			<select class="form-control @error('origin') is-invalid @enderror" name="origin">
				<option value="">- Select -</option>
				@foreach($origins as $origin)
					<option value="{{ $origin }}" @selected(old('origin') == $origin)>{{ $origin }}</option>
				@endforeach
			</select>
			@error('origin')
				<div class="field-error">{{ $message }}</div>
			@enderror
		</div>
		<div class="form-group col-md-4">
			<label class="required">Area</label>
			<input type="text" class="form-control @error('details.area') is-invalid @enderror" name="details[area]" placeholder="Enter Area" value="{{ old('details.area') }}">
			@error('details.area')
				<div class="field-error">{{ $message }}</div>
			@enderror
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col-md-4">
			<label class="required">Teaching Method</label>
			<div class="radio-group @error('details.teaching_method') is-invalid @enderror">
				<label><input type="radio" name="details[teaching_method]" value="online" @checked(old('details.teaching_method', 'online') === 'online')> Online</label>
				<label><input type="radio" name="details[teaching_method]" value="on-campus" @checked(old('details.teaching_method') === 'on-campus')> On-Campus</label>
				<label><input type="radio" name="details[teaching_method]" value="hybrid" @checked(old('details.teaching_method') === 'hybrid')> Hybrid</label>

			</div>
			@error('details.teaching_method')
				<div class="field-error">{{ $message }}</div>
			@enderror
		</div>
		<div class="form-group col-md-4">
			<label class="required">Preferred Campus</label>
			<select class="form-control @error('campus_id') is-invalid @enderror" name="campus_id">
				<option value="">-Select-</option>
				@foreach($campuses as $campus)
					<option value="{{ $campus->id }}" @selected(old('campus_id') == $campus->id)>{{ $campus->name }} ({{ $campus->city }})</option>
				@endforeach
			</select>
			@error('campus_id')
				<div class="field-error">{{ $message }}</div>
			@enderror
		</div>
		<div class="form-group col-md-4">
			<label class="required">Gender</label>
			<div class="radio-group @error('details.gender') is-invalid @enderror">
				<label><input type="radio" name="details[gender]" value="male" @checked(old('details.gender', 'male') === 'male')> Male</label>
				<label><input type="radio" name="details[gender]" value="female" @checked(old('details.gender') === 'female')> Female</label>
				<label><input type="radio" name="details[gender]" value="other" @checked(old('details.gender') === 'other')> Other</label>

			</div>
			@error('details.gender')
				<div class="field-error">{{ $message }}</div>
			@enderror
		</div>
	</div>
	<div class="form-row align-items-center">
		<div class="form-group col-md-4">
			<label class="required">Next Follow Up</label>
			<input type="datetime-local" class="form-control @error('details.next_followup_at') is-invalid @enderror" name="details[next_followup_at]" value="{{ old('details.next_followup_at') }}">
			@error('details.next_followup_at')
				<div class="field-error">{{ $message }}</div>
			@enderror
		</div>
		<div class="form-group col-md-4">
			<label class="required">Probability</label>
			<input type="range" min="0" max="100" step="5" class="form-control-range probability-range @error('details.probability') is-invalid @enderror" name="details[probability]" value="{{ old('details.probability', 0) }}">
			<div class="probability-display">Selected: <span>0%</span></div>
			@error('details.probability')
				<div class="field-error">{{ $message }}</div>
			@enderror
		</div>
	</div>
	<div class="form-group">
		<label class="required">Remarks</label>
		<textarea class="form-control @error('details.remarks') is-invalid @enderror" name="details[remarks]" rows="3" placeholder="Remarks">{{ old('details.remarks') }}</textarea>
		@error('details.remarks')
			<div class="field-error">{{ $message }}</div>
		@enderror
	</div>
</div>
