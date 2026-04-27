@extends('layouts.theme')

@section('title', 'HRM Masters')

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
                        <h3 class="panel-title form-label">Departments</h3>
                    </header>
                    <div class="box-typical-body panel-body">
                        <form method="POST" action="{{ route('hrm.masters.departments.store') }}" class="mb-3">
                            @csrf
                            <div class="form-row mt-3" style = "gap:5px;padding-left:15px">
                                <div class="form-group col-md-3   p-0">
                                    <select class="form-control" name="campus_id">
                                        <option value="">All Campuses</option>
                                        @foreach($campuses as $campus)
                                            <option value="{{ $campus->id }}">{{ $campus->code }} - {{ $campus->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3   p-0">
                                    <input type="text" class="form-control" name="name" placeholder="Department Name" required>
                                </div>
                                <div class="form-group col-md-3   p-0">
                                    <select class="form-control" name="status">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2 p-0">
                                    <button class="btn btn-inline btn-primary-outline w-100" style="margin-top:2px;"    type="submit">Save</button>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered hrm-table">
                                <thead><tr><th>Name</th><th>Campus</th><th>Status</th></tr></thead>
                                <tbody>
                                    @forelse($departments as $department)
                                        <tr>
                                            <td>{{ $department->name }}</td>
                                            <td>{{ $department->campus->code ?? 'All' }}</td>
                                            <td>{{ ucfirst($department->status) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted">No departments found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $departments->links() }}
                    </div>
                </section>
            </div>

            <div class="col-lg-6">
                <section class="box-typical box-typical-dashboard panel panel-default hrm-card">
                    <header class="box-typical-header panel-heading"
                    >
                        <h3 class="panel-title form-label">Designations</h3>
                    </header>
                    <div class="box-typical-body panel-body pt-3">
                        <form method="POST" action="{{ route('hrm.masters.designations.store') }}" class="mb-3">
                            @csrf
                            <div class="form-row" style = "gap:18px;padding-left:15px; padding-top:11px;" >
                                <div class="form-group custom-col-4">
                                    <select class="form-control" name="department_id">
                                        <option value="">No Department</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group custom-col-4">
                                    <input type="text" class="form-control" name="name" placeholder="Designation Name" required>
                                </div>
                                <div class="form-group col-md-2 p-0">
                                    <button class="btn btn-inline btn-primary-outline w-100" type="submit">Save</button>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive mt-0"  style = "padding-left:15px">
                            <table class="table table-bordered hrm-table">
                                <thead><tr><th>Name</th><th>Department</th><th>Status</th></tr></thead>
                                <tbody>
                                    @forelse($designations as $designation)
                                        <tr>
                                            <td>{{ $designation->name }}</td>
                                            <td>{{ $designation->department->name ?? '-' }}</td>
                                            <td>{{ ucfirst($designation->status) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted">No designations found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $designations->links() }}
                    </div>
                </section>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <section class="box-typical box-typical-dashboard panel panel-default hrm-card">
                    <header class="box-typical-header panel-heading">
                        <h3 class="panel-title form-label">Leave Types</h3>
                    </header>
                    <div class="box-typical-body panel-body">
                        <form method="POST" action="{{ route('hrm.masters.leave-types.store') }}" class="mb-3">
                            @csrf
                            <div class="form-row mt-3">
                                <div class="form-group col-md-3 p-0"><input type="text" class="form-control" name="name" placeholder="Name" required></div>
                                <div class="form-group col-md-2 p-0"><input type="text" class="form-control" name="code" placeholder="Code"></div>
                                <div class="form-group col-md-3 p-0"><input type="number" step="0.01" min="0" class="form-control" name="annual_quota" placeholder="Quota"></div>
                                <div class="form-group col-md-3 p-0">
                                    <select class="form-control" name="accrual_frequency">
                                        <option value="none">None</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="yearly" selected>Yearly</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group text-right p-0">
                                <button class="btn btn-inline btn-primary-outline " type="submit">Save</button>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered hrm-table">
                                <thead><tr><th>Name</th><th>Code</th><th>Quota</th><th>Paid</th></tr></thead>
                                <tbody>
                                    @forelse($leaveTypes as $leaveType)
                                        <tr>
                                            <td>{{ $leaveType->name }}</td>
                                            <td>{{ $leaveType->code ?: '-' }}</td>
                                            <td>{{ number_format((float) $leaveType->annual_quota, 2) }}</td>
                                            <td>{{ $leaveType->is_paid ? 'Yes' : 'No' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted">No leave types found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $leaveTypes->links() }}
                    </div>
                </section>
            </div>

            <div class="col-lg-6">
                <section class="box-typical box-typical-dashboard panel panel-default hrm-card">
                    <header class="box-typical-header panel-heading">
                        <h3 class="panel-title form-label">Holidays Calendar</h3>
                    </header>
                    <div class="box-typical-body panel-body">
                        <form method="POST" action="{{ route('hrm.masters.holidays.store') }}" class="mb-3">
                            @csrf
                            <div class="form-row mt-3" style="gap:10px; padding-left:15px;">
                                <div class="form-group col-md-3 p-0">
                                    <select class="form-control" name="campus_id">
                                        <option value="">All Campuses</option>
                                        @foreach($campuses as $campus)
                                            <option value="{{ $campus->id }}">{{ $campus->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3 p-0"><input type="text" class="form-control" name="name" placeholder="Holiday Name" required></div>
                                <div class="form-group col-md-2 p-0"><input type="date" class="form-control" name="holiday_date" required></div>
                                <div class="form-group col-md-3 p-0"><input type="text" class="form-control" name="holiday_type" placeholder="Type"></div>
                            </div>
                            <div class="form-group text-right p-0"><button class="btn btn-inline btn-primary-outline" type="submit">Save</button></div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered hrm-table">
                                <thead><tr><th>Date</th><th>Name</th><th>Campus</th><th>Type</th></tr></thead>
                                <tbody>
                                    @forelse($holidays as $holiday)
                                        <tr>
                                            <td>{{ optional($holiday->holiday_date)->format('Y-m-d') }}</td>
                                            <td>{{ $holiday->name }}</td>
                                            <td>{{ $holiday->campus->code ?? 'All' }}</td>
                                            <td>{{ ucfirst($holiday->holiday_type) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted">No holidays found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{ $holidays->links() }}
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        
.box-typical .panel-heading {
    padding: 7px 20px;
}
.bootstrap-table .table td, .fixed-table-body .table td, .table td {
    height: 32px;
}
.table{
    width:97%;
}

        /* .hrm-shell { padding: 8px 0 16px; } */
        .hrm-shell form .form-row {
            gap: 9px !important;
            padding-left: 8px !important;
            padding-right: 8px !important;
        }
        .hrm-shell form .form-row > [class*="col-"],
        .hrm-shell form .form-row > .custom-col-4 {
            padding-left: 4px !important;
            padding-right: 4px !important;
        }
        .hrm-shell .table-responsive {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }
        .hrm-shell > .row {
            margin-left: -6px;
            margin-right: -6px;
        }
        .hrm-shell > .row > [class*="col-"] {
            padding-left: 6px;
            padding-right: 6px;
        }
        .hrm-table th,
        .hrm-table td {
            padding-left: 6px !important;
            padding-right: 6px !important;
        }
        .hrm-table thead th { background: #eef2f7; color: #334155; }
    </style>
@endpush
