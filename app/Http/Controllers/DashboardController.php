<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Admission;
use App\Models\Lead;
use App\Models\Program;
use App\Models\Registration;
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
        $selectedCampus = $selectedCampusId && Schema::hasTable('campuses')
            ? Campus::query()->find($selectedCampusId, ['id', 'code', 'name', 'campus_type'])
            : null;

        return view('dashboard', [
            'dashboard' => $this->buildDashboardPayload($selectedCampusId),
            'selectedCampus' => $selectedCampus,
        ]);
    }

    public function liveData(Request $request): JsonResponse
    {
        return response()->json([
            'dashboard' => $this->buildDashboardPayload($this->resolveCampusId($request)),
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
    private function buildDashboardPayload(?int $selectedCampusId = null): array
    {
        if (!$this->requiredTablesExist()) {
            $payload = $this->emptyPayload();
            $payload['generatedAt'] = now()->toIso8601String();

            return $payload;
        }

        $today = now()->startOfDay();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $yearStart = now()->startOfYear();
        $yearEnd = now()->endOfYear();

        $stats = $this->buildStats($today, $monthStart, $monthEnd, $selectedCampusId);

        return [
            'stats' => $stats,
            'dailyActivity' => $this->buildDailyActivity($selectedCampusId),
            'admissionsActivity' => $this->buildAdmissionsActivity($selectedCampusId),
            'monthlyAdmissionsInsight' => $this->buildMonthlyAdmissionsInsight($selectedCampusId),
            'incomeSummary' => [
                'today' => $stats['todayCollectionRaw'],
                'week' => $stats['weekCollectionRaw'],
                'month' => $stats['currentMonthCollectionRaw'],
            ],
            'incomeRanges' => $this->buildIncomeRanges($today, $monthStart, $monthEnd, $yearStart, $yearEnd, $selectedCampusId),
            'charts' => $this->buildCharts($monthStart, $monthEnd, $selectedCampusId),
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
                'totalLeads' => 0,
                'currentStudents' => 0,
                'currentMonthAdmissions' => 0,
                'previousMonthAdmissions' => 0,
                'currentMonthCollection' => '0',
                'currentMonthCollectionRaw' => 0,
                'currentMonthPending' => 0,
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
                'month' => ['label' => 'Month income (weekly)', 'points' => [['Week 1', 0]], 'ticks' => [0, 10]],
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
    private function buildStats(Carbon $today, Carbon $monthStart, Carbon $monthEnd, ?int $campusId = null): array
    {
        $todayCollection = $this->registrationDateRange(
            Registration::query()
                ->where('status', 'registered')
                ->when($campusId, fn ($q, $id) => $q->where('campus_id', $id)),
            $today,
            $today->copy()->endOfDay()
        )->sum('net_payable');

        $weekStart = now()->startOfDay()->subDays(6);
        $weekCollection = $this->registrationDateRange(
            Registration::query()
                ->where('status', 'registered')
                ->when($campusId, fn ($q, $id) => $q->where('campus_id', $id)),
            $weekStart,
            now()->endOfDay()
        )->sum('net_payable');

        $monthCollection = $this->registrationDateRange(
            Registration::query()
                ->where('status', 'registered')
                ->when($campusId, fn ($q, $id) => $q->where('campus_id', $id)),
            $monthStart,
            $monthEnd
        )->sum('net_payable');
        $previousMonthStart = $monthStart->copy()->subMonth()->startOfMonth();
        $previousMonthEnd = $previousMonthStart->copy()->endOfMonth();

        $currentStudents = (int) DB::table('admissions')
            ->join('registrations', 'registrations.id', '=', 'admissions.registration_id')
            ->when($campusId, fn ($q, $id) => $q->where('registrations.campus_id', $id))
            ->count();

        $currentMonthAdmissions = (int) DB::table('admissions')
            ->join('registrations', 'registrations.id', '=', 'admissions.registration_id')
            ->when($campusId, fn ($q, $id) => $q->where('registrations.campus_id', $id))
            ->whereBetween('admissions.admission_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->count();

        $previousMonthAdmissions = (int) DB::table('admissions')
            ->join('registrations', 'registrations.id', '=', 'admissions.registration_id')
            ->when($campusId, fn ($q, $id) => $q->where('registrations.campus_id', $id))
            ->whereBetween('admissions.admission_date', [$previousMonthStart->toDateString(), $previousMonthEnd->toDateString()])
            ->count();

        return [
            'totalLeads' => Lead::query()
                ->when($campusId, fn ($q, $id) => $q->where('campus_id', $id))
                ->count(),
            'currentStudents' => $currentStudents,
            'currentMonthAdmissions' => $currentMonthAdmissions,
            'previousMonthAdmissions' => $previousMonthAdmissions,
            'currentMonthCollection' => number_format((float) $monthCollection, 0),
            'currentMonthCollectionRaw' => (float) $monthCollection,
            'currentMonthPending' => Lead::query()
                ->when($campusId, fn ($q, $id) => $q->where('campus_id', $id))
                ->where('status', 'pending')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count(),
            'todayCollectionRaw' => (float) $todayCollection,
            'weekCollectionRaw' => (float) $weekCollection,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDailyActivity(?int $campusId = null): array
    {
        $rows = Lead::query()
            ->with('campus:id,code,name')
            ->when($campusId, fn ($q, $id) => $q->where('campus_id', $id))
            ->latest('created_at')
            ->latest('id')
            ->limit(12)
            ->get(['id', 'campus_id', 'name', 'phone', 'status', 'type', 'created_at'])
            ->map(function (Lead $lead) use ($campusId) {
                $campusLabel = optional($lead->campus)->code
                    ?: optional($lead->campus)->name
                    ?: 'Campus';

                return [
                    'status_label' => $this->formatLeadStatusLabel($lead->status),
                    'status_tone' => $this->leadStatusTone($lead->status),
                    'student_name' => $lead->name ?: 'N/A',
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
            ->get(['id', 'campus_id', 'student_name', 'phone', 'student_status', 'admission_date'])
            ->map(function (Admission $admission) use ($campusId) {
                $campusLabel = optional($admission->campus)->code
                    ?: optional($admission->campus)->name
                    ?: 'Campus';

                return [
                    'status_label' => $this->formatAdmissionStatusLabel($admission->student_status),
                    'status_tone' => $this->admissionStatusTone($admission->student_status),
                    'student_name' => $admission->student_name ?: 'N/A',
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

        $date = Carbon::parse($timestamp);

        if ($date->isToday()) {
            return 'Today ' . $date->format('H:i');
        }

        if ($date->isYesterday()) {
            return 'Yesterday ' . $date->format('H:i');
        }

        return $date->format('d-M H:i');
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
        $todayRows = $this->registrationDateRange(
            Registration::query()
                ->where('status', 'registered')
                ->when($campusId, fn ($q, $id) => $q->where('campus_id', $id)),
            $today,
            $today->copy()->endOfDay()
        )->get(['registered_at', 'created_at', 'net_payable']);

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
            $timestamp = $row->registered_at ?? $row->created_at;
            if (!$timestamp) {
                continue;
            }

            $hour = Carbon::parse($timestamp)->hour;
            foreach ($hourlySlots as $slot) {
                if ($hour >= $slot['start'] && $hour <= $slot['end']) {
                    $hourlyTotals[$slot['label']] += (float) ($row->net_payable ?? 0);
                    break;
                }
            }
        }

        $weekStart = now()->startOfDay()->subDays(6);
        $weekRows = $this->registrationDateRange(
            Registration::query()
                ->where('status', 'registered')
                ->when($campusId, fn ($q, $id) => $q->where('campus_id', $id)),
            $weekStart,
            now()->endOfDay()
        )->get(['registered_at', 'created_at', 'net_payable']);
        $weekDailyTotals = [];
        foreach (CarbonPeriod::create($weekStart, now()->startOfDay()) as $date) {
            $weekDailyTotals[$date->toDateString()] = 0.0;
        }
        foreach ($weekRows as $row) {
            $timestamp = $row->registered_at ?? $row->created_at;
            if (!$timestamp) {
                continue;
            }
            $dayKey = Carbon::parse($timestamp)->toDateString();
            if (array_key_exists($dayKey, $weekDailyTotals)) {
                $weekDailyTotals[$dayKey] += (float) ($row->net_payable ?? 0);
            }
        }

        $monthRows = $this->registrationDateRange(
            Registration::query()
                ->where('status', 'registered')
                ->when($campusId, fn ($q, $id) => $q->where('campus_id', $id)),
            $monthStart,
            $monthEnd
        )->get(['registered_at', 'created_at', 'net_payable']);
        $weekCount = (int) ceil($monthEnd->day / 7);
        $monthWeeklyTotals = [];
        for ($i = 1; $i <= $weekCount; $i++) {
            $monthWeeklyTotals['Week ' . $i] = 0.0;
        }
        foreach ($monthRows as $row) {
            $timestamp = $row->registered_at ?? $row->created_at;
            if (!$timestamp) {
                continue;
            }
            $dayOfMonth = Carbon::parse($timestamp)->day;
            $weekBucket = (int) ceil($dayOfMonth / 7);
            $monthWeeklyTotals['Week ' . $weekBucket] += (float) ($row->net_payable ?? 0);
        }

        $yearRows = $this->registrationDateRange(
            Registration::query()
                ->where('status', 'registered')
                ->when($campusId, fn ($q, $id) => $q->where('campus_id', $id)),
            $yearStart,
            $yearEnd
        )->get(['registered_at', 'created_at', 'net_payable']);
        $yearMonthlyTotals = [];
        foreach (range(1, 12) as $month) {
            $yearMonthlyTotals[Carbon::create(now()->year, $month, 1)->format('M')] = 0.0;
        }
        foreach ($yearRows as $row) {
            $timestamp = $row->registered_at ?? $row->created_at;
            if (!$timestamp) {
                continue;
            }
            $monthKey = Carbon::parse($timestamp)->format('M');
            if (array_key_exists($monthKey, $yearMonthlyTotals)) {
                $yearMonthlyTotals[$monthKey] += (float) ($row->net_payable ?? 0);
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
    private function buildCharts(Carbon $monthStart, Carbon $monthEnd, ?int $campusId = null): array
    {
        $leadCountsByProgram = Lead::query()
            ->selectRaw('program_id, COUNT(*) as aggregate')
            ->when($campusId, fn ($q, $id) => $q->where('campus_id', $id))
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->groupBy('program_id')
            ->pluck('aggregate', 'program_id');

        $admissionCountsByProgram = DB::table('admissions')
            ->join('registrations', 'registrations.id', '=', 'admissions.registration_id')
            ->selectRaw('registrations.program_id as program_id, COUNT(*) as aggregate')
            ->when($campusId, fn ($q, $id) => $q->where('registrations.campus_id', $id))
            ->whereBetween('admissions.admission_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->groupBy('registrations.program_id')
            ->pluck('aggregate', 'program_id');

        $admissionsByCampus = DB::table('admissions')
            ->join('registrations', 'registrations.id', '=', 'admissions.registration_id')
            ->selectRaw('registrations.campus_id as campus_id, COUNT(*) as aggregate')
            ->when($campusId, fn ($q, $id) => $q->where('registrations.campus_id', $id))
            ->whereBetween('admissions.admission_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->groupBy('registrations.campus_id')
            ->pluck('aggregate', 'campus_id');

        $programLabels = Program::query()
            ->whereIn(
                'id',
                collect($leadCountsByProgram->keys())
                    ->merge($admissionCountsByProgram->keys())
                    ->filter()
                    ->unique()
                    ->values()
            )
            ->get(['id', 'code', 'title', 'name'])
            ->mapWithKeys(function (Program $program) {
                $label = $program->code ?: ($program->title ?: $program->name ?: 'Program #' . $program->id);
                return [$program->id => $label];
            });

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

    private function resolveCampusId(Request $request): ?int
    {
        if (!Schema::hasTable('campuses')) {
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
}
