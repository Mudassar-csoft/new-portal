@php
    $batch = $batch ?? new \App\Models\Batch();
    $sessionOptions = $sessionOptions ?? ['morning' => 'Morning', 'evening' => 'Evening', 'weekend' => 'Weekend'];
    $statusOptions = $statusOptions ?? ['active' => 'Active', 'inactive' => 'Inactive', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
    $selectedSession = old('session', $batch->session ?: 'morning');
    $selectedStatus = old('status', $batch->status ?: 'active');
@endphp

        @include('partials.validation-errors-alert')

<div class="form-row">
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label required">Select Campus</label>
        <select class="form-control" name="campus_id" id="batch-campus" required>
            <option value="">- Select -</option>
            @foreach($campuses as $campus)
                <option value="{{ $campus->id }}" @selected((string) old('campus_id', $batch->campus_id) === (string) $campus->id)>
                    {{ $campus->name }}{{ $campus->code ? ' (' . $campus->code . ')' : '' }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label required">Select Course</label>
        <select class="form-control" name="program_id" id="batch-program" required>
            <option value="">- Select -</option>
            @foreach($programs as $program)
                <option value="{{ $program->id }}" data-code="{{ $program->code }}" @selected((string) old('program_id', $batch->program_id) === (string) $program->id)>
                    {{ $program->title ?? $program->name }}{{ $program->code ? ' (' . $program->code . ')' : '' }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label required">Batch Code</label>
        <input type="text" class="form-control" id="batch-code-preview" value="{{ old('code', $batch->code) }}" readonly>
        <!-- <small class="text-muted">Code is auto-generated. If a duplicate exists, the system adds a suffix automatically.</small> -->
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label required">Batch Name</label>
        <input type="text" class="form-control" name="name" value="{{ old('name', $batch->name) }}" placeholder="e.g. Graphic Design Spring Morning" required>
    </div>
   
</div>

<div class="form-row">
     <div class="form-group col-lg-3 col-md-6">
        <label class="form-label required">Instructor / Teacher</label>
        <input type="text" class="form-control" name="instructor" value="{{ old('instructor', $batch->instructor) }}" placeholder="Enter instructor name" required>
    </div>
    <div class="form-group col-lg-3 col-md-6">
        <label class="form-label">Lab / Room</label>
        <input type="text" class="form-control" name="lab" value="{{ old('lab', $batch->lab) }}" placeholder="e.g. Lab-A / Room 4">
    </div>
    <div class="form-group col-md-3">
        <label class="form-label required">Batch Starting Date</label>
        <input type="date" class="form-control" name="start_date" id="batch-start-date" value="{{ old('start_date', optional($batch->start_date)->format('Y-m-d')) }}" required>
    </div>
    <div class="form-group col-md-3">
        <label class="form-label">Expected Ending Date</label>
        <input type="date" class="form-control" name="end_date" value="{{ old('end_date', optional($batch->end_date)->format('Y-m-d')) }}">
    </div>
      
</div>

<div class="form-row">
     <div class="form-group col-md-6 col-lg-3">
        <label class="form-label required d-block">Batch Session</label>
        <div class="row mt-2 choice-group">
            @foreach($sessionOptions as $key => $label)
                <div class="col-4 d-flex justify-content-center mb-1">
                    <div class="form-check d-flex align-items-center{{ $loop->first ? ' mt-0' : '' }}">
                        <input class="form-check-input mt-0" style = "margin-right: 2px;"   
                            type="radio"
                            name="session"
                            id="batch-session-{{ $key }}"
                            value="{{ $key }}"
                            @checked($selectedSession === $key)>
                        <label class="form-check-label  mb-0 mt-1" for="batch-session-{{ $key }}"  style="font-size: 14px !important;">
                            {{ ($label) }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="form-group col-md-3">
        <label class="form-label required">Batch Start Time</label>
        <input type="time" class="form-control" name="start_time" value="{{ old('start_time', $batch->start_time ? \Carbon\Carbon::parse($batch->start_time)->format('H:i') : null) }}" required>
    </div>
    <div class="form-group col-md-3">
        <label class="form-label required">Batch End Time</label>
        <input type="time" class="form-control" name="end_time" value="{{ old('end_time', $batch->end_time ? \Carbon\Carbon::parse($batch->end_time)->format('H:i') : null) }}" required>
    </div>
   
    <div class="form-group col-md-3">
        <label class="form-label required">Batch Status</label>
        <select class="form-control" name="status" required>
            @foreach($statusOptions as $key => $label)
                <option value="{{ $key }}" @selected($selectedStatus === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-row">
</div>

<div class="form-group">
    <label class="form-label">Remarks</label>
    <textarea class="form-control" name="remarks" rows="3" placeholder="Optional notes, capacity, or schedule remarks">{{ old('remarks', $batch->remarks) }}</textarea>
</div>
