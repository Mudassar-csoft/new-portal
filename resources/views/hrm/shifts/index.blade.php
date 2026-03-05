@extends('layouts.theme')

@section('title', 'Shift / Timetable')

@section('content')
    <div class="hrm-shell">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-6">
                <section class="box-typical box-typical-dashboard panel panel-default hrm-card">
                    <header class="box-typical-header panel-heading">
                        <h3 class="panel-title form-label">Create Work Shift</h3>
                    </header>
                    <div class="box-typical-body panel-body">
                        <form method="POST" action="{{ route('hrm.shifts.store') }}">
                            @csrf
                            <div class="form-row" style="gap:0px;">
                                <div class="form-group custom-col-3">
                                    <label class="form-label required" >Campus</label>
                                    <select name="campus_id" class="form-control">
                                        <option value="">All Campuses</option>
                                        @foreach($campuses as $campus)
                                            <option value="{{ $campus->id }}">{{ $campus->code }} - {{ $campus->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group custom-col-3">
                                    <label class="form-label required" >Name</label>
                                    <input type="text" name="name" class="form-control" placeholder="Morning Shift" required>
                                </div>
                                <div class="form-group custom-col-3">
                                    <label class="form-label required" >Start</label>
                                    <input type="time" name="start_time" class="form-control" required>
                                </div>
                                <div class="form-group custom-col-3">
                                    <label class="form-label required" >End</label>
                                    <input type="time" name="end_time" class="form-control" required>
                                </div>
                            </div>

                            <div class="form-row" style="gap:0px;">
                                <div class="form-group custom-col-3">
                                    <label class="form-label required" >Grace In (min)</label>
                                    <input type="number" min="0" name="grace_check_in_minutes" class="form-control" value="10">
                                </div>
                                <div class="form-group custom-col-3">
                                    <label class="form-label required" >Grace Out (min)</label>
                                    <input type="number" min="0" name="grace_check_out_minutes" class="form-control" value="10">
                                </div>
                                <div class="form-group custom-col-3">
                                    <label class="form-label required" >Break (min)</label>
                                    <input type="number" min="0" name="break_minutes" class="form-control" value="60">
                                </div>
                                <div class="form-group custom-col-3">
                                    <label class="form-label required" >Flags</label>
                                    <div class="d-flex gap-3 mt-3">
                                    <div class="d-flex align-items-center justify-center">    
                                    <input type="checkbox" name="is_night_shift" value="1">
                                        <label class="ml-1"> Night</label>
                                        </div>
                                        <div class="d-flex align-items-center justify-center">  <input type="checkbox" name="is_active" value="1" checked>
                                        <label class="ml-1"> Active</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right mr-1">
                            <button type="submit" class="btn btn-inline btn-primary-outline">Save Shift</button>
                            </div>
                            </form>

                        

                        <div class="table-responsive mt-3">
                            <table class="table table-bordered hrm-table ">
                                <thead><tr><th>Campus</th><th>Name</th><th>Time</th><th>Night</th><th>Status</th></tr></thead>
                                <tbody>
                                    @forelse($shifts as $shift)
                                        <tr>
                                            <td>{{ $shift->campus->code ?? 'All' }}</td>
                                            <td>{{ $shift->name }}</td>
                                            <td>{{ $shift->start_time }} - {{ $shift->end_time }}</td>
                                            <td>{{ $shift->is_night_shift ? 'Yes' : 'No' }}</td>
                                            <td>{{ $shift->is_active ? 'Active' : 'Inactive' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted">No shifts found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $shifts->links() }}
                    </div>
                </section>
            </div>

            <div class="col-lg-6">
                <section class="box-typical box-typical-dashboard panel panel-default hrm-card">
                    <header class="box-typical-header panel-heading">
                        <h3 class="panel-title form-label">Assign Shift / Rotation</h3>
                    </header>
                    <div class="box-typical-body panel-body">
                        <form method="POST" action="{{ route('hrm.shifts.assignments.store') }}" class="mb-3">
                            @csrf
                            <div class="form-row mt-1" style="gap:0px;">
                                <div class="form-group custom-col-3">
                                    <label class="form-label required" >Employee</label>
                                    <select name="employee_id" class="form-control" required>
                                        <option value="">- Select -</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->employee_code ?: 'EMP' }} - {{ trim($employee->first_name . ' ' . $employee->last_name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group custom-col-3">
                                    <label class="form-label required" >Shift</label>
                                    <select name="shift_id" class="form-control" required>
                                        <option value="">- Select -</option>
                                        @foreach($shifts as $shift)
                                            <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->start_time }}-{{ $shift->end_time }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group custom-col-3">
                                    <label class="form-label required" >Effective From</label>
                                    <input type="date" class="form-control" name="effective_from" value="{{ now()->toDateString() }}" required>
                                </div>
                                <div class="form-group custom-col-3 ">
                                    <label class="form-label required" >Effective To</label>
                                    <input type="date" class="form-control" name="effective_to">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group custom-col-4">
                                    <label class="form-label required" >Off Days</label>
                                    <select name="off_days[]" class="form-control" multiple>
                                        @foreach(['sun','mon','tue','wed','thu','fri','sat'] as $day)
                                            <option value="{{ $day }}">{{ strtoupper($day) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="d-flex custom-col-4" style="gap:2px;margin-top: 8px;">
                                <input type="checkbox" style="margin-top:37px" name="is_rotational" value="1">
                                <label class="form-label required ml-0" style="margin-top:35px;" > Rotational Shift</label>
                                </div>
                            </div>
                            <div class="text-right">
                            <button type="submit" class="btn btn-inline btn-primary-outline">Assign</button>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered hrm-table">
                                <thead><tr><th>Employee</th><th>Shift</th><th>From</th><th>To</th><th>Rotation</th></tr></thead>
                                <tbody>
                                    @forelse($assignments as $assignment)
                                        <tr>
                                            <td>{{ $assignment->employee?->employee_code ?: 'EMP' }} - {{ $assignment->employee?->full_name ?: '-' }}</td>
                                            <td>{{ $assignment->shift?->name ?: '-' }}</td>
                                            <td>{{ optional($assignment->effective_from)->format('Y-m-d') }}</td>
                                            <td>{{ optional($assignment->effective_to)->format('Y-m-d') ?: '-' }}</td>
                                            <td>{{ $assignment->is_rotational ? 'Yes' : 'No' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted">No shift assignments found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $assignments->links() }}
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
       
.selection{
    height: 20px !important;
}
        .hrm-shell { padding: 8px 0 16px; }
        .hrm-table thead th { background: #eef2f7; color: #334155; }
        .gap-3 { gap: 12px; }
    </style>
@endpush

