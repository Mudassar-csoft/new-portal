@extends('layouts.theme')

@section('title', 'Employee Master')

@section('content')
    @php
        $filters = $filters ?? ['campus_id' => null, 'department_id' => null, 'status' => null, 'qualification' => null];
        $employmentTypes = [
            'full_time' => 'Full Time',
            'part_time' => 'Part Time',
            'contract' => 'Contract',
            'intern' => 'Intern',
        ];
        $canAdminEdit = auth()->user()?->isAdmin() ?? false;
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
                <form method="POST" action="{{ route('hrm.employees.store') }}" class="mb-2">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-4 col-lg-3">
                            <label class="form-label">Employee Code</label>
                            <input type="text" class="form-control" value="Auto-generated on save" readonly disabled>
                        </div>
                        <div class="form-group col-md-4 col-lg-3">
                            <label class="form-label required">First Name</label>
                            <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                        </div>
                        <div class="form-group col-md-4 col-lg-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}">
                        </div>
                        <div class="form-group col-md-4 col-lg-3">
                            <label class="form-label">CNIC</label>
                            <input type="text" name="cnic" class="form-control" value="{{ old('cnic') }}" placeholder="xxxxx-xxxxxxx-x">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4 col-lg-3">
                            <label class="form-label">Contact</label>
                            <input type="text" name="contact_no" class="form-control" value="{{ old('contact_no') }}">
                        </div>
                        <div class="form-group col-md-4 col-lg-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                        </div>
                        <div class="form-group col-md-4 col-lg-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                        </div>
                        <div class="form-group col-md-4 col-lg-3">
                            <label class="form-label">Emergency Name</label>
                            <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name') }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4 col-lg-3">
                            <label class="form-label">Emergency Phone</label>
                            <input type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone') }}">
                        </div>
                        <div class="form-group col-md-4 col-lg-3">
                            <label class="form-label">Relation</label>
                            <input type="text" name="emergency_contact_relation" class="form-control" value="{{ old('emergency_contact_relation') }}">
                        </div>
                        <div class="form-group col-md-4 col-lg-3">
                            <label class="form-label required">Campus</label>
                            <select name="campus_id" class="form-control" required>
                                <option value="">- Select -</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" @selected((string) old('campus_id') === (string) $campus->id)>{{ $campus->code }} - {{ $campus->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4 col-lg-3">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-control">
                                <option value="">- Select -</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" @selected((string) old('department_id') === (string) $department->id)>{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4 col-lg-3">
                            <label class="form-label">Designation</label>
                            <select name="designation_id" class="form-control">
                                <option value="">- Select -</option>
                                @foreach($designations as $designation)
                                    <option value="{{ $designation->id }}" @selected((string) old('designation_id') === (string) $designation->id)>{{ $designation->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4 col-lg-3">
                            <div class="d-flex align-items-center flex-wrap" style="gap: 12px; min-height: 38px;">
                                <label class="form-label required mb-0">Portal User</label>
                                <label class="mb-0">
                                    <input type="radio" name="portal_user" value="1" @checked((string) old('portal_user', '0') === '1') required> Yes
                                </label>
                                <label class="mb-0">
                                    <input type="radio" name="portal_user" value="0" @checked((string) old('portal_user', '0') === '0') required> No
                                </label>
                            </div>
                        </div>
                        <div class="form-group col-md-4 col-lg-3">
                            <label class="form-label required">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', now()->toDateString()) }}" required>
                        </div>
                        <div class="form-group col-md-4 col-lg-3">
                            <label class="form-label">Qualification</label>
                            <input type="text" name="qualification" class="form-control" value="{{ old('qualification') }}" placeholder="Enter qualification">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4 col-lg-3">
                            <label class="form-label required">Employment Type</label>
                            <select name="employment_type" class="form-control">
                                @foreach($employmentTypes as $typeValue => $typeLabel)
                                    <option value="{{ $typeValue }}" @selected(old('employment_type', 'full_time') === $typeValue)>{{ $typeLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4 col-lg-3">
                            <label class="form-label required">Status</label>
                            <select name="status" class="form-control">
                                <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                                <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
                    </div>
                    <div class="text-right mt-3 ml-0 pl-0">
                        <button class="btn btn-inline btn-primary-outline text-end" type="submit">Save Employee</button>
                        <a href="{{ route('hrm.employees.index') }}" class="btn btn-inline btn-danger-outline text-end">Cancel</a>
                    </div>
                </form>

                <hr>

                <div class="employee-filter-block">
                    <h4 class="employee-section-title">Filter Employees</h4>
                    <form method="GET" action="{{ route('hrm.employees.index') }}" class="mb-0">
                        <div class="form-row mt-2">
                            <div class="form-group col-md-6 col-lg-3">
                                <label class="form-label">Campus</label>
                                <select name="campus_id" class="form-control">
                                    <option value="">All Campuses</option>
                                    @foreach($campuses as $campus)
                                        <option value="{{ $campus->id }}" @selected(($filters['campus_id'] ?? null) == $campus->id)>
                                            {{ $campus->code }} - {{ $campus->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6 col-lg-3">
                                <label class="form-label">Department</label>
                                <select name="department_id" class="form-control">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" @selected(($filters['department_id'] ?? null) == $department->id)>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6 col-lg-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All</option>
                                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6 col-lg-3">
                                <label class="form-label">Qualification</label>
                                <input type="text" name="qualification" class="form-control" value="{{ $filters['qualification'] ?? '' }}" placeholder="Filter by qualification">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6 col-lg-3 d-flex align-items-end" style="gap: 8px;">
                                <button type="submit" class="btn btn-inline btn-primary-outline p-2">Filter</button>
                                <a href="{{ route('hrm.employees.index') }}" class="btn btn-inline btn-danger-outline p-2">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>

                <hr>

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
                                <th>Portal User</th>
                                <th>Joining</th>
                                <th>Qualification</th>
                                <th>Status</th>
                                <th>Actions</th>
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
                                    <td>{{ $employee->portal_user ? 'Yes' : 'No' }}</td>
                                    <td>{{ optional($employee->joining_date)->format('Y-m-d') ?: '-' }}</td>
                                    <td>{{ $employee->qualification ?: '-' }}</td>
                                    <td>
                                        <span class="badge {{ $employee->status === 'active' ? 'badge-success' : 'badge-secondary' }}">
                                            {{ ucfirst($employee->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                Actions
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                @if($canAdminEdit)
                                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#editEmployeeModal-{{ $employee->id }}">Edit</a>
                                                @endif
                                                <form method="POST" action="{{ route('hrm.employees.status', $employee) }}">
                                                    @csrf
                                                    <input type="hidden" name="status" value="{{ $employee->status === 'active' ? 'inactive' : 'active' }}">
                                                    <button class="dropdown-item dropdown-item-button" type="submit">
                                                        {{ $employee->status === 'active' ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted">No employee records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($canAdminEdit)
                    @foreach($employees as $employee)
                        <div class="modal fade" id="editEmployeeModal-{{ $employee->id }}" tabindex="-1" role="dialog" aria-labelledby="editEmployeeModalLabel-{{ $employee->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('hrm.employees.update', $employee) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="edit_employee_id" value="{{ $employee->id }}">

                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editEmployeeModalLabel-{{ $employee->id }}">Edit Employee</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label class="form-label">Employee Code</label>
                                                    <input type="text" class="form-control" value="{{ $employee->employee_code }}" readonly disabled>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label class="form-label required">First Name</label>
                                                    <input type="text" name="first_name" class="form-control" value="{{ $employee->first_name }}" required>
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label class="form-label">Last Name</label>
                                                    <input type="text" name="last_name" class="form-control" value="{{ $employee->last_name }}">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label class="form-label">CNIC</label>
                                                    <input type="text" name="cnic" class="form-control" value="{{ $employee->cnic }}">
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label class="form-label">Contact</label>
                                                    <input type="text" name="contact_no" class="form-control" value="{{ $employee->contact_no }}">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label class="form-label">Email</label>
                                                    <input type="email" name="email" class="form-control" value="{{ $employee->email }}">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label">Address</label>
                                                <input type="text" name="address" class="form-control" value="{{ $employee->address }}">
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-4">
                                                    <label class="form-label">Emergency Name</label>
                                                    <input type="text" name="emergency_contact_name" class="form-control" value="{{ $employee->emergency_contact_name }}">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label class="form-label">Emergency Phone</label>
                                                    <input type="text" name="emergency_contact_phone" class="form-control" value="{{ $employee->emergency_contact_phone }}">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label class="form-label">Relation</label>
                                                    <input type="text" name="emergency_contact_relation" class="form-control" value="{{ $employee->emergency_contact_relation }}">
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-4">
                                                    <label class="form-label">Campus</label>
                                                    <select name="campus_id" class="form-control">
                                                        <option value="">- Select -</option>
                                                        @foreach($campuses as $campus)
                                                            <option value="{{ $campus->id }}" @selected((string) $employee->campus_id === (string) $campus->id)>{{ $campus->code }} - {{ $campus->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label class="form-label">Department</label>
                                                    <select name="department_id" class="form-control">
                                                        <option value="">- Select -</option>
                                                        @foreach($departments as $department)
                                                            <option value="{{ $department->id }}" @selected((string) $employee->department_id === (string) $department->id)>{{ $department->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label class="form-label">Designation</label>
                                                    <select name="designation_id" class="form-control">
                                                        <option value="">- Select -</option>
                                                        @foreach($designations as $designation)
                                                            <option value="{{ $designation->id }}" @selected((string) $employee->designation_id === (string) $designation->id)>{{ $designation->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-4">
                                                    <div class="d-flex align-items-center flex-wrap" style="gap: 12px; min-height: 38px;">
                                                        <label class="form-label mb-0">Portal User</label>
                                                        <label class="mb-0">
                                                            <input type="radio" name="portal_user" value="1" @checked($employee->portal_user)> Yes
                                                        </label>
                                                        <label class="mb-0">
                                                            <input type="radio" name="portal_user" value="0" @checked(!$employee->portal_user)> No
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label class="form-label">Joining Date</label>
                                                    <input type="date" name="joining_date" class="form-control" value="{{ optional($employee->joining_date)->format('Y-m-d') }}">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label class="form-label">Qualification</label>
                                                    <input type="text" name="qualification" class="form-control" value="{{ $employee->qualification }}" placeholder="Enter qualification">
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-4">
                                                    <label class="form-label">Employment Type</label>
                                                    <select name="employment_type" class="form-control">
                                                        @foreach($employmentTypes as $typeValue => $typeLabel)
                                                            <option value="{{ $typeValue }}" @selected($employee->employment_type === $typeValue)>{{ $typeLabel }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-control">
                                                        <option value="active" @selected($employee->status === 'active')>Active</option>
                                                        <option value="inactive" @selected($employee->status === 'inactive')>Inactive</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group mb-0">
                                                <label class="form-label">Notes</label>
                                                <input type="text" name="notes" class="form-control" value="{{ $employee->notes }}">
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-inline btn-danger-outline" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-inline btn-primary-outline">Update Employee</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

                {{ $employees->links() }}
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .required::after { content: ' *'; color: #dc2626; }
        .hrm-table thead th { background: #0ea5e9; color: #fff; }
        .employee-section-title { margin: 0 0 12px; font-size: 16px; font-weight: 600; }
        .dropdown-item-button {
            width: 100%;
            border: 0;
            background: transparent;
            text-align: left;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const editEmployeeId = @json(old('edit_employee_id'));

            if (!editEmployeeId || !window.jQuery) {
                return;
            }

            const $modal = $('#editEmployeeModal-' + editEmployeeId);
            if ($modal.length) {
                $modal.modal('show');
            }
        });
    </script>
@endpush
