<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\FinanceBuildingRent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RentController extends Controller
{
    public function index(Request $request): View
    {
        return view('finance.rent.index', [
            'campuses' => Campus::query()->orderBy('name')->get(),
            'rents' => FinanceBuildingRent::query()->with('campus')->latest('id')->paginate(20),
            'isAdmin' => $this->isAdmin($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRent($request);
        $payload = $this->buildRentPayload($validated);

        FinanceBuildingRent::query()
            ->where('campus_id', $validated['campus_id'])
            ->where('is_active', true)
            ->update(['is_active' => false]);

        FinanceBuildingRent::create($payload + [
            'is_active' => true,
            'created_by' => $request->user()?->id,
        ]);

        return back()->with('status', 'Building rent saved successfully.');
    }

    public function update(Request $request, FinanceBuildingRent $rent): RedirectResponse
    {
        $this->ensureAdmin($request);

        $validated = $this->validateRent($request, true);
        $payload = $this->buildRentPayload($validated) + [
            'is_active' => (bool) $validated['is_active'],
        ];

        if ($payload['is_active']) {
            FinanceBuildingRent::query()
                ->where('campus_id', $validated['campus_id'])
                ->where('is_active', true)
                ->where('id', '!=', $rent->id)
                ->update(['is_active' => false]);
        }

        $rent->update($payload);

        return back()->with('status', 'Building rent updated successfully.');
    }

    private function validateRent(Request $request, bool $includeStatus = false): array
    {
        $rules = [
            'campus_id' => ['required', 'exists:campuses,id'],
            'agreement_date' => ['required', 'date'],
            'address' => ['required', 'string', 'max:255'],
            'rent_amount' => ['required', 'numeric', 'min:0'],
            'increment_percentage' => ['nullable', 'numeric', 'min:0'],
            'advance_payment' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ];

        if ($includeStatus) {
            $rules['is_active'] = ['required', 'boolean'];
        }

        return $request->validate($rules);
    }

    private function buildRentPayload(array $validated): array
    {
        $rentAmount = (float) $validated['rent_amount'];
        $incrementPercentage = (float) ($validated['increment_percentage'] ?? 0);
        $currentAmount = $rentAmount + (($rentAmount * $incrementPercentage) / 100);

        return [
            'campus_id' => $validated['campus_id'],
            'agreement_date' => $validated['agreement_date'],
            'address' => $validated['address'],
            'rent_amount' => $rentAmount,
            'increment_percentage' => $incrementPercentage,
            'current_amount' => $currentAmount,
            'advance_payment' => $validated['advance_payment'] ?? 0,
            'remarks' => $validated['remarks'] ?? null,
        ];
    }

    private function ensureAdmin(Request $request): void
    {
        if (!$this->isAdmin($request)) {
            abort(403, 'Only admin can edit building rent records.');
        }
    }

    private function isAdmin(Request $request): bool
    {
        $user = $request->user();
        if (!$user) {
            return false;
        }

        return $user->roles()->whereIn('slug', ['owner', 'admin'])->exists();
    }
}
