<?php

namespace App\Http\Controllers\Hrm;

use App\Models\Campus;
use App\Models\HrEmployee;
use App\Models\HrShift;
use App\Models\HrShiftAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShiftController extends BaseController
{
    public function index(Request $request): View
    {
        $this->authorizeHrm($request, ['hrm_shift.view']);

        $shifts = HrShift::query()
            ->with('campus:id,code,name')
            ->orderBy('name')
            ->paginate(20, ['*'], 'shifts_page');

        $assignments = HrShiftAssignment::query()
            ->with(['employee:id,first_name,last_name,employee_code', 'shift:id,name,start_time,end_time'])
            ->orderByDesc('id')
            ->paginate(20, ['*'], 'assignments_page');

        return view('hrm.shifts.index', [
            'shifts' => $shifts,
            'assignments' => $assignments,
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'code', 'name']),
            'employees' => HrEmployee::query()->where('status', 'active')->orderBy('first_name')->limit(300)->get(['id', 'employee_code', 'first_name', 'last_name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_shift.manage']);

        $validated = $request->validate([
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'name' => ['required', 'string', 'max:120'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'grace_check_in_minutes' => ['nullable', 'integer', 'min:0'],
            'grace_check_out_minutes' => ['nullable', 'integer', 'min:0'],
            'break_minutes' => ['nullable', 'integer', 'min:0'],
            'is_night_shift' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        HrShift::query()->create([
            'campus_id' => $validated['campus_id'] ?? null,
            'name' => $validated['name'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'grace_check_in_minutes' => (int) ($validated['grace_check_in_minutes'] ?? 10),
            'grace_check_out_minutes' => (int) ($validated['grace_check_out_minutes'] ?? 10),
            'break_minutes' => (int) ($validated['break_minutes'] ?? 60),
            'is_night_shift' => (bool) ($validated['is_night_shift'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return back()->with('status', 'Shift created.');
    }

    public function storeAssignment(Request $request): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_shift.assign']);

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:hr_employees,id'],
            'shift_id' => ['required', 'exists:hr_shifts,id'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'off_days' => ['nullable', 'array'],
            'off_days.*' => ['string', Rule::in(['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'])],
            'is_rotational' => ['nullable', 'boolean'],
        ]);

        HrShiftAssignment::query()->create([
            'employee_id' => $validated['employee_id'],
            'shift_id' => $validated['shift_id'],
            'effective_from' => $validated['effective_from'],
            'effective_to' => $validated['effective_to'] ?? null,
            'off_days' => $validated['off_days'] ?? [],
            'is_rotational' => (bool) ($validated['is_rotational'] ?? false),
        ]);

        return back()->with('status', 'Shift assignment saved.');
    }
}

