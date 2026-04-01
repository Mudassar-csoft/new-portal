<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\FinanceBill;
use App\Models\FinanceBillPayment;
use App\Models\FinanceBillType;
use App\Models\FinanceBuildingRent;
use App\Models\FinanceExpense;
use App\Models\FinanceExpenseType;
use App\Models\FinancePayee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    /**
     * @var array<int, string>
     */
    private array $categories = ['general', 'utility', 'rent', 'marketing', 'asset', 'payroll'];

    /**
     * @var array<int, string>
     */
    private array $paymentMethods = ['cash', 'bank', 'cheque', 'online_transfer', 'easypaisa', 'jazzcash'];

    public function addForm(Request $request): View
    {
        $this->ensureDefaultExpenseTypes();

        return view('finance.expense.add', [
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'code', 'name', 'campus_type']),
            'payees' => FinancePayee::query()->where('status', 'active')->orderBy('full_name')->get(),
            'settlementPayees' => $this->settlementPayees(),
            'expenseTypes' => FinanceExpenseType::query()->where('is_active', true)->orderBy('name')->get(),
            'billTypes' => FinanceBillType::query()->where('is_active', true)->orderBy('name')->get(),
            'recentExpenses' => FinanceExpense::query()
                ->with(['campus', 'payee', 'expenseType', 'bill.billType', 'rent'])
                ->latest()
                ->limit(10)
                ->get(),
            'paymentMethods' => $this->paymentMethodOptions(),
            'receiptPreviewByMethod' => $this->receiptPreviewByMethod(),
            'isAdmin' => $this->isAdmin($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
            'payee_id' => ['nullable', 'exists:finance_payees,id'],
            'expense_type_id' => ['required', 'exists:finance_expense_types,id'],
            'rent_id' => ['nullable', 'exists:finance_building_rents,id'],
            'bill_id' => ['nullable', 'exists:finance_bills,id'],
            'expense_month' => ['nullable', 'date_format:Y-m'],
            'amount' => ['nullable', 'numeric', 'min:1'],
            'remarks' => ['nullable', 'string'],
        ]);

        $campus = Campus::query()->findOrFail($validated['campus_id']);
        $expenseType = FinanceExpenseType::query()->findOrFail($validated['expense_type_id']);
        $category = $this->normalizeCategory((string) ($expenseType->category ?? 'general'));

        $payload = [
            'campus_id' => $campus->id,
            'payee_id' => $validated['payee_id'] ?? null,
            'expense_type_id' => $expenseType->id,
            'bill_id' => null,
            'rent_id' => null,
            'category' => $category,
            'payment_date' => now()->toDateString(),
            'expense_month' => null,
            'amount' => 0,
            'payment_method' => null,
            'payment_ref_no' => null,
            'bank_name' => null,
            'cheque_no' => null,
            'bank_receipt_no' => null,
            'voucher_no' => $this->generateExpenseVoucherNo($campus->code),
            'receipt_no' => null,
            'attachment_path' => null,
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
        ];

        if ($category === 'rent') {
            $payload = $this->prepareRentExpensePayload($validated, $payload);
        } elseif ($category === 'utility') {
            $payload = $this->prepareUtilityExpensePayload($validated, $payload);
        } else {
            $payload['amount'] = (float) ($validated['amount'] ?? 0);
            if ($payload['amount'] <= 0) {
                return back()->withErrors(['amount' => 'Amount is required for this expense type.'])->withInput();
            }
        }

        $expense = FinanceExpense::create($payload);

        if ($expense->category === 'utility' && $expense->bill_id) {
            $bill = FinanceBill::query()->find($expense->bill_id);
            if ($bill) {
                $bill->update([
                    'amount' => max((float) $bill->amount, (float) $bill->paid_amount + (float) $expense->amount),
                    'status' => 'pending_approval',
                ]);
            }
        }

        return redirect()
            ->route('finance.payables', ['scope' => 'open'])
            ->with('status', 'Expense request submitted (Voucher: ' . $expense->voucher_no . ').');
    }

    public function rentMeta(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
        ]);

        $rent = FinanceBuildingRent::query()
            ->where('campus_id', $validated['campus_id'])
            ->where('is_active', true)
            ->latest('id')
            ->first();

        if (!$rent) {
            return response()->json([
                'rent' => null,
                'blockedMonths' => [],
                'message' => 'No building rent has been added for the selected campus.',
            ]);
        }

        $blockedMonths = FinanceExpense::query()
            ->where('rent_id', $rent->id)
            ->whereIn('status', ['pending', 'approved', 'paid'])
            ->whereNotNull('expense_month')
            ->get(['expense_month', 'status'])
            ->mapWithKeys(function (FinanceExpense $expense) {
                $month = $expense->expense_month?->format('Y-m');

                return $month ? [$month => $expense->status] : [];
            });

        return response()->json([
            'rent' => [
                'id' => $rent->id,
                'agreement_date' => $rent->agreement_date?->toDateString(),
                'agreement_date_label' => $rent->agreement_date?->format('d-M-Y'),
                'address' => $rent->address,
                'rent_amount' => (float) $rent->rent_amount,
                'increment_percentage' => (float) $rent->increment_percentage,
                'current_amount' => (float) $rent->current_amount,
                'advance_payment' => (float) $rent->advance_payment,
                'remarks' => $rent->remarks,
            ],
            'blockedMonths' => $blockedMonths,
            'message' => null,
        ]);
    }

    public function list(Request $request, string $category = 'all'): View
    {
        $category = strtolower($category);

        $query = FinanceExpense::query()
            ->with(['campus', 'payee', 'expenseType', 'requester', 'approver', 'rejector', 'bill.billType', 'rent'])
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
            'settlementPayees' => $this->settlementPayees(),
            'filters' => [
                'campus_id' => $request->integer('campus_id') ?: null,
                'status' => $request->input('status'),
                'from' => $request->input('from'),
                'to' => $request->input('to'),
            ],
            'paymentMethods' => $this->paymentMethodOptions(),
            'receiptPreviewByMethod' => $this->receiptPreviewByMethod(),
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
                'rent_id' => $expense->rent_id,
                'category' => $expense->category,
                'payment_date' => now()->toDateString(),
                'expense_month' => $expense->expense_month,
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
        $this->ensureCanSettle($request, $expense);

        if ($expense->status === 'paid') {
            return back()->with('status', 'Expense already marked as paid.');
        }
        if ($expense->status !== 'approved') {
            return back()->with('error', 'Only approved expense can be paid.');
        }

        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'amount' => ['nullable', 'numeric', 'min:1'],
            'payee_id' => ['nullable', 'exists:finance_payees,id'],
            'payment_method' => ['required', Rule::in($this->paymentMethods)],
            'payment_ref_no' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:150'],
            'cheque_no' => ['nullable', 'string', 'max:100'],
            'bank_receipt_no' => ['nullable', 'string', 'max:100'],
            'attachment' => ['required', 'image', 'max:5120'],
            'remarks' => ['nullable', 'string'],
        ]);

        if (in_array($validated['payment_method'], ['bank', 'cheque'], true) && empty($validated['bank_name'])) {
            return back()->withErrors(['bank_name' => 'Bank name is required for bank or cheque payments.']);
        }

        if ($validated['payment_method'] === 'cheque' && empty($validated['cheque_no'])) {
            return back()->withErrors(['cheque_no' => 'Cheque number is required for cheque payments.']);
        }

        if (in_array($validated['payment_method'], ['bank', 'online_transfer', 'easypaisa', 'jazzcash'], true) && empty($validated['payment_ref_no'])) {
            return back()->withErrors(['payment_ref_no' => 'Payment reference number is required for the selected payment method.']);
        }

        $amount = $this->isAdmin($request)
            ? (float) ($validated['amount'] ?? $expense->amount)
            : (float) $expense->amount;

        if ($amount <= 0) {
            return back()->withErrors(['amount' => 'Amount must be greater than zero.']);
        }

        $attachmentPath = $this->storeAttachment($request->file('attachment'));
        $expenseRemarks = trim(implode(' | ', array_filter([$expense->remarks, $validated['remarks'] ?? null])));

        if ($expense->category === 'utility' && $expense->bill_id) {
            $bill = FinanceBill::query()->with('campus')->find($expense->bill_id);
            if ($bill) {
                $balance = max(0, (float) $bill->amount - (float) $bill->paid_amount);
                if ($amount > $balance) {
                    return back()->withErrors([
                        'amount' => 'Paid amount cannot exceed utility bill balance of Rs. ' . number_format($balance, 0),
                    ]);
                }
            }
        }

        $expense->update([
            'status' => 'paid',
            'amount' => $amount,
            'payee_id' => $validated['payee_id'] ?? $expense->payee_id,
            'payment_method' => $validated['payment_method'],
            'payment_ref_no' => $validated['payment_ref_no'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'cheque_no' => $validated['cheque_no'] ?? null,
            'bank_receipt_no' => $validated['bank_receipt_no'] ?? null,
            'payment_date' => $validated['payment_date'],
            'receipt_no' => $expense->receipt_no ?: $this->generateSettlementReceiptNo($validated['payment_method']),
            'attachment_path' => $attachmentPath,
            'remarks' => $expenseRemarks !== '' ? $expenseRemarks : $expense->remarks,
            'approved_by' => $expense->approved_by ?: $request->user()?->id,
            'approved_at' => $expense->approved_at ?: now(),
        ]);

        if ($expense->category === 'utility' && $expense->bill_id) {
            $bill = FinanceBill::query()->with('campus')->find($expense->bill_id);
            if ($bill) {
                $this->upsertUtilityBillPayment($expense, $bill, $request);
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

        return back()->with('status', 'Expense paid successfully.');
    }

    public function typesIndex(): View
    {
        $this->ensureDefaultExpenseTypes();

        return view('finance.expense.types', [
            'types' => FinanceExpenseType::query()->orderBy('name')->paginate(30),
            'categories' => $this->categories,
        ]);
    }

    public function typesStore(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:finance_expense_types,name'],
            'category' => ['nullable', Rule::in($this->categories)],
        ]);

        $expenseType = FinanceExpenseType::create([
            'name' => $validated['name'],
            'category' => $validated['category'] ?? 'general',
            'is_active' => true,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Expense type added.',
                'type' => [
                    'id' => $expenseType->id,
                    'name' => $expenseType->name,
                    'category' => $expenseType->category,
                ],
            ]);
        }

        return back()->with('status', 'Expense type added.');
    }

    public function payables(Request $request): View
    {
        $scope = strtolower((string) $request->input('scope', ''));

        $payables = FinanceExpense::query()
            ->with(['campus', 'payee', 'expenseType', 'bill.billType', 'rent'])
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
            'settlementPayees' => $this->settlementPayees(),
            'filters' => [
                'scope' => $scope ?: null,
                'campus_id' => $request->integer('campus_id') ?: null,
                'status' => $request->input('status'),
            ],
            'paymentMethods' => $this->paymentMethodOptions(),
            'receiptPreviewByMethod' => $this->receiptPreviewByMethod(),
            'isAdmin' => $this->isAdmin($request),
        ]);
    }

    protected function prepareRentExpensePayload(array $validated, array $payload): array
    {
        if (empty($validated['rent_id'])) {
            throw ValidationException::withMessages([
                'rent_id' => 'Building rent is required for rent expense type.',
            ]);
        }
        if (empty($validated['expense_month'])) {
            throw ValidationException::withMessages([
                'expense_month' => 'Month is required for building rent.',
            ]);
        }

        $rent = FinanceBuildingRent::query()
            ->where('campus_id', $validated['campus_id'])
            ->findOrFail($validated['rent_id']);

        $month = Carbon::createFromFormat('Y-m', $validated['expense_month'])->startOfMonth();

        $existing = FinanceExpense::query()
            ->where('rent_id', $rent->id)
            ->whereDate('expense_month', $month->toDateString())
            ->whereIn('status', ['pending', 'approved', 'paid'])
            ->exists();

        if ($existing) {
            throw ValidationException::withMessages([
                'expense_month' => 'Selected building rent month already has a request or has been paid.',
            ]);
        }

        $payload['rent_id'] = $rent->id;
        $payload['expense_month'] = $month->toDateString();
        $payload['payment_date'] = $month->copy()->endOfMonth()->toDateString();
        $payload['amount'] = (float) $rent->current_amount;
        $payload['remarks'] = trim(implode(' | ', array_filter([
            'Building rent request for ' . $month->format('F Y'),
            $rent->address,
            $payload['remarks'] ?? null,
        ])));

        return $payload;
    }

    protected function prepareUtilityExpensePayload(array $validated, array $payload): array
    {
        if (empty($validated['bill_id'])) {
            throw ValidationException::withMessages([
                'bill_id' => 'Utility bill reference is required for utility expense type.',
            ]);
        }
        $requestedAmount = (float) ($validated['amount'] ?? 0);
        if ($requestedAmount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Amount is required for utility bill expense.',
            ]);
        }

        $bill = FinanceBill::query()
            ->with(['billType', 'campus'])
            ->where('campus_id', $validated['campus_id'])
            ->findOrFail($validated['bill_id']);

        if (!in_array((string) $bill->status, ['unpaid', 'partial'], true)) {
            throw ValidationException::withMessages([
                'bill_id' => 'Selected bill is not available for approval.',
            ]);
        }

        $balance = max(0, (float) $bill->amount - (float) $bill->paid_amount);
        if ((float) $bill->amount > 0 && $balance <= 0) {
            throw ValidationException::withMessages([
                'bill_id' => 'Selected bill is already fully paid.',
            ]);
        }
        if ((float) $bill->amount > 0 && $requestedAmount > $balance) {
            throw ValidationException::withMessages([
                'amount' => 'Amount cannot exceed utility bill balance of Rs. ' . number_format($balance, 0),
            ]);
        }

        $payload['bill_id'] = $bill->id;
        $payload['payee_id'] = $payload['payee_id'] ?: $bill->billType?->payee_id;
        $payload['expense_month'] = $bill->bill_month?->startOfMonth()?->toDateString();
        $payload['payment_date'] = now()->toDateString();
        $payload['amount'] = $requestedAmount;
        $payload['remarks'] = trim(implode(' | ', array_filter([
            'Utility bill approval request',
            $bill->billType?->display_name,
            'Ref: ' . $bill->reference_number,
            $payload['remarks'] ?? null,
        ])));

        return $payload;
    }

    protected function normalizeCategory(string $category): string
    {
        return in_array($category, $this->categories, true) ? $category : 'general';
    }

    protected function ensureAdmin(Request $request): void
    {
        if (!$this->isAdmin($request)) {
            abort(403, 'Only admin can perform this action.');
        }
    }

    protected function ensureCanSettle(Request $request, FinanceExpense $expense): void
    {
        $user = $request->user();
        if (!$user) {
            abort(403, 'You are not allowed to pay this expense.');
        }

        if ($this->isAdmin($request)) {
            return;
        }

        if ((int) $expense->requested_by === (int) $user->id || (int) $expense->created_by === (int) $user->id) {
            return;
        }

        abort(403, 'Only admin or the requesting campus user can pay this expense.');
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

    protected function generateSettlementReceiptNo(string $paymentMethod): string
    {
        $modeCodes = [
            'cash' => 'CSH',
            'bank' => 'BNK',
            'cheque' => 'CHQ',
            'online_transfer' => 'OTR',
            'easypaisa' => 'EZY',
            'jazzcash' => 'JZZ',
        ];

        $prefix = 'INV-' . ($modeCodes[$paymentMethod] ?? 'GEN') . '-' . now()->format('my');
        $count = FinanceExpense::query()->where('receipt_no', 'like', $prefix . '-%')->count() + 1;

        return $prefix . '-' . str_pad((string) $count, 7, '0', STR_PAD_LEFT);
    }

    protected function upsertUtilityBillPayment(FinanceExpense $expense, FinanceBill $bill, Request $request): FinanceBillPayment
    {
        $paymentDate = $expense->payment_date?->toDateString() ?? now()->toDateString();
        $paymentMethod = (string) ($expense->payment_method ?? 'cash');

        $query = FinanceBillPayment::query()
            ->where('bill_id', $bill->id)
            ->whereDate('payment_date', $paymentDate)
            ->where('paid_amount', (float) $expense->amount)
            ->where('payment_method', $paymentMethod);

        if ($expense->payment_ref_no) {
            $query->where('payment_ref_no', $expense->payment_ref_no);
        } else {
            $query->whereNull('payment_ref_no');
        }

        if ($expense->attachment_path) {
            $query->where('attachment_path', $expense->attachment_path);
        } else {
            $query->whereNull('attachment_path');
        }

        $data = [
            'bill_id' => $bill->id,
            'payment_date' => $paymentDate,
            'paid_amount' => $expense->amount,
            'payment_method' => $paymentMethod,
            'payment_ref_no' => $expense->payment_ref_no,
            'bank_name' => $expense->bank_name,
            'cheque_no' => $expense->cheque_no,
            'bank_receipt_no' => $expense->bank_receipt_no,
            'attachment_path' => $expense->attachment_path,
            'remarks' => $expense->remarks,
            'created_by' => $expense->created_by ?: $request->user()?->id,
        ];

        $payment = $query->latest('id')->first();
        if ($payment) {
            $payment->fill($data);
            if (!$payment->receipt_no) {
                $payment->receipt_no = $this->generateBillPaymentReceiptNo($paymentMethod);
            }
            $payment->save();

            return $payment;
        }

        $data['receipt_no'] = $this->generateBillPaymentReceiptNo($paymentMethod);

        return FinanceBillPayment::create($data);
    }

    protected function generateBillPaymentReceiptNo(string $method): string
    {
        $modeCodes = [
            'cash' => 'CSH',
            'bank' => 'BNK',
            'cheque' => 'CHQ',
            'online_transfer' => 'OTR',
            'easypaisa' => 'EZY',
            'jazzcash' => 'JZZ',
        ];

        $prefix = 'INV-' . ($modeCodes[$method] ?? 'GEN') . '-' . now()->format('my');
        $count = FinanceBillPayment::query()->where('receipt_no', 'like', $prefix . '-%')->count() + 1;

        return $prefix . '-' . str_pad((string) $count, 7, '0', STR_PAD_LEFT);
    }

    protected function paymentMethodOptions(): array
    {
        return [
            'cash' => 'Cash',
            'bank' => 'Bank',
            'cheque' => 'Cheque',
            'online_transfer' => 'Online Transfer',
            'easypaisa' => 'EasyPaisa',
            'jazzcash' => 'JazzCash',
        ];
    }

    protected function settlementPayees()
    {
        return FinancePayee::query()
            ->whereIn('type', ['supplier', 'payee'])
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'display_name', 'company_name', 'type', 'status']);
    }

    protected function receiptPreviewByMethod(): array
    {
        $modeCodes = [
            'cash' => 'CSH',
            'bank' => 'BNK',
            'cheque' => 'CHQ',
            'online_transfer' => 'OTR',
            'easypaisa' => 'EZY',
            'jazzcash' => 'JZZ',
        ];

        $previews = [];
        foreach ($modeCodes as $method => $code) {
            $prefix = 'INV-' . $code . '-' . now()->format('my');
            $count = FinanceExpense::query()->where('receipt_no', 'like', $prefix . '-%')->count() + 1;
            $previews[$method] = $prefix . '-' . str_pad((string) $count, 7, '0', STR_PAD_LEFT);
        }

        return $previews;
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
