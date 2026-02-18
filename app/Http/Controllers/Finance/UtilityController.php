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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UtilityController extends Controller
{
    public function typesIndex(): View
    {
        $this->ensureDefaultBillTypes();

        return view('finance.utility.types', [
            'types' => FinanceBillType::query()->with('payee')->orderBy('name')->paginate(30),
            'payees' => FinancePayee::query()->where('status', 'active')->orderBy('full_name')->get(),
        ]);
    }

    public function typesStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'payee_id' => ['nullable', 'exists:finance_payees,id'],
        ]);

        FinanceBillType::create([
            'name' => $validated['name'],
            'payee_id' => $validated['payee_id'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('status', 'Bill type added.');
    }

    public function billsIndex(): View
    {
        $this->ensureDefaultBillTypes();

        return view('finance.utility.bills', [
            'bills' => FinanceBill::query()->with(['campus', 'billType'])->orderByDesc('id')->paginate(20),
            'campuses' => Campus::query()->orderBy('name')->get(),
            'billTypes' => FinanceBillType::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function billsStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
            'bill_type_id' => ['required', 'exists:finance_bill_types,id'],
            'reference_number' => ['required', 'string', 'max:100'],
            'bill_month' => ['required', 'date'],
            'issue_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'amount_within_due_date' => ['required', 'numeric', 'min:0'],
            'fine' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ]);

        $fine = (float) ($validated['fine'] ?? 0);
        $amount = (float) $validated['amount_within_due_date'] + $fine;

        FinanceBill::create([
            'campus_id' => $validated['campus_id'],
            'bill_type_id' => $validated['bill_type_id'],
            'reference_number' => $validated['reference_number'],
            'bill_month' => $validated['bill_month'],
            'issue_date' => $validated['issue_date'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'amount_within_due_date' => $validated['amount_within_due_date'],
            'fine' => $fine,
            'amount' => $amount,
            'paid_amount' => 0,
            'status' => 'unpaid',
            'remarks' => $validated['remarks'] ?? null,
            'created_by' => $request->user()?->id,
        ]);

        return back()->with('status', 'Utility bill added.');
    }

    public function payIndex(Request $request): View
    {
        $this->ensureDefaultBillTypes();

        return view('finance.utility.pay', [
            'campuses' => Campus::query()->orderBy('name')->get(),
            'bills' => FinanceBill::query()->with(['campus', 'billType'])->whereIn('status', ['unpaid', 'partial'])->orderByDesc('id')->get(),
            'payments' => FinanceBillPayment::query()->with(['bill.campus', 'bill.billType'])->latest()->paginate(20),
            'isAdmin' => $this->isAdmin($request),
        ]);
    }

    public function payStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bill_id' => ['required', 'exists:finance_bills,id'],
            'payment_date' => ['required', 'date'],
            'paid_amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', Rule::in(['cash', 'bank', 'cheque'])],
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

        FinanceBillPayment::create([
            'bill_id' => $bill->id,
            'payment_date' => $validated['payment_date'],
            'paid_amount' => $validated['paid_amount'],
            'payment_method' => $validated['payment_method'],
            'payment_ref_no' => $validated['payment_ref_no'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'cheque_no' => $validated['cheque_no'] ?? null,
            'bank_receipt_no' => $validated['bank_receipt_no'] ?? null,
            'receipt_no' => $this->generateBillPaymentReceiptNo($bill->campus?->code ?? 'GEN', $validated['payment_method']),
            'attachment_path' => $path,
            'remarks' => $validated['remarks'] ?? null,
            'created_by' => $request->user()?->id,
        ]);

        $expenseType = $this->ensureExpenseType('Utility Bill', 'utility');
        FinanceExpense::create([
            'campus_id' => $bill->campus_id,
            'payee_id' => $bill->billType?->payee_id,
            'expense_type_id' => $expenseType->id,
            'bill_id' => $bill->id,
            'category' => 'utility',
            'payment_date' => $validated['payment_date'],
            'amount' => $validated['paid_amount'],
            'payment_method' => $validated['payment_method'],
            'payment_ref_no' => $validated['payment_ref_no'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'cheque_no' => $validated['cheque_no'] ?? null,
            'bank_receipt_no' => $validated['bank_receipt_no'] ?? null,
            'voucher_no' => $this->generateExpenseVoucherNo($bill->campus?->code ?? 'GEN'),
            'receipt_no' => $this->generateExpenseReceiptNo($bill->campus?->code ?? 'GEN', strtoupper(substr($validated['payment_method'], 0, 3))),
            'attachment_path' => $path,
            'status' => 'pending',
            'remarks' => 'Utility bill payment request. Bill Ref: ' . ($bill->reference_number ?? 'N/A'),
            'created_by' => $request->user()?->id,
            'requested_by' => $request->user()?->id,
            'is_reversal' => false,
        ]);

        $bill->update(['status' => 'pending_approval']);

        return back()->with('status', 'Utility payment request submitted for approval.');
    }

    private function isAdmin(Request $request): bool
    {
        $user = $request->user();
        if (!$user) {
            return false;
        }
        return $user->roles()->whereIn('slug', ['owner', 'admin'])->exists();
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

    private function generateExpenseReceiptNo(string $campusCode, string $modeCode): string
    {
        $campusCode = $campusCode !== '' ? $campusCode : 'GEN';
        $modeCode = $modeCode !== '' ? $modeCode : 'GEN';
        $prefix = $campusCode . '-' . strtoupper($modeCode) . '-' . now()->format('my');
        $count = FinanceExpense::query()->where('receipt_no', 'like', $prefix . '-%')->count() + 1;
        return $prefix . '-' . str_pad((string) $count, 6, '0', STR_PAD_LEFT);
    }

    private function generateBillPaymentReceiptNo(string $campusCode, string $method): string
    {
        $campusCode = $campusCode !== '' ? $campusCode : 'GEN';
        $modeCode = strtoupper(substr($method, 0, 3));
        $prefix = $campusCode . '-UTL-' . $modeCode . '-' . now()->format('my');
        $count = FinanceBillPayment::query()->where('receipt_no', 'like', $prefix . '-%')->count() + 1;
        return $prefix . '-' . str_pad((string) $count, 6, '0', STR_PAD_LEFT);
    }

    private function ensureDefaultBillTypes(): void
    {
        foreach (['Electricity', 'Gas', 'Internet', 'Water', 'Telephone'] as $name) {
            FinanceBillType::query()->firstOrCreate(
                ['name' => $name],
                ['payee_id' => null, 'is_active' => true]
            );
        }
    }
}
