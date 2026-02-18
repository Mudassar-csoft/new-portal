<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\FinanceExpense;
use App\Models\FinanceExpenseType;
use App\Models\FinancePayee;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function index(Request $request): View
    {
        return view('finance.expense.payroll', [
            'campuses' => Campus::query()->orderBy('name')->get(),
            'employees' => FinancePayee::query()->where('type', 'employee')->where('status', 'active')->orderBy('full_name')->get(),
            'payrollExpenses' => FinanceExpense::query()->with(['campus', 'payee'])->where('category', 'payroll')->latest()->paginate(20),
            'isAdmin' => $this->isAdmin($request),
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
            'month' => ['required', 'date_format:Y-m'],
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['integer', 'exists:finance_payees,id'],
            'payment_method' => ['required', Rule::in(['cash', 'bank', 'cheque'])],
            'remarks' => ['nullable', 'string'],
        ]);

        $campus = Campus::query()->findOrFail($validated['campus_id']);
        $expenseType = FinanceExpenseType::firstOrCreate(
            ['name' => 'Payroll'],
            ['category' => 'payroll', 'is_active' => true]
        );
        $monthDate = Carbon::createFromFormat('Y-m', $validated['month'])->endOfMonth();

        $employees = FinancePayee::query()
            ->whereIn('id', $validated['employee_ids'])
            ->where('type', 'employee')
            ->get();

        $created = 0;
        foreach ($employees as $employee) {
            $salary = (float) ($employee->monthly_salary ?? 0);
            if ($salary <= 0) {
                continue;
            }

            FinanceExpense::create([
                'campus_id' => $validated['campus_id'],
                'payee_id' => $employee->id,
                'expense_type_id' => $expenseType->id,
                'bill_id' => null,
                'category' => 'payroll',
                'payment_date' => $monthDate->toDateString(),
                'amount' => $salary,
                'payment_method' => $validated['payment_method'],
                'voucher_no' => $this->generateExpenseVoucherNo($campus->code),
                'receipt_no' => $this->generateExpenseReceiptNo($campus->code, strtoupper(substr($validated['payment_method'], 0, 3))),
                'status' => 'pending',
                'remarks' => 'Payroll for ' . $monthDate->format('F Y') . ' - ' . $employee->full_name
                    . (!empty($validated['remarks']) ? ' | ' . $validated['remarks'] : ''),
                'created_by' => $request->user()?->id,
                'requested_by' => $request->user()?->id,
                'is_reversal' => false,
            ]);
            $created++;
        }

        return back()->with('status', $created . ' payroll expense request(s) generated.');
    }

    private function isAdmin(Request $request): bool
    {
        $user = $request->user();
        if (!$user) {
            return false;
        }
        return $user->roles()->whereIn('slug', ['owner', 'admin'])->exists();
    }

    private function generateExpenseVoucherNo(string $campusCode): string
    {
        $campusCode = $campusCode !== '' ? $campusCode : 'GEN';
        $prefix = $campusCode . '-EXP-' . now()->format('my');
        $count = FinanceExpense::query()->where('voucher_no', 'like', $prefix . '-%')->count() + 1;
        return $prefix . '-' . str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }

    private function generateExpenseReceiptNo(string $campusCode, string $modeCode): string
    {
        $campusCode = $campusCode !== '' ? $campusCode : 'GEN';
        $modeCode = $modeCode !== '' ? $modeCode : 'GEN';
        $prefix = $campusCode . '-' . strtoupper($modeCode) . '-' . now()->format('my');
        $count = FinanceExpense::query()->where('receipt_no', 'like', $prefix . '-%')->count() + 1;
        return $prefix . '-' . str_pad((string) $count, 6, '0', STR_PAD_LEFT);
    }
}
