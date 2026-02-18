<?php

namespace App\Http\Controllers\Hrm;

use App\Models\Campus;
use App\Models\HrAdvance;
use App\Models\HrEmployee;
use App\Models\HrPayrollItem;
use App\Models\HrPayrollRun;
use App\Models\HrSalaryStructure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayrollController extends BaseController
{
    public function index(Request $request): View
    {
        $this->authorizeHrm($request, ['hrm_payroll.view']);

        $runs = HrPayrollRun::query()
            ->with(['campus:id,code,name', 'processor:id,name', 'closer:id,name'])
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'runs_page');

        $items = HrPayrollItem::query()
            ->with(['payrollRun:id,payroll_month,status', 'employee:id,employee_code,first_name,last_name'])
            ->orderByDesc('id')
            ->paginate(20, ['*'], 'items_page');

        $payoutSummary = HrPayrollItem::query()
            ->selectRaw('payment_mode, SUM(net_payable) as total')
            ->groupBy('payment_mode')
            ->pluck('total', 'payment_mode');

        return view('hrm.payroll.index', [
            'runs' => $runs,
            'items' => $items,
            'payoutSummary' => [
                'bank' => (float) ($payoutSummary['bank'] ?? 0),
                'cash' => (float) ($payoutSummary['cash'] ?? 0),
                'cheque' => (float) ($payoutSummary['cheque'] ?? 0),
            ],
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'code', 'name']),
            'employees' => HrEmployee::query()->where('status', 'active')->orderBy('first_name')->limit(400)->get(['id', 'employee_code', 'first_name', 'last_name']),
            'openAdvances' => HrAdvance::query()
                ->with('employee:id,employee_code,first_name,last_name')
                ->where('status', 'open')
                ->orderByDesc('id')
                ->limit(100)
                ->get(),
        ]);
    }

    public function storeStructure(Request $request): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_payroll.manage_structure']);

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:hr_employees,id'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'allowances_json' => ['nullable', 'string'],
            'deductions_json' => ['nullable', 'string'],
            'overtime_rate' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        HrSalaryStructure::query()->create([
            'employee_id' => $validated['employee_id'],
            'effective_from' => $validated['effective_from'],
            'effective_to' => $validated['effective_to'] ?? null,
            'basic_salary' => (float) $validated['basic_salary'],
            'allowances' => $this->decodeJsonArray($validated['allowances_json'] ?? null),
            'deductions' => $this->decodeJsonArray($validated['deductions_json'] ?? null),
            'overtime_rate' => (float) ($validated['overtime_rate'] ?? 0),
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'created_by' => $request->user()?->id,
        ]);

        return back()->with('status', 'Salary structure saved.');
    }

    public function storeAdvance(Request $request): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_payroll.process']);

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:hr_employees,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'installment_amount' => ['nullable', 'numeric', 'min:0'],
            'issued_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
        ]);

        $amount = (float) $validated['amount'];
        $installment = (float) ($validated['installment_amount'] ?? 0);
        if ($installment <= 0) {
            $installment = $amount;
        }

        HrAdvance::query()->create([
            'employee_id' => $validated['employee_id'],
            'amount' => $amount,
            'balance_amount' => $amount,
            'installment_amount' => $installment,
            'issued_date' => $validated['issued_date'] ?? now()->toDateString(),
            'status' => 'open',
            'remarks' => $validated['remarks'] ?? null,
            'created_by' => $request->user()?->id,
        ]);

        return back()->with('status', 'Advance/loan saved.');
    }

    public function storeRun(Request $request): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_payroll.process']);

        $validated = $request->validate([
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'payroll_month' => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'notes' => ['nullable', 'string'],
        ]);

        $run = HrPayrollRun::query()->updateOrCreate(
            [
                'campus_id' => $validated['campus_id'] ?? null,
                'payroll_month' => $validated['payroll_month'],
            ],
            [
                'from_date' => $validated['from_date'],
                'to_date' => $validated['to_date'],
                'status' => 'draft',
                'processed_by' => $request->user()?->id,
                'processed_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]
        );

        $this->generatePayrollItems($run);

        return back()->with('status', 'Payroll run generated.');
    }

    public function closeRun(Request $request, HrPayrollRun $run): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_payroll.close']);

        $run->update([
            'status' => 'closed',
            'closed_by' => $request->user()?->id,
            'closed_at' => now(),
        ]);

        return back()->with('status', 'Payroll month closed.');
    }

    public function markItemPaid(Request $request, HrPayrollItem $item): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_payroll.process']);

        $validated = $request->validate([
            'payment_mode' => ['nullable', Rule::in(['bank', 'cash', 'cheque'])],
            'bank_account_no' => ['nullable', 'string', 'max:100'],
        ]);

        $item->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_mode' => $validated['payment_mode'] ?? $item->payment_mode,
            'bank_account_no' => $validated['bank_account_no'] ?? $item->bank_account_no,
        ]);

        return back()->with('status', 'Payroll item marked as paid.');
    }

    private function generatePayrollItems(HrPayrollRun $run): void
    {
        $employees = HrEmployee::query()
            ->where('status', 'active')
            ->when($run->campus_id, fn ($q, $campusId) => $q->where('campus_id', $campusId))
            ->get();

        foreach ($employees as $employee) {
            $structure = HrSalaryStructure::query()
                ->where('employee_id', $employee->id)
                ->where('is_active', true)
                ->whereDate('effective_from', '<=', $run->to_date)
                ->orderByDesc('effective_from')
                ->first();

            if (!$structure) {
                continue;
            }

            $basic = (float) $structure->basic_salary;
            $allowanceTotal = $this->sumJsonValues($structure->allowances);
            $deductionTotal = $this->sumJsonValues($structure->deductions);
            $overtimeAmount = 0.0;

            $advanceDeduction = (float) HrAdvance::query()
                ->where('employee_id', $employee->id)
                ->where('status', 'open')
                ->sum('installment_amount');

            $loanDeduction = 0.0;
            $net = $basic + $allowanceTotal + $overtimeAmount - $deductionTotal - $advanceDeduction - $loanDeduction;

            HrPayrollItem::query()->updateOrCreate(
                [
                    'payroll_run_id' => $run->id,
                    'employee_id' => $employee->id,
                ],
                [
                    'payslip_no' => $this->generatePayslipNo($run->payroll_month, $employee->id),
                    'basic_salary' => $basic,
                    'allowance_total' => $allowanceTotal,
                    'deduction_total' => $deductionTotal,
                    'overtime_amount' => $overtimeAmount,
                    'advance_deduction' => $advanceDeduction,
                    'loan_deduction' => $loanDeduction,
                    'net_payable' => $net,
                    'payment_mode' => 'bank',
                    'status' => 'generated',
                    'allowance_breakdown' => $structure->allowances,
                    'deduction_breakdown' => $structure->deductions,
                ]
            );
        }
    }

    /**
     * @param mixed $raw
     * @return array<string, mixed>|array<int, mixed>
     */
    private function decodeJsonArray(mixed $raw): array
    {
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    /**
     * @param mixed $value
     */
    private function sumJsonValues(mixed $value): float
    {
        if (!is_array($value)) {
            return 0;
        }

        $sum = 0.0;
        foreach ($value as $item) {
            if (is_numeric($item)) {
                $sum += (float) $item;
            }
        }

        return $sum;
    }

    private function generatePayslipNo(string $payrollMonth, int $employeeId): string
    {
        $prefix = 'PS-' . str_replace('-', '', $payrollMonth) . '-';
        $suffix = str_pad((string) $employeeId, 5, '0', STR_PAD_LEFT);

        return $prefix . $suffix;
    }
}

