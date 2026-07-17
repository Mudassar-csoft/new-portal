<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Models\User\Role;
use App\Support\PermissionCatalog;
use Illuminate\Database\Eloquent\Builder;
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
        $this->ensureAdminAccess();

        if ($request->ajax()) {
            $scope = (string) $request->query('scope', 'active');

            $query = Role::withoutGlobalScope('not_deleted')
                ->with('permissions')
                ->select('roles.*');

            if ($scope === 'deleted') {
                $query->whereNotNull('at_deleted');
            } else {
                $query->whereNull('at_deleted');
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('name', fn (Role $role) => sprintf(
                    '<a href="%s" class="table-name-link">%s</a>',
                    e(route('roles.edit', $role)),
                    e($role->name)
                ))
                ->editColumn('slug', fn (Role $role) => e($role->slug))
                ->addColumn('permissions', fn (Role $role) => $role->permissions->count())
                ->addColumn('is_system', fn (Role $role) => $role->is_system
                    ? '<span class="label label-warning">System</span>'
                    : '<span class="label label-default">Custom</span>')
                ->addColumn('date', fn (Role $role) => optional($role->created_at)->format('d-M-Y') ?? 'N/A')
                ->addColumn('actions', fn (Role $role) => view('role.partials.action', ['role' => $role])->render())
                ->filter(function (Builder $query) use ($request): void {
                    $keyword = trim((string) data_get($request->input('search', []), 'value', ''));

                    if ($keyword === '') {
                        return;
                    }

                    $like = $this->toSqlLikePattern($keyword);
                    $normalized = strtolower($keyword);

                    $query->where(function (Builder $searchQuery) use ($like, $normalized): void {
                        $searchQuery
                            ->where('name', 'like', $like)
                            ->orWhere('slug', 'like', $like)
                            ->orWhere('description', 'like', $like)
                            ->orWhereHas('permissions', function (Builder $permissionQuery) use ($like): void {
                                $permissionQuery
                                    ->where('resource', 'like', $like)
                                    ->orWhere('action', 'like', $like)
                                    ->orWhere('slug', 'like', $like);
                            });

                        if (str_contains($normalized, 'system')) {
                            $searchQuery->orWhere('is_system', true);
                        }

                        if (str_contains($normalized, 'custom')) {
                            $searchQuery->orWhere('is_system', false);
                        }
                    });
                })
                ->rawColumns(['name', 'is_system', 'actions'])
                ->make(true);
        }

        $scope = (string) $request->query('scope', 'active');

        return view('role.index', [
            'activeScope' => in_array($scope, ['active', 'deleted'], true) ? $scope : 'active',
        ]);
    }

    private function toSqlLikePattern(string $value): string
    {
        return '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $value) . '%';
    }

    public function create(): View
    {
        $this->ensureAdminAccess();

        $permissionGroups = PermissionCatalog::grouped();

        return view('role.create', compact('permissionGroups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdminAccess();

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
        $this->ensureAdminAccess();

        $permissionGroups = PermissionCatalog::grouped();
        $role->load('permissions');

        return view('role.edit', compact('role', 'permissionGroups'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->ensureAdminAccess();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        // Slug is preserved across updates so seeded role identifiers stay stable.
        // System roles also can't be renamed.
        $updates = [
            'description' => $validated['description'] ?? null,
        ];

        if (!$role->is_system) {
            $updates['name'] = $validated['name'];
        }

        $role->update($updates);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('roles.index')->with('status', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->ensureAdminAccess();

        if ($role->is_system) {
            return redirect()->route('roles.index')
                ->with('error', 'System roles cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return redirect()->route('roles.index')
                ->with('error', 'Cannot delete a role that is still assigned to users.');
        }

        $role->update(['at_deleted' => now()]);

        return redirect()->route('roles.index')->with('status', 'Role deleted.');
    }

    public function restore(int $id): RedirectResponse
    {
        $this->ensureAdminAccess();

        $role = Role::withoutGlobalScope('not_deleted')->findOrFail($id);
        $role->update(['at_deleted' => null]);

        return redirect()->route('roles.index')->with('status', 'Role restored.');
    }

    private function ensureAdminAccess(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }
}
