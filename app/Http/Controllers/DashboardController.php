<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\Program;
use App\Models\Registration;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        if (!$this->requiredTablesExist()) {
            return view('dashboard', ['dashboard' => $this->emptyPayload()]);
        }

        $today = now()->startOfDay();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $yearStart = now()->startOfYear();
        $yearEnd = now()->endOfYear();

        $stats = $this->buildStats($today, $monthStart, $monthEnd);
        $dailyActivity = $this->buildDailyActivity($today);
        $incomeRanges = $this->buildIncomeRanges($today, $monthStart, $monthEnd, $yearStart, $yearEnd);
        $charts = $this->buildCharts($monthStart, $monthEnd);

        return view('dashboard', [
            'dashboard' => [
                'stats' => $stats,
                'dailyActivity' => $dailyActivity,
                'incomeSummary' => [
                    'today' => $stats['todayCollectionRaw'],
                    'week' => $stats['weekCollectionRaw'],
                    'month' => $stats['currentMonthCollectionRaw'],
                ],
                'incomeRanges' => $incomeRanges,
                'charts' => $charts,
            ],
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
    private function emptyPayload(): array
    {
        return [
            'stats' => [
                'totalLeads' => 0,
                'currentStudents' => 0,
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
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildStats(Carbon $today, Carbon $monthStart, Carbon $monthEnd): array
    {
        $todayCollection = $this->registrationDateRange(
            Registration::query()->where('status', 'registered'),
            $today,
            $today->copy()->endOfDay()
        )->sum('net_payable');

        $weekStart = now()->startOfDay()->subDays(6);
        $weekCollection = $this->registrationDateRange(
            Registration::query()->where('status', 'registered'),
            $weekStart,
            now()->endOfDay()
        )->sum('net_payable');

        $monthCollection = $this->registrationDateRange(
            Registration::query()->where('status', 'registered'),
            $monthStart,
            $monthEnd
        )->sum('net_payable');

        $currentStudents = (int) DB::table('admissions')->count();

        return [
            'totalLeads' => Lead::query()->count(),
            'currentStudents' => $currentStudents,
            'currentMonthCollection' => number_format((float) $monthCollection, 0),
            'currentMonthCollectionRaw' => (float) $monthCollection,
            'currentMonthPending' => Lead::query()
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
    private function buildDailyActivity(Carbon $today): array
    {
        $campuses = Campus::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $leadByCampus = Lead::query()
            ->selectRaw('campus_id, COUNT(*) as aggregate')
            ->whereDate('created_at', $today->toDateString())
            ->groupBy('campus_id')
            ->pluck('aggregate', 'campus_id');

        $followupByCampus = LeadFollowup::query()
            ->selectRaw('campus_id, COUNT(*) as aggregate')
            ->whereDate('created_at', $today->toDateString())
            ->groupBy('campus_id')
            ->pluck('aggregate', 'campus_id');

        $admissionByCampus = DB::table('admissions')
            ->join('registrations', 'registrations.id', '=', 'admissions.registration_id')
            ->selectRaw('registrations.campus_id as campus_id, COUNT(*) as aggregate')
            ->whereDate('admissions.admission_date', $today->toDateString())
            ->groupBy('registrations.campus_id')
            ->pluck('aggregate', 'campus_id');

        $collectionByCampus = $this->registrationDateRange(
            Registration::query()
                ->where('status', 'registered')
                ->selectRaw('campus_id, SUM(COALESCE(net_payable, 0)) as aggregate')
                ->groupBy('campus_id'),
            $today->copy()->startOfDay(),
            $today->copy()->endOfDay()
        )->pluck('aggregate', 'campus_id');

        $rows = $campuses->map(function (Campus $campus) use ($leadByCampus, $followupByCampus, $admissionByCampus, $collectionByCampus) {
            return [
                'campus' => $campus->code ?: $campus->name,
                'leads' => (int) ($leadByCampus[$campus->id] ?? 0),
                'followups' => (int) ($followupByCampus[$campus->id] ?? 0),
                'admissions' => (int) ($admissionByCampus[$campus->id] ?? 0),
                'collection' => (float) ($collectionByCampus[$campus->id] ?? 0),
            ];
        });

        return [
            'rows' => $rows->values()->all(),
            'totals' => [
                'leads' => (int) $rows->sum('leads'),
                'followups' => (int) $rows->sum('followups'),
                'admissions' => (int) $rows->sum('admissions'),
                'collection' => (float) $rows->sum('collection'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildIncomeRanges(
        Carbon $today,
        Carbon $monthStart,
        Carbon $monthEnd,
        Carbon $yearStart,
        Carbon $yearEnd
    ): array {
        $todayRows = $this->registrationDateRange(
            Registration::query()->where('status', 'registered'),
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
            Registration::query()->where('status', 'registered'),
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
            Registration::query()->where('status', 'registered'),
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
            Registration::query()->where('status', 'registered'),
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
    private function buildCharts(Carbon $monthStart, Carbon $monthEnd): array
    {
        $leadCountsByProgram = Lead::query()
            ->selectRaw('program_id, COUNT(*) as aggregate')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->groupBy('program_id')
            ->pluck('aggregate', 'program_id');

        $admissionCountsByProgram = DB::table('admissions')
            ->join('registrations', 'registrations.id', '=', 'admissions.registration_id')
            ->selectRaw('registrations.program_id as program_id, COUNT(*) as aggregate')
            ->whereBetween('admissions.admission_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->groupBy('registrations.program_id')
            ->pluck('aggregate', 'program_id');

        $admissionsByCampus = DB::table('admissions')
            ->join('registrations', 'registrations.id', '=', 'admissions.registration_id')
            ->selectRaw('registrations.campus_id as campus_id, COUNT(*) as aggregate')
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
}
