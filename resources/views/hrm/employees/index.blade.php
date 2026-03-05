@extends('layouts.theme')

@section('title', 'Employee Master')

@section('content')
    @php
        $filters = $filters ?? ['campus_id' => null, 'department_id' => null, 'status' => null];
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
                <h3 class="panel-title form-label">Employee Master / Profile</h3>
            </header>
            <div class="box-typical-body panel-body">
                <form method="GET" action="{{ route('hrm.employees.index') }}" class="mb-1">
                    <div class="form-row" style = "gap:18px;padding-left:15px">
                        <div class="form-group custom-col-3">
                            <label class="form-label required">Campus</label>
                            <select name="campus_id" class="form-control">
                                <option value="">All Campuses</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected(($filters['campus_id'] ?? null) == $campus->id)>
                                        {{ $campus->code }} - {{ $campus->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group custom-col-3">
                            <label class="form-label required">Department</label>
                            <select name="department_id" class="form-control">
                                <option value="">All Departments</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" @selected(($filters['department_id'] ?? null) == $department->id)>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group custom-col-3">
                            <label class="form-label required">Status</label>
                            <select name="status" class="form-control">
                                <option value="">All</option>
                                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                            </select>
                        </div>
                        <div>
                        <button type="submit" class="btn btn-inline btn-primary-outline p-2" style=" margin-top: 20px;">Filter</button>
                        <a href="{{ route('hrm.employees.index') }}" class="btn btn-inline btn-danger-outline p-2" style="margin-bottom: 9px; margin-top: 20px;">Reset</a>
                    </div>
                    </div>
                </form>

                <hr>

                <form method="POST" action="{{ route('hrm.employees.store') }}" class="mb-2">
                    @csrf
                    <div class="form-row" style = "gap:7px;padding-left:15px">
                        <div class="form-group custom-col-2">
                            <label class="form-label required">Employee Code</label>
                            <input type="text" name="employee_code" class="form-control" placeholder="Auto if blank">
                        </div>
                        <div class="form-group custom-col-2">
                            <label class="form-label required">First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="form-group custom-col-2">
                            <label class="form-label required">Last Name</label>
                            <input type="text" name="last_name" class="form-control">
                        </div>
                        <div class="form-group custom-col-2">
                            <label class="form-label required">CNIC</label>
                            <input type="text" name="cnic" class="form-control" placeholder="xxxxx-xxxxxxx-x">
                        </div>
                        <div class="form-group custom-col-2">
                            <label class="form-label required">Contact</label>
                            <input type="text" name="contact_no" class="form-control">
                        </div>
                        <div class="form-group custom-col-2">
                            <label class="form-label required">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                    </div>

                    <div class="form-row" style = "gap:7px;padding-left:15px">
                        <div class="form-group custom-col-2">
                            <label class="form-label required">Address</label>
                            <input type="text" name="address" class="form-control">
                        </div>
                        <div class="form-group custom-col-2">
                            <label class="form-label required">Emergency Name</label>
                            <input type="text" name="emergency_contact_name" class="form-control">
                        </div>
                        <div class="form-group custom-col-2">
                            <label class="form-label required">Emergency Phone</label>
                            <input type="text" name="emergency_contact_phone" class="form-control">
                        </div>
                        <div class="form-group custom-col-2">
                            <label class="form-label required">Relation</label>
                            <input type="text" name="emergency_contact_relation" class="form-control">
                        </div>
                        <div class="form-group custom-col-2">
                            <label class="form-label required">Campus</label>
                            <select name="campus_id" class="form-control">
                                <option value="">- Select -</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}">{{ $campus->code }} - {{ $campus->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group custom-col-2">
                            <label class="form-label required">Department</label>
                            <select name="department_id" class="form-control">
                                <option value="">- Select -</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row" style = "gap:7px;padding-left:15px">
                        <div class="form-group custom-col-2">
                            <label class="form-label required">Designation</label>
                            <select name="designation_id" class="form-control">
                                <option value="">- Select -</option>
                                @foreach($designations as $designation)
                                    <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group custom-col-2">
                            <label class="form-label required">Reporting Manager</label>
                            <select name="reporting_manager_id" class="form-control">
                                <option value="">- Select -</option>
                                @foreach($managers as $manager)
                                    <option value="{{ $manager->id }}">{{ $manager->employee_code ?: 'EMP' }} - {{ trim($manager->first_name . ' ' . $manager->last_name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group custom-col-2">
                            <label class="form-label required">System User</label>
                            <select name="user_id" class="form-control">
                                <option value="">- Select -</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group custom-col-2">
                            <label class="form-label required">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control" value="{{ now()->toDateString() }}">
                        </div>
                        <div class="form-group custom-col-2">
                            <label class="form-label required">Employment Type</label>
                            <select name="employment_type" class="form-control">
                                <option value="full_time">Full Time</option>
                                <option value="part_time">Part Time</option>
                                <option value="contract">Contract</option>
                                <option value="intern">Intern</option>
                            </select>
                        </div>
                        <div class="form-group custom-col-2">
                            <label class="form-label required">Status</label>
                            <select name="status" class="form-control">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style = "gap:7px;padding-left:15px">
                        <label class="form-label required">Notes</label>
                        <input type="text" name="notes" class="form-control " style="width:98%">
                    </div>
                    <div class="text-right mr-3">
                    <button class="btn btn-inline btn-primary text-end" type="submit">Save Employee</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered hrm-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>CNIC</th>
                                <th>Contact</th>
                                <th>Campus</th>
                                <th>Department / Designation</th>
                                <th>Manager</th>
                                <th>Joining</th>
                                <th>Status</th>
                                <th>Quick Update</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $employee)
                                <tr>
                                    <td>{{ $employee->employee_code ?: 'N/A' }}</td>
                                    <td>{{ $employee->full_name }}</td>
                                    <td>{{ $employee->cnic ?: '-' }}</td>
                                    <td>{{ $employee->contact_no ?: '-' }}</td>
                                    <td>{{ $employee->campus->code ?? '-' }}</td>
                                    <td>{{ $employee->department->name ?? '-' }} / {{ $employee->designation->name ?? '-' }}</td>
                                    <td>{{ $employee->manager?->full_name ?: '-' }}</td>
                                    <td>{{ optional($employee->joining_date)->format('Y-m-d') ?: '-' }}</td>
                                    <td>
                                        <span class="badge {{ $employee->status === 'active' ? 'badge-success' : 'badge-secondary' }}">
                                            {{ ucfirst($employee->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('hrm.employees.status', $employee) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="status" value="{{ $employee->status === 'active' ? 'inactive' : 'active' }}">
                                            <button class="btn btn-sm btn-outline-primary" type="submit">
                                                {{ $employee->status === 'active' ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted">No employee records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $employees->links() }}
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .hrm-shell { padding: 8px 0 16px; }
        .required::after { content: ' *'; color: #dc2626; }
        .hrm-table thead th { background: #0ea5e9; color: #fff; }
    </style>
@endpush

