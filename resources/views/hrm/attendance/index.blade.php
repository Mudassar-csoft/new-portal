@extends('layouts.theme')

@section('title', 'Attendance')

@section('content')
    @php
        $filters = $filters ?? ['date' => now()->toDateString(), 'campus_id' => null, 'request_status' => null];
    @endphp

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

        <section class="box-typical box-typical-dashboard panel panel-default hrm-card">
            <header class="box-typical-header panel-heading">
                <h3 class="form-label panel-title">Attendance (Daily)</h3>
            </header>
            <div class="box-typical-body panel-body">
                <form method="GET" action="{{ route('hrm.attendance.index') }}" class="mb-3">
                    <div class="form-row mt-2">
                        <div class="form-group col-md-3">
                            <label class="form-label required" >Date</label>
                            <input type="date" class="form-control" name="date" value="{{ $filters['date'] ?? now()->toDateString() }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label class="form-label required" >Campus</label>
                            <select class="form-control" name="campus_id">
                                <option value="">All Campuses</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected(($filters['campus_id'] ?? null) == $campus->id)>
                                        {{ $campus->code }} - {{ $campus->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="form-label required" >Request Status</label>
                            <select class="form-control" name="request_status">
                                <option value="">All</option>
                                @foreach(['pending','approved','rejected'] as $status)
                                    <option value="{{ $status }}" @selected(($filters['request_status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3 leave-button d-flex justify-end ">
                            <button class="btn btn-inline btn-primary-outline m" type="submit">Filter</button>
                            <a href="{{ route('hrm.attendance.index') }}" class="btn btn-inline btn-danger-outline ">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="row" >
                    <div class="col-lg-6"  >
                        <form method="POST" action="{{ route('hrm.attendance.checkin') }}" class="mb-3 hrm-box">
                            @csrf
                            <h5 class="form-label-mute text-muted required" >Check-in</h5>
                            <div class="form-row" style = "gap:0px;padding-left:5px">
                                <div class="form-group col-md-4">
                                    <label class="form-label required" >Employee</label>
                                    <select name="employee_id" class="form-control" required>
                                        <option value="">- Select -</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->employee_code ?: 'EMP' }} - {{ trim($employee->first_name . ' ' . $employee->last_name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="form-label required" >Check-in At</label>
                                    <input type="datetime-local" name="check_in_at" class="form-control">
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="form-label required" >Shift</label>
                                    <select name="shift_id" class="form-control">
                                        <option value="">Auto/None</option>
                                        @foreach($shifts as $shift)
                                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class = "text-right"> 
                            <button class="btn btn-inline btn-primary-outline p-2" type="submit">Save Check-in</button>
                        </div>
                        </form>
                    </div>

                    <div class="col-lg-6" >
                        <form method="POST" action="{{ route('hrm.attendance.import') }}" enctype="multipart/form-data" class="mb-3 hrm-box">
                            @csrf
                            <h5 class="form-label-mute text-muted required" >Biometric / Device Import (Optional)</h5>
                            <div class="form-row" style = "gap:0px;padding-left:5px">
                                <div class="form-group col-md-4">
                                    <label class="form-label required" >Source Name</label>
                                    <input type="text" name="source_name" class="form-control" placeholder="ZKTeco Device A">
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="form-label required" >Remarks</label>
                                    <input type="text" name="remarks" class="form-control">
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="form-label required" >Import File (CSV)</label>
                                    <input type="file" name="import_file" class="form-control-file">
                                </div>
                            </div>
                            <div class = "text-right"> 
                            <button class="btn btn-inline btn-primary-outline p-2 type="submit">Log Import</button>
                        </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-bordered hrm-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Campus</th>
                                <th>Date</th>
                                <th>Shift</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Worked (min)</th>
                                <th>Late (min)</th>
                                <th>Early (min)</th>
                                <th>Status</th>
                                <th>Check-out Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->employee?->employee_code ?: 'EMP' }} - {{ $log->employee?->full_name ?: '-' }}</td>
                                    <td>{{ $log->campus->code ?? '-' }}</td>
                                    <td>{{ optional($log->attendance_date)->format('Y-m-d') }}</td>
                                    <td>{{ $log->shift->name ?? '-' }}</td>
                                    <td>{{ optional($log->check_in_at)->format('Y-m-d H:i') ?: '-' }}</td>
                                    <td>{{ optional($log->check_out_at)->format('Y-m-d H:i') ?: '-' }}</td>
                                    <td>{{ $log->worked_minutes }}</td>
                                    <td>{{ $log->late_minutes }}</td>
                                    <td>{{ $log->early_exit_minutes }}</td>
                                    <td>{{ ucfirst($log->status) }}</td>
                                    <td>
                                        @if(!$log->check_out_at)
                                            <form method="POST" action="{{ route('hrm.attendance.checkout', $log) }}" class="form-inline">
                                                @csrf
                                                <input type="datetime-local" name="check_out_at" class="form-control form-control-sm mr-2">
                                                <button class="btn btn-sm btn-outline-primary" type="submit">Check-out</button>
                                            </form>
                                        @else
                                            <span class="text-muted">Completed</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="11" class="text-center text-muted">No attendance logs found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $logs->links() }}
            </div>
        </section>

        <section class="box-typical box-typical-dashboard panel panel-default hrm-card mt-3">
            <header class="box-typical-header panel-heading">
                <h3 class="panel-title form-label">Manual Attendance Requests</h3>
            </header>
            <div class="box-typical-body panel-body">
                <form method="POST" action="{{ route('hrm.attendance.requests.store') }}" class="mb-3">
                    @csrf
                    <div class="form-row mt-3"  >
                        <div class="form-group col-md-3 col-lg-3">
                            <label class="form-label required" >Employee</label>
                            <select name="employee_id" class="form-control" required>
                                <option value="">- Select -</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_code ?: 'EMP' }} - {{ trim($employee->first_name . ' ' . $employee->last_name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3 col-lg-3">
                            <label class="form-label required" >Type</label>
                            <select name="request_type" class="form-control">
                                <option value="checkin_correction">Check-in</option>
                                <option value="checkout_correction">Check-out</option>
                                <option value="full_day_correction">Full Day</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3 col-lg-2">
                            <label class="form-label required" >Requested In</label>
                            <input type="datetime-local" name="requested_check_in_at" class="form-control">
                        </div>
                        <div class="form-group col-md-3 col-lg-2">
                            <label class="form-label required" >Requested Out</label>
                            <input type="datetime-local" name="requested_check_out_at" class="form-control">
                        </div>
                        <div class="form-group col-md-3 col-lg-2">
                            <label class="form-label required" >Reason</label>
                            <input type="text" name="reason" class="form-control">
                        </div>
                        
                    </div>
                    <div class = "text-right">
                        <button class="btn btn-inline btn-primary-outline  p-2 mr-4" type="submit">Submit Request</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered hrm-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Requested In</th>
                                <th>Requested Out</th>
                                <th>Status</th>
                                <th>Reason</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $requestRow)
                                <tr>
                                    <td>{{ $requestRow->employee?->employee_code ?: 'EMP' }} - {{ $requestRow->employee?->full_name ?: '-' }}</td>
                                    <td>{{ str_replace('_', ' ', ucfirst($requestRow->request_type)) }}</td>
                                    <td>{{ optional($requestRow->requested_check_in_at)->format('Y-m-d H:i') ?: '-' }}</td>
                                    <td>{{ optional($requestRow->requested_check_out_at)->format('Y-m-d H:i') ?: '-' }}</td>
                                    <td>{{ ucfirst($requestRow->status) }}</td>
                                    <td>{{ $requestRow->reason ?: '-' }}</td>
                                    <td>
                                        @if($requestRow->status === 'pending')
                                            <form method="POST" action="{{ route('hrm.attendance.requests.approve', $requestRow) }}" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-success" type="submit">Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('hrm.attendance.requests.reject', $requestRow) }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="rejection_reason" value="Rejected by reviewer">
                                                <button class="btn btn-sm btn-outline-danger" type="submit">Reject</button>
                                            </form>
                                        @else
                                            <span class="text-muted">Processed</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted">No attendance requests found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $requests->links() }}
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
      
        .hrm-shell { padding: 8px 0 16px; }
        .hrm-table thead th { background: #eef2f7; color: #334155; }
        .hrm-box {
            border: 1px solid #e6ebf1;
            border-radius: 8px;
            padding: 10px;
        }
        .leave-button{
                margin: auto;
    padding-top: 33px;
    justify-content: end;
        }
    </style>
@endpush
