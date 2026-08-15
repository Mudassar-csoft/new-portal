<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Campus;
use App\Models\CoworkingRegistration;
use App\Models\CoworkingRegistrationReceipt;
use App\Models\FeeCollection;
use App\Models\FinanceExpense;
use App\Models\Lead;
use App\Models\Registration;
use App\Models\User;
use App\Support\ResolvesCampusScope;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    use ResolvesCampusScope;

    private const PAYMENT_METHOD_LABELS = [
        'cash' => 'Cash',
        'bank' => 'Bank',
        'online' => 'Online',
        'unrecorded' => '-',
    ];

    public function dbr(Request $request): View
    {
        $validated = $request->validate([
            'report_date' => ['nullable', 'date'],
            'campus_id' => ['nullable', 'integer', 'exists:campuses,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $user = $request->user();
        $reportDate = Carbon::parse($validated['report_date'] ?? now()->toDateString())->startOfDay();
        $campusId = $this->effectiveCampusFilter((int) ($validated['campus_id'] ?? 0), $user);
        $userId = (int) ($validated['user_id'] ?? 0) ?: null;

        $campuses = $this->campusOptionsForUser($user, ['id', 'code', 'name']);
        $users = User::query()
            ->select(['id', 'name', 'campus_id'])
            ->when($campusId, fn ($query, int $selectedCampusId) => $query->where('campus_id', $selectedCampusId))
            ->orderBy('name')
            ->get();
        $selectedUser = $userId ? User::query()->select(['id', 'name'])->find($userId) : null;
        $selectedCampus = $campusId ? $campuses->firstWhere('id', $campusId) : null;
        $showCampusColumn = ! $selectedCampus;
        $showUserColumn = false;

        $leadRows = $this->leadRows($reportDate, $campusId, $userId, $user);
        $registrationRows = $this->registrationRows($reportDate, $campusId, $userId, $user);
        $coworkingRegistrationRows = $this->coworkingRegistrationRows($reportDate, $campusId, $userId, $user);
        $admissionRows = $this->admissionRows($reportDate, $campusId, $userId, $user);
        $feeRows = $this->feeRows($reportDate, $campusId, $userId, $user);
        $coworkingRows = $this->coworkingRows($reportDate, $campusId, $userId, $user);
        $expenseRows = $this->expenseRows($reportDate, $campusId, $userId, $user);

        $leadMatrix = $this->buildCourseMatrix(
            $leadRows->map(fn (Lead $lead): array => [
                'campus' => $this->campusCode($lead->campus),
                'user' => $this->leadOwnerName($lead),
                'label' => $this->leadLabel($lead),
            ]),
            $showCampusColumn,
            $showUserColumn
        );

        $registrationMatrix = $this->buildCourseMatrix(
            $registrationRows
                ->map(fn (Registration $registration): array => [
                    'campus' => $this->campusCode($registration->campus),
                    'user' => $this->leadOwnerName($registration->lead),
                    'label' => $this->programLabel($registration->program),
                ])
                ->merge(
                    $coworkingRegistrationRows->map(fn (CoworkingRegistration $registration): array => [
                        'campus' => $this->campusCode($registration->campus),
                        'user' => $this->leadOwnerName($registration->lead),
                        'label' => 'Coworking Space',
                    ])
                ),
            $showCampusColumn,
            $showUserColumn
        );

        $enrollmentMatrix = $this->buildCourseMatrix(
            $admissionRows->map(fn (Admission $admission): array => [
                'campus' => $this->campusCode($admission->campus),
                'user' => $this->leadOwnerName($admission->registration?->lead),
                'label' => $this->programLabel($admission->program),
            ]),
            $showCampusColumn,
            $showUserColumn
        );

        $feeDetailRows = $this->buildFeeDetailRows($feeRows, $showCampusColumn, $showUserColumn);
        $registrationFeeRows = $this->buildRegistrationFeeRows($feeRows, $showCampusColumn, $showUserColumn);
        $coworkingReceiptRows = $this->buildCoworkingReceiptRows($coworkingRows, $showCampusColumn, $showUserColumn);
        $expenseReportRows = $this->buildExpenseReportRows($expenseRows, $showCampusColumn, $showUserColumn);
        $paymentSummary = $this->buildPaymentRows($feeRows, $coworkingRows, $showCampusColumn, $showUserColumn);

        $topline = [
            'leads' => $leadRows->count(),
            'enroll_amount' => round((float) $feeRows
                ->filter(fn (FeeCollection $fee) => $this->feeBucket($fee) === 'full')
                ->sum(fn (FeeCollection $fee) => (float) ($fee->net_amount ?? $fee->amount ?? 0)), 2),
            'registration_amount' => round((float) $feeRows
                ->filter(fn (FeeCollection $fee) => $this->feeBucket($fee) === 'registration')
                ->sum(fn (FeeCollection $fee) => (float) ($fee->net_amount ?? $fee->amount ?? 0)), 2),
            'installment_amount' => round((float) $feeRows
                ->filter(fn (FeeCollection $fee) => $this->feeBucket($fee) === 'installment')
                ->sum(fn (FeeCollection $fee) => (float) ($fee->net_amount ?? $fee->amount ?? 0)), 2),
            'coworking_amount' => round((float) $coworkingRows->sum(fn (CoworkingRegistrationReceipt $receipt) => (float) ($receipt->amount ?? 0)), 2),
        ];

        return view('reports.dbr', [
            'reportDate' => $reportDate,
            'campuses' => $campuses,
            'users' => $users,
            'filters' => [
                'campus_id' => $campusId,
                'user_id' => $userId,
                'report_date' => $reportDate->toDateString(),
            ],
            'selectedCampus' => $selectedCampus,
            'selectedUser' => $selectedUser,
            'showCampusColumn' => $showCampusColumn,
            'showUserColumn' => $showUserColumn,
            'leadMatrix' => $leadMatrix,
            'registrationMatrix' => $registrationMatrix,
            'enrollmentMatrix' => $enrollmentMatrix,
            'feeDetailRows' => $feeDetailRows,
            'registrationFeeRows' => $registrationFeeRows,
            'coworkingReceiptRows' => $coworkingReceiptRows,
            'expenseReportRows' => $expenseReportRows,
            'paymentSummary' => $paymentSummary,
            'topline' => $topline,
            'summaryTotals' => [
                'registrations' => (float) $topline['registration_amount'],
                'enrollment_installments' => (float) $topline['enroll_amount'] + (float) $topline['installment_amount'],
                'coworking' => (float) $topline['coworking_amount'],
            ],
            'paymentMethodLabels' => self::PAYMENT_METHOD_LABELS,
            'preparedBy' => $user?->name,
        ]);
    }

    private function leadRows(Carbon $reportDate, ?int $campusId, ?int $userId, ?User $viewer): Collection
    {
        return $this->scopeQueryToUserCampus(
            Lead::query()->with([
                'campus:id,code,name',
                'program:id,title,name',
                'assignedUser:id,name',
                'createdBy:id,name',
            ]),
            $viewer
        )
            ->when($campusId, fn ($query, int $selectedCampusId) => $query->where('campus_id', $selectedCampusId))
            ->whereDate('created_at', $reportDate->toDateString())
            ->get()
            ->filter(fn (Lead $lead) => $this->matchesLeadOwner($lead, $userId))
            ->values();
    }

    private function registrationRows(Carbon $reportDate, ?int $campusId, ?int $userId, ?User $viewer): Collection
    {
        return $this->scopeQueryToUserCampus(
            Registration::query()->with([
                'campus:id,code,name',
                'program:id,title,name',
                'lead:id,assigned_user_id,created_by,type',
                'lead.assignedUser:id,name',
                'lead.createdBy:id,name',
            ]),
            $viewer
        )
            ->when($campusId, fn ($query, int $selectedCampusId) => $query->where('campus_id', $selectedCampusId))
            ->whereDate('registered_at', $reportDate->toDateString())
            ->get()
            ->filter(fn (Registration $registration) => $this->matchesLeadOwner($registration->lead, $userId))
            ->values();
    }

    private function coworkingRegistrationRows(Carbon $reportDate, ?int $campusId, ?int $userId, ?User $viewer): Collection
    {
        return $this->scopeQueryToUserCampus(
            CoworkingRegistration::query()->with([
                'campus:id,code,name',
                'lead:id,assigned_user_id,created_by,details',
                'lead.assignedUser:id,name',
                'lead.createdBy:id,name',
            ]),
            $viewer
        )
            ->when($campusId, fn ($query, int $selectedCampusId) => $query->where('campus_id', $selectedCampusId))
            ->whereDate('registration_date', $reportDate->toDateString())
            ->get()
            ->filter(fn (CoworkingRegistration $registration) => $this->matchesLeadOwner($registration->lead, $userId))
            ->values();
    }

    private function admissionRows(Carbon $reportDate, ?int $campusId, ?int $userId, ?User $viewer): Collection
    {
        return $this->scopeQueryToUserCampus(
            Admission::query()->with([
                'campus:id,code,name',
                'program:id,title,name',
                'registration.lead:id,assigned_user_id,created_by',
                'registration.lead.assignedUser:id,name',
                'registration.lead.createdBy:id,name',
            ]),
            $viewer
        )
            ->when($campusId, fn ($query, int $selectedCampusId) => $query->where('campus_id', $selectedCampusId))
            ->whereDate('admission_date', $reportDate->toDateString())
            ->get()
            ->filter(fn (Admission $admission) => $this->matchesLeadOwner($admission->registration?->lead, $userId))
            ->values();
    }

    private function feeRows(Carbon $reportDate, ?int $campusId, ?int $userId, ?User $viewer): Collection
    {
        return $this->scopeQueryToUserCampus(
            FeeCollection::query()->with([
                'campus:id,code,name',
                'lead:id,assigned_user_id,created_by',
                'lead.assignedUser:id,name',
                'lead.createdBy:id,name',
                'program:id,title,name',
                'registration.program:id,title,name',
                'registration.lead:id,assigned_user_id,created_by',
                'registration.lead.assignedUser:id,name',
                'registration.lead.createdBy:id,name',
                'admission:id,fee_type,program_id,registration_id',
                'admission.program:id,title,name',
                'admission.registration.lead:id,assigned_user_id,created_by',
                'admission.registration.lead.assignedUser:id,name',
                'admission.registration.lead.createdBy:id,name',
            ]),
            $viewer
        )
            ->when($campusId, fn ($query, int $selectedCampusId) => $query->where('campus_id', $selectedCampusId))
            ->where('status', 'paid')
            ->whereDate('paid_at', $reportDate->toDateString())
            ->get()
            ->filter(function (FeeCollection $fee) use ($userId): bool {
                return $this->matchesLeadOwner($this->resolveFeeLead($fee), $userId);
            })
            ->values();
    }

    private function coworkingRows(Carbon $reportDate, ?int $campusId, ?int $userId, ?User $viewer): Collection
    {
        return $this->scopeQueryToUserCampus(
            CoworkingRegistrationReceipt::query()->with([
                'campus:id,code,name',
                'lead:id,assigned_user_id,created_by,details',
                'lead.assignedUser:id,name',
                'lead.createdBy:id,name',
                'coworkingRegistration:id,lead_id,nature_of_work',
            ]),
            $viewer
        )
            ->when($campusId, fn ($query, int $selectedCampusId) => $query->where('campus_id', $selectedCampusId))
            ->whereNotNull('paid_at')
            ->whereIn('receipt_type', ['security_fee', 'coworking_charge'])
            ->whereDate('paid_at', $reportDate->toDateString())
            ->get()
            ->filter(fn (CoworkingRegistrationReceipt $receipt) => $this->matchesLeadOwner($receipt->lead, $userId))
            ->values();
    }

    private function expenseRows(Carbon $reportDate, ?int $campusId, ?int $userId, ?User $viewer): Collection
    {
        return $this->scopeQueryToUserCampus(
            FinanceExpense::query()->with([
                'campus:id,code,name',
                'creator:id,name',
                'expenseType:id,name',
            ]),
            $viewer
        )
            ->when($campusId, fn ($query, int $selectedCampusId) => $query->where('campus_id', $selectedCampusId))
            ->when($userId, fn ($query, int $selectedUserId) => $query->where('created_by', $selectedUserId))
            ->where('status', 'paid')
            ->whereDate('payment_date', $reportDate->toDateString())
            ->get()
            ->values();
    }

    private function buildCourseMatrix(Collection $records, bool $showCampusColumn, bool $showUserColumn): array
    {
        $columns = $records
            ->pluck('label')
            ->filter()
            ->unique()
            ->sortBy(fn (string $label) => strtolower($label))
            ->values();

        $rows = $records
            ->groupBy(function (array $record) use ($showCampusColumn, $showUserColumn): string {
                return implode('||', [
                    $showCampusColumn ? ($record['campus'] ?? 'N/A') : '__all_campuses__',
                    $showUserColumn ? ($record['user'] ?? 'Unassigned') : '__all_users__',
                ]);
            })
            ->map(function (Collection $group) use ($columns, $showCampusColumn, $showUserColumn): array {
                $first = $group->first();

                return [
                    'campus' => $showCampusColumn ? ($first['campus'] ?? 'N/A') : null,
                    'user' => $showUserColumn ? ($first['user'] ?? 'Unassigned') : null,
                    'counts' => $columns->mapWithKeys(
                        fn (string $column): array => [$column => (int) $group->where('label', $column)->count()]
                    ),
                ];
            })
            ->sortBy(fn (array $row) => strtolower(($row['campus'] ?? '') . '|' . ($row['user'] ?? '')))
            ->values();

        return [
            'columns' => $columns,
            'rows' => $rows,
        ];
    }

    private function buildFeeDetailRows(Collection $rows, bool $showCampusColumn, bool $showUserColumn): Collection
    {
        return $rows
            ->filter(fn (FeeCollection $fee) => $this->feeBucket($fee) !== 'registration')
            ->map(function (FeeCollection $fee): array {
                return [
                    'campus' => $this->campusCode($fee->campus),
                    'user' => $this->leadOwnerName($this->resolveFeeLead($fee)),
                    'course' => $this->courseLabelForFee($fee),
                    'fee_type' => $this->feeTypeLabel($fee),
                    'amount' => (float) ($fee->net_amount ?? $fee->amount ?? 0),
                ];
            })
            ->groupBy(function (array $row) use ($showCampusColumn, $showUserColumn): string {
                return implode('||', [
                    $showCampusColumn ? $row['campus'] : '__all_campuses__',
                    $showUserColumn ? $row['user'] : '__all_users__',
                    $row['course'],
                    $row['fee_type'],
                ]);
            })
            ->map(function (Collection $group) use ($showCampusColumn, $showUserColumn): array {
                $first = $group->first();

                return [
                    'campus' => $showCampusColumn ? ($first['campus'] ?? 'N/A') : null,
                    'user' => $showUserColumn ? ($first['user'] ?? 'Unassigned') : null,
                    'course' => $first['course'] ?? 'Unassigned Course',
                    'fee_type' => $first['fee_type'] ?? 'Fee',
                    'amount' => round((float) $group->sum('amount'), 2),
                ];
            })
            ->sortBy(fn (array $row) => strtolower(($row['campus'] ?? '') . '|' . ($row['user'] ?? '') . '|' . $row['course'] . '|' . $row['fee_type']))
            ->values();
    }

    private function buildRegistrationFeeRows(Collection $rows, bool $showCampusColumn, bool $showUserColumn): Collection
    {
        return $rows
            ->filter(fn (FeeCollection $fee) => $this->feeBucket($fee) === 'registration')
            ->map(function (FeeCollection $fee): array {
                return [
                    'campus' => $this->campusCode($fee->campus),
                    'user' => $this->leadOwnerName($this->resolveFeeLead($fee)),
                    'course' => $this->courseLabelForFee($fee),
                    'amount' => (float) ($fee->net_amount ?? $fee->amount ?? 0),
                ];
            })
            ->groupBy(function (array $row) use ($showCampusColumn, $showUserColumn): string {
                return implode('||', [
                    $showCampusColumn ? $row['campus'] : '__all_campuses__',
                    $showUserColumn ? $row['user'] : '__all_users__',
                    $row['course'],
                ]);
            })
            ->map(function (Collection $group) use ($showCampusColumn, $showUserColumn): array {
                $first = $group->first();

                return [
                    'campus' => $showCampusColumn ? ($first['campus'] ?? 'N/A') : null,
                    'user' => $showUserColumn ? ($first['user'] ?? 'Unassigned') : null,
                    'course' => $first['course'] ?? 'Unassigned Course',
                    'count' => $group->count(),
                    'amount' => round((float) $group->sum('amount'), 2),
                ];
            })
            ->sortBy(fn (array $row) => strtolower(($row['campus'] ?? '') . '|' . ($row['user'] ?? '') . '|' . $row['course']))
            ->values();
    }

    private function buildCoworkingReceiptRows(Collection $rows, bool $showCampusColumn, bool $showUserColumn): Collection
    {
        return $rows
            ->map(function (CoworkingRegistrationReceipt $receipt): array {
                return [
                    'campus' => $this->campusCode($receipt->campus),
                    'user' => $this->leadOwnerName($receipt->lead),
                    'space_type' => $this->coworkingSpaceType($receipt),
                    'type' => $this->coworkingReceiptLabel($receipt),
                    'amount' => (float) ($receipt->amount ?? 0),
                ];
            })
            ->groupBy(function (array $row) use ($showCampusColumn, $showUserColumn): string {
                return implode('||', [
                    $showCampusColumn ? $row['campus'] : '__all_campuses__',
                    $showUserColumn ? $row['user'] : '__all_users__',
                    $row['space_type'],
                    $row['type'],
                ]);
            })
            ->map(function (Collection $group) use ($showCampusColumn, $showUserColumn): array {
                $first = $group->first();

                return [
                    'campus' => $showCampusColumn ? ($first['campus'] ?? 'N/A') : null,
                    'user' => $showUserColumn ? ($first['user'] ?? 'Unassigned') : null,
                    'space_type' => $first['space_type'] ?? 'Coworking Space',
                    'type' => $first['type'] ?? 'Receipt',
                    'amount' => round((float) $group->sum('amount'), 2),
                ];
            })
            ->sortBy(fn (array $row) => strtolower(($row['campus'] ?? '') . '|' . ($row['user'] ?? '') . '|' . $row['space_type'] . '|' . $row['type']))
            ->values();
    }

    private function buildExpenseReportRows(Collection $rows, bool $showCampusColumn, bool $showUserColumn): Collection
    {
        return $rows
            ->map(function (FinanceExpense $expense): array {
                $expenseType = trim((string) ($expense->expenseType?->name ?? ''));

                return [
                    'campus' => $this->campusCode($expense->campus),
                    'user' => trim((string) ($expense->creator?->name ?? 'Unassigned')) ?: 'Unassigned',
                    'expense_type' => $expenseType !== ''
                        ? $expenseType
                        : ucfirst(str_replace('_', ' ', (string) ($expense->category ?? 'expense'))),
                    'amount' => (float) ($expense->amount ?? 0),
                ];
            })
            ->groupBy(function (array $row) use ($showCampusColumn, $showUserColumn): string {
                return implode('||', [
                    $showCampusColumn ? $row['campus'] : '__all_campuses__',
                    $showUserColumn ? $row['user'] : '__all_users__',
                    $row['expense_type'],
                ]);
            })
            ->map(function (Collection $group) use ($showCampusColumn, $showUserColumn): array {
                $first = $group->first();

                return [
                    'campus' => $showCampusColumn ? ($first['campus'] ?? 'N/A') : null,
                    'user' => $showUserColumn ? ($first['user'] ?? 'Unassigned') : null,
                    'expense_type' => $first['expense_type'] ?? 'Expense',
                    'amount' => round((float) $group->sum('amount'), 2),
                ];
            })
            ->sortBy(fn (array $row) => strtolower(($row['campus'] ?? '') . '|' . ($row['user'] ?? '') . '|' . $row['expense_type']))
            ->values();
    }

    private function buildPaymentRows(
        Collection $feeRows,
        Collection $coworkingRows,
        bool $showCampusColumn,
        bool $showUserColumn
    ): Collection {
        $items = collect();

        $feeRows->each(function (FeeCollection $fee) use ($items): void {
            $items->push([
                'campus' => $this->campusCode($fee->campus),
                'user' => $this->leadOwnerName($this->resolveFeeLead($fee)),
                'method' => $this->normalizePaymentMethod($fee->payment_method ?? null),
                'amount' => (float) ($fee->net_amount ?? $fee->amount ?? 0),
            ]);
        });

        $coworkingRows->each(function (CoworkingRegistrationReceipt $receipt) use ($items): void {
            $items->push([
                'campus' => $this->campusCode($receipt->campus),
                'user' => $this->leadOwnerName($receipt->lead),
                'method' => $this->normalizePaymentMethod($receipt->payment_method ?? null),
                'amount' => (float) ($receipt->amount ?? 0),
            ]);
        });

        return $items
            ->groupBy(function (array $row) use ($showCampusColumn, $showUserColumn): string {
                return implode('||', [
                    $showCampusColumn ? $row['campus'] : '__all_campuses__',
                    $showUserColumn ? $row['user'] : '__all_users__',
                    $row['method'],
                ]);
            })
            ->map(function (Collection $group) use ($showCampusColumn, $showUserColumn): array {
                $first = $group->first();
                $method = (string) ($first['method'] ?? 'unrecorded');

                return [
                    'campus' => $showCampusColumn ? ($first['campus'] ?? 'N/A') : null,
                    'user' => $showUserColumn ? ($first['user'] ?? 'Unassigned') : null,
                    'method' => $method,
                    'label' => self::PAYMENT_METHOD_LABELS[$method] ?? ucfirst($method),
                    'count' => $group->count(),
                    'amount' => round((float) $group->sum('amount'), 2),
                ];
            })
            ->sortBy(fn (array $row) => strtolower(($row['campus'] ?? '') . '|' . ($row['user'] ?? '')) . '|' . $this->paymentMethodSortIndex($row['method']))
            ->values();
    }

    private function matchesLeadOwner(?Lead $lead, ?int $userId): bool
    {
        if (! $userId) {
            return true;
        }

        return $this->resolveLeadOwnerId($lead) === $userId;
    }

    private function resolveLeadOwnerId(?Lead $lead): ?int
    {
        $assignedUserId = (int) ($lead?->assigned_user_id ?? 0);
        if ($assignedUserId > 0) {
            return $assignedUserId;
        }

        $createdBy = (int) ($lead?->created_by ?? 0);

        return $createdBy > 0 ? $createdBy : null;
    }

    private function resolveFeeLead(FeeCollection $fee): ?Lead
    {
        return $fee->lead
            ?? $fee->registration?->lead
            ?? $fee->admission?->registration?->lead;
    }

    private function leadLabel(Lead $lead): string
    {
        if ($lead->type === 'coworking') {
            return 'Coworking Space';
        }

        return $this->programLabel($lead->program);
    }

    private function courseLabelForFee(FeeCollection $fee): string
    {
        if ($fee->fee_type === 'registration') {
            return $this->programLabel(
                $fee->program
                ?? $fee->registration?->program
                ?? $fee->admission?->program
            );
        }

        return $this->programLabel(
            $fee->admission?->program
            ?? $fee->program
            ?? $fee->registration?->program
        );
    }

    private function programLabel(mixed $program): string
    {
        return trim((string) ($program?->title ?? $program?->name ?? 'Unassigned Course')) ?: 'Unassigned Course';
    }

    private function feeBucket(FeeCollection $fee): string
    {
        if ($fee->fee_type === 'registration') {
            return 'registration';
        }

        return ($fee->admission?->fee_type ?? 'full') === 'installments'
            ? 'installment'
            : 'full';
    }

    private function feeTypeLabel(FeeCollection $fee): string
    {
        return match ($this->feeBucket($fee)) {
            'registration' => 'Registration Fee',
            'installment' => $fee->installment_no
                ? $this->ordinal((int) $fee->installment_no) . ' Installment'
                : 'Installment',
            default => 'Full Fee',
        };
    }

    private function coworkingReceiptLabel(CoworkingRegistrationReceipt $receipt): string
    {
        return match ((string) $receipt->receipt_type) {
            'security_fee' => 'Security',
            'coworking_charge' => 'Charges',
            default => 'Coworking Receipt',
        };
    }

    private function coworkingSpaceType(CoworkingRegistrationReceipt $receipt): string
    {
        $spaceType = trim((string) data_get($receipt->lead?->details, 'space_required'));

        return $spaceType !== '' ? $spaceType : 'Coworking Space';
    }

    private function campusCode(mixed $campus): string
    {
        $label = trim((string) ($campus?->code ?? $campus?->name ?? 'N/A'));

        return $label !== '' ? $label : 'N/A';
    }

    private function leadOwnerName(?Lead $lead): string
    {
        $assignedName = trim((string) ($lead?->assignedUser?->name ?? ''));
        if ($assignedName !== '') {
            return $assignedName;
        }

        $createdName = trim((string) ($lead?->createdBy?->name ?? ''));

        return $createdName !== '' ? $createdName : 'Unassigned';
    }

    private function normalizePaymentMethod(?string $method): string
    {
        return match (strtolower(trim((string) $method))) {
            'cash' => 'cash',
            'bank', 'cheque' => 'bank',
            'online', 'online_transfer', 'easypaisa', 'jazzcash' => 'online',
            default => 'unrecorded',
        };
    }

    private function paymentMethodSortIndex(string $method): int
    {
        $index = array_search($method, array_keys(self::PAYMENT_METHOD_LABELS), true);

        return $index === false ? 99 : (int) $index;
    }

    private function ordinal(int $number): string
    {
        if ($number <= 0) {
            return 'Installment';
        }

        $suffix = 'th';
        if (($number % 100) < 11 || ($number % 100) > 13) {
            $suffix = match ($number % 10) {
                1 => 'st',
                2 => 'nd',
                3 => 'rd',
                default => 'th',
            };
        }

        return $number . $suffix;
    }
}
