<?php

namespace App\Http\Controllers\Permission;

use App\Http\Controllers\Controller;
use App\Models\User\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function index(): View
    {
        $permissions = Permission::latest()->paginate(20);

        return view('permission.index', compact('permissions'));
    }

    public function create(): View
    {
        return view('permission.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'resource' => ['required', 'string', 'max:255'],
            'action' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $slug = "{$validated['resource']}.{$validated['action']}";

        Permission::create([
            'resource' => $validated['resource'],
            'action' => $validated['action'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('permissions.index')->with('status', 'Permission created.');
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
                    ->where(fn ($q) => $q->where('resource', $request->resource)->where('action', $request->action))
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
        $permission->delete();

        return redirect()->route('permissions.index')->with('status', 'Permission deleted.');
    }
}
