<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeUserMail;
use App\Models\Campus;
use App\Models\User;
use App\Models\User\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class UserController extends Controller
{
    public function index(Request $request)
    {
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
                ->addColumn('campus_code', fn (User $user) => e(data_get($user, 'campus.code', 'N/A')))
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
        $campuses = Campus::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('user.create', compact('campuses', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        try {
            $user = User::create([
                'campus_id' => $validated['campus_id'] ?? null,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make(Str::random(40)), // random placeholder; user sets via setup link
            ]);

            if (!empty($validated['roles'])) {
                $user->roles()->sync(
                    collect($validated['roles'])->mapWithKeys(fn ($id) => [
                        $id => ['assigned_by' => optional($request->user())->id],
                    ])
                );
            }

            $this->sendWelcomeEmail($user, $request->user());

            return redirect()->route('users.index')
                ->with('status', 'User created. A setup link has been emailed.');
        } catch (Throwable $e) {
            report($e);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Unable to save the user right now. Please try again.');
        }
    }

    public function edit(User $user): View
    {
        $campuses = Campus::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        $user->load('roles');

        return view('user.edit', compact('user', 'campuses', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        // Prevent demoting the last admin
        if ($this->wouldBeLastAdminDemotion($user, $validated['roles'] ?? [])) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['roles' => 'You cannot remove the admin role from the only remaining admin user.']);
        }

        $user->fill([
            'campus_id' => $validated['campus_id'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $user->roles()->sync(
            collect($validated['roles'] ?? [])->mapWithKeys(fn ($id) => [
                $id => ['assigned_by' => optional($request->user())->id],
            ])
        );

        return redirect()->route('users.index')->with('status', 'User updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
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
        $user = User::withoutGlobalScope('not_deleted')->findOrFail($id);
        $user->update(['at_deleted' => null]);

        return redirect()->route('users.index')->with('status', 'User restored.');
    }

    private function sendWelcomeEmail(User $user, ?User $createdBy): void
    {
        try {
            $setupUrl = URL::temporarySignedRoute(
                'users.setup.show',
                now()->addHour(),
                ['user' => $user->id]
            );

            $roleList = $user->roles()->pluck('name')->implode(', ');

            Mail::send(new WelcomeUserMail(
                user: $user,
                setupUrl: $setupUrl,
                roleList: $roleList,
                assignedBy: optional($createdBy)->name,
            ));
        } catch (Throwable $e) {
            report($e);
        }
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
}
