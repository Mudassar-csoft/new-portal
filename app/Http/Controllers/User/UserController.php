<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\User;
use App\Models\User\Permission;
use App\Models\User\Role;
use App\Support\PermissionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    private const EMAIL_DOMAIN = 'career.edu.pk';

    public function index(Request $request)
    {
        $this->ensureAdminAccess();

        if ($request->ajax()) {
            $scope = (string) $request->query('scope', 'active');
            $currentUser = $request->user();

            $query = User::withoutGlobalScope('not_deleted')
                ->with(['campus', 'roles'])
                ->select('users.*');

            if ($scope === 'deleted') {
                $query->whereNotNull('at_deleted');
            } else {
                $query->whereNull('at_deleted');
            }

            // Campus scoping: non-admin users see only their own campus
            $currentRoles = $currentUser->roles->pluck('slug')->all();
            $isAdmin = (bool) array_intersect(['owner', 'admin'], $currentRoles);
            if (!$isAdmin && $currentUser->campus_id) {
                $query->where('campus_id', $currentUser->campus_id);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('name', fn (User $user) => sprintf(
                    '<a href="%s" class="table-name-link">%s</a>',
                    e(route('users.edit', $user)),
                    e($user->name)
                ))
                ->editColumn('email', fn (User $user) => e($user->email))
                ->addColumn('role', function (User $user) {
                    $roles = $user->roles->pluck('name');

                    if ($roles->isEmpty()) {
                        return '<span class="text-muted">N/A</span>';
                    }

                    return $roles->map(fn ($n) => e($n))->implode(', ');
                })
                ->addColumn('status', function (User $user) {
                    if ($user->at_deleted) {
                        return '<span class="label label-danger">Deleted</span>';
                    }
                    return '<span class="label label-success">Active</span>';
                })
                ->addColumn('campus_code', fn (User $user) => e(data_get($user, 'campus.code') ?: 'All Campuses'))
                ->addColumn('date', fn (User $user) => optional($user->created_at)->format('d-M-Y') ?? 'N/A')
                ->addColumn('actions', fn (User $user) => view('user.partials.action', ['user' => $user])->render())
                ->filterColumn('role', function ($query, $keyword) {
                    $query->whereHas('roles', function ($roleQuery) use ($keyword) {
                        $roleQuery->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('campus_code', function ($query, $keyword) {
                    $query->whereHas('campus', function ($campusQuery) use ($keyword) {
                        $campusQuery->where('code', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['name', 'role', 'status', 'actions'])
                ->make(true);
        }

        $scope = (string) $request->query('scope', 'active');

        return view('user.index', [
            'activeScope' => in_array($scope, ['active', 'deleted'], true) ? $scope : 'active',
        ]);
    }

    public function create(): View
    {
        $this->ensureAdminAccess();

        $campuses = Campus::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('user.create', [
            'campuses' => $campuses,
            'roles' => $roles,
            'emailDomain' => self::EMAIL_DOMAIN,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdminAccess();

        $validated = $request->validate([
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'name' => ['required', 'string', 'max:255'],
            'email_local' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9._%+-]+$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'roles' => ['sometimes', 'array', 'max:1'],
            'roles.*' => ['nullable', 'exists:roles,id'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $email = $this->buildInstitutionEmail($validated['email_local']);
        if ($this->emailExists($email)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['email_local' => 'This email address is already taken.']);
        }

        $roleIds = $this->singleRoleIds($validated);

        try {
            $user = User::create([
                'campus_id' => $validated['campus_id'] ?? null,
                'name' => $validated['name'],
                'email' => $email,
                'password' => Hash::make($validated['password']),
            ]);

            if ($roleIds !== []) {
                $user->roles()->sync(
                    collect($roleIds)->mapWithKeys(fn ($id) => [
                        $id => ['assigned_by' => optional($request->user())->id],
                    ])
                );
            }

            $this->syncUserPermissions($user, $roleIds, $validated['permissions'] ?? []);

            return redirect()->route('users.index')
                ->with('status', 'User created.');
        } catch (Throwable $e) {
            report($e);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Unable to save the user right now. Please try again.');
        }
    }

    public function edit(User $user): View
    {
        $this->ensureAdminAccess();

        $campuses = Campus::orderBy('name')->get();
        $roles = Role::with('permissions:id')->orderBy('name')->get();
        $permissionGroups = PermissionCatalog::grouped();
        $user->load(['roles', 'permissions']);

        if ($user->roles->pluck('slug')->intersect(['owner', 'admin'])->isNotEmpty()) {
            $user->setRelation('permissions', Permission::query()->orderBy('resource')->orderBy('action')->get());
        }

        return view('user.edit', [
            'user' => $user,
            'campuses' => $campuses,
            'roles' => $roles,
            'permissionGroups' => $permissionGroups,
            'emailDomain' => $this->extractEmailDomain($user->email),
            'emailLocal' => $this->extractEmailLocalPart($user->email),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureAdminAccess();

        $validated = $request->validate([
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'name' => ['required', 'string', 'max:255'],
            'email_local' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9._%+-]+$/'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'roles' => ['sometimes', 'array', 'max:1'],
            'roles.*' => ['nullable', 'exists:roles,id'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $email = $this->buildInstitutionEmail($validated['email_local'], $this->extractEmailDomain($user->email));
        if ($this->emailExists($email, $user->id)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['email_local' => 'This email address is already taken.']);
        }

        $roleIds = $this->singleRoleIds($validated);

        // Prevent demoting the last admin
        if ($this->wouldBeLastAdminDemotion($user, $roleIds)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['roles' => 'You cannot remove the admin role from the only remaining admin user.']);
        }

        $user->fill([
            'campus_id' => $validated['campus_id'] ?? null,
            'name' => $validated['name'],
            'email' => $email,
        ]);

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $user->roles()->sync(
            collect($roleIds)->mapWithKeys(fn ($id) => [
                $id => ['assigned_by' => optional($request->user())->id],
            ])
        );
        $this->syncUserPermissions($user, $roleIds, $validated['permissions'] ?? []);

        return redirect()->route('users.index')->with('status', 'User updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->ensureAdminAccess();

        if ($user->id === optional($request->user())->id) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        if ($this->isLastAdmin($user)) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete the last admin user. Promote another user first.');
        }

        $user->update(['at_deleted' => now()]);

        return redirect()->route('users.index')->with('status', 'User deleted.');
    }

    public function restore(int $id): RedirectResponse
    {
        $this->ensureAdminAccess();

        $user = User::withoutGlobalScope('not_deleted')->findOrFail($id);
        $user->update(['at_deleted' => null]);

        return redirect()->route('users.index')->with('status', 'User restored.');
    }

    private function isLastAdmin(User $user): bool
    {
        $userIsAdmin = $user->roles->whereIn('slug', ['owner', 'admin'])->isNotEmpty();
        if (!$userIsAdmin) {
            return false;
        }

        $remainingAdmins = User::whereHas('roles', fn ($q) => $q->whereIn('slug', ['owner', 'admin']))
            ->where('id', '!=', $user->id)
            ->count();

        return $remainingAdmins === 0;
    }

    private function wouldBeLastAdminDemotion(User $user, array $newRoleIds): bool
    {
        if (!$this->isLastAdmin($user)) {
            return false;
        }

        $newRoleSlugs = Role::whereIn('id', $newRoleIds)->pluck('slug')->all();

        return !array_intersect(['owner', 'admin'], $newRoleSlugs);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<int, int>
     */
    private function singleRoleIds(array $validated): array
    {
        if (!empty($validated['role_id'])) {
            return [(int) $validated['role_id']];
        }

        return collect($validated['roles'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->take(1)
            ->values()
            ->all();
    }

    /**
     * @param  array<int, int|string>  $roleIds
     * @param  array<int, int|string>  $permissionIds
     */
    private function syncUserPermissions(User $user, array $roleIds, array $permissionIds): void
    {
        $roleSlugs = Role::query()
            ->whereIn('id', $roleIds)
            ->pluck('slug');

        if ($roleSlugs->intersect(['owner', 'admin'])->isNotEmpty()) {
            $user->permissions()->sync(Permission::query()->pluck('id')->all());

            return;
        }

        $user->permissions()->sync($permissionIds);
    }

    private function ensureAdminAccess(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    private function buildInstitutionEmail(string $emailLocal, ?string $domain = null): string
    {
        return $this->extractEmailLocalPart(trim($emailLocal)) . '@' . ($domain ?: self::EMAIL_DOMAIN);
    }

    private function extractEmailLocalPart(string $email): string
    {
        return explode('@', trim($email), 2)[0];
    }

    private function extractEmailDomain(string $email): string
    {
        return explode('@', trim($email), 2)[1] ?? self::EMAIL_DOMAIN;
    }

    private function emailExists(string $email, ?int $ignoreUserId = null): bool
    {
        return User::withoutGlobalScope('not_deleted')
            ->where('email', $email)
            ->when($ignoreUserId !== null, fn ($query) => $query->where('id', '!=', $ignoreUserId))
            ->exists();
    }

}
