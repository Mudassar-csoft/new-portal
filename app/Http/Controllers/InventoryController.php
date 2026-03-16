<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\InventoryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'campus_id' => $request->integer('campus_id') ?: null,
            'category' => $request->input('category') ?: null,
            'search' => trim((string) $request->input('search')),
        ];

        $query = InventoryItem::query()
            ->with(['campus:id,code,name,campus_type', 'creator:id,name'])
            ->when($filters['campus_id'], fn ($q, $campusId) => $q->where('campus_id', $campusId))
            ->when($filters['category'], fn ($q, $category) => $q->where('category', $category))
            ->when($filters['search'] !== '', function ($q) use ($filters) {
                $search = $filters['search'];

                $q->where(function ($inner) use ($search): void {
                    $inner->where('item_code', 'like', "%{$search}%")
                        ->orWhere('item_name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model_no', 'like', "%{$search}%")
                        ->orWhere('serial_no', 'like', "%{$search}%")
                        ->orWhere('room_location', 'like', "%{$search}%");
                });
            })
            ->latest('id');

        $summaryBase = clone $query;

        return view('inventory.index', [
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'code', 'name', 'campus_type']),
            'items' => $query->paginate(20)->withQueryString(),
            'filters' => $filters,
            'categories' => InventoryItem::categories(),
            'categoryLabels' => InventoryItem::categoryLabels(),
            'isAdmin' => $this->isAdmin($request),
            'summary' => [
                'records' => (clone $summaryBase)->count(),
                'total_quantity' => (int) (clone $summaryBase)->sum('quantity'),
                'low_stock' => (clone $summaryBase)->whereColumn('quantity', '<=', 'minimum_stock')->count(),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $selectedCampusId = $request->integer('campus_id') ?: null;

        return view('inventory.create', [
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'code', 'name', 'campus_type']),
            'categories' => InventoryItem::categories(),
            'categoryLabels' => InventoryItem::categoryLabels(),
            'units' => InventoryItem::units(),
            'conditions' => InventoryItem::conditionStatuses(),
            'selectedCampusId' => $selectedCampusId,
            'inventoryItem' => null,
            'isAdmin' => $this->isAdmin($request),
            'recentItems' => InventoryItem::query()
                ->with('campus:id,code,name')
                ->when($selectedCampusId, fn ($q, $campusId) => $q->where('campus_id', $campusId))
                ->latest('id')
                ->limit(12)
                ->get(),
        ]);
    }

    public function edit(Request $request, InventoryItem $inventoryItem): View
    {
        $this->ensureAdmin($request);

        return view('inventory.create', [
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'code', 'name', 'campus_type']),
            'categories' => InventoryItem::categories(),
            'categoryLabels' => InventoryItem::categoryLabels(),
            'units' => InventoryItem::units(),
            'conditions' => InventoryItem::conditionStatuses(),
            'selectedCampusId' => $inventoryItem->campus_id,
            'inventoryItem' => $inventoryItem,
            'isAdmin' => true,
            'recentItems' => InventoryItem::query()
                ->with('campus:id,code,name')
                ->where('campus_id', $inventoryItem->campus_id)
                ->latest('id')
                ->limit(12)
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
            'category' => ['required', 'in:' . implode(',', array_keys(InventoryItem::categories()))],
            'item_name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model_no' => ['nullable', 'string', 'max:255'],
            'serial_no' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit' => ['required', 'in:' . implode(',', array_keys(InventoryItem::units()))],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'condition_status' => ['required', 'in:' . implode(',', array_keys(InventoryItem::conditionStatuses()))],
            'room_location' => ['nullable', 'string', 'max:255'],
            'purchase_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
        ]);

        $campus = Campus::query()->findOrFail($validated['campus_id']);

        InventoryItem::query()->create([
            'campus_id' => $validated['campus_id'],
            'item_code' => $this->generateItemCode($campus),
            'category' => $validated['category'],
            'item_name' => $validated['item_name'],
            'brand' => $validated['brand'] ?? null,
            'model_no' => $validated['model_no'] ?? null,
            'serial_no' => $validated['serial_no'] ?? null,
            'quantity' => $validated['quantity'],
            'unit' => $validated['unit'],
            'minimum_stock' => $validated['minimum_stock'] ?? 0,
            'condition_status' => $validated['condition_status'],
            'room_location' => $validated['room_location'] ?? null,
            'purchase_date' => $validated['purchase_date'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
            'created_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('inventory.index', ['campus_id' => $validated['campus_id']])
            ->with('status', 'Inventory item added for ' . $campus->name . '.');
    }

    public function update(Request $request, InventoryItem $inventoryItem): RedirectResponse
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
            'category' => ['required', 'in:' . implode(',', array_keys(InventoryItem::categories()))],
            'item_name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model_no' => ['nullable', 'string', 'max:255'],
            'serial_no' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit' => ['required', 'in:' . implode(',', array_keys(InventoryItem::units()))],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'condition_status' => ['required', 'in:' . implode(',', array_keys(InventoryItem::conditionStatuses()))],
            'room_location' => ['nullable', 'string', 'max:255'],
            'purchase_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
        ]);

        $inventoryItem->update([
            'campus_id' => $validated['campus_id'],
            'category' => $validated['category'],
            'item_name' => $validated['item_name'],
            'brand' => $validated['brand'] ?? null,
            'model_no' => $validated['model_no'] ?? null,
            'serial_no' => $validated['serial_no'] ?? null,
            'quantity' => $validated['quantity'],
            'unit' => $validated['unit'],
            'minimum_stock' => $validated['minimum_stock'] ?? 0,
            'condition_status' => $validated['condition_status'],
            'room_location' => $validated['room_location'] ?? null,
            'purchase_date' => $validated['purchase_date'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return redirect()
            ->route('inventory.index', ['campus_id' => $validated['campus_id']])
            ->with('status', 'Inventory item updated successfully.');
    }

    private function generateItemCode(Campus $campus): string
    {
        do {
            $code = strtoupper($campus->code ?: 'GEN')
                . '-INV-'
                . now()->format('ymdHis')
                . '-'
                . strtoupper(Str::random(4));
        } while (InventoryItem::query()->where('item_code', $code)->exists());

        return $code;
    }

    private function ensureAdmin(Request $request): void
    {
        if (!$this->isAdmin($request)) {
            abort(403, 'Only admin can edit inventory records.');
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
