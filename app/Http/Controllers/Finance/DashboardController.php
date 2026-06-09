<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\FinanceExpense;
use App\Models\FinanceOtherCharge;
use App\Models\FinanceOtherChargePayment;
use App\Models\FinanceRoyalty;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        FinanceOtherCharge::syncLifecycleStatuses();

        $campusId = $request->integer('campus_id') ?: null;
        $from = $this->resolveDate($request->input('from'), now()->startOfMonth());
        $to = $this->resolveDate($request->input('to'), now()->endOfMonth());

        $registrationIncome = Registration::query()
            ->where('status', 'registered')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('registered_at', [$from, $to])
            ->sum('net_payable');

        $otherChargesBreakdown = $this->otherChargesBreakdown($campusId, $from, $to);

        $coWorkingIncome = (float) ($otherChargesBreakdown->coworking_total ?? 0);
        $otherIncome = (float) ($otherChargesBreakdown->other_total ?? 0);

        $franchiseRoyaltyIncome = (float) FinanceRoyalty::query()
            ->where('status', 'paid')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('paid_at', [$from, $to])
            ->sum('amount');

        $totalIncome = (float) $registrationIncome + $coWorkingIncome + $franchiseRoyaltyIncome + $otherIncome;

        $expenseApprovedPaid = (float) FinanceExpense::query()
            ->whereIn('status', ['approved', 'paid'])
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $expenseReversed = (float) FinanceExpense::query()
            ->where('status', 'reversed')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $totalExpense = $expenseApprovedPaid + $expenseReversed;

        $payablesPending = (float) FinanceExpense::query()
            ->where('status', 'pending')
            ->where('amount', '>', 0)
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->sum('amount');

        $payablesApproved = (float) FinanceExpense::query()
            ->where('status', 'approved')
            ->where('amount', '>', 0)
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->sum('amount');

        $payables = $payablesPending + $payablesApproved;

        $pendingOtherReceivables = $this->pendingOtherReceivablesTotal($campusId);

        $pendingRoyaltyReceivables = (float) FinanceRoyalty::query()
            ->where('status', 'pending')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->sum('amount');

        $receivables = $pendingOtherReceivables + $pendingRoyaltyReceivables;

        $expenseByCategory = FinanceExpense::query()
            ->selectRaw('category, SUM(amount) as total')
            ->whereIn('status', ['approved', 'paid'])
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('category')
            ->pluck('total', 'category');

        $recentIncomeRows = collect();

        $recentIncomeRows = $recentIncomeRows
            ->merge(
                Registration::query()
                    ->with(['campus:id,code,name'])
                    ->where('status', 'registered')
                    ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
                    ->whereBetween('registered_at', [$from, $to])
                    ->latest('registered_at')
                    ->limit(10)
                    ->get(['id', 'campus_id', 'registration_number', 'student_name', 'net_payable', 'registered_at'])
                    ->map(fn ($registration) => [
                        'type' => 'Admission Fee',
                        'reference' => $registration->registration_number ?: 'N/A',
                        'name' => $registration->student_name ?: 'N/A',
                        'campus' => $registration->campus->code ?? 'N/A',
                        'date' => optional($registration->registered_at)->format('Y-m-d') ?: 'N/A',
                        'amount' => (float) $registration->net_payable,
                        'sort_at' => $registration->registered_at?->timestamp ?? 0,
                    ])
            )
            ->merge($this->recentOtherIncomeRows($campusId, $from, $to))
            ->merge(
                FinanceRoyalty::query()
                    ->with(['campus:id,code,name'])
                    ->where('status', 'paid')
                    ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
                    ->whereBetween('paid_at', [$from, $to])
                    ->latest('paid_at')
                    ->limit(10)
                    ->get(['id', 'campus_id', 'amount', 'paid_at', 'remarks'])
                    ->map(fn ($royalty) => [
                        'type' => 'Franchise Royalty',
                        'reference' => 'Royalty',
                        'name' => $royalty->remarks ?: 'Franchise Royalty',
                        'campus' => $royalty->campus->code ?? 'N/A',
                        'date' => optional($royalty->paid_at)->format('Y-m-d') ?: 'N/A',
                        'amount' => (float) $royalty->amount,
                        'sort_at' => $royalty->paid_at?->timestamp ?? 0,
                    ])
            )
            ->sortByDesc('sort_at')
            ->take(10)
            ->values();

        $recentExpenseRows = FinanceExpense::query()
            ->with(['campus:id,code,name', 'payee:id,full_name', 'expenseType:id,name'])
            ->whereIn('status', ['approved', 'paid', 'reversed'])
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
            ->latest('payment_date')
            ->limit(10)
            ->get(['id', 'campus_id', 'payee_id', 'expense_type_id', 'voucher_no', 'category', 'payment_date', 'amount', 'status'])
            ->map(fn ($expense) => [
                'type' => $expense->expenseType->name ?? ucfirst((string) $expense->category),
                'reference' => $expense->voucher_no ?: 'N/A',
                'name' => $expense->payee->full_name ?? ucfirst((string) $expense->category),
                'campus' => $expense->campus->code ?? 'N/A',
                'date' => optional($expense->payment_date)->format('Y-m-d') ?: 'N/A',
                'status' => ucfirst((string) $expense->status),
                'amount' => (float) $expense->amount,
            ]);

        return view('finance.dashboard', [
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'code', 'name', 'campus_type']),
            'filters' => [
                'campus_id' => $campusId,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'stats' => [
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'payables' => $payables,
                'receivables' => $receivables,
                'net_cashflow' => $totalIncome - $totalExpense,
            ],
            'incomeMix' => [
                'admission_fee' => (float) $registrationIncome,
                'coworking_fee' => $coWorkingIncome,
                'franchise_royalty' => $franchiseRoyaltyIncome,
                'other_income' => $otherIncome,
            ],
            'expenseMix' => [
                'rent' => (float) ($expenseByCategory['rent'] ?? 0),
                'utility' => (float) ($expenseByCategory['utility'] ?? 0),
                'marketing' => (float) ($expenseByCategory['marketing'] ?? 0),
                'asset' => (float) ($expenseByCategory['asset'] ?? 0),
                'payroll' => (float) ($expenseByCategory['payroll'] ?? 0),
                'general' => (float) ($expenseByCategory['general'] ?? 0),
            ],
            'recentIncomeRows' => $recentIncomeRows,
            'recentExpenseRows' => $recentExpenseRows,
        ]);
    }

    public function incomeDetails(Request $request): View
    {
        FinanceOtherCharge::syncLifecycleStatuses();

        $campusId = $request->integer('campus_id') ?: null;
        $from = $this->resolveDate($request->input('from'), now()->startOfMonth());
        $to = $this->resolveDate($request->input('to'), now()->endOfMonth());

        $registrationIncome = (float) Registration::query()
            ->where('status', 'registered')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('registered_at', [$from, $to])
            ->sum('net_payable');

        $otherChargesBreakdown = $this->otherChargesBreakdown($campusId, $from, $to);

        $coWorkingIncome = (float) ($otherChargesBreakdown->coworking_total ?? 0);
        $otherIncome = (float) ($otherChargesBreakdown->other_total ?? 0);

        $franchiseRoyaltyIncome = (float) FinanceRoyalty::query()
            ->where('status', 'paid')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('paid_at', [$from, $to])
            ->sum('amount');

        $registrations = Registration::query()
            ->with(['campus:id,code,name'])
            ->where('status', 'registered')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('registered_at', [$from, $to])
            ->orderByDesc('registered_at')
            ->limit(100)
            ->get(['id', 'campus_id', 'registration_number', 'student_name', 'net_payable', 'registered_at']);

        $coworkingCharges = $this->incomeChargeRows($campusId, $from, $to, true);
        $otherCharges = $this->incomeChargeRows($campusId, $from, $to, false);

        $royalties = FinanceRoyalty::query()
            ->with(['campus:id,code,name'])
            ->where('status', 'paid')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('paid_at', [$from, $to])
            ->orderByDesc('paid_at')
            ->limit(100)
            ->get(['id', 'campus_id', 'amount', 'paid_at', 'due_date', 'remarks']);

        return view('finance.income.details', [
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'code', 'name']),
            'filters' => [
                'campus_id' => $campusId,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'summary' => [
                'admission_fee' => $registrationIncome,
                'coworking_fee' => $coWorkingIncome,
                'franchise_royalty' => $franchiseRoyaltyIncome,
                'other_income' => $otherIncome,
                'total_income' => $registrationIncome + $coWorkingIncome + $franchiseRoyaltyIncome + $otherIncome,
            ],
            'registrations' => $registrations,
            'coworkingCharges' => $coworkingCharges,
            'otherCharges' => $otherCharges,
            'royalties' => $royalties,
        ]);
    }

    public function expenseDetails(Request $request): View
    {
        FinanceOtherCharge::syncLifecycleStatuses();

        $campusId = $request->integer('campus_id') ?: null;
        $from = $this->resolveDate($request->input('from'), now()->startOfMonth());
        $to = $this->resolveDate($request->input('to'), now()->endOfMonth());

        $expenseByCategory = FinanceExpense::query()
            ->selectRaw('category, SUM(amount) as total')
            ->whereIn('status', ['approved', 'paid'])
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('category')
            ->pluck('total', 'category');

        $expenseReversed = (float) FinanceExpense::query()
            ->where('status', 'reversed')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $components = [
            ['label' => 'Rent', 'amount' => (float) ($expenseByCategory['rent'] ?? 0)],
            ['label' => 'Utility', 'amount' => (float) ($expenseByCategory['utility'] ?? 0)],
            ['label' => 'Marketing', 'amount' => (float) ($expenseByCategory['marketing'] ?? 0)],
            ['label' => 'Asset', 'amount' => (float) ($expenseByCategory['asset'] ?? 0)],
            ['label' => 'Payroll', 'amount' => (float) ($expenseByCategory['payroll'] ?? 0)],
            ['label' => 'General', 'amount' => (float) ($expenseByCategory['general'] ?? 0)],
            ['label' => 'Reversed', 'amount' => $expenseReversed],
        ];

        $totalExpense = array_reduce($components, fn ($sum, $item) => $sum + (float) ($item['amount'] ?? 0), 0.0);

        $expenses = FinanceExpense::query()
            ->with(['campus:id,code,name', 'payee:id,full_name', 'expenseType:id,name'])
            ->whereIn('status', ['approved', 'paid', 'reversed'])
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->limit(150)
            ->get(['id', 'campus_id', 'payee_id', 'expense_type_id', 'category', 'voucher_no', 'payment_date', 'amount', 'status']);

        return view('finance.expense.details', [
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'code', 'name']),
            'filters' => [
                'campus_id' => $campusId,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'summary' => [
                'total_expense' => $totalExpense,
            ],
            'components' => $components,
            'expenses' => $expenses,
        ]);
    }

    public function payablesDetails(Request $request): View
    {
        FinanceOtherCharge::syncLifecycleStatuses();

        $campusId = $request->integer('campus_id') ?: null;
        $from = $this->resolveDate($request->input('from'), now()->startOfMonth());
        $to = $this->resolveDate($request->input('to'), now()->endOfMonth());

        $pendingAmount = (float) FinanceExpense::query()
            ->where('status', 'pending')
            ->where('amount', '>', 0)
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->sum('amount');

        $approvedAmount = (float) FinanceExpense::query()
            ->where('status', 'approved')
            ->where('amount', '>', 0)
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->sum('amount');

        $payables = FinanceExpense::query()
            ->with(['campus:id,code,name', 'payee:id,full_name', 'expenseType:id,name'])
            ->where('amount', '>', 0)
            ->whereIn('status', ['pending', 'approved'])
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->limit(150)
            ->get(['id', 'campus_id', 'payee_id', 'expense_type_id', 'category', 'voucher_no', 'payment_date', 'amount', 'status']);

        return view('finance.payables.details', [
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'code', 'name']),
            'filters' => [
                'campus_id' => $campusId,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'summary' => [
                'pending' => $pendingAmount,
                'approved' => $approvedAmount,
                'total' => $pendingAmount + $approvedAmount,
            ],
            'payables' => $payables,
        ]);
    }

    public function receivablesDetails(Request $request): View
    {
        FinanceOtherCharge::syncLifecycleStatuses();

        $campusId = $request->integer('campus_id') ?: null;
        $from = $this->resolveDate($request->input('from'), now()->startOfMonth());
        $to = $this->resolveDate($request->input('to'), now()->endOfMonth());

        $pendingOther = $this->pendingOtherReceivablesTotal($campusId);

        $pendingRoyalty = (float) FinanceRoyalty::query()
            ->where('status', 'pending')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->sum('amount');

        $otherCharges = $this->openOtherChargeRows($campusId);

        $royalties = FinanceRoyalty::query()
            ->with(['campus:id,code,name'])
            ->where('status', 'pending')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->orderByDesc('id')
            ->limit(150)
            ->get(['id', 'campus_id', 'amount', 'due_date', 'remarks']);

        return view('finance.receivables.details', [
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'code', 'name']),
            'filters' => [
                'campus_id' => $campusId,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'summary' => [
                'pending_other' => $pendingOther,
                'pending_royalty' => $pendingRoyalty,
                'total' => $pendingOther + $pendingRoyalty,
            ],
            'otherCharges' => $otherCharges,
            'royalties' => $royalties,
        ]);
    }

    public function netCashflowDetails(Request $request): View
    {
        FinanceOtherCharge::syncLifecycleStatuses();

        $campusId = $request->integer('campus_id') ?: null;
        $from = $this->resolveDate($request->input('from'), now()->startOfMonth());
        $to = $this->resolveDate($request->input('to'), now()->endOfMonth());

        $registrationIncome = (float) Registration::query()
            ->where('status', 'registered')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('registered_at', [$from, $to])
            ->sum('net_payable');

        $otherChargesBreakdown = $this->otherChargesBreakdown($campusId, $from, $to);

        $coWorkingIncome = (float) ($otherChargesBreakdown->coworking_total ?? 0);
        $otherIncome = (float) ($otherChargesBreakdown->other_total ?? 0);

        $franchiseRoyaltyIncome = (float) FinanceRoyalty::query()
            ->where('status', 'paid')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('paid_at', [$from, $to])
            ->sum('amount');

        $totalIncome = $registrationIncome + $coWorkingIncome + $franchiseRoyaltyIncome + $otherIncome;

        $expenseByCategory = FinanceExpense::query()
            ->selectRaw('category, SUM(amount) as total')
            ->whereIn('status', ['approved', 'paid'])
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('category')
            ->pluck('total', 'category');

        $expenseReversed = (float) FinanceExpense::query()
            ->where('status', 'reversed')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $totalExpense = (float) (
            (float) ($expenseByCategory['rent'] ?? 0)
            + (float) ($expenseByCategory['utility'] ?? 0)
            + (float) ($expenseByCategory['marketing'] ?? 0)
            + (float) ($expenseByCategory['asset'] ?? 0)
            + (float) ($expenseByCategory['payroll'] ?? 0)
            + (float) ($expenseByCategory['general'] ?? 0)
            + $expenseReversed
        );

        $netCashflow = $totalIncome - $totalExpense;

        return view('finance.net_cashflow.details', [
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'code', 'name']),
            'filters' => [
                'campus_id' => $campusId,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'summary' => [
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'net_cashflow' => $netCashflow,
            ],
            'incomeComponents' => [
                ['label' => 'Admission Fee', 'amount' => $registrationIncome],
                ['label' => 'Coworking Fee', 'amount' => $coWorkingIncome],
                ['label' => 'Franchise Royalty', 'amount' => $franchiseRoyaltyIncome],
                ['label' => 'Other Income', 'amount' => $otherIncome],
            ],
            'expenseComponents' => [
                ['label' => 'Rent', 'amount' => (float) ($expenseByCategory['rent'] ?? 0)],
                ['label' => 'Utility', 'amount' => (float) ($expenseByCategory['utility'] ?? 0)],
                ['label' => 'Marketing', 'amount' => (float) ($expenseByCategory['marketing'] ?? 0)],
                ['label' => 'Asset', 'amount' => (float) ($expenseByCategory['asset'] ?? 0)],
                ['label' => 'Payroll', 'amount' => (float) ($expenseByCategory['payroll'] ?? 0)],
                ['label' => 'General', 'amount' => (float) ($expenseByCategory['general'] ?? 0)],
                ['label' => 'Reversed', 'amount' => $expenseReversed],
            ],
        ]);
    }

    private function resolveDate(?string $date, Carbon $fallback): Carbon
    {
        if (!$date) {
            return $fallback->copy();
        }

        try {
            return Carbon::parse($date);
        } catch (\Throwable $e) {
            return $fallback->copy();
        }
    }

    private function invoiceSchemaReady(): bool
    {
        return FinanceOtherCharge::hasInvoiceSchema();
    }

    private function coWorkingCondition(): string
    {
        return "LOWER(COALESCE(charge_types.category, '')) = 'coworking'
            OR LOWER(COALESCE(charge_types.name, '')) LIKE '%cowork%'";
    }

    private function otherChargesBreakdown(?int $campusId, Carbon $from, Carbon $to): object
    {
        $condition = $this->coWorkingCondition();

        if ($this->invoiceSchemaReady()) {
            return FinanceOtherChargePayment::query()
                ->join('finance_other_charges', 'finance_other_charge_payments.finance_other_charge_id', '=', 'finance_other_charges.id')
                ->leftJoin('finance_charge_types as charge_types', 'finance_other_charges.charge_type_id', '=', 'charge_types.id')
                ->when($campusId, fn ($q) => $q->where('finance_other_charges.campus_id', $campusId))
                ->whereBetween('finance_other_charge_payments.payment_date', [$from->toDateString(), $to->toDateString()])
                ->selectRaw("SUM(CASE WHEN {$condition} THEN finance_other_charge_payments.amount ELSE 0 END) as coworking_total")
                ->selectRaw("SUM(CASE WHEN {$condition} THEN 0 ELSE finance_other_charge_payments.amount END) as other_total")
                ->first();
        }

        return FinanceOtherCharge::query()
            ->leftJoin('finance_charge_types as charge_types', 'finance_other_charges.charge_type_id', '=', 'charge_types.id')
            ->when($campusId, fn ($q) => $q->where('finance_other_charges.campus_id', $campusId))
            ->where('finance_other_charges.status', 'paid')
            ->whereBetween('finance_other_charges.paid_at', [$from, $to])
            ->selectRaw("SUM(CASE WHEN {$condition} THEN finance_other_charges.net_amount ELSE 0 END) as coworking_total")
            ->selectRaw("SUM(CASE WHEN {$condition} THEN 0 ELSE finance_other_charges.net_amount END) as other_total")
            ->first();
    }

    private function pendingOtherReceivablesTotal(?int $campusId): float
    {
        if ($this->invoiceSchemaReady()) {
            return (float) FinanceOtherCharge::query()
                ->whereIn('status', ['pending', 'partial', 'overdue'])
                ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
                ->sum('balance_amount');
        }

        return (float) FinanceOtherCharge::query()
            ->where('status', 'pending')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->sum('net_amount');
    }

    private function recentOtherIncomeRows(?int $campusId, Carbon $from, Carbon $to): Collection
    {
        if ($this->invoiceSchemaReady()) {
            return FinanceOtherChargePayment::query()
                ->with(['charge.campus:id,code,name', 'charge.chargeType:id,name,category'])
                ->whereHas('charge', fn ($q) => $q->when($campusId, fn ($inner) => $inner->where('campus_id', $campusId)))
                ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
                ->latest('payment_date')
                ->limit(10)
                ->get(['id', 'finance_other_charge_id', 'amount', 'payment_date'])
                ->map(function (FinanceOtherChargePayment $payment) {
                    $charge = $payment->charge;
                    $isCoworking = $charge?->chargeType
                        && (
                            strtolower((string) $charge->chargeType->category) === 'coworking'
                            || str_contains(strtolower((string) $charge->chargeType->name), 'cowork')
                        );

                    return [
                        'type' => $isCoworking ? 'Coworking Fee' : ($charge?->chargeType->name ?: 'Other Income'),
                        'reference' => $charge?->invoice_number ?: $charge?->voucher_number ?: 'N/A',
                        'name' => $charge?->student_name ?: ($charge?->chargeType->name ?? 'N/A'),
                        'campus' => $charge?->campus->code ?? 'N/A',
                        'date' => optional($payment->payment_date)->format('Y-m-d') ?: 'N/A',
                        'amount' => (float) $payment->amount,
                        'sort_at' => $payment->payment_date?->timestamp ?? 0,
                    ];
                });
        }

        return FinanceOtherCharge::query()
            ->with(['campus:id,code,name', 'chargeType:id,name,category'])
            ->where('status', 'paid')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('paid_at', [$from, $to])
            ->latest('paid_at')
            ->limit(10)
            ->get(['id', 'campus_id', 'student_name', 'net_amount', 'paid_at', 'voucher_number', 'charge_type_id'])
            ->map(function (FinanceOtherCharge $charge) {
                $isCoworking = $charge->chargeType
                    && (
                        strtolower((string) $charge->chargeType->category) === 'coworking'
                        || str_contains(strtolower((string) $charge->chargeType->name), 'cowork')
                    );

                return [
                    'type' => $isCoworking ? 'Coworking Fee' : ($charge->chargeType->name ?: 'Other Income'),
                    'reference' => $charge->voucher_number ?: 'N/A',
                    'name' => $charge->student_name ?: ($charge->chargeType->name ?? 'N/A'),
                    'campus' => $charge->campus->code ?? 'N/A',
                    'date' => optional($charge->paid_at)->format('Y-m-d') ?: 'N/A',
                    'amount' => (float) $charge->net_amount,
                    'sort_at' => $charge->paid_at?->timestamp ?? 0,
                ];
            });
    }

    private function incomeChargeRows(?int $campusId, Carbon $from, Carbon $to, bool $coworking): Collection
    {
        $chargeTypeIsCoworking = function ($query): void {
            $query->where(function ($inner): void {
                $inner->whereRaw("LOWER(COALESCE(category, '')) = 'coworking'")
                    ->orWhereRaw("LOWER(COALESCE(name, '')) LIKE '%cowork%'");
            });
        };

        if ($this->invoiceSchemaReady()) {
            $payments = FinanceOtherChargePayment::query()
                ->with(['charge.campus:id,code,name', 'charge.chargeType:id,name,category'])
                ->whereHas('charge', fn ($q) => $q->when($campusId, fn ($inner) => $inner->where('campus_id', $campusId)))
                ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()]);

            $payments = $coworking
                ? $payments->whereHas('charge.chargeType', $chargeTypeIsCoworking)
                : $payments->whereDoesntHave('charge.chargeType', $chargeTypeIsCoworking);

            return $payments
                ->orderByDesc('payment_date')
                ->limit(100)
                ->get(['id', 'finance_other_charge_id', 'amount', 'payment_date'])
                ->map(function (FinanceOtherChargePayment $payment) use ($coworking) {
                    $charge = $payment->charge;

                    return (object) [
                        'reference' => $charge?->invoice_number ?: $charge?->voucher_number ?: 'N/A',
                        $coworking ? 'source' : 'type' => $coworking
                            ? ($charge?->student_name ?: ($charge?->chargeType->name ?? 'Coworking'))
                            : ($charge?->chargeType->name ?? 'Other'),
                        'campus' => $charge?->campus->code ?? 'N/A',
                        'date' => optional($payment->payment_date)->format('Y-m-d') ?: 'N/A',
                        'amount' => (float) $payment->amount,
                    ];
                });
        }

        $charges = FinanceOtherCharge::query()
            ->with(['campus:id,code,name', 'chargeType:id,name,category'])
            ->where('status', 'paid')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('paid_at', [$from, $to]);

        $charges = $coworking
            ? $charges->whereHas('chargeType', $chargeTypeIsCoworking)
            : $charges->whereDoesntHave('chargeType', $chargeTypeIsCoworking);

        return $charges
            ->orderByDesc('paid_at')
            ->limit(100)
            ->get(['id', 'campus_id', 'student_name', 'net_amount', 'paid_at', 'voucher_number', 'charge_type_id'])
            ->map(function (FinanceOtherCharge $charge) use ($coworking) {
                return (object) [
                    'reference' => $charge->voucher_number ?: 'N/A',
                    $coworking ? 'source' : 'type' => $coworking
                        ? ($charge->student_name ?: ($charge->chargeType->name ?? 'Coworking'))
                        : ($charge->chargeType->name ?? 'Other'),
                    'campus' => $charge->campus->code ?? 'N/A',
                    'date' => optional($charge->paid_at)->format('Y-m-d') ?: 'N/A',
                    'amount' => (float) $charge->net_amount,
                ];
            });
    }

    private function openOtherChargeRows(?int $campusId): Collection
    {
        if ($this->invoiceSchemaReady()) {
            return FinanceOtherCharge::query()
                ->with(['campus:id,code,name', 'chargeType:id,name'])
                ->whereIn('status', ['pending', 'partial', 'overdue'])
                ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
                ->orderByDesc('id')
                ->limit(150)
                ->get([
                    'id',
                    'campus_id',
                    'charge_type_id',
                    'student_name',
                    'invoice_number',
                    'voucher_number',
                    'balance_amount',
                    'invoice_date',
                    'due_date',
                    'status',
                ]);
        }

        return FinanceOtherCharge::query()
            ->with(['campus:id,code,name', 'chargeType:id,name'])
            ->where('status', 'pending')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->orderByDesc('id')
            ->limit(150)
            ->get([
                'id',
                'campus_id',
                'charge_type_id',
                'student_name',
                'voucher_number',
                'net_amount',
                'created_at',
                'status',
            ])
            ->map(function (FinanceOtherCharge $charge) {
                $charge->invoice_number = $charge->voucher_number;
                $charge->balance_amount = $charge->net_amount;
                $charge->invoice_date = $charge->created_at;
                $charge->due_date = null;

                return $charge;
            });
    }
}
