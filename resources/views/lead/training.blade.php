
<div class="lead-form active fs-6" data-type="training">
<div class="container-fluid ">
    <!-- ROW 1 -->
    <div class="row" >
        <!-- Full Name -->
        <div class="col-md-6 col-lg-3">
            <label class="form-label small text-dark required">
                Full Name (As per CNIC)
            </label>
            <input type="text"
                   name="name"
                   class="form-control form-control-sm @error('name') is-invalid @enderror"
                   placeholder="Enter Full Name"
                   value="{{ old('name') }}">
            @error('name')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <!-- Phone -->
        <div class="col-md-6 col-lg-3">
            <label class="form-label small fw-semibold text-dark required">
                Primary Contact Number
            </label>
            <input type="tel"
                   name="phone"
                   class="form-control form-control-sm @error('phone') is-invalid @enderror"
                   placeholder="0300-0000000"
                   value="{{ old('phone') }}">
            @error('phone')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6 col-lg-3">
                <label class="form-label small fw-semibold text-dark required">
                    Course Interested
                </label>
                <select name="program_id"
                        class="form-select form-select-sm @error('program_id') is-invalid @enderror" 
                        
                        required>
                    <option value="">-Select-</option>
                    @foreach ($programs as $program)
                        <option value="{{ $program->id }}"
                            data-title="{{ $program->title ?? $program->name }}"
                            data-fee="{{ number_format($program->fee) }}"
                            data-duration="{{ $program->duration_weeks / 4 }}"
                            @selected(old('program_id') == $program->id)>
                            {{ $program->title ?? $program->name }}
                            - Fee: {{ number_format($program->fee) }}
                            ({{ $program->duration_weeks / 4 }} months)
                        </option>
                    @endforeach
                </select>
                @error('program_id')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>
        
        <div class="col-md-6 col-lg-3 ">
            <label class="form-label text-dark fw-semibold small ">
                Teaching Method <span class="text-danger">*</span>
            </label>
            <div class="row  mt-2 ml-3" >
                <div class="col-sm-4 d-flex align-items-center mb-1">
                
                        <input class="form-check-input  "
                            type="radio"
                            id="teaching-method-online"
                            name="details[teaching_method]"
                            value="online"
                            
                            @checked(old('details.teaching_method','online')==='online')>
                        <label class="form-check-label small mt-1 ml-1"
                            for="teaching-method-online">
                            Online
                        </label>
                
                </div>
                <div class="d-flex justify-content-center col-sm-4">
                    <div class="form-check d-flex align-items-center ">
                        <input class="form-check-input mt-0 mr-1"
                            type="radio"
                            id="teaching-method-campus"
                            name="details[teaching_method]"
                            value="campus"
                            @checked(old('details.teaching_method')==='on-campus')>
                        <label class="form-check-label small "
                            for="teaching-method-campus">
                            Campus
                        </label>
                    </div>
                </div>
                <div class=" d-flex justify-content-center col-sm-4">
                    <div class="form-check d-flex align-items-center">
                        <input class="form-check-input mt-0  mr-1"
                            type="radio"
                            id="teaching-method-hybrid"
                            name="details[teaching_method]"
                            value="hybrid"
                            @checked(old('details.teaching_method')==='hybrid')>
                        <label class="form-check-label small mb-0"
                            for="teaching-method-hybrid">
                            Hybrid
                        </label>
                    </div>
                </div>

            </div>
            @error('details.teaching_method')
            <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
        <!-- Program -->
    <!-- ROW 2 -->
    <div class="row mt-1" >
        <!-- Email -->
        <div class="col-md-6 col-lg-3">
            <label class="form-label small fw-semibold text-dark">
                Email Address
            </label>
            <input type="email"
                   name="email"
                   class="form-control form-control-sm @error('email') is-invalid @enderror"
                   placeholder="Enter Email"
                   value="{{ old('email') }}">
            @error('email')
                <div class="field-error">{{ $message }}</div>
            @enderror
        
        </div>
        <div class="col-md-6 col-lg-3">
            <label class="form-label small fw-semibold text-dark required">Country</label>
            <select id="lead-country-select"
                    name="details[country]"
                    class="form-select form-select-sm @error('details.country') is-invalid @enderror">
            </select>
            @error('details.country')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6 col-lg-3">
            <label class="form-label small fw-semibold text-dark required">City</label>
            <select id="lead-city-select"
                    name="city"
                    class="form-select form-select-sm @error('city') is-invalid @enderror">
                <option>Loading...</option>
            </select>
            @error('city')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
         <div class="col-md-6 col-lg-3 ">
            <label class="form-label small fw-semibold text-dark required">Area</label>
            <input type="text"
                   name="details[area]"
                   class="form-control form-control-sm @error('details.area') is-invalid @enderror"
                   value="{{ old('details.area') }}"
                   >
            @error('details.area')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <!-- ROW 3 -->
    <div class="row mt-1">
        <div class="col-md-6 col-lg-3">
            <label class="form-label small fw-semibold text-dark required">Marketing Source</label>
            <select name="marketing_source"
                    class="form-select form-select-sm @error('marketing_source') is-invalid @enderror">
                <option value="">- Select -</option>
                @foreach($marketingSources as $source)
                    <option value="{{ $source }}"
                        @selected(old('marketing_source') == $source)>
                        {{ $source }}
                    </option>
                @endforeach
            </select>
            @error('marketing_source')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
         <div class="col-md-6 col-lg-3">
            <label class="form-label small fw-semibold text-dark required">Origin</label>
            <select name="origin"
                    class="form-select form-select-sm @error('origin') is-invalid @enderror">
                <option value="">- Select -</option>
                @foreach($origins as $origin)
                    <option value="{{ $origin }}"
                        @selected(old('origin') == $origin)>
                        {{ $origin }}
                    </option>
                @endforeach
            </select>
            @error('origin')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6 col-lg-3">
            <label class="form-label small fw-semibold text-dark required">Preferred Campus</label>
            <select name="campus_id"
                    class="form-select form-select-sm @error('campus_id') is-invalid @enderror">
                <option value="">-Select-</option>
               

                @foreach($campuses as $campus)
                     <option value="{{ $campus->id }}"
                        @selected(old('campus_id') == $campus->id)>
                        {{ $campus->name }} ({{ $campus->city }})
                    </option>
                @endforeach
            </select>
            @error('campus_id')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6 col-lg-3">
            <label class="form-label text-dark fw-semibold small mt-0">
                Gender <span class="text-danger">*</span>
            </label>
             <div class=" row d-flex align-items-center mt-1 ml-3 ">
                <div class="form-check d-flex align-items-center col-sm-4 " >
                    <input class="form-check-input mt-0  " type="radio" id="details-gender-male" name="details[gender]" value="male" @checked(old('details.gender','male')==='male')>
                    <label class="form-check-label small mb-0 ml-1" for="details-gender-male">Male</label>
                </div>
                <div class="form-check d-flex align-items-center col-sm-4">
                    <input class="form-check-input mt-0 me-2 mr-1" type="radio" id="details-gender-female" name="details[gender]" value="female" @checked(old('details.gender')==='female')>
                    <label class="form-check-label small mb-0 " for="details-gender-female">Female</label>
                </div>
                <div class="form-check d-flex align-items-center col-sm-4">
                    <input class="form-check-input mt-0 me-2 mr-1" type="radio" id="details-gender-other" name="details[gender]" value="other" @checked(old('details.gender')==='other')>
                    <label class="form-check-label small mb-0" for="details-gender-other">Other</label>
                </div>
            </div>
        </div>
    <!-- Validation error -->
    @error('details.gender')
        <div class="field-error mt-1">{{ $message }}</div>
    @enderror
    </div>

    <!-- Radio Section -->
    <div class="row ">
        <div class="col-md-6 col-lg-3">
                <label class="form-label small fw-semibold text-dark required">Next Follow Up</label>
                <input type="datetime-local"
                    name="details[next_followup_at]"
                    class="form-control form-control-sm @error('details.next_followup_at') is-invalid @enderror"
                    value="{{ old('details.next_followup_at') }}">
                @error('details.next_followup_at')
                    <div class="field-error">{{ $message }}</div>
                @enderror
        </div>
        <div class="col-md-6 col-lg-3 mt-4">
            <label class="form-label small fw-semibold text-dark required">
                Probability
            </label>
            <input type="range"
                min="0"
                max="100"
                step="5"
                id="probabilitySlider"
                name="details[probability]"
                value="{{ old('details.probability', 10) }}"
                class="custom-range">

            <div class="range-numbers pt-0 mt-1.5">
                <span>0</span>
                <span>10</span>
                <span>20</span>
                <span>40</span>
                <span>60</span>
                <span>80</span>
                <span>100</span>
            </div>
            <div class=" semibold">
                Selected: <span id="probabilityValue">
                    {{ old('details.probability', 10) }}%
                </span>
            </div>
        </div>
    </div>
    <!-- Remarks -->
    <div class="row">
        <div class="col-12">
            <label class="form-label small fw-semibold text-dark required">
                Remarks
            </label>
        <textarea name="details[remarks]" class="form-control form-control-sm @error('details.remarks') is-invalid @enderror" rows="3"  style = "width:98.8%;height:5rem; margin-right:10px;"placeholder="Remarks" style= "padding:10px">{{ old('details.remarks', '') }}</textarea>
            @error('details.remarks')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
<style>
.form-radio{
    display: flex !important;
    flex-direction: row;
    
}
.lead-form {
  line-height: 1.2;
}

.lead-form .form-control {
  padding: 4px 8px;
}
.radio-group{
display:flex;
flex-direction:column;
gap:.5rem;
}

.form-label{
font-size:12px !important;
font-weight:600;
text-transform:uppercase;
margin-bottom:0.1875rem;
color:#535558 !important;
}
.select2-container--arrow .select2-selection--single .select2-selection__rendered, .select2-container--default .select2-selection--single .select2-selection__rendered, .select2-container--white .select2-selection--single .select2-selection__rendered

 {
    border: solid 1px #d8e2e7;
    -webkit-border-radius: .25rem;
    border-radius: .25rem;
    font-size: 12px !important;
    line-height: 2 !important;
    color: #343434;
    padding: .300rem 25px .300rem 1rem;
    height: 32px !important;
    background: #fff;
}
/* ---------- Input, Select, Textarea ---------- */
input.form-control-sm,
select.form-select-sm,
textarea.form-control-sm {
    font-size:0.75rem;
padding:0.375rem 0.625rem;
border:1px solid #ccc;
border-radius:0.25rem;
height:2.25rem;
    color: #343434;
}

textarea.form-control-sm {
    height:5rem;
    resize: vertical;
}

/* ---------- Radio Buttons ---------- */

.lead-form input[type="range"] {
    min-height: 0 !important;
    height: auto !important;
}

.form-check-input[type="radio"] {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    width: 14px;
    height: 14px;
    border: 2px solid grey;
    border-radius: 50%;
    outline: none;
    cursor: pointer;
    position: relative;
    background-color: #fff;
    transition: background 0.2s, box-shadow 0.2s;
}

.form-check-input[type="radio"]:checked {
    border-color: #00a8ff;
}

.form-check-input[type="radio"]:checked::before {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background-color: #00a8ff;
}

.form-check-label {
   font-size:.75rem;
    margin-bottom: 0;
    cursor: pointer;
}
.custom-range {
    -webkit-appearance: none;
    width: 100%;
    
   height:0.375rem;
    border-radius: 4px;
    background: #ddd;
    outline: none;
}

/* Webkit Track */
.custom-range::-webkit-slider-runnable-track {
    height: 6px;
    border-radius: 4px;
}

/* Webkit Thumb */
.custom-range::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 22px;
    height: 22px;
    background: #1e88e5;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 4px rgba(0,0,0,0.3);
    cursor: pointer;
    margin-top: -8px;
}

/* Firefox Thumb */
.custom-range::-moz-range-thumb {
    width: 22px;
    height: 22px;
    background: #1e88e5;
    border-radius: 50%;
    border: 3px solid #fff;
    cursor: pointer;
}

/* Numbers Below */
.range-numbers {
    display: flex;
    justify-content: space-between;
    font-size:0.625rem;
margin-top:0.5rem;
    color: #666;
    
}


/* ---------- Row & Column Spacing ---------- */
.row.g-3 > [class*="col-"] {
    display: flex;
    flex-direction: column;
    padding: 0;
}

.col-3.d-flex.justify-content-center,
.col-6.d-flex.justify-content-center {
    justify-content: start; 
}

/* Small tweak for slider label */
input[name="details[probability]"] + .small {
    margin-top: 0px;
    font-size: 12px;
}
.range-numbers span{
    font-size:10px;
    margin-bottom: 3px ;
}

/* Textarea Responsive */
textarea.form-control-sm {
    min-height: 40px;
    resize: vertical;
}

@media (max-width: 768px) {
    textarea.form-control-sm {
        min-height: 60px;
    }
    
    .radio-group{
        flex-direction:row;
        flex-wrap:wrap;
        gap:1rem;
    }
}

@media (min-width:1200px){

.radio-group{
gap:1.5rem;
}

}


</style>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const slider = document.getElementById("probabilitySlider");
    const output = document.getElementById("probabilityValue");
    if (!slider || !output) {
        return;
    }

    function updateSlider() {
        const value = slider.value;
        const percent = (value - slider.min) / (slider.max - slider.min) * 100;

        slider.style.background =
            `linear-gradient(to right, #1e88e5 0%, #1e88e5 ${percent}%, #ddd ${percent}%, #ddd 100%)`;

        output.textContent = value + "%";
    }

    updateSlider();
    slider.addEventListener("input", updateSlider);
});


</script>
