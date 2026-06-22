<?php

namespace App\Http\Controllers\Hrm;

use App\Models\Campus;
use App\Models\HrDepartment;
use App\Models\HrDesignation;
use App\Models\HrEmployee;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class EmployeeController extends BaseController
{
    public function index(Request $request): View
    {
        $this->authorizeHrm($request, ['hrm_employee.view']);

        $employees = HrEmployee::query()
            ->with(['campus', 'department', 'designation'])
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
            'campus_id' => ['required', 'exists:campuses,id'],
            'department_id' => ['nullable', 'exists:hr_departments,id'],
            'designation_id' => ['nullable', 'exists:hr_designations,id'],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'cnic' => ['nullable', 'string', 'max:30', Rule::unique('hr_employees', 'cnic')],
            'contact_no' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'emergency_contact_name' => ['nullable', 'string', 'max:120'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:40'],
            'emergency_contact_relation' => ['nullable', 'string', 'max:60'],
            'joining_date' => ['required', 'date'],
            'employment_type' => ['nullable', 'string', 'max:40'],
            'portal_user' => ['required', 'boolean'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['employment_type'] = $validated['employment_type'] ?? 'full_time';
        $validated['status'] = $validated['status'] ?? 'active';
        $validated['created_by'] = $request->user()?->id;
        $campus = Campus::query()->findOrFail($validated['campus_id']);

        $this->createEmployeeAtomically((string) $campus->code, (string) $validated['joining_date'], $validated);

        return back()->with('status', 'Employee profile created.');
    }

    public function update(Request $request, HrEmployee $employee): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_employee.update']);

        $validated = $request->validate([
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'department_id' => ['nullable', 'exists:hr_departments,id'],
            'designation_id' => ['nullable', 'exists:hr_designations,id'],
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
            'portal_user' => ['nullable', 'boolean'],
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

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createEmployeeAtomically(string $campusCode, string $joiningDate, array $attributes): HrEmployee
    {
        $maxAttempts = 10;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $attributes['employee_code'] = $this->generateEmployeeCode($campusCode, $joiningDate);

            try {
                return DB::transaction(fn () => HrEmployee::create($attributes));
            } catch (QueryException $e) {
                $message = $e->getMessage();
                if (str_contains($message, 'UNIQUE') || str_contains($message, 'Duplicate entry') || $e->getCode() === '23000') {
                    continue;
                }

                throw $e;
            }
        }

        throw new RuntimeException('Unable to generate a unique employee code after ' . $maxAttempts . ' attempts.');
    }

    private function generateEmployeeCode(string $campusCode, string $joiningDate): string
    {
        $prefix = strtoupper(trim($campusCode)) . '-' . Carbon::parse($joiningDate)->format('d-y') . '-';
        $next = $this->nextSequence('employee_code', $prefix);

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function nextSequence(string $column, string $prefix): int
    {
        $max = HrEmployee::query()
            ->where($column, 'like', $prefix . '%')
            ->get([$column])
            ->map(function (HrEmployee $employee) use ($column, $prefix) {
                $tail = substr((string) $employee->{$column}, strlen($prefix));

                return ctype_digit($tail) ? (int) $tail : 0;
            })
            ->max();

        return ((int) $max) + 1;
    }
}
