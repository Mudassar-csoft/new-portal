<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\FinanceBill;
use App\Models\FinanceBillPayment;
use App\Models\FinanceBillType;
use App\Models\FinanceExpense;
use App\Models\FinanceExpenseType;
use App\Models\FinancePayee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UtilityController extends Controller
{
    public function typesIndex(Request $request): View
    {
        $this->ensureDefaultBillTypes();

        return view('finance.utility.types', [
            'types' => FinanceBillType::query()->with('payee')->orderBy('name')->paginate(30),
            'payees' => FinancePayee::query()->where('status', 'active')->orderBy('full_name')->get(),
            'canCreateBillTypes' => $this->canCreateBillTypes($request),
            'canViewBillTypes' => $this->canViewBillTypes($request),
        ]);
    }

    public function typesStore(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'company_name' => ['nullable', 'string', 'max:150'],
            'service_name' => ['required', 'string', 'max:150'],
            'payee_id' => ['nullable', 'exists:finance_payees,id'],
        ]);

        $name = trim(implode(' - ', array_filter([$validated['company_name'] ?? null, $validated['service_name']])));

        $type = FinanceBillType::create([
            'name' => $name,
            'company_name' => $validated['company_name'] ?? null,
            'service_name' => $validated['service_name'],
            'payee_id' => $validated['payee_id'] ?? null,
            'is_active' => true,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Bill type added.',
                'type' => [
                    'id' => $type->id,
                    'name' => $type->name,
                    'display_name' => $type->display_name,
                    'company_name' => $type->company_name,
                    'service_name' => $type->service_name,
                ],
            ]);
        }

        return back()->with('status', 'Bill type added.');
    }

    public function billsIndex(Request $request): View
    {
        $this->ensureDefaultBillTypes();

        return view('finance.utility.bills', [
            'bills' => FinanceBill::query()->with(['campus', 'billType'])->orderByDesc('id')->paginate(20),
            'campuses' => Campus::query()->orderBy('name')->get(),
            'billTypes' => FinanceBillType::query()->where('is_active', true)->orderBy('name')->get(),
            'payees' => FinancePayee::query()->where('status', 'active')->orderBy('full_name')->get(),
            'canCreateBills' => $this->canCreateBills($request),
            'canUpdateBills' => $this->canUpdateBills($request),
            'canViewBills' => $this->canViewBills($request),
            'canCreateBillTypes' => $this->canCreateBillTypes($request),
        ]);
    }

    public function billsStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
            'bill_type_id' => ['required', 'exists:finance_bill_types,id'],
            'reference_number' => ['required', 'string', 'max:100'],
            'remarks' => ['nullable', 'string'],
        ]);

        FinanceBill::create([
            'campus_id' => $validated['campus_id'],
            'bill_type_id' => $validated['bill_type_id'],
            'reference_number' => $validated['reference_number'],
            'bill_month' => null,
            'issue_date' => null,
            'due_date' => null,
            'amount_within_due_date' => 0,
            'fine' => 0,
            'amount' => 0,
            'paid_amount' => 0,
            'status' => 'unpaid',
            'remarks' => $validated['remarks'] ?? null,
            'created_by' => $request->user()?->id,
        ]);

        return back()->with('status', 'Utility bill added.');
    }

    public function billsUpdate(Request $request, FinanceBill $bill): RedirectResponse
    {
        $this->ensureCanUpdateBills($request);

        $validated = $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
            'bill_type_id' => ['required', 'exists:finance_bill_types,id'],
            'reference_number' => ['required', 'string', 'max:100'],
            'remarks' => ['nullable', 'string'],
        ]);

        $bill->update([
            'campus_id' => $validated['campus_id'],
            'bill_type_id' => $validated['bill_type_id'],
            'reference_number' => $validated['reference_number'],
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return back()->with('status', 'Utility bill updated successfully.');
    }

    public function lookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
            'bill_type_id' => ['nullable', 'exists:finance_bill_types,id'],
        ]);

        $bills = FinanceBill::query()
            ->with('billType')
            ->where('campus_id', $validated['campus_id'])
            ->whereIn('status', ['unpaid', 'partial'])
            ->when($validated['bill_type_id'] ?? null, fn ($q, $billTypeId) => $q->where('bill_type_id', $billTypeId))
            ->orderByDesc('id')
            ->get();

        $billTypes = $bills
            ->filter(fn (FinanceBill $bill) => $bill->billType)
            ->map(fn (FinanceBill $bill) => [
                'id' => $bill->bill_type_id,
                'name' => $bill->billType->service_name ?: $bill->billType->display_name,
                'display_name' => $bill->billType->display_name,
                'company_name' => $bill->billType->company_name,
                'service_name' => $bill->billType->service_name,
            ])
            ->unique('id')
            ->values();

        return response()->json([
            'billTypes' => $billTypes,
            'bills' => $bills->map(function (FinanceBill $bill) {
                $balance = max(0, (float) $bill->amount - (float) $bill->paid_amount);

                return [
                    'id' => $bill->id,
                    'bill_type_id' => $bill->bill_type_id,
                    'company_name' => $bill->billType?->company_name,
                    'service_name' => $bill->billType?->service_name,
                    'bill_type_name' => $bill->billType?->display_name ?? 'N/A',
                    'reference_number' => $bill->reference_number,
                    'amount' => (float) $bill->amount,
                    'paid_amount' => (float) $bill->paid_amount,
                    'balance' => $balance,
                    'status' => $bill->status,
                ];
            })->values(),
        ]);
    }

    public function payIndex(Request $request): View
    {
        $this->ensureDefaultBillTypes();

        return view('finance.utility.pay', [
            'campuses' => Campus::query()->orderBy('name')->get(),
            'bills' => FinanceBill::query()->with(['campus', 'billType'])->whereIn('status', ['unpaid', 'partial'])->orderByDesc('id')->get(),
            'payments' => FinanceBillPayment::query()->with(['bill.campus', 'bill.billType'])->latest()->paginate(20),
            'canCreatePayments' => $this->canCreatePayments($request),
            'canViewPayments' => $this->canViewPayments($request),
        ]);
    }

    public function payStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bill_id' => ['required', 'exists:finance_bills,id'],
            'payment_date' => ['required', 'date'],
            'paid_amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', Rule::in(['cash', 'bank', 'cheque', 'online_transfer', 'easypaisa', 'jazzcash'])],
            'payment_ref_no' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:150'],
            'cheque_no' => ['nullable', 'string', 'max:100'],
            'bank_receipt_no' => ['nullable', 'string', 'max:100'],
            'attachment' => ['required', 'image', 'max:5120'],
            'remarks' => ['nullable', 'string'],
        ]);

        if (in_array($validated['payment_method'], ['bank', 'cheque'], true) && empty($validated['bank_name'])) {
            return back()->withErrors(['bank_name' => 'Bank name is required for bank/cheque payments.'])->withInput();
        }

        if (in_array($validated['payment_method'], ['bank', 'online_transfer', 'easypaisa', 'jazzcash'], true) && empty($validated['payment_ref_no'])) {
            return back()->withErrors(['payment_ref_no' => 'Payment reference number is required for the selected payment method.'])->withInput();
        }

        if ($validated['payment_method'] === 'cheque' && empty($validated['cheque_no'])) {
            return back()->withErrors(['cheque_no' => 'Cheque number is required for cheque payments.'])->withInput();
        }

        $bill = FinanceBill::query()->with(['campus', 'billType'])->findOrFail($validated['bill_id']);
        if (!in_array((string) $bill->status, ['unpaid', 'partial'], true)) {
            return back()->withErrors(['bill_id' => 'Selected bill cannot accept payment request in current status.'])->withInput();
        }
        $balance = max(0, (float) $bill->amount - (float) $bill->paid_amount);
        if ((float) $validated['paid_amount'] > $balance) {
            return back()->withErrors([
                'paid_amount' => 'Paid amount cannot exceed bill balance of Rs. ' . number_format($balance, 0),
            ])->withInput();
        }
        $path = $this->storeAttachment($request->file('attachment'));

        $expenseRemarks = 'Utility bill payment request. Bill Ref: ' . ($bill->reference_number ?? 'N/A');
        if (!empty($validated['remarks'])) {
            $expenseRemarks .= ' | ' . $validated['remarks'];
        }

        $expenseType = $this->ensureExpenseType('Utility Bill', 'utility');
        FinanceExpense::create([
            'campus_id' => $bill->campus_id,
            'payee_id' => $bill->billType?->payee_id,
            'expense_type_id' => $expenseType->id,
            'bill_id' => $bill->id,
            'category' => 'utility',
            'payment_date' => $validated['payment_date'],
            'expense_month' => $bill->bill_month?->toDateString(),
            'amount' => $validated['paid_amount'],
            'payment_method' => $validated['payment_method'],
            'payment_ref_no' => $validated['payment_ref_no'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'cheque_no' => $validated['cheque_no'] ?? null,
            'bank_receipt_no' => $validated['bank_receipt_no'] ?? null,
            'voucher_no' => $this->generateExpenseVoucherNo($bill->campus?->code ?? 'GEN'),
            'receipt_no' => null,
            'attachment_path' => $path,
            'status' => 'pending',
            'remarks' => $expenseRemarks,
            'created_by' => $request->user()?->id,
            'requested_by' => $request->user()?->id,
            'is_reversal' => false,
        ]);

        $bill->update(['status' => 'pending_approval']);

        return back()->with('status', 'Utility payment request submitted for approval.');
    }

    private function canViewBillTypes(Request $request): bool
    {
        return $request->user()?->hasAnyPermission(['finance.utility.view']) ?? false;
    }

    private function canCreateBillTypes(Request $request): bool
    {
        return $request->user()?->hasAnyPermission(['finance.utility.create']) ?? false;
    }

    private function canViewBills(Request $request): bool
    {
        return $request->user()?->hasAnyPermission(['finance.bill.view']) ?? false;
    }

    private function canCreateBills(Request $request): bool
    {
        return $request->user()?->hasAnyPermission(['finance.bill.create']) ?? false;
    }

    private function ensureCanUpdateBills(Request $request): void
    {
        if (!$this->canUpdateBills($request)) {
            abort(403, 'You do not have permission to update utility bill records.');
        }
    }

    private function canUpdateBills(Request $request): bool
    {
        return $request->user()?->hasAnyPermission(['finance.bill.update', 'finance.utility.update']) ?? false;
    }

    private function canViewPayments(Request $request): bool
    {
        return $request->user()?->hasAnyPermission(['finance.utility.view']) ?? false;
    }

    private function canCreatePayments(Request $request): bool
    {
        return $request->user()?->hasAnyPermission(['finance.utility.create']) ?? false;
    }

    private function storeAttachment(?UploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }

        return $file->store('finance/transactions', 'public');
    }

    private function ensureExpenseType(string $name, string $category): FinanceExpenseType
    {
        return FinanceExpenseType::firstOrCreate(
            ['name' => $name],
            ['category' => $category, 'is_active' => true]
        );
    }

    private function generateExpenseVoucherNo(string $campusCode): string
    {
        $campusCode = $campusCode !== '' ? $campusCode : 'GEN';
        $prefix = $campusCode . '-EXP-' . now()->format('my');
        $count = FinanceExpense::query()->where('voucher_no', 'like', $prefix . '-%')->count() + 1;

        return $prefix . '-' . str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }

    private function ensureDefaultBillTypes(): void
    {
        $defaults = [
            ['company_name' => 'FESCO', 'service_name' => 'Electricity'],
            ['company_name' => 'SNGPL', 'service_name' => 'Gas'],
            ['company_name' => 'PTCL', 'service_name' => 'Internet'],
            ['company_name' => 'WASA', 'service_name' => 'Water'],
            ['company_name' => 'PTCL', 'service_name' => 'Telephone'],
        ];

        foreach ($defaults as $row) {
            FinanceBillType::query()->firstOrCreate(
                ['name' => trim($row['company_name'] . ' - ' . $row['service_name'])],
                [
                    'company_name' => $row['company_name'],
                    'service_name' => $row['service_name'],
                    'payee_id' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
