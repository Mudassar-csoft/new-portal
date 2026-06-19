<?php

namespace App\Services;

use App\Models\CoworkingRegistrationReceipt;
use App\Models\FeeCollection;
use App\Models\FinanceExpense;
use App\Models\FinanceJournalEntry;
use App\Models\FinanceOtherCharge;
use App\Models\FinanceOtherChargePayment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinanceAccountingService
{
    private const ACCOUNTS = [
        'cash_bank' => ['code' => '1100', 'name' => 'Cash / Bank', 'normal' => 'debit'],
        'accounts_receivable' => ['code' => '1200', 'name' => 'Accounts Receivable', 'normal' => 'debit'],
        'security_deposit' => ['code' => '2100', 'name' => 'Security Deposit Liability', 'normal' => 'credit'],
        'registration_income' => ['code' => '4000', 'name' => 'Registration Fee Income', 'normal' => 'credit'],
        'admission_income' => ['code' => '4010', 'name' => 'Admission Fee Income', 'normal' => 'credit'],
        'coworking_income' => ['code' => '4020', 'name' => 'Coworking Fee Income', 'normal' => 'credit'],
        'certificate_income' => ['code' => '4030', 'name' => 'Certificate Fee Income', 'normal' => 'credit'],
        'fine_income' => ['code' => '4040', 'name' => 'Fine Income', 'normal' => 'credit'],
        'other_income' => ['code' => '4050', 'name' => 'Other Invoice Income', 'normal' => 'credit'],
        'general_expense' => ['code' => '5000', 'name' => 'General Expense', 'normal' => 'debit'],
        'utility_expense' => ['code' => '5010', 'name' => 'Utility Expense', 'normal' => 'debit'],
        'rent_expense' => ['code' => '5020', 'name' => 'Rent Expense', 'normal' => 'debit'],
        'marketing_expense' => ['code' => '5030', 'name' => 'Marketing Expense', 'normal' => 'debit'],
        'asset_expense' => ['code' => '5040', 'name' => 'Asset Purchase Expense', 'normal' => 'debit'],
        'payroll_expense' => ['code' => '5050', 'name' => 'Payroll Expense', 'normal' => 'debit'],
    ];

    public function syncHistoricalTransactions(): void
    {
        FeeCollection::query()
            ->with(['registration:id,student_name', 'admission:id,student_name'])
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->orderBy('id')
            ->get()
            ->each(fn (FeeCollection $feeCollection) => $this->syncFeeCollection($feeCollection));

        CoworkingRegistrationReceipt::query()
            ->with(['coworkingRegistration:id,full_name'])
            ->whereNotNull('paid_at')
            ->orderBy('id')
            ->get()
            ->each(fn (CoworkingRegistrationReceipt $receipt) => $this->syncCoworkingReceipt($receipt));

        if (FinanceOtherCharge::hasInvoiceSchema()) {
            FinanceOtherCharge::query()
                ->with(['chargeType:id,name,category'])
                ->whereNotNull('invoice_number')
                ->orderBy('id')
                ->get()
                ->each(fn (FinanceOtherCharge $charge) => $this->syncInvoiceIssue($charge));

            FinanceOtherChargePayment::query()
                ->with(['charge:id,campus_id,student_name,invoice_number,voucher_number,charge_type_id', 'charge.chargeType:id,name,category'])
                ->orderBy('id')
                ->get()
                ->each(fn (FinanceOtherChargePayment $payment) => $this->syncInvoicePayment($payment));
        }

        FinanceExpense::query()
            ->with(['payee:id,full_name', 'expenseType:id,name'])
            ->whereIn('status', ['paid', 'reversed', 'rejected'])
            ->whereNotNull('payment_date')
            ->orderBy('id')
            ->get()
            ->each(fn (FinanceExpense $expense) => $this->syncExpense($expense));
    }

    public function syncFeeCollection(FeeCollection $feeCollection): ?FinanceJournalEntry
    {
        if ($feeCollection->status !== 'paid' || !$feeCollection->paid_at) {
            return null;
        }

        $amount = round((float) $feeCollection->net_amount, 2);
        if ($amount <= 0) {
            return null;
        }

        $studentName = $feeCollection->admission?->student_name
            ?? $feeCollection->registration?->student_name
            ?? 'Student';

        return $this->upsertEntry(
            eventKey: 'fee_collection:' . $feeCollection->id,
            entryType: 'fee_collection',
            sourceType: FeeCollection::class,
            sourceId: $feeCollection->id,
            campusId: $feeCollection->campus_id,
            entryDate: Carbon::parse($feeCollection->paid_at)->toDateString(),
            referenceNumber: $feeCollection->receipt_number ?: ('FEE-' . $feeCollection->id),
            description: ucfirst((string) $feeCollection->fee_type) . ' fee collected for ' . $studentName . '.',
            createdBy: $feeCollection->created_by,
            lines: [
                $this->debitLine('cash_bank', $amount, 'Receipt posted'),
                $this->creditLine($feeCollection->fee_type === 'admission' ? 'admission_income' : 'registration_income', $amount, 'Income recognized'),
            ]
        );
    }

    public function syncCoworkingReceipt(CoworkingRegistrationReceipt $receipt): ?FinanceJournalEntry
    {
        if (!$receipt->paid_at) {
            return null;
        }

        $amount = round((float) $receipt->amount, 2);
        if ($amount <= 0) {
            return null;
        }

        $accountKey = match ((string) $receipt->receipt_type) {
            'security_fee' => 'security_deposit',
            'security_refund' => 'security_deposit',
            default => 'coworking_income',
        };

        $isRefund = (string) $receipt->receipt_type === 'security_refund';
        $description = match ((string) $receipt->receipt_type) {
            'security_fee' => 'Security deposit received for coworking registration.',
            'security_refund' => 'Security deposit refunded for coworking registration.',
            default => 'Coworking fee received.',
        };

        $lines = $isRefund
            ? [
                $this->debitLine($accountKey, $amount, 'Liability reduced'),
                $this->creditLine('cash_bank', $amount, 'Refund paid'),
            ]
            : [
                $this->debitLine('cash_bank', $amount, 'Receipt posted'),
                $this->creditLine($accountKey, $amount, $accountKey === 'security_deposit' ? 'Deposit liability recorded' : 'Income recognized'),
            ];

        return $this->upsertEntry(
            eventKey: 'coworking_receipt:' . $receipt->id,
            entryType: 'coworking_receipt',
            sourceType: CoworkingRegistrationReceipt::class,
            sourceId: $receipt->id,
            campusId: $receipt->campus_id,
            entryDate: Carbon::parse($receipt->paid_at)->toDateString(),
            referenceNumber: $receipt->receipt_number ?: ('CWR-' . $receipt->id),
            description: $description,
            createdBy: $receipt->created_by,
            lines: $lines
        );
    }

    public function syncInvoiceIssue(FinanceOtherCharge $charge): ?FinanceJournalEntry
    {
        if (!FinanceOtherCharge::hasInvoiceSchema() || !$charge->invoice_date) {
            return null;
        }

        $amount = round((float) $charge->net_amount, 2);
        if ($amount <= 0) {
            return null;
        }

        $charge->loadMissing('chargeType');

        return $this->upsertEntry(
            eventKey: 'invoice_issue:' . $charge->id,
            entryType: 'invoice_issue',
            sourceType: FinanceOtherCharge::class,
            sourceId: $charge->id,
            campusId: $charge->campus_id,
            entryDate: Carbon::parse($charge->invoice_date)->toDateString(),
            referenceNumber: $charge->invoice_number ?: $charge->voucher_number ?: ('INV-' . $charge->id),
            description: 'Invoice issued for ' . ($charge->student_name ?: 'customer') . '.',
            createdBy: $charge->created_by,
            lines: [
                $this->debitLine('accounts_receivable', $amount, 'Receivable created'),
                $this->creditLine($this->invoiceIncomeAccountKey($charge), $amount, 'Invoice income recognized'),
            ]
        );
    }

    public function syncInvoicePayment(FinanceOtherChargePayment $payment): ?FinanceJournalEntry
    {
        $payment->loadMissing('charge');

        if (!$payment->payment_date || !$payment->charge) {
            return null;
        }

        $amount = round((float) $payment->amount, 2);
        if ($amount <= 0) {
            return null;
        }

        return $this->upsertEntry(
            eventKey: 'invoice_payment:' . $payment->id,
            entryType: 'invoice_payment',
            sourceType: FinanceOtherChargePayment::class,
            sourceId: $payment->id,
            campusId: $payment->charge->campus_id,
            entryDate: Carbon::parse($payment->payment_date)->toDateString(),
            referenceNumber: $payment->payment_ref_no
                ?: $payment->charge->invoice_number
                ?: $payment->charge->voucher_number
                ?: ('INVPAY-' . $payment->id),
            description: 'Invoice payment received for ' . ($payment->charge->student_name ?: 'customer') . '.',
            createdBy: $payment->created_by,
            lines: [
                $this->debitLine('cash_bank', $amount, 'Receipt posted'),
                $this->creditLine('accounts_receivable', $amount, 'Receivable settled'),
            ]
        );
    }

    public function syncExpense(FinanceExpense $expense): ?FinanceJournalEntry
    {
        if (!$expense->payment_date) {
            return null;
        }

        $amount = round(abs((float) $expense->amount), 2);
        if ($amount <= 0) {
            return null;
        }

        $isReversal = (string) $expense->status === 'reversed';
        $isSettledRejectedExpense = (string) $expense->status === 'rejected'
            && (
                filled($expense->receipt_no)
                || filled($expense->payment_method)
                || filled($expense->attachment_path)
            );

        if (!$isReversal && (string) $expense->status !== 'paid' && !$isSettledRejectedExpense) {
            return null;
        }

        $expense->loadMissing(['payee', 'expenseType']);

        $expenseAccountKey = $this->expenseAccountKey($expense);

        return $this->upsertEntry(
            eventKey: 'expense:' . $expense->id,
            entryType: $isReversal ? 'expense_reversal' : 'expense_payment',
            sourceType: FinanceExpense::class,
            sourceId: $expense->id,
            campusId: $expense->campus_id,
            entryDate: Carbon::parse($expense->payment_date)->toDateString(),
            referenceNumber: $expense->voucher_no ?: $expense->receipt_no ?: ('EXP-' . $expense->id),
            description: $isReversal
                ? 'Expense reversal for ' . ($expense->payee?->full_name ?: ($expense->expenseType?->name ?: 'expense')) . '.'
                : 'Expense paid to ' . ($expense->payee?->full_name ?: ($expense->expenseType?->name ?: 'expense')) . '.',
            createdBy: $expense->created_by,
            lines: $isReversal
                ? [
                    $this->debitLine('cash_bank', $amount, 'Amount reversed'),
                    $this->creditLine($expenseAccountKey, $amount, 'Expense reversed'),
                ]
                : [
                    $this->debitLine($expenseAccountKey, $amount, 'Expense recognized'),
                    $this->creditLine('cash_bank', $amount, 'Payment settled'),
                ]
        );
    }

    /**
     * @return array<int, array{key: string, code: string, name: string, normal: string}>
     */
    public function accountOptions(): array
    {
        return collect(self::ACCOUNTS)
            ->map(fn (array $account, string $key) => [
                'key' => $key,
                'code' => $account['code'],
                'name' => $account['name'],
                'normal' => $account['normal'],
            ])
            ->values()
            ->all();
    }

    public function accountMetaByCode(?string $accountCode): ?array
    {
        if (!$accountCode) {
            return null;
        }

        return collect(self::ACCOUNTS)
            ->first(fn (array $account) => $account['code'] === $accountCode);
    }

    public function signedBalanceFor(string $accountCode, float $debitAmount, float $creditAmount): float
    {
        $normalSide = $this->accountMetaByCode($accountCode)['normal'] ?? 'debit';

        return $normalSide === 'credit'
            ? round($creditAmount - $debitAmount, 2)
            : round($debitAmount - $creditAmount, 2);
    }

    private function invoiceIncomeAccountKey(FinanceOtherCharge $charge): string
    {
        $category = strtolower((string) ($charge->chargeType?->category ?? ''));
        $name = strtolower((string) ($charge->chargeType?->name ?? ''));

        if ($category === 'certificate' || str_contains($name, 'certificate')) {
            return 'certificate_income';
        }

        if ($category === 'fine' || str_contains($name, 'fine')) {
            return 'fine_income';
        }

        if ($category === 'coworking' || str_contains($name, 'cowork')) {
            return 'coworking_income';
        }

        return 'other_income';
    }

    private function expenseAccountKey(FinanceExpense $expense): string
    {
        return match (strtolower((string) $expense->category)) {
            'utility' => 'utility_expense',
            'rent' => 'rent_expense',
            'marketing' => 'marketing_expense',
            'asset' => 'asset_expense',
            'payroll' => 'payroll_expense',
            default => 'general_expense',
        };
    }

    /**
     * @param  array<int, array<string, float|int|string|null>>  $lines
     */
    private function upsertEntry(
        string $eventKey,
        string $entryType,
        string $sourceType,
        ?int $sourceId,
        ?int $campusId,
        string $entryDate,
        ?string $referenceNumber,
        ?string $description,
        ?int $createdBy,
        array $lines
    ): ?FinanceJournalEntry {
        $preparedLines = collect($lines)
            ->filter(function (array $line): bool {
                $debit = round((float) ($line['debit_amount'] ?? 0), 2);
                $credit = round((float) ($line['credit_amount'] ?? 0), 2);

                return $debit > 0 || $credit > 0;
            })
            ->values();

        if ($preparedLines->count() < 2) {
            return null;
        }

        return DB::transaction(function () use (
            $eventKey,
            $entryType,
            $sourceType,
            $sourceId,
            $campusId,
            $entryDate,
            $referenceNumber,
            $description,
            $createdBy,
            $preparedLines
        ) {
            $entry = FinanceJournalEntry::query()->where('event_key', $eventKey)->first();

            if (!$entry) {
                $entry = new FinanceJournalEntry([
                    'event_key' => $eventKey,
                    'journal_no' => $this->generateJournalNumber(),
                ]);
            }

            $entry->fill([
                'campus_id' => $campusId,
                'entry_date' => $entryDate,
                'entry_type' => $entryType,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'reference_number' => $referenceNumber,
                'description' => $description,
                'created_by' => $createdBy,
            ]);
            $entry->save();

            $entry->lines()->delete();
            $entry->lines()->createMany(
                $preparedLines->map(function (array $line, int $index): array {
                    return [
                        'line_no' => $index + 1,
                        'account_code' => (string) $line['account_code'],
                        'account_name' => (string) $line['account_name'],
                        'debit_amount' => round((float) ($line['debit_amount'] ?? 0), 2),
                        'credit_amount' => round((float) ($line['credit_amount'] ?? 0), 2),
                        'memo' => $line['memo'] ?? null,
                    ];
                })->all()
            );

            return $entry->load(['campus', 'lines']);
        });
    }

    private function generateJournalNumber(): string
    {
        return 'JRN-' . now()->format('YmdHisv') . '-' . Str::upper(Str::random(4));
    }

    /**
     * @return array<string, float|string>
     */
    private function debitLine(string $accountKey, float $amount, ?string $memo = null): array
    {
        $account = self::ACCOUNTS[$accountKey];

        return [
            'account_code' => $account['code'],
            'account_name' => $account['name'],
            'debit_amount' => round($amount, 2),
            'credit_amount' => 0,
            'memo' => $memo,
        ];
    }

    /**
     * @return array<string, float|string>
     */
    private function creditLine(string $accountKey, float $amount, ?string $memo = null): array
    {
        $account = self::ACCOUNTS[$accountKey];

        return [
            'account_code' => $account['code'],
            'account_name' => $account['name'],
            'debit_amount' => 0,
            'credit_amount' => round($amount, 2),
            'memo' => $memo,
        ];
    }
}
