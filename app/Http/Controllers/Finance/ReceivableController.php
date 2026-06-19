<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\FinanceChargeType;
use App\Models\FinanceOtherCharge;
use App\Models\FinanceOtherChargePayment;
use App\Services\FinanceAccountingService;
use App\Support\ResolvesCampusScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReceivableController extends Controller
{
    use ResolvesCampusScope;

    public function index(Request $request): View
    {
        $this->ensureDefaultChargeTypes();
        FinanceOtherCharge::syncLifecycleStatuses();
        $invoiceSchemaReady = FinanceOtherCharge::hasInvoiceSchema();

        $campusId = $this->effectiveCampusFilter($request->integer('campus_id') ?: null, $request->user());
        $status = $request->input('status');
        $search = trim((string) $request->input('search', ''));

        $chargesQuery = $this->scopeQueryToUserCampus(
            FinanceOtherCharge::query()->with(['campus', 'chargeType']),
            $request->user()
        )
            ->when($invoiceSchemaReady, fn ($q) => $q->with('latestPayment'))
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->when(
                $invoiceSchemaReady && in_array($status, ['pending', 'partial', 'paid', 'overdue'], true),
                fn ($q) => $q->where('status', $status)
            )
            ->when(
                !$invoiceSchemaReady && in_array($status, ['pending', 'paid'], true),
                fn ($q) => $q->where('status', $status)
            )
            ->when($search !== '', function ($query) use ($search, $invoiceSchemaReady) {
                $query->where(function ($inner) use ($search, $invoiceSchemaReady) {
                    if ($invoiceSchemaReady) {
                        $inner->where('invoice_number', 'like', '%' . $search . '%')
                            ->orWhere('bill_to_phone', 'like', '%' . $search . '%')
                            ->orWhere('bill_to_email', 'like', '%' . $search . '%');
                    }

                    $inner->orWhere('voucher_number', 'like', '%' . $search . '%')
                        ->orWhere('student_name', 'like', '%' . $search . '%');
                });
            });

        $charges = (clone $chargesQuery)
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $summaryBase = clone $chargesQuery;
        $summary = $invoiceSchemaReady
            ? [
                'outstanding' => (float) (clone $summaryBase)->open()->sum('balance_amount'),
                'overdue' => (float) (clone $summaryBase)->where('status', 'overdue')->sum('balance_amount'),
                'overdue_count' => (int) (clone $summaryBase)->where('status', 'overdue')->count(),
                'collected_this_month' => (float) FinanceOtherChargePayment::query()
                    ->whereHas('charge', function ($query) use ($request, $campusId) {
                        $this->scopeQueryToUserCampus($query, $request->user())
                            ->when($campusId, fn ($inner) => $inner->where('campus_id', $campusId));
                    })
                    ->whereBetween('payment_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                    ->sum('amount'),
            ]
            : [
                'outstanding' => (float) (clone $summaryBase)->where('status', 'pending')->sum('net_amount'),
                'overdue' => 0,
                'overdue_count' => 0,
                'collected_this_month' => (float) FinanceOtherCharge::query()
                    ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
                    ->where('status', 'paid')
                    ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->sum('net_amount'),
            ];

        return view('finance.receivables.index', [
            'charges' => $charges,
            'campuses' => $this->campusOptionsForUser($request->user(), ['id', 'code', 'name']),
            'chargeTypes' => FinanceChargeType::query()->where('is_active', true)->orderBy('name')->get(),
            'filters' => [
                'campus_id' => $campusId,
                'status' => $status,
                'search' => $search,
            ],
            'summary' => $summary,
            'invoiceSchemaReady' => $invoiceSchemaReady,
            'canCreateReceivables' => $this->canCreateReceivables($request),
            'canUpdateReceivables' => $this->canUpdateReceivables($request),
            'canViewReceivables' => $this->canViewReceivables($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureInvoiceSchemaReady();

        $validated = $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
            'charge_type_id' => ['required', 'exists:finance_charge_types,id'],
            'registration_id' => ['nullable', 'exists:registrations,id'],
            'admission_id' => ['nullable', 'exists:admissions,id'],
            'student_name' => ['required', 'string', 'max:150'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:invoice_date'],
            'bill_to_email' => ['nullable', 'email', 'max:150'],
            'bill_to_phone' => ['nullable', 'string', 'max:50'],
            'bill_to_address' => ['nullable', 'string'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $this->ensureCampusAccess((int) $validated['campus_id'], $request->user());

        $campus = Campus::query()->findOrFail($validated['campus_id']);
        $items = $this->sanitizeItems($validated['items']);
        $subtotal = (float) $items->sum('line_total');
        $discount = (float) ($validated['discount_amount'] ?? 0);

        if ($discount > $subtotal) {
            return back()->withErrors(['discount_amount' => 'Discount cannot exceed invoice subtotal.'])->withInput();
        }

        $net = max(0, $subtotal - $discount);
        $dueDate = Carbon::parse($validated['due_date']);

        $charge = DB::transaction(function () use ($request, $validated, $campus, $items, $subtotal, $discount, $net, $dueDate) {
            $invoiceNumber = $this->generateInvoiceNumber($campus->code);

            $charge = FinanceOtherCharge::query()->create([
                'campus_id' => $validated['campus_id'],
                'registration_id' => $validated['registration_id'] ?? null,
                'admission_id' => $validated['admission_id'] ?? null,
                'student_name' => $validated['student_name'],
                'charge_type_id' => $validated['charge_type_id'],
                'amount' => $subtotal,
                'discount_amount' => $discount,
                'net_amount' => $net,
                'voucher_number' => $invoiceNumber,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'bill_to_email' => $validated['bill_to_email'] ?? null,
                'bill_to_phone' => $validated['bill_to_phone'] ?? null,
                'bill_to_address' => $validated['bill_to_address'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'status' => $this->resolveStatus($dueDate, 0, $net),
                'paid_at' => null,
                'paid_amount' => 0,
                'balance_amount' => $net,
                'remarks' => $validated['remarks'] ?? null,
                'created_by' => $request->user()?->id,
            ]);

            $charge->items()->createMany(
                $items->map(fn (array $item, int $index) => [
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['line_total'],
                    'sort_order' => $index + 1,
                ])->all()
            );

            return $charge->fresh(['campus', 'chargeType']);
        });

        app(FinanceAccountingService::class)->syncInvoiceIssue($charge);

        return redirect()
            ->route('finance.receivables.show', $charge)
            ->with('status', 'Invoice created successfully.');
    }

    public function collect(Request $request, FinanceOtherCharge $charge): RedirectResponse
    {
        $this->ensureInvoiceSchemaReady();
        $this->ensureCampusAccess($charge->campus_id, $request->user());
        FinanceOtherCharge::syncLifecycleStatuses();
        $charge->refresh();

        if ((float) $charge->balance_amount <= 0) {
            return back()->withErrors(['amount' => 'This invoice is already fully paid.']);
        }

        $validated = $request->validate([
            'charge_id' => ['nullable', 'integer'],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['required', Rule::in(['cash', 'online', 'bank', 'cheque'])],
            'payment_ref_no' => ['nullable', 'string', 'max:100'],
            'receiver_name' => ['required', 'string', 'max:150'],
            'depositor_name' => ['required', 'string', 'max:150'],
            'bank_name' => ['nullable', 'string', 'max:150'],
            'account_no' => ['nullable', 'string', 'max:100'],
            'transfer_id' => ['nullable', 'string', 'max:150'],
            'cheque_no' => ['nullable', 'string', 'max:100'],
            'cheque_date' => ['nullable', 'date'],
            'cheque_payee_name' => ['nullable', 'string', 'max:150'],
            'attachment' => ['required', 'image', 'max:5120'],
            'payment_remarks' => ['nullable', 'string'],
        ]);

        $this->validatePaymentMeta($validated);

        if ((float) $validated['amount'] > (float) $charge->balance_amount) {
            return back()->withErrors([
                'amount' => 'Payment amount cannot exceed outstanding balance of Rs. ' . number_format((float) $charge->balance_amount, 0),
            ]);
        }

        $payment = $this->recordPayment(
            $charge,
            $validated,
            $request->file('attachment'),
            $request->user()?->id
        );

        app(FinanceAccountingService::class)->syncInvoicePayment($payment);

        return redirect()
            ->route('finance.receivables.show', $charge)
            ->with('status', 'Invoice payment recorded successfully.');
    }

    public function show(Request $request, FinanceOtherCharge $charge): View
    {
        $this->ensureInvoiceSchemaReady();
        $this->ensureCampusAccess($charge->campus_id, $request->user());
        FinanceOtherCharge::syncLifecycleStatuses();

        $charge = $this->loadInvoiceCharge($charge);

        return view('finance.receivables.show', [
            'charge' => $charge,
            'canUpdateReceivables' => $this->canUpdateReceivables($request),
            'canViewReceivables' => $this->canViewReceivables($request),
        ]);
    }

    public function print(Request $request, FinanceOtherCharge $charge): View
    {
        $this->ensureInvoiceSchemaReady();
        $this->ensureCampusAccess($charge->campus_id, $request->user());
        FinanceOtherCharge::syncLifecycleStatuses();

        return view('finance.receivables.print', [
            'charge' => $this->loadInvoiceCharge($charge),
        ]);
    }

    private function storeAttachment(?UploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }
        return $file->store('finance/transactions', 'public');
    }

    private function generateInvoiceNumber(string $campusCode): string
    {
        $campusCode = $campusCode !== '' ? $campusCode : 'GEN';
        $prefix = $campusCode . '-INV-' . now()->format('my');
        $count = FinanceOtherCharge::query()
            ->where('invoice_number', 'like', $prefix . '-%')
            ->count() + 1;
        return $prefix . '-' . str_pad((string) $count, 6, '0', STR_PAD_LEFT);
    }

    private function generateCampusReceiptRef(string $campusCode): string
    {
        $campusCode = $campusCode !== '' ? $campusCode : 'GEN';
        $prefix = $campusCode . '-RCV-' . now()->format('my');
        $count = FinanceOtherChargePayment::query()
            ->where('payment_ref_no', 'like', $prefix . '-%')
            ->count() + 1;
        return $prefix . '-' . str_pad((string) $count, 6, '0', STR_PAD_LEFT);
    }

    private function generatePaymentRef(FinanceOtherCharge $charge): string
    {
        $campusCode = $charge->campus?->code ?: 'GEN';
        return $this->generateCampusReceiptRef($campusCode);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return Collection<int, array<string, float|string>>
     */
    private function sanitizeItems(array $items): Collection
    {
        return collect($items)
            ->map(function (array $item): array {
                $quantity = (float) ($item['quantity'] ?? 0);
                $unitPrice = (float) ($item['unit_price'] ?? 0);

                return [
                    'description' => trim((string) ($item['description'] ?? '')),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => round($quantity * $unitPrice, 2),
                ];
            })
            ->filter(fn (array $item): bool => $item['description'] !== '' && $item['quantity'] > 0)
            ->values();
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function validatePaymentMeta(array $validated): void
    {
        $paymentMethod = $this->normalizePaymentMethod((string) ($validated['payment_method'] ?? ''));

        if (in_array($paymentMethod, ['online', 'cheque'], true)
            && empty($validated['bank_name'])
        ) {
            throw ValidationException::withMessages([
                'bank_name' => 'Bank name is required for online transfer or cheque payments.',
            ]);
        }

        if ($paymentMethod === 'online' && empty($validated['account_no'])) {
            throw ValidationException::withMessages([
                'account_no' => 'Account number is required for online transfer payments.',
            ]);
        }

        if ($paymentMethod === 'online' && empty($validated['transfer_id'])) {
            throw ValidationException::withMessages([
                'transfer_id' => 'Transfer ID is required for online transfer payments.',
            ]);
        }

        if ($paymentMethod === 'cheque' && empty($validated['cheque_no'])) {
            throw ValidationException::withMessages([
                'cheque_no' => 'Cheque number is required for cheque payments.',
            ]);
        }

        if ($paymentMethod === 'cheque' && empty($validated['cheque_date'])) {
            throw ValidationException::withMessages([
                'cheque_date' => 'Cheque date is required for cheque payments.',
            ]);
        }

        if ($paymentMethod === 'cheque' && empty($validated['cheque_payee_name'])) {
            throw ValidationException::withMessages([
                'cheque_payee_name' => 'Payee name is required for cheque payments.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function recordPayment(
        FinanceOtherCharge $charge,
        array $validated,
        ?UploadedFile $attachment,
        ?int $userId
    ): FinanceOtherChargePayment {
        $charge->loadMissing('campus');

        return DB::transaction(function () use ($charge, $validated, $attachment, $userId) {
            $paymentAmount = (float) $validated['amount'];
            $paymentDate = Carbon::parse((string) $validated['payment_date']);
            $path = $this->storeAttachment($attachment);
            $paymentMethod = $this->normalizePaymentMethod((string) ($validated['payment_method'] ?? ''));
            $paymentRef = !empty($validated['payment_ref_no'])
                ? (string) $validated['payment_ref_no']
                : $this->generatePaymentRef($charge);

            $payment = $charge->payments()->create([
                'payment_date' => $paymentDate->toDateString(),
                'amount' => $paymentAmount,
                'payment_method' => $paymentMethod,
                'payment_ref_no' => $paymentRef,
                'receiver_name' => $validated['receiver_name'] ?? null,
                'depositor_name' => $validated['depositor_name'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'account_no' => $validated['account_no'] ?? null,
                'transfer_id' => $validated['transfer_id'] ?? null,
                'cheque_no' => $validated['cheque_no'] ?? null,
                'cheque_date' => $validated['cheque_date'] ?? null,
                'cheque_payee_name' => $validated['cheque_payee_name'] ?? null,
                'attachment_path' => $path,
                'remarks' => $validated['payment_remarks'] ?? null,
                'created_by' => $userId,
            ]);

            $paidAmount = min((float) $charge->net_amount, (float) $charge->paid_amount + $paymentAmount);
            $balanceAmount = max(0, (float) $charge->net_amount - $paidAmount);
            $status = $this->resolveStatus($charge->due_date, $paidAmount, (float) $charge->net_amount);

            $charge->fill([
                'status' => $status,
                'paid_at' => $paymentDate->copy()->endOfDay(),
                'paid_amount' => $paidAmount,
                'balance_amount' => $balanceAmount,
                'payment_method' => $payment->payment_method,
                'payment_ref_no' => $payment->payment_ref_no,
                'bank_name' => $payment->bank_name,
                'cheque_no' => $payment->cheque_no,
                'bank_receipt_no' => null,
                'attachment_path' => $payment->attachment_path ?: $charge->attachment_path,
            ])->save();

            return $payment;
        });
    }

    private function resolveStatus(?Carbon $dueDate, float $paidAmount, float $netAmount): string
    {
        $balance = max(0, $netAmount - $paidAmount);

        if ($balance <= 0) {
            return 'paid';
        }

        if ($paidAmount > 0) {
            return $dueDate && $dueDate->isPast() ? 'overdue' : 'partial';
        }

        return $dueDate && $dueDate->isPast() ? 'overdue' : 'pending';
    }

    private function ensureDefaultChargeTypes(): void
    {
        $defaults = [
            ['name' => 'Certificate Fee', 'category' => 'certificate', 'default_amount' => 2500],
            ['name' => 'Fine', 'category' => 'fine', 'default_amount' => 1000],
            ['name' => 'Other Receivable', 'category' => 'other', 'default_amount' => 0],
        ];

        foreach ($defaults as $row) {
            FinanceChargeType::query()->firstOrCreate(
                ['name' => $row['name']],
                [
                    'category' => $row['category'],
                    'default_amount' => $row['default_amount'],
                    'is_active' => true,
                ]
            );
        }
    }

    private function canCreateReceivables(Request $request): bool
    {
        return $request->user()?->hasAnyPermission(['finance.receivable.create']) ?? false;
    }

    private function canUpdateReceivables(Request $request): bool
    {
        return $request->user()?->hasAnyPermission(['finance.receivable.update']) ?? false;
    }

    private function canViewReceivables(Request $request): bool
    {
        return $request->user()?->hasAnyPermission([
            'finance.receivable.view',
            'finance.receivable.update',
            'finance.receivable.create',
        ]) ?? false;
    }

    private function ensureInvoiceSchemaReady(): void
    {
        if (!FinanceOtherCharge::hasInvoiceSchema()) {
            abort(500, 'Invoice generator upgrade is not available until the latest finance migration is run.');
        }
    }

    private function normalizePaymentMethod(string $paymentMethod): string
    {
        return $paymentMethod === 'bank' ? 'online' : $paymentMethod;
    }

    private function loadInvoiceCharge(FinanceOtherCharge $charge): FinanceOtherCharge
    {
        return $charge->fresh([
            'campus',
            'chargeType',
            'items',
            'payments.creator',
            'creator',
        ]);
    }
}
