
<div class="lead-form active fs-6" data-type="training">
<div class="container-fluid ">
    <!-- ROW 1 -->
    <div class="row mt-1" >
        <!-- Full Name -->
        <div class="col-md-6 col-lg-3">
            <label class="form-label small text-dark required">
                Full Name (As per CNIC)
            </label>
            <input type="text"
                   name="name"
                   class="form-control form-control-sm @error('name') is-invalid @enderror"
                   placeholder="Enter Full Name"
                   value="{{ old('name', $leadPrefill['name'] ?? '') }}">
            @error('name')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <!-- Phone -->
        <div class="col-md-6 col-lg-3">
            <label class="form-label small text-dark required">
                Primary Contact Number
            </label>
            <input type="tel"
                   name="phone"
                   class="form-control form-control-sm @error('phone') is-invalid @enderror"
                   placeholder="0300-0000000"
                   value="{{ old('phone', $leadPrefill['phone'] ?? '') }}">
            @error('phone')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6 col-lg-3">
                <label class="form-label small required">
                    Course Interested
                </label>
                <select name="program_id"
                        class="form-select form-select-sm training-course-select @error('program_id') is-invalid @enderror" 
                        
                        required>
                    <option value="">-Select-</option>
                    @foreach ($programs as $program)
                        <option value="{{ $program->id }}"
                            data-title="{{ $program->title ?? $program->name }}"
                            data-fee="{{ number_format($program->fee) }}"
                            data-duration="{{ $program->duration_weeks / 4 }}"
                            @selected(old('program_id', $leadPrefill['program_id'] ?? null) == $program->id)>
                            {{ $program->title ?? $program->name }}
                        </option>
                    @endforeach
                </select>
                @error('program_id')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>
        
        <div class="col-md-6 col-lg-3 ">
            <label class="form-label text-dark fw-semibold small ">
                Teaching Method <span class="required-feild_symbol">*</span>
            </label>
            <div class="row mt-2 choice-group @error('details.teaching_method') is-invalid @enderror">
                <div class="col-4 d-flex justify-content-center mb-1">
                    <div class="form-check d-flex align-items-center">
                        <input class="form-check-input mt-0 mr-1"
                            type="radio"
                            id="teaching-method-online"
                            name="details[teaching_method]"
                            value="online"
                            @checked(old('details.teaching_method', data_get($leadPrefill, 'details.teaching_method', 'online')) === 'online')>
                        <label class="form-check-label small mb-0"
                            for="teaching-method-online">
                            Online
                        </label>
                    </div>
                </div>
                <div class="col-4 d-flex justify-content-center mb-1">
                    <div class="form-check d-flex align-items-center">
                        <input class="form-check-input mt-0 mr-1"
                            type="radio"
                            id="teaching-method-campus"
                            name="details[teaching_method]"
                            value="campus"
                            @checked(old('details.teaching_method', data_get($leadPrefill, 'details.teaching_method')) === 'campus')>
                        <label class="form-check-label small mb-0"
                            for="teaching-method-campus">
                            Campus
                        </label>
                    </div>
                </div>
                <div class="col-4 d-flex justify-content-center mb-1">
                    <div class="form-check d-flex align-items-center">
                        <input class="form-check-input mt-0 mr-1"
                            type="radio"
                            id="teaching-method-hybrid"
                            name="details[teaching_method]"
                            value="hybrid"
                            @checked(old('details.teaching_method', data_get($leadPrefill, 'details.teaching_method')) === 'hybrid')>
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
    <div class="row mt-2" >
        <!-- Email -->
        <div class="col-md-6 col-lg-3">
            <label class="form-label small fw-semibold text-dark">
                Email Address <span class="required-feild_symbol">*</span>
            </label>
            <input type="email"
                   name="email"
                   class="form-control form-control-sm @error('email') is-invalid @enderror"
                   placeholder="Enter Email"
                   value="{{ old('email', $leadPrefill['email'] ?? '') }}">
            @error('email')
                <div class="field-error">{{ $message }}</div>
            @enderror
        
        </div>
        <div class="col-md-6 col-lg-3">
            <label class="form-label small fw-semibold text-dark required"> Country </label>
            <select id="lead-country-select"
                    name="details[country]"
                    class="form-select form-select-sm @error('details.country') is-invalid @enderror">
            </select>
            @error('details.country')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6 col-lg-3">
            <label class="form-label small fw-semibold text-dark required"> City </label>
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
            <label class="form-label small fw-semibold text-dark required"> Area </label>
            <input type="text" placeholder = "Enter Area Here"
                   name="details[area]"
                   class="form-control form-control-sm @error('details.area') is-invalid @enderror"
                   value="{{ old('details.area', data_get($leadPrefill, 'details.area')) }}"
                   >
            @error('details.area')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <!-- ROW 3 -->
    <div class="row mt-2" style="align-items: flex-start !important;">
        <div class="col-md-6 col-lg-3 mt-lg-1">
            <label class="form-label small fw-semibold text-dark required"> Marketing Source </label>
            <select name="marketing_source"
                    class="form-select form-select-sm @error('marketing_source') is-invalid @enderror">
                <option value="">- Select -</option>
                @foreach($marketingSources as $source)
                    <option value="{{ $source }}"
                        @selected(old('marketing_source', $leadPrefill['marketing_source'] ?? null) == $source)>
                        {{ $source }}
                    </option>
                @endforeach
            </select>
            @error('marketing_source')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
         <div class="col-md-6 col-lg-3 mt-lg-1">
            <label class="form-label small fw-semibold text-dark required"> Origin </label>
            <select name="origin"
                    class="form-select form-select-sm @error('origin') is-invalid @enderror">
                <option value="">- Select -</option>
                @foreach($origins as $origin)
                    <option value="{{ $origin }}"
                        @selected(old('origin', $leadPrefill['origin'] ?? null) == $origin)>
                        {{ $origin }}
                    </option>
                @endforeach
            </select>
            @error('origin')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6 col-lg-3 mt-lg-1">
            <label class="form-label small fw-semibold text-dark required"> Preferred Campus </label>
            <select name="campus_id"
                    class="form-select form-select-sm @error('campus_id') is-invalid @enderror">
                <option value="">-Select-</option>
               

                @foreach($campuses as $campus)
                     <option value="{{ $campus->id }}"
                        @selected(old('campus_id', $leadPrefill['campus_id'] ?? null) == $campus->id)>
                        {{ $campus->name }} ({{ $campus->city }})
                    </option>
                @endforeach
            </select>
            @error('campus_id')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6 col-lg-3 mb-lg-1">
            <label class="form-label text-dark fw-semibold small ">
                Gender <span class="required-feild_symbol">*</span>
            </label>
             <div class="row mt-2 choice-group @error('details.gender') is-invalid @enderror">
                <div class="col-4 d-flex justify-content-center mb-1 mt-1">
                    <div class="form-check d-flex align-items-center mt-0">
                        <input class="form-check-input mt-0 mr-1"
                            type="radio"
                            id="details-gender-male"
                            name="details[gender]"
                            value="male"
                            @checked(old('details.gender', data_get($leadPrefill, 'details.gender', 'male')) === 'male')>
                        <label class="form-check-label small mb-0"
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
                            @checked(old('details.gender', data_get($leadPrefill, 'details.gender')) === 'female')>
                        <label class="form-check-label small mb-0"
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
                            @checked(old('details.gender', data_get($leadPrefill, 'details.gender')) === 'other')>
                        <label class="form-check-label small mb-0"
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

    <!-- Radio Section -->
    <div class="row mt-2" style="align-items: flex-start !important;">
        <div class="col-md-6 col-lg-3">
                <label class="form-label small fw-semibold text-dark required">Next Follow Up </label>
                <input type="datetime-local"
                    name="details[next_followup_at]"
                    class="form-control form-control-sm @error('details.next_followup_at') is-invalid @enderror"
                    value="{{ old('details.next_followup_at', data_get($leadPrefill, 'details.next_followup_at')) }}">
                @error('details.next_followup_at')
                    <div class="field-error">{{ $message }}</div>
                @enderror
        </div>

<div class="col-md-6 col-lg-3">
    <label class="form-label small fw-semibold text-dark required">
        Probability
    </label>

    <!-- Slider -->
    <input type="range"
        name="details[probability]"
        min="0"
        max="100"
        step="5"
        id="probabilitySlider"
        value="{{ old('details.probability', data_get($leadPrefill, 'details.probability', 10)) }}"
        class="custom-range @error('details.probability') is-invalid @enderror">

    <!-- Scale -->
    <div class="range-scale" aria-hidden="true">
        @for ($tick = 0; $tick <= 100; $tick += 5)
            <span
                class="range-tick {{ $tick % 20 === 0 ? 'range-tick-major' : 'range-tick-minor' }}{{ $tick === 0 ? ' range-tick-start' : '' }}{{ $tick === 100 ? ' range-tick-end' : '' }}"
                style="left: {{ $tick }}%;"
            ></span>
        @endfor
    </div>

    <!-- Numbers -->
    <div class="range-numbers text-muted">
        <span>0</span>
        <span>20</span>
        <span>40</span>
        <span>60</span>
        <span>80</span>
        <span>100</span>
    </div>

    <!-- Selected -->
    <div class="  ">
        Selected: <span id="probabilityValue">{{ old('details.probability', data_get($leadPrefill, 'details.probability', 10)) }}%</span>
    </div>
    @error('details.probability')
        <div class="field-error">{{ $message }}</div>
    @enderror
</div>

<style>
.custom-range {
    width: 100%;
}

.custom-range.is-invalid {
    box-shadow: 0 0 0 2px rgba(229, 57, 53, 0.12);
}

.choice-group.is-invalid {
    border: 1px solid #e53935;
    border-radius: 6px;
    margin-left: 0;
    margin-right: 0;
    padding: 4px 0;
}

/* SCALE WITH BIG + SMALL TICKS */
.range-scale {
    position: relative;
    width: calc(100% - 4px);
    height: 12px;
    margin: 1px 2px 0;
}

.range-tick {
    position: absolute;
    top: 0;
    transform: translateX(-50%);
    border-radius: 999px;
}

.range-tick-minor {
    width: 1px;
    height: 7px;
    background: #d0d3d8;
}

.range-tick-major {
    width: 1px;
    height: 10px;
    background: #b8c1cb;
}

.range-tick-start {
    left: 0 !important;
    transform: none;
}

.range-tick-end {
    left: 100% !important;
    transform: translateX(-100%);
}

/* Numbers */
.range-numbers {
    display: flex;
    justify-content: space-between;
    width: calc(100% - 4px);
    margin: 1px 2px 0 !important;
}

/* JS */
</style>

<script>
const slider = document.getElementById("probabilitySlider");
const output = document.getElementById("probabilityValue");

slider.oninput = function () {
    output.innerText = this.value + "%";
};
</script>

    </div>
    <!-- Remarks -->
    <div class="row mt-lg-1">
        <div class="col-12">
            <label class="form-label small fw-semibold text-dark required">
                Remarks
            </label>
        <textarea name="details[remarks]" class="form-control form-control-sm @error('details.remarks') is-invalid @enderror" rows="3" style="width:98.8%; margin-right:10px;" placeholder="Remarks">{{ old('details.remarks', data_get($leadPrefill, 'details.remarks', '')) }}</textarea>
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


.select2-container--arrow .select2-selection--single .select2-selection__rendered, .select2-container--default .select2-selection--single .select2-selection__rendered, .select2-container--white .select2-selection--single .select2-selection__rendered

 {
    border: solid 1px #d8e2e7;
    -webkit-border-radius: .25rem;
    border-radius: .25rem;
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
.training-course-select{
    width: 100%;
    min-width: 0;
    max-width: 100%;
    display: block;
}

.training-course-select + .select2-container {
    width: 100% !important;
}

.training-course-option {
    display: flex;
    flex-direction: column;
    gap: 0px;
    line-height: 1.25;
}

.training-course-option-line {
    display: block;
    white-space: normal;
    margin-bottom: 0px;
}

.training-course-option-line:last-child {
    margin-bottom: 0;
}

.training-course-option-label {
    font-weight: 700 !important;
    color: #54667a;
}

.training-course-option-value {
    color: #343434;
    display: inline;
    white-space: normal;
}

.select2-results__option--highlighted .training-course-option-label,
.select2-results__option--highlighted .training-course-option-value {
    color: inherit;
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
    gap: 5%;
    width: calc(100% - -2px);
    font-size:0.625rem;
    margin: 0.5rem 2px 0;
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
    font-size:12px !important;
    font-weight:bold;
    color: #99a4ac;
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
