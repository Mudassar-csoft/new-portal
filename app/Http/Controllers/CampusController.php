<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CampusController extends Controller
{
    public function index(Request $request): View
    {
        $scope = (string) $request->query('scope', 'all');
        $allowedScopes = ['all', 'campuses', 'franchise', 'suspended_campuses', 'suspended_franchise'];
        if (!in_array($scope, $allowedScopes, true)) {
            $scope = 'all';
        }

        $campuses = Campus::query()
            ->withCount([
                'batches',
                'admissions',
                'inventoryItems',
                'programDiscounts',
            ])
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN campus_type = 'company' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        return view('campus.index', [
            'campuses' => $campuses,
            'activeScope' => $scope,
            'scopeCards' => $this->buildScopeCards(),
            'typeOptions' => $this->typeOptions(),
            'pageTitle' => $this->resolvePageTitle($scope),
            'pageDescription' => $this->resolvePageDescription($scope),
        ]);
    }

    public function create(): View
    {
        return view('campus.create', $this->formPayload(new Campus()));
    }

    public function edit(Campus $campus): View
    {
        return view('campus.edit', $this->formPayload($campus));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCampus($request);

        Campus::query()->create($this->extractCampusData($validated));

        return redirect()->route('campus.index')->with('status', 'Campus created successfully.');
    }

    public function update(Request $request, Campus $campus): RedirectResponse
    {
        $validated = $this->validateCampus($request, $campus);

        $campus->update($this->extractCampusData($validated, $campus));

        return redirect()->route('campus.index')->with('status', 'Campus updated successfully.');
    }

    public function countPreview(Request $request, string $abbr): JsonResponse
    {
        $normalizedAbbr = $this->normalizeCityAbbr($abbr);
        $ignoreId = $request->integer('ignore_id') ?: null;

        if ($normalizedAbbr === '') {
            return response()->json([
                'abbr' => '',
                'count' => 0,
                'next_code' => null,
            ]);
        }

        $count = Campus::query()
            ->where('city_abbr', $normalizedAbbr)
            ->when($ignoreId, fn (Builder $builder, int $id) => $builder->whereKeyNot($id))
            ->count();

        return response()->json([
            'abbr' => $normalizedAbbr,
            'count' => $count,
            'next_code' => 'CI' . $normalizedAbbr . str_pad((string) ($count + 1), 2, '0', STR_PAD_LEFT),
        ]);
    }

    private function formPayload(Campus $campus): array
    {
        return [
            'campus' => $campus,
            'typeOptions' => $this->typeOptions(),
            'statusOptions' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
            ],
        ];
    }

    private function validateCampus(Request $request, ?Campus $campus = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('campuses', 'name')->ignore($campus?->id)],
            'title' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'city_abbr' => ['required', 'string', 'max:10', 'regex:/[A-Za-z]/'],
            'country' => ['required', 'string', 'max:255'],
            'campus_email' => ['nullable', 'email', 'max:255'],
            'campus_type' => ['required', Rule::in(array_keys($this->typeOptions()))],
            'landline' => ['nullable', 'string', 'max:50'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'address' => ['required', 'string'],
            'labs_count' => ['nullable', 'integer', 'min:0'],
            'royalty_rate' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'remarks' => ['nullable', 'string'],
        ]);
    }

    private function extractCampusData(array $validated, ?Campus $campus = null): array
    {
        $name = trim((string) $validated['name']);
        $title = isset($validated['title']) ? trim((string) $validated['title']) : '';
        $cityAbbr = $this->normalizeCityAbbr($validated['city_abbr']);
        $campusType = $validated['campus_type'];

        $code = $campus && $campus->city_abbr === $cityAbbr && $campus->code
            ? $campus->code
            : $this->generateCampusCode($cityAbbr, $campus?->id);

        return [
            'name' => $name,
            'title' => $title !== '' ? $title : null,
            'slug' => $this->generateUniqueSlug($name . '-' . $cityAbbr, $campus?->id),
            'code' => $code,
            'country' => trim((string) $validated['country']),
            'city' => trim((string) $validated['city']),
            'city_abbr' => $cityAbbr,
            'campus_type' => $campusType,
            'campus_email' => $validated['campus_email'] ?? null,
            'landline' => $validated['landline'] ?? null,
            'mobile' => $validated['mobile'] ?? null,
            'address' => trim((string) $validated['address']),
            'labs_count' => $validated['labs_count'] ?? 0,
            'royalty_rate' => $campusType === 'franchise' ? ($validated['royalty_rate'] ?? null) : null,
            'status' => $validated['status'],
            'remarks' => $validated['remarks'] ?? null,
        ];
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        $query
            ->when($request->filled('campus_type'), fn (Builder $builder) => $builder->where('campus_type', $request->input('campus_type')))
            ->when($request->filled('status'), fn (Builder $builder) => $builder->where('status', $request->input('status')))
            ->when($request->filled('country'), fn (Builder $builder) => $builder->where('country', $request->input('country')))
            ->when($request->filled('city'), fn (Builder $builder) => $builder->where('city', $request->input('city')))
            ->when($request->filled('search'), function (Builder $builder) use ($request) {
                $keyword = trim((string) $request->input('search'));

                $builder->where(function (Builder $inner) use ($keyword) {
                    $inner
                        ->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('title', 'like', '%' . $keyword . '%')
                        ->orWhere('code', 'like', '%' . $keyword . '%')
                        ->orWhere('city', 'like', '%' . $keyword . '%')
                        ->orWhere('country', 'like', '%' . $keyword . '%')
                        ->orWhere('city_abbr', 'like', '%' . $keyword . '%')
                        ->orWhere('campus_email', 'like', '%' . $keyword . '%')
                        ->orWhere('landline', 'like', '%' . $keyword . '%')
                        ->orWhere('mobile', 'like', '%' . $keyword . '%')
                        ->orWhere('address', 'like', '%' . $keyword . '%')
                        ->orWhere('remarks', 'like', '%' . $keyword . '%');
                });
            });
    }

    private function applyScope(Builder $query, string $scope): void
    {
        switch ($scope) {
            case 'campuses':
                $query->where('campus_type', 'company');
                break;

            case 'franchise':
                $query->where('campus_type', 'franchise');
                break;

            case 'suspended_campuses':
                $query
                    ->where('campus_type', 'company')
                    ->where('status', 'inactive');
                break;

            case 'suspended_franchise':
                $query
                    ->where('campus_type', 'franchise')
                    ->where('status', 'inactive');
                break;
        }
    }

    private function buildScopeCards(): array
    {
        $scopes = [
            'all' => 'All Campuses / Franchise',
            'campuses' => 'All Campuses',
            'franchise' => 'All Franchise',
            'suspended_campuses' => 'Suspended Campuses',
            'suspended_franchise' => 'Suspended Franchise',
        ];

        $cards = [];

        foreach ($scopes as $scope => $label) {
            $query = Campus::query();

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
            'campuses' => 'All Campuses',
            'franchise' => 'All Franchise',
            'suspended_campuses' => 'Suspended Campuses',
            'suspended_franchise' => 'Suspended Franchise',
            default => 'All Campuses / Franchise',
        };
    }

    private function resolvePageDescription(string $scope): string
    {
        return match ($scope) {
            'campuses' => '',
            'franchise' => '',
            'suspended_campuses' => '',
            'suspended_franchise' => '',
            default => '',
        };
    }

    private function typeOptions(): array
    {
        return [
            'company' => 'Company Owned',
            'franchise' => 'Franchise',
        ];
    }

    private function normalizeCityAbbr(string $cityAbbr): string
    {
        return Str::upper(substr(preg_replace('/[^A-Z]/i', '', $cityAbbr), 0, 10));
    }

    private function generateCampusCode(string $cityAbbr, ?int $ignoreId = null): string
    {
        return DB::transaction(function () use ($cityAbbr, $ignoreId) {
            $count = Campus::query()
                ->where('city_abbr', $cityAbbr)
                ->when($ignoreId, fn (Builder $builder, int $id) => $builder->whereKeyNot($id))
                ->lockForUpdate()
                ->count();

            return 'CI' . $cityAbbr . str_pad((string) ($count + 1), 2, '0', STR_PAD_LEFT);
        });
    }

    private function generateUniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base);
        $candidate = $slug;
        $counter = 2;

        while (
            Campus::query()
                ->where('slug', $candidate)
                ->when($ignoreId, fn (Builder $builder, int $id) => $builder->whereKeyNot($id))
                ->exists()
        ) {
            $candidate = $slug . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }
}
