<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\FinanceExpense;
use App\Models\FinanceOtherCharge;
use App\Models\FinanceRoyalty;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $campusId = $request->integer('campus_id') ?: null;
        $from = $this->resolveDate($request->input('from'), now()->startOfMonth());
        $to = $this->resolveDate($request->input('to'), now()->endOfMonth());

        $registrationIncome = Registration::query()
            ->where('status', 'registered')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('registered_at', [$from, $to])
            ->sum('net_payable');

        $coWorkingCondition = "LOWER(COALESCE(charge_types.category, '')) = 'coworking'
            OR LOWER(COALESCE(charge_types.name, '')) LIKE '%cowork%'";

        $otherChargesBreakdown = FinanceOtherCharge::query()
            ->leftJoin('finance_charge_types as charge_types', 'finance_other_charges.charge_type_id', '=', 'charge_types.id')
            ->when($campusId, fn ($q) => $q->where('finance_other_charges.campus_id', $campusId))
            ->where('finance_other_charges.status', 'paid')
            ->whereBetween('finance_other_charges.paid_at', [$from, $to])
            ->selectRaw("SUM(CASE WHEN {$coWorkingCondition} THEN finance_other_charges.net_amount ELSE 0 END) as coworking_total")
            ->selectRaw("SUM(CASE WHEN {$coWorkingCondition} THEN 0 ELSE finance_other_charges.net_amount END) as other_total")
            ->first();

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

        $pendingOtherReceivables = (float) FinanceOtherCharge::query()
            ->where('status', 'pending')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->sum('net_amount');

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
        ]);
    }

    public function incomeDetails(Request $request): View
    {
        $campusId = $request->integer('campus_id') ?: null;
        $from = $this->resolveDate($request->input('from'), now()->startOfMonth());
        $to = $this->resolveDate($request->input('to'), now()->endOfMonth());

        $registrationIncome = (float) Registration::query()
            ->where('status', 'registered')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('registered_at', [$from, $to])
            ->sum('net_payable');

        $coWorkingCondition = "LOWER(COALESCE(charge_types.category, '')) = 'coworking'
            OR LOWER(COALESCE(charge_types.name, '')) LIKE '%cowork%'";

        $otherChargesBreakdown = FinanceOtherCharge::query()
            ->leftJoin('finance_charge_types as charge_types', 'finance_other_charges.charge_type_id', '=', 'charge_types.id')
            ->when($campusId, fn ($q) => $q->where('finance_other_charges.campus_id', $campusId))
            ->where('finance_other_charges.status', 'paid')
            ->whereBetween('finance_other_charges.paid_at', [$from, $to])
            ->selectRaw("SUM(CASE WHEN {$coWorkingCondition} THEN finance_other_charges.net_amount ELSE 0 END) as coworking_total")
            ->selectRaw("SUM(CASE WHEN {$coWorkingCondition} THEN 0 ELSE finance_other_charges.net_amount END) as other_total")
            ->first();

        $coWorkingIncome = (float) ($otherChargesBreakdown->coworking_total ?? 0);
        $otherIncome = (float) ($otherChargesBreakdown->other_total ?? 0);

        $franchiseRoyaltyIncome = (float) FinanceRoyalty::query()
            ->where('status', 'paid')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('paid_at', [$from, $to])
            ->sum('amount');

        $chargeTypeIsCoworking = function ($query): void {
            $query->where(function ($inner): void {
                $inner->whereRaw("LOWER(COALESCE(category, '')) = 'coworking'")
                    ->orWhereRaw("LOWER(COALESCE(name, '')) LIKE '%cowork%'");
            });
        };

        $registrations = Registration::query()
            ->with(['campus:id,code,name'])
            ->where('status', 'registered')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('registered_at', [$from, $to])
            ->orderByDesc('registered_at')
            ->limit(100)
            ->get(['id', 'campus_id', 'registration_number', 'student_name', 'net_payable', 'registered_at']);

        $paidChargesBase = FinanceOtherCharge::query()
            ->with(['campus:id,code,name', 'chargeType:id,name,category'])
            ->where('status', 'paid')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('paid_at', [$from, $to]);

        $coworkingCharges = (clone $paidChargesBase)
            ->whereHas('chargeType', $chargeTypeIsCoworking)
            ->orderByDesc('paid_at')
            ->limit(100)
            ->get(['id', 'campus_id', 'student_name', 'net_amount', 'paid_at', 'voucher_number', 'charge_type_id']);

        $otherCharges = (clone $paidChargesBase)
            ->whereDoesntHave('chargeType', $chargeTypeIsCoworking)
            ->orderByDesc('paid_at')
            ->limit(100)
            ->get(['id', 'campus_id', 'student_name', 'net_amount', 'paid_at', 'voucher_number', 'charge_type_id']);

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
        $campusId = $request->integer('campus_id') ?: null;
        $from = $this->resolveDate($request->input('from'), now()->startOfMonth());
        $to = $this->resolveDate($request->input('to'), now()->endOfMonth());

        $pendingOther = (float) FinanceOtherCharge::query()
            ->where('status', 'pending')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->sum('net_amount');

        $pendingRoyalty = (float) FinanceRoyalty::query()
            ->where('status', 'pending')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->sum('amount');

        $otherCharges = FinanceOtherCharge::query()
            ->with(['campus:id,code,name', 'chargeType:id,name'])
            ->where('status', 'pending')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->orderByDesc('id')
            ->limit(150)
            ->get(['id', 'campus_id', 'charge_type_id', 'student_name', 'voucher_number', 'net_amount', 'created_at']);

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
        $campusId = $request->integer('campus_id') ?: null;
        $from = $this->resolveDate($request->input('from'), now()->startOfMonth());
        $to = $this->resolveDate($request->input('to'), now()->endOfMonth());

        $registrationIncome = (float) Registration::query()
            ->where('status', 'registered')
            ->when($campusId, fn ($q) => $q->where('campus_id', $campusId))
            ->whereBetween('registered_at', [$from, $to])
            ->sum('net_payable');

        $coWorkingCondition = "LOWER(COALESCE(charge_types.category, '')) = 'coworking'
            OR LOWER(COALESCE(charge_types.name, '')) LIKE '%cowork%'";

        $otherChargesBreakdown = FinanceOtherCharge::query()
            ->leftJoin('finance_charge_types as charge_types', 'finance_other_charges.charge_type_id', '=', 'charge_types.id')
            ->when($campusId, fn ($q) => $q->where('finance_other_charges.campus_id', $campusId))
            ->where('finance_other_charges.status', 'paid')
            ->whereBetween('finance_other_charges.paid_at', [$from, $to])
            ->selectRaw("SUM(CASE WHEN {$coWorkingCondition} THEN finance_other_charges.net_amount ELSE 0 END) as coworking_total")
            ->selectRaw("SUM(CASE WHEN {$coWorkingCondition} THEN 0 ELSE finance_other_charges.net_amount END) as other_total")
            ->first();

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
}
