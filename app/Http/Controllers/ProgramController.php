<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Program;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProgramController extends Controller
{
    public function index(Request $request): View
    {
        $scope = (string) $request->query('scope', 'all');

        $programs = Program::query()
            ->with(['campusDiscounts.campus'])
            ->withCount([
                'batches',
                'admissions',
                'campusDiscounts as discounts_count',
                'campusDiscounts as active_discounts_count' => fn (Builder $builder) => $builder->where('status', 'active'),
            ]);

        $this->applyScope($programs, $scope);
        $this->applyFilters($programs, $request);

        $programs = $programs
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderByRaw('COALESCE(title, name)')
            ->paginate(15)
            ->withQueryString();

        return view('program.index', [
            'programs' => $programs,
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'code', 'name', 'city']),
            'typeOptions' => $this->typeOptions(),
            'filters' => [
                'scope' => $scope,
                'program_type' => $request->input('program_type'),
                'status' => $request->input('status'),
                'campus_id' => $request->integer('campus_id') ?: null,
                'search' => $request->input('search'),
            ],
            'scopeCards' => $this->buildScopeCards($request),
            'pageTitle' => $this->resolvePageTitle($scope),
            'pageDescription' => $this->resolvePageDescription($scope),
        ]);
    }

    public function create(): View
    {
        return view('program.create', $this->formPayload(new Program()));
    }

    public function edit(Program $program): View
    {
        return view('program.edit', $this->formPayload($program));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProgram($request);
        $program = Program::query()->create($this->extractProgramData($request, $validated));

        $this->syncDiscounts($program, $this->normalizeDiscounts($request));

        return redirect()->route('program.index')->with('status', 'Programme created successfully.');
    }

    public function update(Request $request, Program $program): RedirectResponse
    {
        $validated = $this->validateProgram($request, $program);
        $program->update($this->extractProgramData($request, $validated, $program));

        $this->syncDiscounts($program, $this->normalizeDiscounts($request));

        return redirect()->route('program.index')->with('status', 'Programme updated successfully.');
    }

    public function outline(Program $program): StreamedResponse
    {
        abort_unless(
            $program->outline_path && Storage::disk('public')->exists($program->outline_path),
            404
        );

        return Storage::disk('public')->download($program->outline_path);
    }

    private function formPayload(Program $program): array
    {
        $program->loadMissing('campusDiscounts.campus');

        $discountRows = $program->exists
            ? $program->campusDiscounts
                ->sortBy(function ($discount) {
                    return [
                        $discount->campus_id === null ? 0 : 1,
                        $discount->campus?->name ?? '',
                    ];
                })
                ->map(function ($discount) {
                    return [
                        'campus_id' => $discount->campus_id === null ? 'all' : (string) $discount->campus_id,
                        'discount_percent' => $discount->discount_percent,
                        'status' => $discount->status ?: 'active',
                    ];
                })
                ->values()
                ->all()
            : [];

        if ($discountRows === []) {
            $discountRows = [[
                'campus_id' => '',
                'discount_percent' => null,
                'status' => 'active',
            ]];
        }

        return [
            'program' => $program,
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'code', 'name', 'city']),
            'typeOptions' => $this->typeOptions(),
            'statusOptions' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
            ],
            'discountRows' => $discountRows,
        ];
    }

    private function validateProgram(Request $request, ?Program $program = null): array
    {
        return $request->validate([
            'program_type' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('programs', 'code')->ignore($program?->id)],
            'fee' => ['required', 'numeric', 'min:0'],
            'duration_weeks' => ['required', 'integer', 'min:1'],
            'discount_limit' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'installments' => ['required', 'integer', 'min:1', 'max:36'],
            'outline' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'remove_outline' => ['nullable', 'boolean'],
            'prerequisite' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'discounts' => ['nullable', 'array'],
            'discounts.*.campus_id' => ['nullable'],
            'discounts.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discounts.*.status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);
    }

    private function extractProgramData(Request $request, array $validated, ?Program $program = null): array
    {
        $outlinePath = $program?->outline_path;

        if ($request->boolean('remove_outline') && $outlinePath) {
            Storage::disk('public')->delete($outlinePath);
            $outlinePath = null;
        }

        if ($request->hasFile('outline')) {
            if ($outlinePath) {
                Storage::disk('public')->delete($outlinePath);
            }

            $outlinePath = $request->file('outline')->store('programs/outlines', 'public');
        }

        $title = trim((string) $validated['title']);

        return [
            'program_type' => trim((string) $validated['program_type']),
            'title' => $title,
            'name' => $title,
            'code' => Str::upper(trim((string) $validated['code'])),
            'fee' => $validated['fee'],
            'duration_weeks' => $validated['duration_weeks'],
            'discount_limit' => $validated['discount_limit'] ?? null,
            'installments' => $validated['installments'],
            'outline_path' => $outlinePath,
            'prerequisite' => $validated['prerequisite'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
            'status' => $validated['status'],
        ];
    }

    private function normalizeDiscounts(Request $request): array
    {
        $campusLookup = Campus::query()
            ->pluck('id')
            ->mapWithKeys(fn ($id) => [(string) $id => true]);

        $rows = collect($request->input('discounts', []))
            ->map(function ($row) {
                return [
                    'campus_id' => isset($row['campus_id']) ? trim((string) $row['campus_id']) : '',
                    'discount_percent' => ($row['discount_percent'] ?? '') !== '' ? (float) $row['discount_percent'] : null,
                    'status' => isset($row['status']) ? trim((string) $row['status']) : '',
                ];
            })
            ->filter(fn (array $row) => $row['campus_id'] !== '' || $row['discount_percent'] !== null)
            ->values();

        $normalized = [];
        $seenScopes = [];

        foreach ($rows as $index => $row) {
            $line = $index + 1;
            $campusValue = $row['campus_id'];
            $status = $row['status'] !== '' ? $row['status'] : 'active';

            if ($campusValue === '') {
                throw ValidationException::withMessages([
                    "discounts.$index.campus_id" => "Select a campus scope for discount row {$line}.",
                ]);
            }

            if ($row['discount_percent'] === null) {
                throw ValidationException::withMessages([
                    "discounts.$index.discount_percent" => "Enter a discount percent for row {$line}.",
                ]);
            }

            $scopeKey = $campusValue === 'all' ? 'all' : (string) (int) $campusValue;

            if ($campusValue !== 'all' && !$campusLookup->has($scopeKey)) {
                throw ValidationException::withMessages([
                    "discounts.$index.campus_id" => "Selected campus is invalid for discount row {$line}.",
                ]);
            }

            if (isset($seenScopes[$scopeKey])) {
                throw ValidationException::withMessages([
                    "discounts.$index.campus_id" => "Discount row {$line} duplicates an existing campus selection.",
                ]);
            }

            $seenScopes[$scopeKey] = true;
            $normalized[] = [
                'campus_id' => $scopeKey === 'all' ? null : (int) $scopeKey,
                'discount_percent' => round($row['discount_percent'], 2),
                'status' => $status,
            ];
        }

        return $normalized;
    }

    private function syncDiscounts(Program $program, array $discounts): void
    {
        $program->loadMissing('campusDiscounts');
        $retainKeys = [];

        foreach ($discounts as $discount) {
            $retainKeys[] = $discount['campus_id'] === null ? 'all' : (string) $discount['campus_id'];

            $program->campusDiscounts()->updateOrCreate(
                ['campus_id' => $discount['campus_id']],
                [
                    'discount_percent' => $discount['discount_percent'],
                    'status' => $discount['status'],
                ]
            );
        }

        $program->campusDiscounts
            ->filter(function ($discount) use ($retainKeys) {
                $key = $discount->campus_id === null ? 'all' : (string) $discount->campus_id;

                return !in_array($key, $retainKeys, true);
            })
            ->each
            ->delete();
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        $query
            ->when($request->filled('program_type'), fn (Builder $builder) => $builder->where('program_type', $request->input('program_type')))
            ->when($request->filled('status'), fn (Builder $builder) => $builder->where('status', $request->input('status')))
            ->when($request->integer('campus_id'), function (Builder $builder) use ($request) {
                $campusId = $request->integer('campus_id');

                $builder->whereHas('campusDiscounts', function (Builder $discounts) use ($campusId) {
                    $discounts->where(function (Builder $campusScope) use ($campusId) {
                        $campusScope
                            ->whereNull('campus_id')
                            ->orWhere('campus_id', $campusId);
                    });
                });
            })
            ->when($request->filled('search'), function (Builder $builder) use ($request) {
                $keyword = trim((string) $request->input('search'));

                $builder->where(function (Builder $inner) use ($keyword) {
                    $inner
                        ->where('title', 'like', '%' . $keyword . '%')
                        ->orWhere('name', 'like', '%' . $keyword . '%')
                        ->orWhere('code', 'like', '%' . $keyword . '%')
                        ->orWhere('program_type', 'like', '%' . $keyword . '%')
                        ->orWhere('prerequisite', 'like', '%' . $keyword . '%')
                        ->orWhere('remarks', 'like', '%' . $keyword . '%')
                        ->orWhereHas('campusDiscounts', function (Builder $discounts) use ($keyword) {
                            $discounts->whereHas('campus', function (Builder $campuses) use ($keyword) {
                                $campuses
                                    ->where('name', 'like', '%' . $keyword . '%')
                                    ->orWhere('code', 'like', '%' . $keyword . '%')
                                    ->orWhere('city', 'like', '%' . $keyword . '%');
                            });
                        });
                });
            });
    }

    private function applyScope(Builder $query, string $scope): void
    {
        switch ($scope) {
            case 'ongoing':
                $query->where('status', 'active');
                break;

            case 'suspended':
                $query->where('status', 'inactive');
                break;

            case 'discounted':
                $query->whereHas('campusDiscounts', fn (Builder $builder) => $builder->where('status', 'active'));
                break;
        }
    }

    private function buildScopeCards(Request $request): array
    {
        $scopes = [
            'all' => 'All Programmes',
            'ongoing' => 'Ongoing',
            'suspended' => 'Suspended',
            'discounted' => 'Discounted',
        ];

        $cards = [];

        foreach ($scopes as $scope => $label) {
            $query = Program::query();
            $this->applyFilters($query, $request);

            if ($scope !== 'all') {
                $this->applyScope($query, $scope);
            }

            $cards[] = [
                'scope' => $scope,
                'label' => $label,
                'count' => $query->count(),
            ];
        }

        return $cards;
    }

    private function resolvePageTitle(string $scope): string
    {
        return match ($scope) {
            'ongoing' => 'Ongoing Programmes',
            'suspended' => 'Suspended Programmes',
            'discounted' => 'Discounted Programmes',
            default => 'Programmes',
        };
    }

    private function resolvePageDescription(string $scope): string
    {
        return match ($scope) {
            'ongoing' => 'Programmes currently open and active in the system.',
            'suspended' => 'Programmes currently marked inactive.',
            'discounted' => 'Programmes with at least one active campus or global discount.',
            default => 'Manage programme setup, campus discounts, pricing, and linked batch activity.',
        };
    }

    private function typeOptions(): array
    {
        return collect([
            'certificate',
            'diploma',
            'advanced diploma',
            'bootcamp',
            'workshop',
            'short course',
        ])
            ->merge(
                Program::query()
                    ->whereNotNull('program_type')
                    ->orderBy('program_type')
                    ->pluck('program_type')
            )
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
