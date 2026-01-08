<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CampusController extends Controller
{
    public function index(): View
    {
        $campuses = Campus::latest()->paginate(15);

        return view('campus.index', compact('campuses'));
    }

    public function create(): View
    {
        return view('campus.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'city_abbr' => ['required', 'string', 'max:10'],
            'country' => ['required', 'string', 'max:255'],
            'campus_email' => ['nullable', 'email', 'max:255'],
            'campus_type' => ['required', Rule::in(['company', 'franchise'])],
            'landline' => ['nullable', 'string', 'max:50'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'address' => ['required', 'string'],
            'labs_count' => ['nullable', 'integer', 'min:0'],
            'royalty_rate' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'remarks' => ['nullable', 'string'],
        ]);

        $validated['city_abbr'] = strtoupper(preg_replace('/[^A-Z]/i', '', $validated['city_abbr']));
        $code = $this->generateCampusCode($validated['city_abbr']);

        Campus::create(array_merge($validated, [
            'slug' => str($validated['name'] . '-' . $validated['city_abbr'])->slug(),
            'code' => $code,
            'campus_type' => $validated['campus_type'],
        ]));

        return redirect()->route('campus.index')->with('status', 'Campus created.');
    }

    private function generateCampusCode(string $cityAbbr): string
    {
        $abbr = strtoupper(preg_replace('/[^A-Z]/i', '', $cityAbbr));

        return DB::transaction(function () use ($abbr) {
            $count = Campus::where('city_abbr', $abbr)->lockForUpdate()->count();
            $next = str_pad((string) ($count + 1), 2, '0', STR_PAD_LEFT);
            return 'CI' . $abbr . $next;
        });
    }
}
