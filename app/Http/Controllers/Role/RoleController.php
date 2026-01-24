<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Models\User\Permission;
use App\Models\User\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Throwable;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Role::with('permissions')->select('roles.*');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('name', fn (Role $role) => e($role->name))
                ->editColumn('slug', fn (Role $role) => e($role->slug))
                ->addColumn('permissions', fn (Role $role) => $role->permissions->count())
                ->addColumn('date', fn (Role $role) => optional($role->created_at)->format('d-M-Y') ?? 'N/A')
                ->addColumn('actions', fn (Role $role) => view('role.partials.action', ['role' => $role])->render())
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('role.index');
    }

    public function create(): View
    {
        $permissions = Permission::orderBy('resource')->get()->groupBy('resource');

        return view('role.create', compact('permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('roles', 'slug')],
            'description' => ['nullable', 'string'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        try {
            $slug = $validated['slug'] ?? Str::slug($validated['name']);

            $role = Role::create([
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
            ]);

            $role->permissions()->sync($validated['permissions'] ?? []);

            return redirect()->route('roles.index')->with('status', 'Role created.');
        } catch (Throwable $e) {
            report($e);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Unable to save the role right now. Please try again.');
        }
    }

    public function edit(Role $role): View
    {
        $permissions = Permission::orderBy('resource')->get()->groupBy('resource');
        $role->load('permissions');

        return view('role.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('roles', 'slug')->ignore($role->id)],
            'description' => ['nullable', 'string'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['name']);

        $role->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('roles.index')->with('status', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $role->update(['at_deleted' => now()]);

        return redirect()->route('roles.index')->with('status', 'Role deleted.');
    }
}
