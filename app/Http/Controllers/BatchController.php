<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Campus;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BatchController extends Controller
{
    public function create(): View
    {
        $campuses = Campus::orderBy('name')->get();
        $programs = Program::orderBy('title')->get();

        return view('batch.create', compact('campuses', 'programs'));
    }

    public function index(): View
    {
        $batches = Batch::with(['program', 'campus'])->latest()->paginate(15);

        return view('batch.index', compact('batches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
            'program_id' => ['required', 'exists:programs,id'],
            'instructor' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'session' => ['required', Rule::in(['morning', 'evening', 'weekend'])],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'lab' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        $program = Program::findOrFail($validated['program_id']);
        $code = $this->generateBatchCode($program->code, $validated['start_date']);

        // Prevent duplicate code per campus
        $exists = Batch::where('campus_id', $validated['campus_id'])
            ->where('code', $code)
            ->exists();

        if ($exists) {
            return Redirect::back()
                ->withInput()
                ->withErrors(['code' => 'This month batch already exists for the selected campus.']);
        }

        Batch::create([
            'campus_id' => $validated['campus_id'],
            'program_id' => $validated['program_id'],
            'name' => $code,
            'code' => $code,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'session' => $validated['session'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'instructor' => $validated['instructor'],
            'lab' => $validated['lab'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
            'status' => 'active',
        ]);

        return redirect()->route('batch.create')->with('status', 'Batch created.');
    }

    private function generateBatchCode(string $programCode, string $startDate): string
    {
        $dt = \Carbon\Carbon::parse($startDate);
        $month = $dt->format('m');
        $year = $dt->format('y'); // two-digit year
        return strtoupper($programCode) . $month . '-' . $year;
    }
}
