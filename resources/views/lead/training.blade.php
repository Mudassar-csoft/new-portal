
<div class="lead-form active fs-6" data-type="training">
<div class="container-fluid ">

    <!-- ROW 1 -->
    <div class="row g-0" style = "gap:18px;">

        <!-- Full Name -->
        <div class="custom-col-3">
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
        <div class="custom-col-3 ml-1">
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
     <div class="custom-col-3">
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
       
         <div class="custom-col-3 ml-2" style = "margin-top:-3px;">
    <label class="form-label text-dark fw-semibold small ">
        Teaching Method <span class="text-danger">*</span>
    </label>

   <div class="form-radio  mt-2 g-5" >

    <div class="teaching-method form-check d-flex align-items-center ">
       
            <input class="form-check-input mt-0 mr-1"
                   type="radio"
                   id="teaching-method-online"
                   name="details[teaching_method]"
                   value="online"
                   
                   @checked(old('details.teaching_method','online')==='online')>
            <label class="form-check-label small mb-0 "
                   for="teaching-method-online">
                Online
            </label>
       
    </div>

    <div class="d-flex justify-content-center teaching-method">
        <div class="form-check d-flex align-items-center ">
            <input class="form-check-input mt-0 mr-1"
                   type="radio"
                   id="teaching-method-campus"
                   name="details[teaching_method]"
                   value="on-campus"
                   @checked(old('details.teaching_method')==='on-campus')>
            <label class="form-check-label small "
                   for="teaching-method-campus">
                On-Campus
            </label>
        </div>
    </div>

    <div class=" d-flex justify-content-center teaching-method">
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
       

    </div>

    <!-- ROW 2 -->
    <div class="row g-0 " style = "gap:18px;padding-left:15px">

        <!-- Email -->
        <div class="custom-col-3">
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




        <div class="custom-col-3">
            <label class="form-label small fw-semibold text-dark required">Country</label>
            <select id="lead-country-select"
                    name="details[country]"
                    class="form-select form-select-sm @error('details.country') is-invalid @enderror">
            </select>
            @error('details.country')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="custom-col-3">
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

         <div class="custom-col-3 ">
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
    <div class="row g-0 mt-1" style = "gap:18px;padding-left:15px">


        <div class="custom-col-3">
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

         <div class="custom-col-3">
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

       

        <div class="custom-col-3">
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


    <div class="custom-col-3">
    <label class="form-label text-dark fw-semibold small mb-3">
        Gender <span class="text-danger">*</span>
    </label>

  
    <div class=" form-radio d-flex align-items-center mt-1  " style="gap:11%;" >

        <div class="form-check d-flex align-items-center gender" >
            <input class="form-check-input mt-0 me-2 mr-1 " type="radio" id="details-gender-male" name="details[gender]" value="male" @checked(old('details.gender','male')==='male')>
            <label class="form-check-label small mb-0" for="details-gender-male">Male</label>
        </div>

        <div class="form-check d-flex align-items-center gender">
            <input class="form-check-input mt-0 me-2 mr-1" type="radio" id="details-gender-female" name="details[gender]" value="female" @checked(old('details.gender')==='female')>
            <label class="form-check-label small mb-0 " for="details-gender-female">Female</label>
        </div>

        <div class="form-check d-flex align-items-center gender">
            <input class="form-check-input mt-0 me-2 mr-1" type="radio" id="details-gender-other" name="details[gender]" value="other" @checked(old('details.gender')==='other')>
            <label class="form-check-label small mb-0" for="details-gender-other">Other</label>
        </div>

    </div>

    <!-- Validation error -->
    @error('details.gender')
        <div class="field-error mt-1">{{ $message }}</div>
    @enderror
</div>



       

    </div>

    <!-- Radio Section -->
    <div class="row g-0 mt-1" style = "gap:18px;
    padding-left:15px">

   
     <div class="custom-col-3">
            <label class="form-label small fw-semibold text-dark required">Next Follow Up</label>
            <input type="datetime-local"
                   name="details[next_followup_at]"
                   class="form-control form-control-sm @error('details.next_followup_at') is-invalid @enderror"
                   value="{{ old('details.next_followup_at') }}">
            @error('details.next_followup_at')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

 <div class="custom-col-3">
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

    <div class="small semibold">
        Selected: <span id="probabilityValue">
            {{ old('details.probability', 10) }}%
        </span>
    </div>
</div>


    <!-- Remarks -->
  
</div>
<div class="row mt-0 mr-1" style = "padding-left:15px">
    <div class="col-12">
        <label class="form-label small fw-semibold text-dark required">
            Remarks
        </label>
       <textarea name="details[remarks]"
    class="form-control form-control-sm @error('details.remarks') is-invalid @enderror"
    rows="3"
    style = "width:98.8%;height:5rem; margin-right:10px;"
    placeholder="Remarks" style= "padding:10px">{{ old('details.remarks', '') }}</textarea>
        @error('details.remarks')
            <div class="field-error">{{ $message }}</div>
        @enderror
    </div>
</div>
</div>

<style>

/* Default Mobile ( <768px ) */
.custom-col-1 
.custom-col-2,
.custom-col-3,
.custom-col-4,
.custom-col-5,
.custom-col-8 {
  flex: 0 0 100%;
  max-width: 100%;
  margin-bottom: 10px;
}
.form-radio{
    flex-direction: column;
}

/* Medium Devices ( ≥768px ) */
@media (min-width: 768px) {
.custom-col-1 {
    flex: 0 0 50%;
    max-width: 50%;
  }
  .custom-col-2 {
    flex: 0 0 16%;
    max-width: 16%;
  }

  .custom-col-3 {
    flex: 0 0 25%;
    max-width: 25%;
  }

  .custom-col-4 {
    flex: 0 0 33.333%;
    max-width: 33.333%;
  }

  .custom-col-5 {
    flex: 0 0 41.666%;
    max-width: 41.666%;
  }

  .custom-col-8 {
    flex: 0 0 66.666%;
    max-width: 66.666%;
  }
  .form-radio{
    gap:2% ;
}

}

/* Large Devices ( ≥992px ) */
@media (min-width: 992px) {
	.custom-col-1 {
    flex: 0 0 25%;
    max-width: 25%;
  }
  .form-radio{
    gap:2% ;
}
  .custom-col-2 { flex: 0 0 14%; max-width: 14%; }

  .custom-col-3 { flex: 0 0 22%; max-width: 22%; }

  .custom-col-4 { flex: 0 0 30%; max-width: 30%; }

  .custom-col-5 { flex: 0 0 38%; max-width: 38%; }

  .custom-col-8 { flex: 0 0 60%; max-width: 60%; }

}

/* Extra Large ( ≥1200px ) */
@media (min-width: 1200px) {
.form-radio{
    gap:2% ;
}
.custom-col-1 { flex: 0 0 10.666%; max-width: 12.666%; }
  .custom-col-2 { flex: 0 0 15.666%; max-width: 18.666%; }

  .custom-col-3 { flex: 0 0 25%; max-width: 23%; }

  .custom-col-4 { flex: 0 0 33.333%; max-width: 33.333%; }

  .custom-col-5 { flex: 0 0 20%; max-width: 20%; }

  .custom-col-8 { flex: 0 0 66.666%; max-width: 66.666%; }

}
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

.form-label {
    font-size: 11px;
    font-weight: 600 ;
    color: #566a7f;
    text-transform: uppercase;
    margin-bottom: 3px;
    
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
    font-size: 12px !important;
    padding: 6px 10px;
    box-sizing: border-box;
    border: 1px solid #ccc;
    border-radius: 4px;
    height: 37px;
    color: #343434;
}

textarea.form-control-sm {
    height: 85px;
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
    font-size: 12px;
    margin-bottom: 0;
    cursor: pointer;
}
.custom-range {
    -webkit-appearance: none;
    width: 100%;
    min-height: 0 !important;
    height: 6px;
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
    font-size: 12px;
    color: #666;
    margin-top: 8px;
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
