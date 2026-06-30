<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Admission;
use App\Models\CoworkingRegistrationReceipt;
use App\Models\FeeCollection;
use App\Models\Lead;
use App\Models\Program;
use App\Models\Registration;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $selectedCampusId = $this->resolveCampusId($request);
        $dashboardAccess = $this->resolveDashboardAccess($request->user());
        $selectedCampus = $selectedCampusId && Schema::hasTable('campuses')
            ? Campus::query()->find($selectedCampusId, ['id', 'code', 'name', 'campus_type'])
            : null;

        $dashboard = $this->buildDashboardPayload($selectedCampusId, $dashboardAccess);

        return view('dashboard', [
            'dashboard' => $dashboard,
            'dashboardAccess' => $dashboardAccess,
            'selectedCampus' => $selectedCampus,
            'dashboardGeneratedAt' => $dashboard['generatedAt'] ?? null,
        ]);
    }

    public function liveData(Request $request): JsonResponse
    {
        $dashboardAccess = $this->resolveDashboardAccess($request->user());

        return response()->json([
            'dashboard' => $this->buildDashboardPayload($this->resolveCampusId($request), $dashboardAccess),
        ]);
    }

    public function pendingRecovery(Request $request): View
    {
        $dashboardAccess = $this->resolveDashboardAccess($request->user());
        abort_unless(
            (bool) ($dashboardAccess['admissions'] ?? false) || (bool) ($dashboardAccess['income'] ?? false),
            403
        );

        $selectedCampusId = $this->resolveCampusId($request);
        $selectedCampus = $selectedCampusId && Schema::hasTable('campuses')
            ? Campus::query()->find($selectedCampusId, ['id', 'code', 'name', 'title', 'campus_type'])
            : null;

        [$selectedMonth, $selectedYear] = $this->resolvePendingRecoveryPeriod($request);
        $monthStart = Carbon::createFromDate($selectedYear, $selectedMonth, 1, $this->dashboardTimezone())->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        return view('dashboard.pending-recovery', [
            'rows' => $this->buildPendingRecoveryRows($monthStart, $monthEnd, $selectedCampusId),
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'monthOptions' => $this->pendingRecoveryMonthOptions(),
            'yearOptions' => $this->pendingRecoveryYearOptions($selectedCampusId, $selectedYear),
            'selectedCampus' => $selectedCampus,
        ]);
    }

    public function pendingRecoveryCampusReport(Request $request, Campus $campus): View
    {
        $dashboardAccess = $this->resolveDashboardAccess($request->user());
        abort_unless(
            (bool) ($dashboardAccess['admissions'] ?? false) || (bool) ($dashboardAccess['income'] ?? false),
            403
        );

        $this->ensureDashboardCampusAccess((int) $campus->id, $request->user());

        [$selectedMonth, $selectedYear] = $this->resolvePendingRecoveryPeriod($request);
        $monthStart = Carbon::createFromDate($selectedYear, $selectedMonth, 1, $this->dashboardTimezone())->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        return view('dashboard.pending-recovery-campus', [
            'campus' => $campus,
            'sections' => $this->buildPendingRecoveryProgramSections((int) $campus->id, $monthStart, $monthEnd),
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'monthOptions' => $this->pendingRecoveryMonthOptions(),
            'yearOptions' => $this->pendingRecoveryYearOptions((int) $campus->id, $selectedYear),
            'reportStart' => $monthStart,
            'reportEnd' => $monthEnd,
        ]);
    }

    private function requiredTablesExist(): bool
    {
        return Schema::hasTable('campuses')
            && Schema::hasTable('leads')
            && Schema::hasTable('lead_followups')
            && Schema::hasTable('programs')
            && Schema::hasTable('registrations')
            && Schema::hasTable('admissions');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDashboardPayload(?int $selectedCampusId = null, array $dashboardAccess = []): array
    {
        $emptyPayload = $this->emptyPayload();

        if (!$this->requiredTablesExist()) {
            $payload = $emptyPayload;
            $payload['generatedAt'] = now()->toIso8601String();

            return $payload;
        }

        $canViewLeads = (bool) ($dashboardAccess['leads'] ?? false);
        $canViewAdmissions = (bool) ($dashboardAccess['admissions'] ?? false);
        $canViewIncome = (bool) ($dashboardAccess['income'] ?? false);
        $today = now()->startOfDay();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $yearStart = now()->startOfYear();
        $yearEnd = now()->endOfYear();
        $incomeNow = $this->dashboardNow();
        $incomeToday = $incomeNow->copy()->startOfDay();
        $incomeMonthStart = $incomeNow->copy()->startOfMonth();
        $incomeMonthEnd = $incomeNow->copy()->endOfMonth();
        $incomeYearStart = $incomeNow->copy()->startOfYear();
        $incomeYearEnd = $incomeNow->copy()->endOfYear();

        $stats = $this->buildStats($today, $monthStart, $monthEnd, $selectedCampusId, $dashboardAccess);

        return [
            'stats' => $stats,
            'dailyActivity' => $canViewLeads ? $this->buildDailyActivity($selectedCampusId, $dashboardAccess) : $emptyPayload['dailyActivity'],
            'admissionsActivity' => $canViewAdmissions ? $this->buildAdmissionsActivity($selectedCampusId) : $emptyPayload['admissionsActivity'],
            'monthlyAdmissionsInsight' => $canViewAdmissions ? $this->buildMonthlyAdmissionsInsight($selectedCampusId) : $emptyPayload['monthlyAdmissionsInsight'],
            'incomeSummary' => [
                'today' => $canViewIncome ? $stats['todayCollectionRaw'] : 0,
                'week' => $canViewIncome ? $stats['weekCollectionRaw'] : 0,
                'month' => $canViewIncome ? $stats['currentMonthCollectionRaw'] : 0,
            ],
            'incomeRanges' => $canViewIncome
                ? $this->buildIncomeRanges($incomeToday, $incomeMonthStart, $incomeMonthEnd, $incomeYearStart, $incomeYearEnd, $selectedCampusId)
                : $emptyPayload['incomeRanges'],
            'charts' => ($canViewLeads || $canViewAdmissions)
                ? $this->buildCharts($monthStart, $monthEnd, $selectedCampusId, $dashboardAccess)
                : $emptyPayload['charts'],
            'generatedAt' => now()->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyPayload(): array
    {
        return [
            'stats' => [
                'todayLeads' => 0,
                'totalLeads' => 0,
                'currentStudents' => 0,
                'currentMonthAdmissions' => 0,
                'previousMonthAdmissions' => 0,
                'currentMonthCollection' => '0',
                'currentMonthCollectionRaw' => 0,
                'pendingRecoveryRaw' => 0,
                'todayCollectionRaw' => 0,
                'weekCollectionRaw' => 0,
            ],
            'dailyActivity' => [
                'rows' => [],
                'totals' => [
                    'leads' => 0,
                    'followups' => 0,
                    'admissions' => 0,
                    'collection' => 0,
                ],
            ],
            'admissionsActivity' => [
                'rows' => [],
            ],
            'incomeSummary' => [
                'today' => 0,
                'week' => 0,
                'month' => 0,
            ],
            'incomeRanges' => [
                'today' => ['label' => 'Today income (hourly)', 'points' => [['08 AM', 0]], 'ticks' => [0, 10]],
                'week' => ['label' => 'Week income (daily)', 'points' => [['Mon', 0]], 'ticks' => [0, 10]],
                'month' => ['label' => 'Month income (weekly)', 'points' => [['Week 1', 0], ['Week 2', 0], ['Week 3', 0], ['Week 4', 0]], 'ticks' => [0, 10]],
                'year' => ['label' => 'Year income (monthly)', 'points' => [['Jan', 0]], 'ticks' => [0, 10]],
            ],
            'charts' => [
                'leads' => ['categories' => ['No Data'], 'counts' => [0]],
                'admissions' => ['categories' => ['No Data'], 'counts' => [0]],
                'campusAdmissions' => ['categories' => ['No Data'], 'counts' => [0]],
            ],
            'monthlyAdmissionsInsight' => [
                'labels' => [],
                'counts' => [],
                'current' => 0,
                'previous' => 0,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildStats(
        Carbon $today,
        Carbon $monthStart,
        Carbon $monthEnd,
        ?int $campusId = null,
        array $dashboardAccess = []
    ): array
    {
        $canViewLeads = (bool) ($dashboardAccess['leads'] ?? false);
        $canViewAdmissions = (bool) ($dashboardAccess['admissions'] ?? false);
        $canViewIncome = (bool) ($dashboardAccess['income'] ?? false);
        $canViewRecovery = $canViewAdmissions || $canViewIncome;
        $incomeNow = $this->dashboardNow();
        $incomeToday = $incomeNow->copy()->startOfDay();
        $incomeWeekStart = $incomeToday->copy()->subDays(6);
        $incomeMonthStart = $incomeNow->copy()->startOfMonth();
        $incomeMonthEnd = $incomeNow->copy()->endOfMonth();

        $todayCollection = $canViewIncome
            ? $this->collectionTotalForRange($incomeToday, $incomeToday->copy()->endOfDay(), $campusId)
            : 0;

        $weekCollection = $canViewIncome
            ? $this->collectionTotalForRange($incomeWeekStart, $incomeNow->copy()->endOfDay(), $campusId)
            : 0;

        $monthCollection = $canViewIncome
            ? $this->collectionTotalForRange($incomeMonthStart, $incomeMonthEnd, $campusId)
            : 0;
        $previousMonthStart = $monthStart->copy()->subMonth()->startOfMonth();
        $previousMonthEnd = $previousMonthStart->copy()->endOfMonth();

        $currentStudents = (int) DB::table('admissions')
            ->join('registrations', 'registrations.id', '=', 'admissions.registration_id')
            ->when($campusId, fn ($query, $id) => $query->where('registrations.campus_id', $id))
            ->where('admissions.student_status', 'enrolled')
            ->count();
        $currentStudents = $canViewAdmissions ? $currentStudents : 0;

        $currentMonthAdmissions = (int) DB::table('admissions')
            ->join('registrations', 'registrations.id', '=', 'admissions.registration_id')
            ->when($campusId, fn ($q, $id) => $q->where('registrations.campus_id', $id))
            ->whereBetween('admissions.admission_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->count();
        $currentMonthAdmissions = $canViewAdmissions ? $currentMonthAdmissions : 0;

        $previousMonthAdmissions = (int) DB::table('admissions')
            ->join('registrations', 'registrations.id', '=', 'admissions.registration_id')
            ->when($campusId, fn ($q, $id) => $q->where('registrations.campus_id', $id))
            ->whereBetween('admissions.admission_date', [$previousMonthStart->toDateString(), $previousMonthEnd->toDateString()])
            ->count();
        $previousMonthAdmissions = $canViewAdmissions ? $previousMonthAdmissions : 0;

        return [
            'todayLeads' => $canViewLeads
                ? $this->leadQueryForDashboard($campusId, $dashboardAccess)
                    ->whereBetween('created_at', [$today, $today->copy()->endOfDay()])
                    ->count()
                : 0,
            'totalLeads' => $canViewLeads
                ? $this->leadQueryForDashboard($campusId, $dashboardAccess)
                    ->count()
                : 0,
            'currentStudents' => $currentStudents,
            'currentMonthAdmissions' => $currentMonthAdmissions,
            'previousMonthAdmissions' => $previousMonthAdmissions,
            'currentMonthCollection' => number_format((float) $monthCollection, 0),
            'currentMonthCollectionRaw' => (float) $monthCollection,
            'pendingRecoveryRaw' => $canViewRecovery
                ? $this->pendingRecoveryTotal($campusId)
                : 0,
            'todayCollectionRaw' => (float) $todayCollection,
            'weekCollectionRaw' => (float) $weekCollection,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDailyActivity(?int $campusId = null, array $dashboardAccess = []): array
    {
        $rows = $this->leadQueryForDashboard($campusId, $dashboardAccess)
            ->with([
                'campus:id,code,name',
                'registrations:id,lead_id',
            ])
            ->latest('created_at')
            ->latest('id')
            ->limit(12)
            ->get(['id', 'campus_id', 'name', 'phone', 'status', 'type', 'created_at'])
            ->map(function (Lead $lead) use ($campusId) {
                $campusLabel = optional($lead->campus)->code
                    ?: optional($lead->campus)->name
                    ?: 'Campus';
                $status = (string) $lead->status;
                $registrationId = $lead->registrations->sortByDesc('id')->first()?->id;
                $detailUrl = in_array($status, ['registered', 'enrolled'], true) && $registrationId
                    ? route('student.show', $registrationId)
                    : route('leads.show', $lead);

                return [
                    'status_label' => $this->formatLeadStatusLabel($lead->status),
                    'status_tone' => $this->leadStatusTone($lead->status),
                    'student_name' => $lead->name ?: 'N/A',
                    'detail_url' => $detailUrl,
                    'phone' => $lead->phone ?: 'N/A',
                    'date_label' => $this->formatDashboardDate($lead->created_at),
                    'campus' => $campusLabel,
                    'show_campus' => !$campusId,
                ];
            });

        return [
            'rows' => $rows->values()->all(),
            'totals' => [
                'leads' => (int) $rows->count(),
                'followups' => 0,
                'admissions' => 0,
                'collection' => 0,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAdmissionsActivity(?int $campusId = null): array
    {
        $rows = Admission::query()
            ->with('campus:id,code,name')
            ->when($campusId, fn ($q, $id) => $q->where('campus_id', $id))
            ->latest('admission_date')
            ->latest('id')
            ->limit(12)
            ->get(['id', 'registration_id', 'campus_id', 'student_name', 'phone', 'student_status', 'admission_date'])
            ->map(function (Admission $admission) use ($campusId) {
                $campusLabel = optional($admission->campus)->code
                    ?: optional($admission->campus)->name
                    ?: 'Campus';

                return [
                    'status_label' => $this->formatAdmissionStatusLabel($admission->student_status),
                    'status_tone' => $this->admissionStatusTone($admission->student_status),
                    'student_name' => $admission->student_name ?: 'N/A',
                    'detail_url' => $admission->registration_id ? route('student.show', $admission->registration_id) : null,
                    'phone' => $admission->phone ?: 'N/A',
                    'date_label' => $this->formatDashboardDate($admission->admission_date),
                    'campus' => $campusLabel,
                    'show_campus' => !$campusId,
                ];
            });

        return [
            'rows' => $rows->values()->all(),
        ];
    }

    private function formatLeadStatusLabel(?string $status): string
    {
        $status = (string) $status;

        if ($status === '') {
            return 'New';
        }

        return ucfirst(str_replace('_', ' ', $status));
    }

    private function leadStatusTone(?string $status): string
    {
        return match ((string) $status) {
            'registered', 'enrolled' => 'success',
            'contacted', 'new' => 'info',
            'pending' => 'warning',
            'transferred' => 'orange',
            'not_interesting', 'not_interested', 'inactive' => 'muted',
            default => 'primary',
        };
    }

    private function formatAdmissionStatusLabel(?string $status): string
    {
        $status = (string) $status;

        if ($status === '') {
            return 'Enrolled';
        }

        if ($status === 'admission_cancelled') {
            return 'Cancelled';
        }

        return ucfirst(str_replace('_', ' ', $status));
    }

    private function admissionStatusTone(?string $status): string
    {
        return match ((string) $status) {
            'enrolled' => 'success',
            'concluded' => 'primary',
            'frozen' => 'warning',
            'suspended' => 'info',
            'admission_cancelled', 'dropped' => 'danger',
            'incomplete' => 'muted',
            default => 'primary',
        };
    }

    private function formatDashboardDate($timestamp): string
    {
        if (!$timestamp) {
            return 'N/A';
        }

        return Carbon::parse($timestamp)->format('d-M-Y');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildIncomeRanges(
        Carbon $today,
        Carbon $monthStart,
        Carbon $monthEnd,
        Carbon $yearStart,
        Carbon $yearEnd,
        ?int $campusId = null
    ): array {
        $todayRows = $this->paidCollectionRows($today, $today->copy()->endOfDay(), $campusId);

        $hourlySlots = [
            ['label' => '08 AM', 'start' => 8, 'end' => 9],
            ['label' => '10 AM', 'start' => 10, 'end' => 11],
            ['label' => '12 PM', 'start' => 12, 'end' => 13],
            ['label' => '02 PM', 'start' => 14, 'end' => 15],
            ['label' => '04 PM', 'start' => 16, 'end' => 17],
            ['label' => '06 PM', 'start' => 18, 'end' => 19],
            ['label' => '08 PM', 'start' => 20, 'end' => 21],
            ['label' => '10 PM', 'start' => 22, 'end' => 23],
        ];
        $hourlyTotals = array_fill_keys(array_column($hourlySlots, 'label'), 0.0);

        foreach ($todayRows as $row) {
            $timestamp = $this->resolveCollectionGraphTimestamp($row->paid_at ?? null, $row->created_at ?? null);
            if (!$timestamp) {
                continue;
            }

            $hourlyTotals[$this->resolveHourlySlotLabel($timestamp, $hourlySlots)] += (float) ($row->amount ?? 0);
        }

        $incomeNow = $this->dashboardNow();
        $weekStart = $incomeNow->copy()->startOfDay()->subDays(6);
        $weekRows = $this->paidCollectionRows($weekStart, $incomeNow->copy()->endOfDay(), $campusId);
        $weekDailyTotals = [];
        foreach (CarbonPeriod::create($weekStart, $incomeNow->copy()->startOfDay()) as $date) {
            $weekDailyTotals[$date->toDateString()] = 0.0;
        }
        foreach ($weekRows as $row) {
            $timestamp = $this->resolveCollectionGraphTimestamp($row->paid_at ?? null, $row->created_at ?? null);
            if (!$timestamp) {
                continue;
            }
            $dayKey = $timestamp->toDateString();
            if (array_key_exists($dayKey, $weekDailyTotals)) {
                $weekDailyTotals[$dayKey] += (float) ($row->amount ?? 0);
            }
        }

        $monthRows = $this->paidCollectionRows($monthStart, $monthEnd, $campusId);
        $monthWeeklyTotals = [
            'Week 1' => 0.0,
            'Week 2' => 0.0,
            'Week 3' => 0.0,
            'Week 4' => 0.0,
        ];
        foreach ($monthRows as $row) {
            $timestamp = $this->resolveCollectionGraphTimestamp($row->paid_at ?? null, $row->created_at ?? null);
            if (!$timestamp) {
                continue;
            }
            $dayOfMonth = $timestamp->day;
            $weekBucket = min(4, (int) ceil($dayOfMonth / 7));
            $monthWeeklyTotals['Week ' . $weekBucket] += (float) ($row->amount ?? 0);
        }

        $yearRows = $this->paidCollectionRows($yearStart, $yearEnd, $campusId);
        $yearMonthlyTotals = [];
        foreach (range(1, 12) as $month) {
            $yearMonthlyTotals[Carbon::create($yearStart->year, $month, 1, 0, 0, 0, $this->dashboardTimezone())->format('M')] = 0.0;
        }
        foreach ($yearRows as $row) {
            $timestamp = $this->resolveCollectionGraphTimestamp($row->paid_at ?? null, $row->created_at ?? null);
            if (!$timestamp) {
                continue;
            }
            $monthKey = $timestamp->format('M');
            if (array_key_exists($monthKey, $yearMonthlyTotals)) {
                $yearMonthlyTotals[$monthKey] += (float) ($row->amount ?? 0);
            }
        }

        return [
            'today' => $this->formatSeries('Today income (hourly)', $hourlyTotals),
            'week' => $this->formatSeries(
                'Week income (daily)',
                collect($weekDailyTotals)
                    ->mapWithKeys(fn ($value, $date) => [Carbon::parse($date)->format('D') => $value])
                    ->all()
            ),
            'month' => $this->formatSeries('Month income (weekly)', $monthWeeklyTotals),
            'year' => $this->formatSeries('Year income (monthly)', $yearMonthlyTotals),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCharts(Carbon $monthStart, Carbon $monthEnd, ?int $campusId = null, array $dashboardAccess = []): array
    {
        $canViewLeads = (bool) ($dashboardAccess['leads'] ?? false);
        $canViewAdmissions = (bool) ($dashboardAccess['admissions'] ?? false);

        $leadCountsByProgram = $canViewLeads
            ? $this->leadQueryForDashboard($campusId, $dashboardAccess, true)
                ->selectRaw('program_id, COUNT(*) as aggregate')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->groupBy('program_id')
                ->pluck('aggregate', 'program_id')
            : collect();

        $admissionCountsByProgram = $canViewAdmissions
            ? DB::table('admissions')
                ->join('registrations', 'registrations.id', '=', 'admissions.registration_id')
                ->selectRaw('registrations.program_id as program_id, COUNT(*) as aggregate')
                ->when($campusId, fn ($q, $id) => $q->where('registrations.campus_id', $id))
                ->whereBetween('admissions.admission_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->groupBy('registrations.program_id')
                ->pluck('aggregate', 'program_id')
            : collect();

        $admissionsByCampus = $canViewAdmissions
            ? DB::table('admissions')
                ->join('registrations', 'registrations.id', '=', 'admissions.registration_id')
                ->selectRaw('registrations.campus_id as campus_id, COUNT(*) as aggregate')
                ->when($campusId, fn ($q, $id) => $q->where('registrations.campus_id', $id))
                ->whereBetween('admissions.admission_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->groupBy('registrations.campus_id')
                ->pluck('aggregate', 'campus_id')
            : collect();
$programIds = Program::query()
    ->orderByDesc('id')   // latest programs
    ->take(12)            // sirf latest 12 programs
    ->pluck('id');

$leadCountsByProgram = $leadCountsByProgram->only($programIds);

$admissionCountsByProgram = $admissionCountsByProgram->only($programIds);

$programLabels = Program::query()
    ->whereIn('id', $programIds)
    ->get(['id', 'code', 'title', 'name'])
    ->mapWithKeys(function (Program $program) {
        $label = $program->code ?: ($program->title ?: ($program->name ?: 'Program #' . $program->id));
        return [$program->id => $label];
    });
        // $programLabels = Program::query()
        //     ->whereIn(
        //         'id',
        //         collect($leadCountsByProgram->keys())
        //             ->merge($admissionCountsByProgram->keys())
        //             ->filter()
        //             ->unique()
        //             ->values()
        //     )
        //     ->get(['id', 'code', 'title', 'name'])
        //     ->mapWithKeys(function (Program $program) {
        //         $label = $program->code ?: ($program->title ?: $program->name ?: 'Program #' . $program->id);
        //         return [$program->id => $label];
        //     });

        $leadCategories = [];
        $leadCounts = [];
        foreach ($leadCountsByProgram as $programId => $count) {
            $leadCategories[] = (string) ($programLabels[$programId] ?? ('Program #' . $programId));
            $leadCounts[] = (int) $count;
        }

        $admissionCategories = [];
        $admissionCounts = [];
        foreach ($admissionCountsByProgram as $programId => $count) {
            $admissionCategories[] = (string) ($programLabels[$programId] ?? ('Program #' . $programId));
            $admissionCounts[] = (int) $count;
        }

        $campusLabels = Campus::query()
            ->whereIn('id', $admissionsByCampus->keys())
            ->get(['id', 'code', 'name'])
            ->mapWithKeys(function (Campus $campus) {
                $label = $campus->code ?: ($campus->name ?: 'Campus #' . $campus->id);
                return [$campus->id => $label];
            });

        $campusCategories = [];
        $campusCounts = [];
        foreach ($admissionsByCampus as $campusId => $count) {
            $campusCategories[] = (string) ($campusLabels[$campusId] ?? ('Campus #' . $campusId));
            $campusCounts[] = (int) $count;
        }

        return [
            'leads' => [
                'categories' => $leadCategories ?: ['No Data'],
                'counts' => $leadCounts ?: [0],
            ],
            'admissions' => [
                'categories' => $admissionCategories ?: ['No Data'],
                'counts' => $admissionCounts ?: [0],
            ],
            'campusAdmissions' => [
                'categories' => $campusCategories ?: ['No Data'],
                'counts' => $campusCounts ?: [0],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildMonthlyAdmissionsInsight(?int $campusId = null): array
    {
        $startMonth = now()->copy()->startOfMonth()->subMonths(11);
        $endMonth = now()->copy()->endOfMonth();

        $monthlyCounts = [];
        foreach (range(0, 11) as $offset) {
            $month = $startMonth->copy()->addMonths($offset);
            $monthlyCounts[$month->format('Y-m')] = 0;
        }

        $rows = Admission::query()
            ->when($campusId, fn ($q, $id) => $q->where('campus_id', $id))
            ->whereBetween('admission_date', [$startMonth->toDateString(), $endMonth->toDateString()])
            ->get(['admission_date']);

        foreach ($rows as $row) {
            if (!$row->admission_date) {
                continue;
            }

            $monthKey = Carbon::parse($row->admission_date)->format('Y-m');
            if (array_key_exists($monthKey, $monthlyCounts)) {
                $monthlyCounts[$monthKey]++;
            }
        }

        $labels = [];
        $counts = [];
        foreach (array_keys($monthlyCounts) as $monthKey) {
            $month = Carbon::createFromFormat('Y-m', $monthKey);
            $labels[] = $month->format('M');
            $counts[] = (int) $monthlyCounts[$monthKey];
        }

        return [
            'labels' => $labels,
            'counts' => $counts,
            'current' => (int) ($counts[11] ?? 0),
            'previous' => (int) ($counts[10] ?? 0),
        ];
    }

    /**
     * @param array<string, float|int> $series
     * @return array<string, mixed>
     */
    private function formatSeries(string $label, array $series): array
    {
        $points = [];
        $max = 0.0;

        foreach ($series as $key => $value) {
            $numeric = (float) $value;
            $points[] = [(string) $key, round($numeric, 2)];
            if ($numeric > $max) {
                $max = $numeric;
            }
        }

        return [
            'label' => $label,
            'points' => $points,
            'ticks' => $this->buildTicks($max),
        ];
    }

    /**
     * @return array<int, float|int>
     */
    private function buildTicks(float $max): array
    {
        $safeMax = max($max, 10);
        $segments = 5;
        $step = $safeMax / $segments;
        $ticks = [];

        for ($i = 0; $i <= $segments; $i++) {
            $ticks[] = round($step * $i, 2);
        }

        return $ticks;
    }

    private function registrationDateRange(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->where(function (Builder $builder) use ($start, $end) {
            $builder->whereBetween('registered_at', [$start, $end])
                ->orWhere(function (Builder $fallback) use ($start, $end) {
                    $fallback->whereNull('registered_at')
                        ->whereBetween('created_at', [$start, $end]);
                });
        });
    }

    private function collectionTotalForRange(Carbon $start, Carbon $end, ?int $campusId = null): float
    {
        return $this->feeCollectionTotalForRange($start, $end, $campusId)
            + $this->coworkingCollectionTotalForRange($start, $end, $campusId);
    }

    private function feeCollectionTotalForRange(Carbon $start, Carbon $end, ?int $campusId = null): float
    {
        if (!Schema::hasTable('fee_collections')) {
            return 0;
        }

        [$queryStart, $queryEnd] = $this->queryTimestampRange($start, $end);

        return (float) FeeCollection::query()
            ->whereIn('fee_type', ['registration', 'admission'])
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->when($campusId, fn ($q, $id) => $q->where('campus_id', $id))
            ->whereBetween('paid_at', [$queryStart, $queryEnd])
            ->sum('net_amount');
    }

    private function pendingRecoveryTotal(?int $campusId = null): float
    {
        if (!Schema::hasTable('fee_collections')) {
            return 0;
        }

        return (float) FeeCollection::query()
            ->when($campusId, fn ($query, $id) => $query->where('campus_id', $id))
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'paid');
            })
            ->sum('net_amount');
    }

    /**
     * @return array<int, array<string, float|int|string>>
     */
    private function buildPendingRecoveryRows(Carbon $monthStart, Carbon $monthEnd, ?int $campusId = null): array
    {
        if (!Schema::hasTable('campuses')) {
            return [];
        }

        $campuses = Campus::query()
            ->when($campusId, fn ($query, $id) => $query->whereKey($id))
            ->orderBy('code')
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $weeklyByCampus = collect();

        if (Schema::hasTable('fee_collections')) {
            $referenceDate = $this->pendingRecoveryReferenceDateExpression();
            $monthStartValue = $monthStart->toDateString();
            $monthEndValue = $monthEnd->toDateString();

            $weeklyByCampus = DB::table('fee_collections')
                ->select('campus_id')
                ->selectRaw(
                    "SUM(CASE WHEN {$referenceDate} BETWEEN ? AND ? AND DAY({$referenceDate}) BETWEEN 1 AND 7 THEN net_amount ELSE 0 END) as week_1",
                    [$monthStartValue, $monthEndValue]
                )
                ->selectRaw(
                    "SUM(CASE WHEN {$referenceDate} BETWEEN ? AND ? AND DAY({$referenceDate}) BETWEEN 8 AND 14 THEN net_amount ELSE 0 END) as week_2",
                    [$monthStartValue, $monthEndValue]
                )
                ->selectRaw(
                    "SUM(CASE WHEN {$referenceDate} BETWEEN ? AND ? AND DAY({$referenceDate}) BETWEEN 15 AND 21 THEN net_amount ELSE 0 END) as week_3",
                    [$monthStartValue, $monthEndValue]
                )
                ->selectRaw(
                    "SUM(CASE WHEN {$referenceDate} BETWEEN ? AND ? AND DAY({$referenceDate}) >= 22 THEN net_amount ELSE 0 END) as week_4",
                    [$monthStartValue, $monthEndValue]
                )
                ->selectRaw(
                    "SUM(CASE WHEN {$referenceDate} BETWEEN ? AND ? THEN net_amount ELSE 0 END) as month_total",
                    [$monthStartValue, $monthEndValue]
                )
                ->selectRaw('SUM(net_amount) as overall_total')
                ->whereNotNull('campus_id')
                ->when($campusId, fn ($query, $id) => $query->where('campus_id', $id))
                ->where(function ($query) {
                    $query->whereNull('status')
                        ->orWhere('status', '!=', 'paid');
                })
                ->groupBy('campus_id')
                ->get()
                ->keyBy('campus_id');
        }

        return $campuses
            ->map(function (Campus $campus) use ($weeklyByCampus) {
                $bucket = $weeklyByCampus->get($campus->id);

                return [
                    'campus_id' => (int) $campus->id,
                    'campus_code' => (string) ($campus->code ?: $campus->name ?: ('Campus #' . $campus->id)),
                    'week_1' => round((float) ($bucket->week_1 ?? 0), 2),
                    'week_2' => round((float) ($bucket->week_2 ?? 0), 2),
                    'week_3' => round((float) ($bucket->week_3 ?? 0), 2),
                    'week_4' => round((float) ($bucket->week_4 ?? 0), 2),
                    'month_total' => round((float) ($bucket->month_total ?? 0), 2),
                    'overall_total' => round((float) ($bucket->overall_total ?? 0), 2),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildPendingRecoveryProgramSections(int $campusId, Carbon $monthStart, Carbon $monthEnd): array
    {
        if (!Schema::hasTable('fee_collections')) {
            return [];
        }

        $rows = FeeCollection::query()
            ->with([
                'program:id,code,title,name',
                'admission:id,registration_id,campus_id,program_id,student_name,guardian_name,admission_date,fee_package,discounted_fee,roll_number',
                'registration:id,campus_id,program_id,student_name,guardian_name,registered_at,registration_number,fee,discount,net_payable',
            ])
            ->where('campus_id', $campusId)
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'paid');
            })
            ->where(function ($query) use ($monthStart, $monthEnd) {
                if (Schema::hasColumn('fee_collections', 'due_at')) {
                    $query->whereBetween('due_at', [$monthStart->toDateString(), $monthEnd->toDateString()])
                        ->orWhere(function ($fallback) use ($monthStart, $monthEnd) {
                            $fallback->whereNull('due_at')
                                ->whereBetween(DB::raw('DATE(created_at)'), [$monthStart->toDateString(), $monthEnd->toDateString()]);
                        });

                    return;
                }

                $query->whereBetween(DB::raw('DATE(created_at)'), [$monthStart->toDateString(), $monthEnd->toDateString()]);
            })
            ->orderBy('program_id')
            ->orderByRaw($this->pendingRecoveryReferenceDateExpression())
            ->orderBy('installment_no')
            ->orderBy('id')
            ->get([
                'id',
                'registration_id',
                'admission_id',
                'program_id',
                'fee_type',
                'installment_no',
                'net_amount',
                'status',
                'due_at',
                'created_at',
            ]);

        if ($rows->isEmpty()) {
            return [];
        }

        $admissionTotals = FeeCollection::query()
            ->select('admission_id')
            ->selectRaw("SUM(CASE WHEN status = 'paid' THEN net_amount ELSE 0 END) as received_total")
            ->selectRaw("SUM(CASE WHEN status IS NULL OR status != 'paid' THEN net_amount ELSE 0 END) as pending_total")
            ->where('fee_type', 'admission')
            ->whereIn('admission_id', $rows->pluck('admission_id')->filter()->unique()->values())
            ->groupBy('admission_id')
            ->get()
            ->keyBy('admission_id');

        $registrationTotals = FeeCollection::query()
            ->select('registration_id')
            ->selectRaw("SUM(CASE WHEN status = 'paid' THEN net_amount ELSE 0 END) as received_total")
            ->selectRaw("SUM(CASE WHEN status IS NULL OR status != 'paid' THEN net_amount ELSE 0 END) as pending_total")
            ->where('fee_type', 'registration')
            ->whereIn('registration_id', $rows->pluck('registration_id')->filter()->unique()->values())
            ->groupBy('registration_id')
            ->get()
            ->keyBy('registration_id');

        return $rows
            ->groupBy(fn (FeeCollection $row) => $this->pendingRecoveryProgramKey($row))
            ->map(function ($programRows) use ($admissionTotals, $registrationTotals) {
                $firstRow = $programRows->first();

                $detailRows = $programRows->values()->map(function (FeeCollection $row, int $index) use ($admissionTotals, $registrationTotals) {
                    $registration = $row->registration;
                    $admission = $row->admission;
                    $isAdmissionFee = $row->fee_type === 'admission' && $row->admission_id;
                    $totals = $isAdmissionFee
                        ? $admissionTotals->get($row->admission_id)
                        : $registrationTotals->get($row->registration_id);

                    $feePackage = $isAdmissionFee
                        ? $this->pendingRecoveryAdmissionFeePackage($admission)
                        : $this->pendingRecoveryRegistrationFeePackage($registration);

                    return [
                        'sr' => $index + 1,
                        'roll_no' => (string) ($admission?->roll_number ?: $registration?->registration_number ?: 'N/A'),
                        'name' => (string) ($admission?->student_name ?: $registration?->student_name ?: 'N/A'),
                        'father_name' => (string) ($admission?->guardian_name ?: $registration?->guardian_name ?: 'N/A'),
                        'admission_date' => $this->pendingRecoveryFormatDate($admission?->admission_date ?? $registration?->registered_at),
                        'fee_package' => round((float) $feePackage, 2),
                        'total_received' => round((float) ($totals->received_total ?? 0), 2),
                        'total_pending' => round((float) ($totals->pending_total ?? 0), 2),
                        'this_month_due' => round((float) $row->net_amount, 2),
                        'installment_label' => $this->pendingRecoveryInstallmentLabel($row),
                        'due_date' => $this->pendingRecoveryFormatDate(
                            Schema::hasColumn('fee_collections', 'due_at') && $row->due_at
                                ? $row->due_at
                                : $row->created_at
                        ),
                    ];
                })->all();

                return [
                    'program_title' => $this->pendingRecoveryProgramTitle($firstRow),
                    'rows' => $detailRows,
                    'section_total' => round((float) $programRows->sum('net_amount'), 2),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function pendingRecoveryMonthOptions(): array
    {
        $options = [];

        foreach (range(1, 12) as $month) {
            $options[$month] = Carbon::createFromDate(2000, $month, 1, $this->dashboardTimezone())->format('F');
        }

        return $options;
    }

    /**
     * @return array<int, int>
     */
    private function pendingRecoveryYearOptions(?int $campusId, int $selectedYear): array
    {
        if (!Schema::hasTable('fee_collections')) {
            return [$selectedYear => $selectedYear];
        }

        $referenceDate = $this->pendingRecoveryReferenceDateExpression();
        $bounds = DB::table('fee_collections')
            ->selectRaw("MIN({$referenceDate}) as min_date")
            ->selectRaw("MAX({$referenceDate}) as max_date")
            ->whereNotNull('campus_id')
            ->when($campusId, fn ($query, $id) => $query->where('campus_id', $id))
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'paid');
            })
            ->first();

        $minYear = $bounds?->min_date ? Carbon::parse($bounds->min_date)->year : $selectedYear;
        $maxYear = $bounds?->max_date ? Carbon::parse($bounds->max_date)->year : $selectedYear;

        $startYear = min($minYear, $selectedYear);
        $endYear = max($maxYear, $selectedYear);
        $years = [];

        for ($year = $endYear; $year >= $startYear; $year--) {
            $years[$year] = $year;
        }

        return $years !== [] ? $years : [$selectedYear => $selectedYear];
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function resolvePendingRecoveryPeriod(Request $request): array
    {
        $now = $this->dashboardNow();
        $month = (int) $request->input('month', $now->month);
        $year = (int) $request->input('year', $now->year);

        if ($month < 1 || $month > 12) {
            $month = (int) $now->month;
        }

        if ($year < 2000 || $year > 2100) {
            $year = (int) $now->year;
        }

        return [$month, $year];
    }

    private function pendingRecoveryReferenceDateExpression(): string
    {
        if (Schema::hasColumn('fee_collections', 'due_at')) {
            return 'COALESCE(due_at, DATE(created_at))';
        }

        return 'DATE(created_at)';
    }

    private function pendingRecoveryProgramKey(FeeCollection $row): string
    {
        if ($row->program_id) {
            return 'program:' . $row->program_id;
        }

        if ($row->admission?->program_id) {
            return 'program:' . $row->admission->program_id;
        }

        if ($row->registration?->program_id) {
            return 'program:' . $row->registration->program_id;
        }

        return 'program:0';
    }

    private function pendingRecoveryProgramTitle(FeeCollection $row): string
    {
        $program = $row->program;

        if (!$program && $row->admission?->program_id) {
            $program = Program::query()->find($row->admission->program_id, ['id', 'code', 'title', 'name']);
        } elseif (!$program && $row->registration?->program_id) {
            $program = Program::query()->find($row->registration->program_id, ['id', 'code', 'title', 'name']);
        }

        return (string) ($program?->title ?: $program?->name ?: $program?->code ?: 'Program');
    }

    private function pendingRecoveryAdmissionFeePackage(?Admission $admission): float
    {
        if (!$admission) {
            return 0;
        }

        $discountedFee = (float) ($admission->discounted_fee ?? 0);

        if ($discountedFee > 0) {
            return $discountedFee;
        }

        return (float) ($admission->fee_package ?? 0);
    }

    private function pendingRecoveryRegistrationFeePackage(?Registration $registration): float
    {
        if (!$registration) {
            return 0;
        }

        $netPayable = (float) ($registration->net_payable ?? 0);

        if ($netPayable > 0) {
            return $netPayable;
        }

        return (float) ($registration->fee ?? 0);
    }

    private function pendingRecoveryInstallmentLabel(FeeCollection $row): string
    {
        if ($row->fee_type === 'registration') {
            return 'Registration Fee';
        }

        if ($row->installment_no) {
            return $this->ordinalLabel((int) $row->installment_no) . ' Installment';
        }

        return 'Full Fee';
    }

    private function ordinalLabel(int $number): string
    {
        $absolute = abs($number);
        $modHundred = $absolute % 100;

        if ($modHundred >= 11 && $modHundred <= 13) {
            return $number . 'th';
        }

        return match ($absolute % 10) {
            1 => $number . 'st',
            2 => $number . 'nd',
            3 => $number . 'rd',
            default => $number . 'th',
        };
    }

    private function pendingRecoveryFormatDate($value): string
    {
        if (!$value) {
            return 'N/A';
        }

        return Carbon::parse($value)->format('Y-m-d');
    }

    private function coworkingCollectionTotalForRange(Carbon $start, Carbon $end, ?int $campusId = null): float
    {
        if (!Schema::hasTable('coworking_registration_receipts')) {
            return 0;
        }

        [$queryStart, $queryEnd] = $this->queryTimestampRange($start, $end);

        return (float) CoworkingRegistrationReceipt::query()
            ->where('receipt_type', 'coworking_charge')
            ->whereNotNull('paid_at')
            ->when($campusId, fn ($q, $id) => $q->where('campus_id', $id))
            ->whereBetween('paid_at', [$queryStart, $queryEnd])
            ->sum('amount');
    }

    private function paidCollectionRows(Carbon $start, Carbon $end, ?int $campusId = null)
    {
        [$queryStart, $queryEnd] = $this->queryTimestampRange($start, $end);

        $feeRows = Schema::hasTable('fee_collections')
            ? FeeCollection::query()
                ->whereIn('fee_type', ['registration', 'admission'])
                ->where('status', 'paid')
                ->whereNotNull('paid_at')
                ->when($campusId, fn ($q, $id) => $q->where('campus_id', $id))
                ->whereBetween('paid_at', [$queryStart, $queryEnd])
                ->get(['paid_at', 'created_at', 'net_amount'])
                ->map(fn (FeeCollection $fee) => (object) [
                    'paid_at' => $fee->paid_at,
                    'created_at' => $fee->created_at,
                    'amount' => (float) $fee->net_amount,
                ])
            : collect();

        $coworkingRows = Schema::hasTable('coworking_registration_receipts')
            ? CoworkingRegistrationReceipt::query()
                ->where('receipt_type', 'coworking_charge')
                ->whereNotNull('paid_at')
                ->when($campusId, fn ($q, $id) => $q->where('campus_id', $id))
                ->whereBetween('paid_at', [$queryStart, $queryEnd])
                ->get(['paid_at', 'created_at', 'amount'])
                ->map(fn (CoworkingRegistrationReceipt $receipt) => (object) [
                    'paid_at' => $receipt->paid_at,
                    'created_at' => $receipt->created_at,
                    'amount' => (float) $receipt->amount,
                ])
            : collect();

        return $feeRows->concat($coworkingRows)->values();
    }

    private function resolveCollectionGraphTimestamp($paidAt, $createdAt): ?Carbon
    {
        $timezone = $this->dashboardTimezone();
        $paidAtCarbon = $this->normalizeTimestamp($paidAt);
        $createdAtCarbon = $this->normalizeTimestamp($createdAt);
        $paidAtLocal = $paidAtCarbon?->copy()->timezone($timezone);
        $createdAtLocal = $createdAtCarbon?->copy()->timezone($timezone);

        if ($paidAtCarbon && $createdAtLocal && $paidAtLocal
            && $paidAtCarbon->copy()->startOfDay()->equalTo($paidAtCarbon)
            && $createdAtLocal->isSameDay($paidAtLocal)
            && $createdAtLocal->gt($paidAtLocal)
        ) {
            return $createdAtLocal;
        }

        return $paidAtLocal ?? $createdAtLocal;
    }

    /**
     * @param  array<int, array{label: string, start: int, end: int}>  $hourlySlots
     */
    private function resolveHourlySlotLabel(Carbon $timestamp, array $hourlySlots): string
    {
        $hour = $timestamp->hour;

        foreach ($hourlySlots as $slot) {
            if ($hour >= $slot['start'] && $hour <= $slot['end']) {
                return $slot['label'];
            }
        }

        if ($hour < $hourlySlots[0]['start']) {
            return $hourlySlots[0]['label'];
        }

        return $hourlySlots[array_key_last($hourlySlots)]['label'];
    }

    private function dashboardTimezone(): string
    {
        return 'Asia/Karachi';
    }

    private function dashboardNow(): Carbon
    {
        return now($this->dashboardTimezone());
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function queryTimestampRange(Carbon $start, Carbon $end): array
    {
        return [
            $start->copy()->utc(),
            $end->copy()->utc(),
        ];
    }

    private function normalizeTimestamp($value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        return $value instanceof Carbon
            ? $value->copy()
            : Carbon::parse($value);
    }

    private function leadQueryForDashboard(?int $campusId = null, array $dashboardAccess = [], bool $trainingOnly = false): Builder
    {
        $query = Lead::query()
            ->when($campusId, fn (Builder $builder, int $id) => $builder->where('campus_id', $id));

        $canViewTrainingLeads = (bool) ($dashboardAccess['training_leads'] ?? false);
        $canViewCoworkingLeads = (bool) ($dashboardAccess['coworking_leads'] ?? false);

        if ($trainingOnly) {
            return $canViewTrainingLeads
                ? $query->training()
                : $query->whereRaw('1 = 0');
        }

        if ($canViewTrainingLeads && $canViewCoworkingLeads) {
            return $query->whereIn('type', ['training', 'coworking']);
        }

        if ($canViewTrainingLeads) {
            return $query->training();
        }

        if ($canViewCoworkingLeads) {
            return $query->coworking();
        }

        return $query->whereRaw('1 = 0');
    }

    private function resolveCampusId(Request $request): ?int
    {
        if (!Schema::hasTable('campuses')) {
            session()->forget('dashboard_campus_id');

            return null;
        }

        $user = $request->user();

        if (!$this->canSelectDashboardCampus($user)) {
            $userCampusId = (int) ($user?->campus_id ?? 0);

            if ($userCampusId > 0 && Campus::query()->whereKey($userCampusId)->exists()) {
                session(['dashboard_campus_id' => $userCampusId]);

                return $userCampusId;
            }

            session()->forget('dashboard_campus_id');

            return null;
        }

        if ($request->has('campus_id')) {
            $value = strtolower(trim((string) $request->input('campus_id')));

            if ($value === '' || $value === '0' || $value === 'all') {
                session()->forget('dashboard_campus_id');

                return null;
            }

            $campusId = (int) $value;

            if ($campusId > 0 && Campus::query()->whereKey($campusId)->exists()) {
                session(['dashboard_campus_id' => $campusId]);

                return $campusId;
            }

            session()->forget('dashboard_campus_id');

            return null;
        }

        $sessionCampusId = (int) session('dashboard_campus_id', 0);

        if ($sessionCampusId > 0 && Campus::query()->whereKey($sessionCampusId)->exists()) {
            return $sessionCampusId;
        }

        return null;
    }

    /**
     * @return array{leads: bool, admissions: bool, income: bool}
     */
    private function resolveDashboardAccess(?User $user): array
    {
        if (!$user) {
            return [
                'leads' => false,
                'admissions' => false,
                'income' => false,
            ];
        }

        return [
            'leads' => $user->canAccessModule('lead-management')
                || $user->canAccessModule('training-leads')
                || $user->canAccessModule('coworking-space')
                || $user->canAccessModule('web-leads'),
            'training_leads' => $user->hasAnyPermission([
                'lead.view',
                'lead.create',
                'lead.update',
                'lead.delete',
                'lead.followup.view',
                'lead.followup.update',
                'lead.transfer.approve',
            ]),
            'coworking_leads' => $user->hasAnyPermission([
                'lead.coworking.view',
            ]),
            'admissions' => $user->canAccessModule('admission-management')
                || $user->canAccessModule('student-management'),
            'income' => $user->canAccessModule('registration-management')
                || $user->canAccessModule('finance-management'),
        ];
    }

    private function canSelectDashboardCampus(?User $user): bool
    {
        return (bool) ($user?->isAdmin() ?? false);
    }

    private function ensureDashboardCampusAccess(?int $campusId, ?User $user): void
    {
        if ($this->canSelectDashboardCampus($user)) {
            return;
        }

        $userCampusId = (int) ($user?->campus_id ?? 0);

        if ($userCampusId > 0 && $campusId && $campusId !== $userCampusId) {
            abort(403, 'You are not allowed to access records from another campus.');
        }
    }
}
