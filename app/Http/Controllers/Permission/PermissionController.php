<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Models\User\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Throwable;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $scope = (string) $request->query('scope', 'active');

            $query = Permission::withoutGlobalScope('not_deleted')
                ->select('permissions.*');

            if ($scope === 'deleted') {
                $query->whereNotNull('at_deleted');
            } else {
                $query->whereNull('at_deleted');
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('resource', fn (Permission $permission) => e($permission->resource))
                ->editColumn('action', fn (Permission $permission) => e($permission->action))
                ->editColumn('slug', fn (Permission $permission) => e($permission->slug))
                ->addColumn('date', fn (Permission $permission) => optional($permission->created_at)->format('d-M-Y') ?? 'N/A')
                ->addColumn('actions', fn (Permission $permission) => view('permission.partials.action', ['permission' => $permission])->render())
                ->rawColumns(['actions'])
                ->make(true);
        }

        $scope = (string) $request->query('scope', 'active');

        return view('permission.index', [
            'activeScope' => in_array($scope, ['active', 'deleted'], true) ? $scope : 'active',
        ]);
    }

    public function create(): View
    {
        return view('permission.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'resource' => ['required', 'string', 'max:255'],
            'action' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions')
                    ->where(fn ($q) => $q->where('resource', $request->resource)->whereNull('at_deleted')),
            ],
            'description' => ['nullable', 'string'],
        ]);

        try {
            $slug = "{$validated['resource']}.{$validated['action']}";

            Permission::create([
                'resource' => $validated['resource'],
                'action' => $validated['action'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
            ]);

            return redirect()->route('permissions.index')->with('status', 'Permission created.');
        } catch (Throwable $e) {
            report($e);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Unable to save the permission right now. Please try again.');
        }
    }

    public function edit(Permission $permission): View
    {
        return view('permission.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $validated = $request->validate([
            'resource' => ['required', 'string', 'max:255'],
            'action' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions')
                    ->where(fn ($q) => $q->where('resource', $request->resource)->whereNull('at_deleted'))
                    ->ignore($permission->id),
            ],
            'description' => ['nullable', 'string'],
        ]);

        $slug = "{$validated['resource']}.{$validated['action']}";

        $permission->update([
            'resource' => $validated['resource'],
            'action' => $validated['action'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('permissions.index')->with('status', 'Permission updated.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        if ($permission->roles()->exists()) {
            return redirect()->route('permissions.index')
                ->with('error', 'Cannot delete a permission that is still assigned to roles.');
        }

        $permission->update(['at_deleted' => now()]);

        return redirect()->route('permissions.index')->with('status', 'Permission deleted.');
    }

    public function restore(int $id): RedirectResponse
    {
        $permission = Permission::withoutGlobalScope('not_deleted')->findOrFail($id);
        $permission->update(['at_deleted' => null]);

        return redirect()->route('permissions.index')->with('status', 'Permission restored.');
    }
}
