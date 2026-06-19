<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinanceJournalEntry;
use App\Models\FinanceJournalLine;
use App\Services\FinanceAccountingService;
use App\Support\ResolvesCampusScope;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountingController extends Controller
{
    use ResolvesCampusScope;

    public function journal(Request $request, FinanceAccountingService $accountingService): View
    {
        $accountingService->syncHistoricalTransactions();

        $campusId = $this->effectiveCampusFilter($request->integer('campus_id') ?: null, $request->user());
        $from = $this->resolveDate($request->input('from'), now()->startOfMonth());
        $to = $this->resolveDate($request->input('to'), now()->endOfMonth());
        $search = trim((string) $request->input('search', ''));

        $query = $this->scopeQueryToUserCampus(
            FinanceJournalEntry::query()->with(['campus:id,code,name', 'lines']),
            $request->user()
        )
            ->when($campusId, fn ($builder) => $builder->where('campus_id', $campusId))
            ->whereBetween('entry_date', [$from->toDateString(), $to->toDateString()])
            ->when($search !== '', function ($builder) use ($search) {
                $builder->where(function ($inner) use ($search) {
                    $inner->where('journal_no', 'like', '%' . $search . '%')
                        ->orWhere('reference_number', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            });

        $entries = (clone $query)
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $summary = $this->journalSummary(clone $query);

        return view('finance.accounting.journal', [
            'entries' => $entries,
            'campuses' => $this->campusOptionsForUser($request->user(), ['id', 'code', 'name']),
            'filters' => [
                'campus_id' => $campusId,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'search' => $search,
            ],
            'summary' => $summary,
        ]);
    }

    public function ledger(Request $request, FinanceAccountingService $accountingService): View
    {
        $accountingService->syncHistoricalTransactions();

        $campusId = $this->effectiveCampusFilter($request->integer('campus_id') ?: null, $request->user());
        $from = $this->resolveDate($request->input('from'), now()->startOfMonth());
        $to = $this->resolveDate($request->input('to'), now()->endOfMonth());
        $accountCode = trim((string) $request->input('account_code', ''));
        $accountOptions = collect($accountingService->accountOptions());
        $selectedAccount = $accountOptions->firstWhere('code', $accountCode);

        if (!$selectedAccount) {
            $accountCode = '';
        }

        $summaryBase = $this->ledgerBaseQuery($request)
            ->when($campusId, fn ($builder) => $builder->where('entries.campus_id', $campusId))
            ->whereBetween('entries.entry_date', [$from->toDateString(), $to->toDateString()]);

        $accountSummaries = (clone $summaryBase)
            ->selectRaw('finance_journal_lines.account_code')
            ->selectRaw('finance_journal_lines.account_name')
            ->selectRaw('SUM(finance_journal_lines.debit_amount) as total_debit')
            ->selectRaw('SUM(finance_journal_lines.credit_amount) as total_credit')
            ->groupBy('finance_journal_lines.account_code', 'finance_journal_lines.account_name')
            ->orderBy('finance_journal_lines.account_code')
            ->get()
            ->map(function ($row) use ($accountingService) {
                $row->balance = $accountingService->signedBalanceFor(
                    (string) $row->account_code,
                    (float) $row->total_debit,
                    (float) $row->total_credit
                );

                return $row;
            });

        $openingBalance = 0.0;
        $periodDebit = 0.0;
        $periodCredit = 0.0;
        $closingBalance = 0.0;
        $ledgerRows = collect();

        if ($accountCode !== '') {
            $openingTotals = $this->ledgerBaseQuery($request)
                ->when($campusId, fn ($builder) => $builder->where('entries.campus_id', $campusId))
                ->where('finance_journal_lines.account_code', $accountCode)
                ->whereDate('entries.entry_date', '<', $from->toDateString())
                ->selectRaw('SUM(finance_journal_lines.debit_amount) as total_debit')
                ->selectRaw('SUM(finance_journal_lines.credit_amount) as total_credit')
                ->first();

            $openingBalance = $accountingService->signedBalanceFor(
                $accountCode,
                (float) ($openingTotals->total_debit ?? 0),
                (float) ($openingTotals->total_credit ?? 0)
            );

            $periodSummary = $accountSummaries->firstWhere('account_code', $accountCode);
            $periodDebit = (float) ($periodSummary->total_debit ?? 0);
            $periodCredit = (float) ($periodSummary->total_credit ?? 0);

            $runningBalance = $openingBalance;

            $ledgerRows = $this->ledgerBaseQuery($request)
                ->when($campusId, fn ($builder) => $builder->where('entries.campus_id', $campusId))
                ->where('finance_journal_lines.account_code', $accountCode)
                ->whereBetween('entries.entry_date', [$from->toDateString(), $to->toDateString()])
                ->orderBy('entries.entry_date')
                ->orderBy('entries.id')
                ->orderBy('finance_journal_lines.line_no')
                ->get([
                    'entries.entry_date',
                    'entries.journal_no',
                    'entries.reference_number',
                    'entries.entry_type',
                    'entries.description',
                    'campuses.code as campus_code',
                    'campuses.name as campus_name',
                    'finance_journal_lines.debit_amount',
                    'finance_journal_lines.credit_amount',
                    'finance_journal_lines.memo',
                ])
                ->map(function ($row) use (&$runningBalance, $accountingService, $accountCode) {
                    $movement = $accountingService->signedBalanceFor(
                        $accountCode,
                        (float) $row->debit_amount,
                        (float) $row->credit_amount
                    );

                    $runningBalance = round($runningBalance + $movement, 2);
                    $row->running_balance = $runningBalance;

                    return $row;
                });

            $closingBalance = $runningBalance;
        }

        return view('finance.accounting.ledger', [
            'campuses' => $this->campusOptionsForUser($request->user(), ['id', 'code', 'name']),
            'filters' => [
                'campus_id' => $campusId,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'account_code' => $accountCode,
            ],
            'accountOptions' => $accountOptions,
            'selectedAccount' => $selectedAccount,
            'accountSummaries' => $accountSummaries,
            'openingBalance' => $openingBalance,
            'periodDebit' => $periodDebit,
            'periodCredit' => $periodCredit,
            'closingBalance' => $closingBalance,
            'ledgerRows' => $ledgerRows,
        ]);
    }

    private function ledgerBaseQuery(Request $request)
    {
        return $this->scopeQueryToUserCampus(
            FinanceJournalLine::query()
                ->join('finance_journal_entries as entries', 'finance_journal_lines.finance_journal_entry_id', '=', 'entries.id')
                ->leftJoin('campuses', 'entries.campus_id', '=', 'campuses.id'),
            $request->user(),
            'entries.campus_id'
        );
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

    private function journalSummary($query): array
    {
        $lineTotals = (clone $query)
            ->leftJoin('finance_journal_lines', 'finance_journal_entries.id', '=', 'finance_journal_lines.finance_journal_entry_id')
            ->selectRaw('COUNT(DISTINCT finance_journal_entries.id) as entry_count')
            ->selectRaw('COALESCE(SUM(finance_journal_lines.debit_amount), 0) as total_debit')
            ->selectRaw('COALESCE(SUM(finance_journal_lines.credit_amount), 0) as total_credit')
            ->first();

        return [
            'entries' => (int) ($lineTotals->entry_count ?? 0),
            'debit' => (float) ($lineTotals->total_debit ?? 0),
            'credit' => (float) ($lineTotals->total_credit ?? 0),
        ];
    }
}
