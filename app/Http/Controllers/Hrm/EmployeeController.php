<?php

namespace App\Http\Controllers\Hrm;

use App\Models\Campus;
use App\Models\HrDepartment;
use App\Models\HrDesignation;
use App\Models\HrEmployee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeController extends BaseController
{
    public function index(Request $request): View
    {
        $this->authorizeHrm($request, ['hrm_employee.view']);

        $employees = HrEmployee::query()
            ->with(['campus', 'department', 'designation', 'manager'])
            ->when($request->integer('campus_id'), fn ($q, $campusId) => $q->where('campus_id', $campusId))
            ->when($request->integer('department_id'), fn ($q, $departmentId) => $q->where('department_id', $departmentId))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('hrm.employees.index', [
            'employees' => $employees,
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'code', 'name']),
            'departments' => HrDepartment::query()->orderBy('name')->get(['id', 'name']),
            'designations' => HrDesignation::query()->orderBy('name')->get(['id', 'name']),
            'managers' => HrEmployee::query()->where('status', 'active')->orderBy('first_name')->limit(300)->get(['id', 'first_name', 'last_name']),
            'users' => User::query()->orderBy('name')->limit(300)->get(['id', 'name', 'email']),
            'filters' => [
                'campus_id' => $request->integer('campus_id') ?: null,
                'department_id' => $request->integer('department_id') ?: null,
                'status' => $request->input('status'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_employee.create']);

        $validated = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'department_id' => ['nullable', 'exists:hr_departments,id'],
            'designation_id' => ['nullable', 'exists:hr_designations,id'],
            'reporting_manager_id' => ['nullable', 'exists:hr_employees,id'],
            'employee_code' => ['nullable', 'string', 'max:50', Rule::unique('hr_employees', 'employee_code')],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'cnic' => ['nullable', 'string', 'max:30', Rule::unique('hr_employees', 'cnic')],
            'contact_no' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'emergency_contact_name' => ['nullable', 'string', 'max:120'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:40'],
            'emergency_contact_relation' => ['nullable', 'string', 'max:60'],
            'joining_date' => ['nullable', 'date'],
            'employment_type' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['employee_code'] = $validated['employee_code'] ?? $this->generateEmployeeCode();
        $validated['employment_type'] = $validated['employment_type'] ?? 'full_time';
        $validated['status'] = $validated['status'] ?? 'active';
        $validated['created_by'] = $request->user()?->id;

        HrEmployee::create($validated);

        return back()->with('status', 'Employee profile created.');
    }

    public function update(Request $request, HrEmployee $employee): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_employee.update']);

        $validated = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'department_id' => ['nullable', 'exists:hr_departments,id'],
            'designation_id' => ['nullable', 'exists:hr_designations,id'],
            'reporting_manager_id' => ['nullable', 'exists:hr_employees,id', Rule::notIn([$employee->id])],
            'employee_code' => ['nullable', 'string', 'max:50', Rule::unique('hr_employees', 'employee_code')->ignore($employee->id)],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'cnic' => ['nullable', 'string', 'max:30', Rule::unique('hr_employees', 'cnic')->ignore($employee->id)],
            'contact_no' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'emergency_contact_name' => ['nullable', 'string', 'max:120'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:40'],
            'emergency_contact_relation' => ['nullable', 'string', 'max:60'],
            'joining_date' => ['nullable', 'date'],
            'employment_type' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'notes' => ['nullable', 'string'],
        ]);

        $employee->update($validated);

        return back()->with('status', 'Employee profile updated.');
    }

    public function updateStatus(Request $request, HrEmployee $employee): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_employee.manage_status']);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $employee->update(['status' => $validated['status']]);

        return back()->with('status', 'Employee status updated.');
    }

    private function generateEmployeeCode(): string
    {
        $next = HrEmployee::query()->max('id');
        $next = $next ? $next + 1 : 1;

        return 'EMP-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}

