<?php

namespace App\Http\Controllers\Hrm;

use App\Models\HrEmployee;
use App\Models\HrLeaveBalance;
use App\Models\HrLeaveRequest;
use App\Models\HrLeaveType;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeaveController extends BaseController
{
    public function index(Request $request): View
    {
        $this->authorizeHrm($request, ['hrm_leave.view']);

        $requests = HrLeaveRequest::query()
            ->with(['employee:id,employee_code,first_name,last_name', 'leaveType:id,name', 'approver:id,name'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderByDesc('id')
            ->paginate(20, ['*'], 'requests_page')
            ->withQueryString();

        $balances = HrLeaveBalance::query()
            ->with(['employee:id,employee_code,first_name,last_name', 'leaveType:id,name'])
            ->where('year', (int) now()->year)
            ->orderByDesc('id')
            ->paginate(20, ['*'], 'balances_page')
            ->withQueryString();

        return view('hrm.leaves.index', [
            'requests' => $requests,
            'balances' => $balances,
            'employees' => HrEmployee::query()->where('status', 'active')->orderBy('first_name')->limit(400)->get(['id', 'employee_code', 'first_name', 'last_name']),
            'leaveTypes' => HrLeaveType::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'annual_quota']),
            'filters' => [
                'status' => $request->input('status'),
            ],
        ]);
    }

    public function storeRequest(Request $request): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_leave.request']);

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:hr_employees,id'],
            'leave_type_id' => ['required', 'exists:hr_leave_types,id'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'days' => ['nullable', 'numeric', 'min:0.5'],
            'reason' => ['nullable', 'string'],
        ]);

        $from = Carbon::parse($validated['from_date']);
        $to = Carbon::parse($validated['to_date']);
        $days = isset($validated['days']) ? (float) $validated['days'] : (float) $from->diffInDays($to) + 1;

        HrLeaveRequest::query()->create([
            'employee_id' => $validated['employee_id'],
            'leave_type_id' => $validated['leave_type_id'],
            'from_date' => $validated['from_date'],
            'to_date' => $validated['to_date'],
            'days' => $days,
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
            'applied_at' => now(),
        ]);

        return back()->with('status', 'Leave request submitted.');
    }

    public function approveRequest(Request $request, HrLeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_leave.approve']);

        if ($leaveRequest->status !== 'pending') {
            return back()->with('status', 'Leave request already processed.');
        }

        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => $request->user()?->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        $year = (int) Carbon::parse($leaveRequest->from_date)->year;
        $balance = HrLeaveBalance::query()->firstOrCreate(
            [
                'employee_id' => $leaveRequest->employee_id,
                'leave_type_id' => $leaveRequest->leave_type_id,
                'year' => $year,
            ],
            [
                'opening_balance' => 0,
                'accrued' => 0,
                'used' => 0,
                'encashed' => 0,
                'closing_balance' => 0,
            ]
        );

        $balance->used = (float) $balance->used + (float) $leaveRequest->days;
        $balance->closing_balance = (float) $balance->opening_balance + (float) $balance->accrued - (float) $balance->used - (float) $balance->encashed;
        $balance->save();

        return back()->with('status', 'Leave request approved.');
    }

    public function rejectRequest(Request $request, HrLeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_leave.approve']);

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string'],
        ]);

        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => $request->user()?->id,
            'approved_at' => now(),
            'rejection_reason' => $validated['rejection_reason'] ?? 'Rejected by approver',
        ]);

        return back()->with('status', 'Leave request rejected.');
    }

    public function storeBalance(Request $request): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_leave.manage_balance']);

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:hr_employees,id'],
            'leave_type_id' => ['required', 'exists:hr_leave_types,id'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'accrued' => ['nullable', 'numeric', 'min:0'],
            'used' => ['nullable', 'numeric', 'min:0'],
            'encashed' => ['nullable', 'numeric', 'min:0'],
        ]);

        $opening = (float) ($validated['opening_balance'] ?? 0);
        $accrued = (float) ($validated['accrued'] ?? 0);
        $used = (float) ($validated['used'] ?? 0);
        $encashed = (float) ($validated['encashed'] ?? 0);

        HrLeaveBalance::query()->updateOrCreate(
            [
                'employee_id' => $validated['employee_id'],
                'leave_type_id' => $validated['leave_type_id'],
                'year' => (int) $validated['year'],
            ],
            [
                'opening_balance' => $opening,
                'accrued' => $accrued,
                'used' => $used,
                'encashed' => $encashed,
                'closing_balance' => $opening + $accrued - $used - $encashed,
            ]
        );

        return back()->with('status', 'Leave balance saved.');
    }
}

