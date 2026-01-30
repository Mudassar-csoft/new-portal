<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Program;
use App\Models\ProgramCampusDiscount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function index(): View
    {
        $programs = Program::with('campusDiscounts')->latest()->paginate(15);

        return view('program.index', compact('programs'));
    }

    public function create(): View
    {
        $campuses = Campus::orderBy('name')->get();

        return view('program.create', compact('campuses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'program_type' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'unique:programs,code'],
            'fee' => ['required', 'numeric', 'min:0'],
            'duration_weeks' => ['required', 'integer', 'min:1'],
            'installments' => ['required', 'integer', 'min:1'],
            'prerequisite' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'discount_percent' => ['nullable', 'numeric', 'min:0'],
            'discount_campuses' => ['array'],
            'discount_campuses.*' => ['nullable'],
        ]);

        $program = Program::create([
            'program_type' => $validated['program_type'],
            'title' => $validated['title'],
            'name' => $validated['title'],
            'code' => $validated['code'],
            'fee' => $validated['fee'],
            'duration_weeks' => $validated['duration_weeks'],
            'installments' => $validated['installments'],
            'prerequisite' => $validated['prerequisite'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
            'status' => $validated['status'],
        ]);

        if (!empty($validated['discount_percent'])) {
            $campusSelections = $request->input('discount_campuses', []);
            $campusIds = collect($campusSelections)->filter()->all();
            $applyAll = in_array('all', $campusIds, true);

            $targets = $applyAll ? [null] : collect($campusIds)->filter(fn ($id) => $id !== 'all')->map(fn ($id) => (int) $id)->unique()->all();

            foreach ($targets as $campusId) {
                ProgramCampusDiscount::updateOrCreate(
                    ['program_id' => $program->id, 'campus_id' => $campusId],
                    ['discount_percent' => $validated['discount_percent'], 'status' => 'active']
                );
            }
        }

        return redirect()->route('program.create')->with('status', 'Program created.');
    }
}
