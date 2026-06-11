@php
    $studyAbroadGender = ($selectedLeadType ?? 'training') === 'study_abroad'
        ? old('details.gender', data_get($leadPrefill, 'details.gender', 'male'))
        : null;
@endphp
<div id="lead-form-study-abroad" class="lead-form active" data-type="study_abroad">
    <div class="form-row">
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Full Name (As Per CNIC)</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter Full Name" value="{{ old('name', $leadPrefill['name'] ?? '') }}">
            @error('name')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Primary Contact Number</label>
            <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="03000000000" value="{{ old('phone', $leadPrefill['phone'] ?? '') }}">
            @error('phone')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label">Email Adress</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Enter Email" value="{{ old('email', $leadPrefill['email'] ?? '') }}">
            @error('email')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
         <div class="col-md-6 col-lg-3 mb-lg-1">
            <label class="form-label text-dark fw-semibold small ">
                Gender 
                <!-- <span class="required-feild_symbol">*</span> -->
            </label>
             <div class="row mt-2 choice-group @error('details.gender') is-invalid @enderror">
                <div class="col-4 d-flex justify-content-center mb-1">
                    <div class="form-check d-flex align-items-center mt-0">
                        <input class="form-check-input mt-0 mr-1"
                            type="radio"
                            id="details-gender-male"
                            name="details[gender]"
                            value="male"
                           @checked($studyAbroadGender === 'male')>
                        <label class="form-check-label  mb-0 mt-1"
                            for="details-gender-male">
                            Male
                        </label>
                    </div>
                </div>
                <div class="col-4 d-flex justify-content-center">
                    <div class="form-check d-flex align-items-center">
                        <input class="form-check-input mt-0 mr-1"
                            type="radio"
                            id="details-gender-female"
                            name="details[gender]"
                            value="female"
                            @checked($studyAbroadGender === 'female')>
                        <label class="form-check-label  mb-0 mt-1"
                            for="details-gender-female">
                            Female
                        </label>
                    </div>
                </div>
                <div class="col-4 d-flex justify-content-center ">
                    <div class="form-check d-flex align-items-center">
                        <input class="form-check-input mt-0 mr-1"
                            type="radio"
                            id="details-gender-other"
                            name="details[gender]"
                            value="other"
                            @checked($studyAbroadGender === 'other')>
                        <label class="form-check-label  mb-0 mt-1"
                            for="details-gender-other">
                            Other
                        </label>
                    </div>
                </div>
            </div>
            @error('details.gender')
                <div class="field-error mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>
     <div class="form-row">
        
       <!-- <div class="col-md-6 col-lg-3 mb-lg-1">
            <label class="form-label text-dark fw-semibold small ">
                Gender
            </label>
             <div class="row mt-2 choice-group @error('details.gender') is-invalid @enderror">
                <div class="col-4 d-flex justify-content-center mb-1">
                    <div class="form-check d-flex align-items-center mt-0">
                        <input class="form-check-input mt-0 mr-1"
                            type="radio"
                            id="study-abroad-gender-male"
                            name="details[gender]"
                            value="male"
                            @checked($studyAbroadGender === 'male')>
                        <label class="form-check-label small mb-0"
                            for="study-abroad-gender-male">
                            Male
                        </label>
                    </div>
                </div>
                <div class="col-4 d-flex justify-content-center">
                    <div class="form-check d-flex align-items-center">
                        <input class="form-check-input mt-0 mr-1"
                            type="radio"
                            id="study-abroad-gender-female"
                            name="details[gender]"
                            value="female"
                            @checked($studyAbroadGender === 'female')>
                        <label class="form-check-label small mb-0"
                            for="study-abroad-gender-female">
                            Female
                        </label>
                    </div>
                </div>
                <div class="col-4 d-flex justify-content-center ">
                    <div class="form-check d-flex align-items-center">
                        <input class="form-check-input mt-0 mr-1"
                            type="radio"
                            id="study-abroad-gender-other"
                            name="details[gender]"
                            value="other"
                            @checked($studyAbroadGender === 'other')>
                        <label class="form-check-label small mb-0"
                            for="study-abroad-gender-other">
                            Other
                        </label>
                    </div>
                </div>
            </div>
            @error('details.gender')
                <div class="field-error mt-1">{{ $message }}</div>
            @enderror
        </div> -->
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Country</label>
            <select id="study-abroad-country-select" name="details[country]" class="form-control @error('details.country') is-invalid @enderror">
                <option value="">Loading countries...</option>
            </select>
            @error('details.country')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">City</label>
            <select id="study-abroad-city-select" name="city" class="form-control @error('city') is-invalid @enderror">
                <option value="">Loading cities...</option>
            </select>
            @error('city')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
         <div class="form-group col-lg-3 col-md-6">
            <label class="form-label">Area</label>
            <input type="text" name="details[area]" class="form-control @error('details.area') is-invalid @enderror" placeholder="Enter Area" value="{{ old('details.area', data_get($leadPrefill, 'details.area')) }}">
            @error('details.area')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
          <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Date Of Birth</label>
            <input type="datetime-local" name="details[next_followup_at]" class="form-control @error('details.next_followup_at') is-invalid @enderror" value="{{ old('details.next_followup_at', data_get($leadPrefill, 'details.next_followup_at')) }}">
            @error('')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label" for="qualification">Academic Qualification</label>
            <input type="text" name="details[area]" class="form-control @error('details.area') is-invalid @enderror" placeholder="Enter Academic Qualification" value="{{ old('details.area', data_get($leadPrefill, 'details.area')) }}">
 
         </div>
     
         <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Degree Completion Date</label>
            <input type="datetime-local" name="details[next_followup_at]" class="form-control @error('details.next_followup_at') is-invalid @enderror" value="{{ old('details.next_followup_at', data_get($leadPrefill, 'details.next_followup_at')) }}">
            @error('')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
      

      <!-- Academic Performance -->
      <div class="form-group col-lg-3 col-md-6">
        <label class="form-label" for="grades">Academic Grades/CGPA</label>
        <input type="text" name="details[area]" class="form-control @error('details.area') is-invalid @enderror" placeholder="Enter Grades/CGPA" value="{{ old('details.area', data_get($leadPrefill, 'details.area')) }}">

      </div>

      <!-- English Proficiency Tests -->
      <div class="form-group col-lg-3 col-md-6">
             <label class="form-label required">English Proficiency Test(s)</label>

            <select 
                name="details[english_tests][]" 
                class="form-control @error('details.english_tests') is-invalid @enderror"
                multiple
            >
                <option value="IELTS">IELTS</option>
                <option value="TOEFL">TOEFL</option>
                <option value="PTE">PTE</option>
                <option value="Duolingo">Duolingo English Test</option>
                <option value="Cambridge">Cambridge English Test</option>
                <option value="None">Not Attempted Yet</option>
            </select>

        </div>

        <!-- <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Current  School/Uni</label>
            <input type="text" name="details[current_education]" class="form-control @error('details.current_education') is-invalid @enderror" placeholder="Current Education" value="{{ old('details.current_education') }}">
            @error('details.current_education')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Field of Study</label>
            <input type="text" name="details[preferred_country]" class="form-control @error('details.preferred_country') is-invalid @enderror" placeholder="Preferred Country" value="{{ old('details.preferred_country') }}">
            @error('details.preferred_country')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div> -->
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Destination Country</label>
            <input type="text" name="details[preferred_country]" class="form-control @error('details.preferred_country') is-invalid @enderror" placeholder="Preferred Country" value="{{ old('details.preferred_country', data_get($leadPrefill, 'details.preferred_country')) }}">
            @error('details.preferred_country')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Preferred Study Program</label>
            <input type="text" name="details[preferred_study_program]" class="form-control @error('details.preferred_study_program') is-invalid @enderror" placeholder="Preferred Program" value="{{ old('details.preferred_study_program', data_get($leadPrefill, 'details.preferred_study_program')) }}">
            @error('details.preferred_study_program')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <!-- <div class="form-row">
        
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label">Preferred University (Optional)</label>
            <input type="text" name="details[preferred_university]" class="form-control @error('details.preferred_university') is-invalid @enderror" placeholder="Preferred University" value="{{ old('details.preferred_university') }}">
            @error('details.preferred_university')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Marketing Source</label>
            <select name="marketing_source" class="form-control @error('marketing_source') is-invalid @enderror">
                <option value="">- Select -</option>
                @foreach($marketingSources as $source)
                    <option value="{{ $source }}" @selected(old('marketing_source') == $source)>{{ $source }}</option>
                @endforeach
            </select>
            @error('marketing_source')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Origin</label>
            <select name="origin" class="form-control @error('origin') is-invalid @enderror">
                <option value="">- Select -</option>
                @foreach($origins as $origin)
                    <option value="{{ $origin }}" @selected(old('origin') == $origin)>{{ $origin }}</option>
                @endforeach
            </select>
            @error('origin')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        
    </div>
    <div class="form-row">
       
      
        <div class="form-group col-lg-3 col-md-6">
            <label class="form-label required">Probability</label>
            @include('lead.partials.probability_slider', [
                'inputName' => 'details[probability]',
                'value' => old('details.probability', data_get($leadPrefill, 'details.probability', 0)),
                'errorKey' => 'details.probability',
            ])
        </div>
    </div> -->
    <div class="form-row">
        <div class="form-group col-12">
            <label class="form-label required">Remarks</label>
            <textarea name="details[remarks]" class="form-control @error('details.remarks') is-invalid @enderror" rows="3" placeholder="Remarks">{{ old('details.remarks', data_get($leadPrefill, 'details.remarks')) }}</textarea>
            @error('details.remarks')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<style>
#lead-form-study-abroad .form-check-input[type="radio"] {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    width: 13px;
    height: 13px;
    border: 1px solid grey;
    border-radius: 50%;
    outline: none;
    cursor: pointer;
    position: relative;
    background-color: #fff;
    transition: background 0.2s, box-shadow 0.2s;
}

#lead-form-study-abroad .form-check-input[type="radio"]:checked {
    border-color: #00a8ff;
}

#lead-form-study-abroad .form-check-input[type="radio"]:checked::before {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background-color: #00a8ff;
}
</style>
