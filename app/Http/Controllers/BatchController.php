<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Campus;
use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BatchController extends Controller
{
    public function index(Request $request): View
    {
        $scope = (string) $request->query('scope', 'all');
        $perPage = $this->resolvePerPage($request);
        $today = now()->startOfDay();
        $recentWindow = $today->copy()->subDays(30);

        $batches = Batch::query()
            ->with(['program', 'campus'])
            ->withCount(['admissions', 'timetables']);

        $this->applyScope($batches, $scope, $today, $recentWindow);
        $this->applyFilters($batches, $request);

        $batches = $batches
            ->orderByDesc('start_date')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('batch.index', [
            'batches' => $batches,
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'code', 'name']),
            'programs' => Program::query()->orderByRaw('COALESCE(title, name)')->get(['id', 'code', 'title', 'name']),
            'filters' => [
                'scope' => $scope,
                'campus_id' => $request->integer('campus_id') ?: null,
                'program_id' => $request->integer('program_id') ?: null,
                'session' => $request->input('session'),
                'status' => $request->input('status'),
                'search' => $request->input('search'),
                'per_page' => $perPage,
            ],
            'scopeCards' => $this->buildScopeCards($request, $today, $recentWindow),
            'pageTitle' => $this->resolvePageTitle($scope),
        ]);
    }

    public function create(): View
    {
        return view('batch.create', $this->formPayload(new Batch()));
    }

    public function edit(Batch $batch): View
    {
        return view('batch.edit', $this->formPayload($batch));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateBatch($request);
        $validated['code'] = $this->generateBatchCode($validated['program_id'], $validated['start_date']);

        Batch::query()->create($validated);

        return redirect()->route('batch.index')->with('status', 'Batch created successfully.');
    }

    public function update(Request $request, Batch $batch): RedirectResponse
    {
        $validated = $this->validateBatch($request, $batch);
        $validated['code'] = $this->generateBatchCode($validated['program_id'], $validated['start_date'], $batch->id);

        $batch->update($validated);

        return redirect()->route('batch.index')->with('status', 'Batch updated successfully.');
    }

    public function destroy(Batch $batch): RedirectResponse
    {
        $hasReferences = $batch->admissions()->exists()
            || $batch->timetables()->exists();

        if ($hasReferences) {
            return redirect()->route('batch.index')
                ->with('error', 'Cannot delete: this batch has linked admissions or timetable entries. Suspend it instead.');
        }

        $batch->delete();

        return redirect()->route('batch.index')->with('status', 'Batch deleted successfully.');
    }

    public function toggleStatus(Batch $batch): RedirectResponse
    {
        if (in_array($batch->status, ['completed', 'cancelled'], true)) {
            return redirect()->route('batch.index')
                ->with('error', 'Completed or cancelled batches cannot be toggled. Edit the batch to change status.');
        }

        $batch->update([
            'status' => $batch->status === 'active' ? 'inactive' : 'active',
        ]);

        $message = $batch->status === 'active'
            ? 'Batch activated.'
            : 'Batch suspended.';

        return redirect()->route('batch.index')->with('status', $message);
    }

    private function formPayload(Batch $batch): array
    {
        return [
            'batch' => $batch,
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'code', 'name']),
            'programs' => Program::query()->orderByRaw('COALESCE(title, name)')->get(['id', 'code', 'title', 'name']),
            'statusOptions' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
            ],
            'sessionOptions' => [
                'morning' => 'Morning',
                'evening' => 'Evening',
                'weekend' => 'Weekend',
            ],
        ];
    }

    private function validateBatch(Request $request, ?Batch $batch = null): array
    {
        $validated = $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
            'program_id' => ['required', 'exists:programs,id'],
            'name' => ['required', 'string', 'max:255'],
            'instructor' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'session' => ['required', Rule::in(['morning', 'evening', 'weekend'])],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'lab' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive', 'completed', 'cancelled'])],
            'remarks' => ['nullable', 'string'],
        ]);

        if (Carbon::createFromFormat('H:i', $validated['end_time'])->lessThanOrEqualTo(Carbon::createFromFormat('H:i', $validated['start_time']))) {
            throw ValidationException::withMessages([
                'end_time' => 'End time must be after start time.',
            ]);
        }

        return $validated;
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        $query
            ->when($request->integer('campus_id'), fn (Builder $builder, int $campusId) => $builder->where('campus_id', $campusId))
            ->when($request->integer('program_id'), fn (Builder $builder, int $programId) => $builder->where('program_id', $programId))
            ->when($request->filled('session'), fn (Builder $builder) => $builder->where('session', $request->input('session')))
            ->when($request->filled('status'), fn (Builder $builder) => $builder->where('status', $request->input('status')))
            ->when($request->filled('search'), function (Builder $builder) use ($request) {
                $keyword = trim((string) $request->input('search'));

                $builder->where(function (Builder $inner) use ($keyword) {
                    $inner
                        ->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('code', 'like', '%' . $keyword . '%')
                        ->orWhere('instructor', 'like', '%' . $keyword . '%')
                        ->orWhere('lab', 'like', '%' . $keyword . '%')
                        ->orWhereHas('program', function (Builder $programs) use ($keyword) {
                            $programs
                                ->where('title', 'like', '%' . $keyword . '%')
                                ->orWhere('name', 'like', '%' . $keyword . '%')
                                ->orWhere('code', 'like', '%' . $keyword . '%');
                        })
                        ->orWhereHas('campus', function (Builder $campuses) use ($keyword) {
                            $campuses
                                ->where('name', 'like', '%' . $keyword . '%')
                                ->orWhere('code', 'like', '%' . $keyword . '%');
                        });
                });
            });
    }

    private function resolvePerPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 25);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;
    }

    private function applyScope(Builder $query, string $scope, Carbon $today, Carbon $recentWindow): void
    {
        switch ($scope) {
            case 'upcoming':
                $query->whereDate('start_date', '>', $today);
                break;

            case 'recently_started':
                $query
                    ->whereDate('start_date', '>=', $recentWindow)
                    ->whereDate('start_date', '<=', $today);
                break;

            case 'in_progress':
                $query
                    ->whereDate('start_date', '<=', $today)
                    ->where(function (Builder $builder) use ($today) {
                        $builder
                            ->whereNull('end_date')
                            ->orWhereDate('end_date', '>=', $today);
                    });
                break;

            case 'recently_ended':
                $query
                    ->whereNotNull('end_date')
                    ->whereDate('end_date', '>=', $recentWindow)
                    ->whereDate('end_date', '<', $today);
                break;

            case 'completed':
                $query
                    ->whereNotNull('end_date')
                    ->whereDate('end_date', '<', $today);
                break;
        }
    }

    private function buildScopeCards(Request $request, Carbon $today, Carbon $recentWindow): array
    {
        $todayStr = $today->toDateString();
        $recentStr = $recentWindow->toDateString();

        $row = Batch::query()
            ->tap(fn (Builder $query) => $this->applyFilters($query, $request))
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN start_date > ? THEN 1 ELSE 0 END) as upcoming,
                SUM(CASE WHEN start_date BETWEEN ? AND ? THEN 1 ELSE 0 END) as recently_started,
                SUM(CASE WHEN start_date <= ? AND (end_date IS NULL OR end_date >= ?) THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN end_date IS NOT NULL AND end_date BETWEEN ? AND ? AND end_date < ? THEN 1 ELSE 0 END) as recently_ended,
                SUM(CASE WHEN end_date IS NOT NULL AND end_date < ? THEN 1 ELSE 0 END) as completed
            ', [
                $todayStr,
                $recentStr, $todayStr,
                $todayStr, $todayStr,
                $recentStr, $todayStr, $todayStr,
                $todayStr,
            ])
            ->first();

        return [
            ['scope' => 'all', 'label' => 'All Batches', 'count' => (int) ($row->total ?? 0)],
            ['scope' => 'upcoming', 'label' => 'Upcoming', 'count' => (int) ($row->upcoming ?? 0)],
            ['scope' => 'recently_started', 'label' => 'Recently Started', 'count' => (int) ($row->recently_started ?? 0)],
            ['scope' => 'in_progress', 'label' => 'In Progress', 'count' => (int) ($row->in_progress ?? 0)],
            ['scope' => 'recently_ended', 'label' => 'Recently Ended', 'count' => (int) ($row->recently_ended ?? 0)],
            ['scope' => 'completed', 'label' => 'Completed', 'count' => (int) ($row->completed ?? 0)],
        ];
    }

    private function resolvePageTitle(string $scope): string
    {
        return match ($scope) {
            'upcoming' => 'Upcoming Batches',
            'recently_started' => 'Recently Started Batches',
            'in_progress' => 'Batches In Progress',
            'recently_ended' => 'Recently Ended Batches',
            'completed' => 'Completed Batches',
            default => 'Batches & Time Table',
        };
    }

    private function generateBatchCode(int $programId, string $startDate, ?int $ignoreId = null): string
    {
        $program = Program::query()->findOrFail($programId);
        $baseCode = strtoupper((string) $program->code) . Carbon::parse($startDate)->format('m-y');
        $candidate = $baseCode;
        $counter = 2;

        while (
            Batch::query()
                ->where('code', $candidate)
                ->when($ignoreId, fn (Builder $builder, int $id) => $builder->whereKeyNot($id))
                ->exists()
        ) {
            $candidate = $baseCode . '-' . str_pad((string) $counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        }

        return $candidate;
    }
}
