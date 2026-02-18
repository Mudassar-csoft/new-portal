<?php

namespace App\Http\Controllers\Hrm;

use App\Models\Campus;
use App\Models\HrDepartment;
use App\Models\HrDesignation;
use App\Models\HrHoliday;
use App\Models\HrLeaveType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MasterController extends BaseController
{
    public function index(Request $request): View
    {
        $this->authorizeHrm($request, ['hrm_department.view', 'hrm_leave.manage_type', 'hrm_holiday.view']);

        return view('hrm.masters.index', [
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'code', 'name']),
            'departments' => HrDepartment::query()->with('campus:id,code,name')->orderBy('name')->paginate(15, ['*'], 'departments_page'),
            'designations' => HrDesignation::query()->with('department:id,name')->orderBy('name')->paginate(15, ['*'], 'designations_page'),
            'leaveTypes' => HrLeaveType::query()->orderBy('name')->paginate(15, ['*'], 'leave_types_page'),
            'holidays' => HrHoliday::query()->with('campus:id,code,name')->orderByDesc('holiday_date')->paginate(15, ['*'], 'holidays_page'),
        ]);
    }

    public function storeDepartment(Request $request): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_department.create']);

        $validated = $request->validate([
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'name' => ['required', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'description' => ['nullable', 'string'],
        ]);

        HrDepartment::query()->updateOrCreate(
            ['campus_id' => $validated['campus_id'] ?? null, 'name' => $validated['name']],
            ['status' => $validated['status'] ?? 'active', 'description' => $validated['description'] ?? null]
        );

        return back()->with('status', 'Department saved.');
    }

    public function storeDesignation(Request $request): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_designation.create']);

        $validated = $request->validate([
            'department_id' => ['nullable', 'exists:hr_departments,id'],
            'name' => ['required', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'description' => ['nullable', 'string'],
        ]);

        HrDesignation::query()->updateOrCreate(
            ['department_id' => $validated['department_id'] ?? null, 'name' => $validated['name']],
            ['status' => $validated['status'] ?? 'active', 'description' => $validated['description'] ?? null]
        );

        return back()->with('status', 'Designation saved.');
    }

    public function storeLeaveType(Request $request): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_leave.manage_type']);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['nullable', 'string', 'max:30'],
            'is_paid' => ['nullable', 'boolean'],
            'annual_quota' => ['nullable', 'numeric', 'min:0'],
            'accrual_frequency' => ['nullable', Rule::in(['none', 'monthly', 'yearly'])],
            'accrual_rate' => ['nullable', 'numeric', 'min:0'],
            'carry_forward_limit' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        HrLeaveType::query()->updateOrCreate(
            ['name' => $validated['name']],
            [
                'code' => $validated['code'] ?? null,
                'is_paid' => (bool) ($validated['is_paid'] ?? true),
                'annual_quota' => (float) ($validated['annual_quota'] ?? 0),
                'accrual_frequency' => $validated['accrual_frequency'] ?? 'yearly',
                'accrual_rate' => (float) ($validated['accrual_rate'] ?? 0),
                'carry_forward_limit' => isset($validated['carry_forward_limit']) ? (float) $validated['carry_forward_limit'] : null,
                'is_active' => (bool) ($validated['is_active'] ?? true),
            ]
        );

        return back()->with('status', 'Leave type saved.');
    }

    public function storeHoliday(Request $request): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_holiday.manage']);

        $validated = $request->validate([
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'name' => ['required', 'string', 'max:150'],
            'holiday_date' => ['required', 'date'],
            'holiday_type' => ['nullable', 'string', 'max:60'],
            'is_optional' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ]);

        HrHoliday::query()->updateOrCreate(
            [
                'campus_id' => $validated['campus_id'] ?? null,
                'holiday_date' => $validated['holiday_date'],
                'name' => $validated['name'],
            ],
            [
                'holiday_type' => $validated['holiday_type'] ?? 'company',
                'is_optional' => (bool) ($validated['is_optional'] ?? false),
                'description' => $validated['description'] ?? null,
            ]
        );

        return back()->with('status', 'Holiday saved.');
    }
}

