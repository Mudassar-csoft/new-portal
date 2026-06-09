<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\BatchTimetable;
use App\Models\Campus;
use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BatchTimetableController extends Controller
{
    public function index(Request $request): View
    {
        $entries = BatchTimetable::query()
            ->with(['batch.program', 'batch.campus']);

        $this->applyFilters($entries, $request);

        $boardEntries = (clone $entries)
            ->orderByRaw($this->daySortSql())
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        $entries = $entries
            ->orderByRaw($this->daySortSql())
            ->orderBy('start_time')
            ->paginate(20)
            ->withQueryString();

        $editingEntry = null;
        if ($request->user()?->isAdmin() && $request->integer('edit')) {
            $editingEntry = BatchTimetable::query()->with(['batch.program', 'batch.campus'])->find($request->integer('edit'));
        }

        return view('batch.timetable.index', [
            'entries' => $entries,
            'boardEntries' => $boardEntries,
            'editingEntry' => $editingEntry,
            'batches' => Batch::query()->with(['program', 'campus'])->orderByDesc('start_date')->get(),
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'code', 'name']),
            'programs' => Program::query()->orderByRaw('COALESCE(title, name)')->get(['id', 'code', 'title', 'name']),
            'dayOptions' => $this->dayOptions(),
            'statusOptions' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
                'cancelled' => 'Cancelled',
            ],
            'filters' => [
                'campus_id' => $request->integer('campus_id') ?: null,
                'program_id' => $request->integer('program_id') ?: null,
                'batch_id' => $request->integer('batch_id') ?: null,
                'day_of_week' => $request->input('day_of_week'),
                'status' => $request->input('status'),
                'search' => $request->input('search'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTimetable($request);

        BatchTimetable::query()->create($validated);

        return redirect()->route('batch.timetable.index')->with('status', 'Timetable entry created successfully.');
    }

    public function update(Request $request, BatchTimetable $timetable): RedirectResponse
    {
        $validated = $this->validateTimetable($request, $timetable);

        $timetable->update($validated);

        return redirect()->route('batch.timetable.index')->with('status', 'Timetable entry updated successfully.');
    }

    public function destroy(BatchTimetable $timetable): RedirectResponse
    {
        $timetable->delete();

        return redirect()->route('batch.timetable.index')->with('status', 'Timetable entry deleted successfully.');
    }

    private function validateTimetable(Request $request, ?BatchTimetable $timetable = null): array
    {
        $validated = $request->validate([
            'batch_id' => ['required', 'exists:batches,id'],
            'day_of_week' => ['required', Rule::in(array_keys($this->dayOptions()))],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'lab' => ['nullable', 'string', 'max:255'],
            'instructor' => ['nullable', 'string', 'max:255'],
            'topic' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive', 'cancelled'])],
            'remarks' => ['nullable', 'string'],
        ]);

        if (Carbon::createFromFormat('H:i', $validated['end_time'])->lessThanOrEqualTo(Carbon::createFromFormat('H:i', $validated['start_time']))) {
            throw ValidationException::withMessages([
                'end_time' => 'End time must be after start time.',
            ]);
        }

        $conflictExists = BatchTimetable::query()
            ->where('batch_id', $validated['batch_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->when($timetable, fn (Builder $builder, BatchTimetable $entry) => $builder->whereKeyNot($entry->id))
            ->where('start_time', '<', $validated['end_time'])
            ->where('end_time', '>', $validated['start_time'])
            ->exists();

        if ($conflictExists) {
            throw ValidationException::withMessages([
                'start_time' => 'This batch already has an overlapping timetable slot for the selected day.',
            ]);
        }

        return $validated;
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        $query
            ->when($request->integer('batch_id'), fn (Builder $builder, int $batchId) => $builder->where('batch_id', $batchId))
            ->when($request->filled('day_of_week'), fn (Builder $builder) => $builder->where('day_of_week', $request->input('day_of_week')))
            ->when($request->filled('status'), fn (Builder $builder) => $builder->where('status', $request->input('status')))
            ->when($request->integer('campus_id'), function (Builder $builder, int $campusId) {
                $builder->whereHas('batch', fn (Builder $batch) => $batch->where('campus_id', $campusId));
            })
            ->when($request->integer('program_id'), function (Builder $builder, int $programId) {
                $builder->whereHas('batch', fn (Builder $batch) => $batch->where('program_id', $programId));
            })
            ->when($request->filled('search'), function (Builder $builder) use ($request) {
                $keyword = trim((string) $request->input('search'));

                $builder->where(function (Builder $inner) use ($keyword) {
                    $inner
                        ->where('lab', 'like', '%' . $keyword . '%')
                        ->orWhere('instructor', 'like', '%' . $keyword . '%')
                        ->orWhere('topic', 'like', '%' . $keyword . '%')
                        ->orWhereHas('batch', function (Builder $batch) use ($keyword) {
                            $batch
                                ->where('code', 'like', '%' . $keyword . '%')
                                ->orWhere('name', 'like', '%' . $keyword . '%');
                        });
                });
            });
    }

    private function dayOptions(): array
    {
        return [
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday',
        ];
    }

    private function daySortSql(): string
    {
        return "case day_of_week
            when 'monday' then 1
            when 'tuesday' then 2
            when 'wednesday' then 3
            when 'thursday' then 4
            when 'friday' then 5
            when 'saturday' then 6
            when 'sunday' then 7
            else 8
        end";
    }
}
