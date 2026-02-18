<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\FinanceBill;
use App\Models\FinanceExpense;
use App\Models\FinanceExpenseType;
use App\Models\FinancePayee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    /**
     * @var array<int, string>
     */
    private array $categories = ['general', 'utility', 'rent', 'marketing', 'asset', 'payroll'];

    public function addForm(Request $request): View
    {
        $this->ensureDefaultExpenseTypes();

        return view('finance.expense.add', [
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'code', 'name', 'campus_type']),
            'payees' => FinancePayee::query()->where('status', 'active')->orderBy('full_name')->get(),
            'expenseTypes' => FinanceExpenseType::query()->where('is_active', true)->orderBy('name')->get(),
            'recentExpenses' => FinanceExpense::query()->with(['campus', 'payee', 'expenseType'])->latest()->limit(10)->get(),
            'selectedCategory' => $request->query('category', 'general'),
            'isAdmin' => $this->isAdmin($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
            'payee_id' => ['nullable', 'exists:finance_payees,id'],
            'expense_type_id' => ['required', 'exists:finance_expense_types,id'],
            'category' => ['required', Rule::in($this->categories)],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:1'],
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

        $campus = Campus::query()->findOrFail($validated['campus_id']);

        $expense = FinanceExpense::create([
            'campus_id' => $validated['campus_id'],
            'payee_id' => $validated['payee_id'] ?? null,
            'expense_type_id' => $validated['expense_type_id'],
            'bill_id' => null,
            'category' => $validated['category'],
            'payment_date' => $validated['payment_date'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'payment_ref_no' => $validated['payment_ref_no'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'cheque_no' => $validated['cheque_no'] ?? null,
            'bank_receipt_no' => $validated['bank_receipt_no'] ?? null,
            'voucher_no' => $this->generateExpenseVoucherNo($campus->code),
            'receipt_no' => $this->generateExpenseReceiptNo($campus->code, strtoupper(substr($validated['payment_method'], 0, 3))),
            'attachment_path' => $this->storeAttachment($request->file('attachment')),
            'status' => 'pending',
            'remarks' => $validated['remarks'] ?? null,
            'created_by' => $request->user()?->id,
            'requested_by' => $request->user()?->id,
            'approved_by' => null,
            'approved_at' => null,
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
            'is_reversal' => false,
        ]);

        return redirect()->route('finance.expense.all')->with('status', 'Expense request submitted (Voucher: ' . $expense->voucher_no . ').');
    }

    public function list(Request $request, string $category = 'all'): View
    {
        $category = strtolower($category);

        $query = FinanceExpense::query()
            ->with(['campus', 'payee', 'expenseType', 'requester', 'approver', 'rejector'])
            ->when($category !== 'all', fn ($q) => $q->where('category', $category))
            ->when($request->integer('campus_id'), fn ($q, $campusId) => $q->where('campus_id', $campusId))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('payment_date', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('payment_date', '<=', $request->input('to')))
            ->orderByDesc('id');

        $titleMap = [
            'all' => 'All Expenses',
            'rent' => 'Building Rent',
            'marketing' => 'Marketing',
            'asset' => 'Asset Purchase',
            'payroll' => 'Payroll',
            'utility' => 'Utility Expenses',
        ];

        return view('finance.expense.list', [
            'title' => $titleMap[$category] ?? 'Expenses',
            'category' => $category,
            'expenses' => $query->paginate(20)->withQueryString(),
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'name', 'code']),
            'filters' => [
                'campus_id' => $request->integer('campus_id') ?: null,
                'status' => $request->input('status'),
                'from' => $request->input('from'),
                'to' => $request->input('to'),
            ],
            'isAdmin' => $this->isAdmin($request),
        ]);
    }

    public function approve(Request $request, FinanceExpense $expense): RedirectResponse
    {
        $this->ensureAdmin($request);

        if (in_array($expense->status, ['approved', 'paid'], true)) {
            return back()->with('status', 'Expense already approved.');
        }

        $expense->update([
            'status' => 'approved',
            'approved_by' => $request->user()?->id,
            'approved_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        return back()->with('status', 'Expense approved.');
    }

    public function reject(Request $request, FinanceExpense $expense): RedirectResponse
    {
        $this->ensureAdmin($request);

        if ($expense->is_reversal) {
            return back()->with('error', 'Reversal entry cannot be rejected.');
        }

        $oldStatus = $expense->status;
        $reason = trim((string) $request->input('reason'));

        $expense->update([
            'status' => 'rejected',
            'rejected_by' => $request->user()?->id,
            'rejected_at' => now(),
            'rejection_reason' => $reason !== '' ? $reason : 'Rejected by admin',
        ]);

        if ($expense->category === 'utility' && $expense->bill_id) {
            $bill = FinanceBill::query()->find($expense->bill_id);
            if ($bill) {
                if ($oldStatus === 'paid') {
                    $bill->paid_amount = max(0, (float) $bill->paid_amount - (float) $expense->amount);
                }

                if ((float) $bill->paid_amount <= 0) {
                    $bill->status = 'unpaid';
                } elseif ((float) $bill->paid_amount < (float) $bill->amount) {
                    $bill->status = 'partial';
                } else {
                    $bill->status = 'paid';
                }

                $bill->save();
            }
        }

        if ($oldStatus === 'paid') {
            FinanceExpense::create([
                'campus_id' => $expense->campus_id,
                'payee_id' => $expense->payee_id,
                'expense_type_id' => $expense->expense_type_id,
                'bill_id' => $expense->bill_id,
                'category' => $expense->category,
                'payment_date' => now()->toDateString(),
                'amount' => ((float) $expense->amount) * -1,
                'payment_method' => $expense->payment_method,
                'payment_ref_no' => $expense->payment_ref_no,
                'bank_name' => $expense->bank_name,
                'cheque_no' => $expense->cheque_no,
                'bank_receipt_no' => $expense->bank_receipt_no,
                'voucher_no' => ($expense->voucher_no ?: 'REV') . '-REV',
                'receipt_no' => ($expense->receipt_no ?: 'REV') . '-REV',
                'attachment_path' => $expense->attachment_path,
                'status' => 'reversed',
                'remarks' => 'Auto reversal for rejected expense #' . $expense->id,
                'created_by' => $request->user()?->id,
                'requested_by' => $request->user()?->id,
                'approved_by' => $request->user()?->id,
                'approved_at' => now(),
                'is_reversal' => true,
            ]);
        }

        return back()->with('status', 'Expense rejected.');
    }

    public function markPaid(Request $request, FinanceExpense $expense): RedirectResponse
    {
        $this->ensureAdmin($request);

        if ($expense->status === 'paid') {
            return back()->with('status', 'Expense already marked as paid.');
        }
        if ($expense->status !== 'approved') {
            return back()->with('error', 'Only approved expense can be marked as paid.');
        }

        $validated = $request->validate([
            'payment_method' => ['nullable', Rule::in(['cash', 'bank', 'cheque'])],
            'payment_ref_no' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:150'],
            'cheque_no' => ['nullable', 'string', 'max:100'],
            'bank_receipt_no' => ['nullable', 'string', 'max:100'],
            'payment_date' => ['nullable', 'date'],
        ]);

        $effectiveMethod = $validated['payment_method'] ?? $expense->payment_method;
        $effectiveBankName = $validated['bank_name'] ?? $expense->bank_name;
        if (in_array((string) $effectiveMethod, ['bank', 'cheque'], true) && empty($effectiveBankName)) {
            return back()->withErrors(['bank_name' => 'Bank name is required for bank/cheque payments.']);
        }

        $expense->update([
            'status' => 'paid',
            'payment_method' => $validated['payment_method'] ?? $expense->payment_method,
            'payment_ref_no' => $validated['payment_ref_no'] ?? $expense->payment_ref_no,
            'bank_name' => $validated['bank_name'] ?? $expense->bank_name,
            'cheque_no' => $validated['cheque_no'] ?? $expense->cheque_no,
            'bank_receipt_no' => $validated['bank_receipt_no'] ?? $expense->bank_receipt_no,
            'payment_date' => $validated['payment_date'] ?? $expense->payment_date ?? now()->toDateString(),
            'approved_by' => $expense->approved_by ?: $request->user()?->id,
            'approved_at' => $expense->approved_at ?: now(),
        ]);

        if ($expense->category === 'utility' && $expense->bill_id) {
            $bill = FinanceBill::query()->find($expense->bill_id);
            if ($bill) {
                $bill->paid_amount = (float) $bill->paid_amount + (float) $expense->amount;
                if ((float) $bill->paid_amount <= 0) {
                    $bill->status = 'unpaid';
                } elseif ((float) $bill->paid_amount < (float) $bill->amount) {
                    $bill->status = 'partial';
                } else {
                    $bill->status = 'paid';
                }
                $bill->save();
            }
        }

        return back()->with('status', 'Expense marked as paid.');
    }

    public function typesIndex(): View
    {
        $this->ensureDefaultExpenseTypes();

        return view('finance.expense.types', [
            'types' => FinanceExpenseType::query()->orderBy('name')->paginate(30),
            'categories' => $this->categories,
        ]);
    }

    public function typesStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:finance_expense_types,name'],
            'category' => ['nullable', Rule::in($this->categories)],
        ]);

        FinanceExpenseType::create([
            'name' => $validated['name'],
            'category' => $validated['category'] ?? 'general',
            'is_active' => true,
        ]);

        return back()->with('status', 'Expense type added.');
    }

    public function payables(Request $request): View
    {
        $scope = strtolower((string) $request->input('scope', ''));

        $payables = FinanceExpense::query()
            ->with(['campus', 'payee', 'expenseType'])
            ->where('amount', '>', 0)
            ->whereIn('status', ['pending', 'approved', 'paid', 'rejected'])
            ->when($scope === 'open', fn ($q) => $q->whereIn('status', ['pending', 'approved']))
            ->when($request->integer('campus_id'), fn ($q, $campusId) => $q->where('campus_id', $campusId))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('finance.payables.index', [
            'payables' => $payables,
            'campuses' => Campus::query()->orderBy('name')->get(),
            'filters' => [
                'scope' => $scope ?: null,
                'campus_id' => $request->integer('campus_id') ?: null,
                'status' => $request->input('status'),
            ],
            'isAdmin' => $this->isAdmin($request),
        ]);
    }

    protected function ensureAdmin(Request $request): void
    {
        if (!$this->isAdmin($request)) {
            abort(403, 'Only admin can perform this action.');
        }
    }

    protected function isAdmin(Request $request): bool
    {
        $user = $request->user();
        if (!$user) {
            return false;
        }
        return $user->roles()->whereIn('slug', ['owner', 'admin'])->exists();
    }

    protected function storeAttachment(?UploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }
        return $file->store('finance/transactions', 'public');
    }

    protected function generateExpenseVoucherNo(string $campusCode): string
    {
        $campusCode = $campusCode !== '' ? $campusCode : 'GEN';
        $prefix = $campusCode . '-EXP-' . now()->format('my');
        $count = FinanceExpense::query()->where('voucher_no', 'like', $prefix . '-%')->count() + 1;
        return $prefix . '-' . str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }

    protected function generateExpenseReceiptNo(string $campusCode, string $modeCode): string
    {
        $campusCode = $campusCode !== '' ? $campusCode : 'GEN';
        $modeCode = $modeCode !== '' ? $modeCode : 'GEN';
        $prefix = $campusCode . '-' . strtoupper($modeCode) . '-' . now()->format('my');
        $count = FinanceExpense::query()->where('receipt_no', 'like', $prefix . '-%')->count() + 1;
        return $prefix . '-' . str_pad((string) $count, 6, '0', STR_PAD_LEFT);
    }

    protected function ensureDefaultExpenseTypes(): void
    {
        $defaults = [
            ['name' => 'General Expense', 'category' => 'general'],
            ['name' => 'Building Rent', 'category' => 'rent'],
            ['name' => 'Marketing Voucher', 'category' => 'marketing'],
            ['name' => 'Asset Purchase', 'category' => 'asset'],
            ['name' => 'Payroll', 'category' => 'payroll'],
            ['name' => 'Utility Bill', 'category' => 'utility'],
        ];

        foreach ($defaults as $row) {
            FinanceExpenseType::query()->firstOrCreate(
                ['name' => $row['name']],
                [
                    'category' => $row['category'],
                    'is_active' => true,
                ]
            );
        }
    }
}
