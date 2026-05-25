<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\FinanceChargeType;
use App\Models\FinanceOtherCharge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReceivableController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureDefaultChargeTypes();

        $charges = FinanceOtherCharge::query()
            ->with(['campus', 'chargeType'])
            ->when($request->integer('campus_id'), fn ($q, $campusId) => $q->where('campus_id', $campusId))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('finance.receivables.index', [
            'charges' => $charges,
            'campuses' => Campus::query()->orderBy('name')->get(),
            'chargeTypes' => FinanceChargeType::query()->where('is_active', true)->orderBy('name')->get(),
            'filters' => [
                'campus_id' => $request->integer('campus_id') ?: null,
                'status' => $request->input('status'),
            ],
            'canCreateReceivables' => $this->canCreateReceivables($request),
            'canUpdateReceivables' => $this->canUpdateReceivables($request),
            'canViewReceivables' => $this->canViewReceivables($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
            'charge_type_id' => ['required', 'exists:finance_charge_types,id'],
            'registration_id' => ['nullable', 'exists:registrations,id'],
            'admission_id' => ['nullable', 'exists:admissions,id'],
            'student_name' => ['nullable', 'string', 'max:150'],
            'amount' => ['required', 'numeric', 'min:1'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', Rule::in(['cash', 'bank', 'cheque'])],
            'payment_ref_no' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:150'],
            'cheque_no' => ['nullable', 'string', 'max:100'],
            'bank_receipt_no' => ['nullable', 'string', 'max:100'],
            'attachment' => ['nullable', 'image', 'max:5120'],
            'remarks' => ['nullable', 'string'],
        ]);

        $campus = Campus::query()->findOrFail($validated['campus_id']);
        $discount = (float) ($validated['discount_amount'] ?? 0);
        $net = max(0, (float) $validated['amount'] - $discount);
        $isPaid = !empty($validated['payment_method']);
        $path = $this->storeAttachment($request->file('attachment'));

        if ($isPaid && !$path) {
            return back()->withErrors(['attachment' => 'Transaction image is required for paid manual invoices.'])->withInput();
        }
        if ($isPaid && in_array($validated['payment_method'], ['bank', 'cheque'], true) && empty($validated['bank_name'])) {
            return back()->withErrors(['bank_name' => 'Bank name is required for bank/cheque payments.'])->withInput();
        }

        FinanceOtherCharge::create([
            'campus_id' => $validated['campus_id'],
            'registration_id' => $validated['registration_id'] ?? null,
            'admission_id' => $validated['admission_id'] ?? null,
            'student_name' => $validated['student_name'] ?? null,
            'charge_type_id' => $validated['charge_type_id'],
            'amount' => $validated['amount'],
            'discount_amount' => $discount,
            'net_amount' => $net,
            'voucher_number' => $this->generateManualVoucherNo($campus->code),
            'status' => $isPaid ? 'paid' : 'pending',
            'paid_at' => $isPaid ? now() : null,
            'payment_method' => $validated['payment_method'] ?? null,
            'payment_ref_no' => $validated['payment_ref_no'] ?? ($isPaid ? $this->generateCampusReceiptRef($campus->code) : null),
            'bank_name' => $validated['bank_name'] ?? null,
            'cheque_no' => $validated['cheque_no'] ?? null,
            'bank_receipt_no' => $validated['bank_receipt_no'] ?? null,
            'attachment_path' => $path,
            'remarks' => $validated['remarks'] ?? null,
            'created_by' => $request->user()?->id,
        ]);

        return back()->with('status', 'Manual invoice created.');
    }

    public function collect(Request $request, FinanceOtherCharge $charge): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method' => ['required', Rule::in(['cash', 'bank', 'cheque'])],
            'payment_ref_no' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:150'],
            'cheque_no' => ['nullable', 'string', 'max:100'],
            'bank_receipt_no' => ['nullable', 'string', 'max:100'],
            'attachment' => ['nullable', 'image', 'max:5120'],
        ]);

        $path = $this->storeAttachment($request->file('attachment'));
        if (!$path && empty($charge->attachment_path)) {
            return back()->withErrors(['attachment' => 'Transaction image is required while collecting payment.']);
        }
        if (in_array($validated['payment_method'], ['bank', 'cheque'], true) && empty($validated['bank_name'])) {
            return back()->withErrors(['bank_name' => 'Bank name is required for bank/cheque payments.']);
        }
        if ($path) {
            $charge->attachment_path = $path;
        }

        $charge->status = 'paid';
        $charge->paid_at = now();
        $charge->payment_method = $validated['payment_method'];
        $charge->payment_ref_no = $validated['payment_ref_no'] ?? $this->generatePaymentRef($charge);
        $charge->bank_name = $validated['bank_name'] ?? null;
        $charge->cheque_no = $validated['cheque_no'] ?? null;
        $charge->bank_receipt_no = $validated['bank_receipt_no'] ?? null;
        $charge->save();

        return back()->with('status', 'Receivable collected successfully.');
    }

    private function storeAttachment(?UploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }
        return $file->store('finance/transactions', 'public');
    }

    private function generateManualVoucherNo(string $campusCode): string
    {
        $campusCode = $campusCode !== '' ? $campusCode : 'GEN';
        $prefix = $campusCode . '-MIV-' . now()->format('my');
        $count = FinanceOtherCharge::query()->where('voucher_number', 'like', $prefix . '-%')->count() + 1;
        return $prefix . '-' . str_pad((string) $count, 6, '0', STR_PAD_LEFT);
    }

    private function generateCampusReceiptRef(string $campusCode): string
    {
        $campusCode = $campusCode !== '' ? $campusCode : 'GEN';
        $prefix = $campusCode . '-RCV-' . now()->format('my');
        $count = FinanceOtherCharge::query()->where('payment_ref_no', 'like', $prefix . '-%')->count() + 1;
        return $prefix . '-' . str_pad((string) $count, 6, '0', STR_PAD_LEFT);
    }

    private function generatePaymentRef(FinanceOtherCharge $charge): string
    {
        $campusCode = $charge->campus?->code ?: 'GEN';
        return $this->generateCampusReceiptRef($campusCode);
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
        return $request->user()?->hasAnyPermission(['finance.receivable.view']) ?? false;
    }
}
