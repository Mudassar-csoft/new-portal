<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeUserMail;
use App\Models\Campus;
use App\Models\User;
use App\Models\User\Permission;
use App\Models\User\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
        $permissionGroups = $this->permissionGroups();

        return view('user.create', compact('campuses', 'roles', 'permissionGroups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,id'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
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

            $this->syncUserPermissions($user, $validated['roles'] ?? [], $validated['permissions'] ?? []);

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
        $permissionGroups = $this->permissionGroups();
        $user->load(['roles', 'permissions']);

        if ($user->roles->pluck('slug')->intersect(['owner', 'admin'])->isNotEmpty()) {
            $user->setRelation('permissions', Permission::query()->orderBy('resource')->orderBy('action')->get());
        }

        return view('user.edit', compact('user', 'campuses', 'roles', 'permissionGroups'));
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
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
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
        $this->syncUserPermissions($user, $validated['roles'] ?? [], $validated['permissions'] ?? []);

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

    /**
     * @return array<int, array{key: string, label: string, permissions: array<int, array{id: int, label: string}>}>
     */
    private function permissionGroups(): array
    {
        $permissions = Permission::query()
            ->orderBy('resource')
            ->orderBy('action')
            ->get()
            ->sortBy(fn (Permission $permission) => sprintf(
                '%02d:%s',
                $this->permissionPriority($permission),
                $this->canonicalPermissionKey($permission)
            ))
            ->unique(fn (Permission $permission) => $this->canonicalPermissionKey($permission));

        return $permissions
            ->groupBy(fn (Permission $permission) => $this->permissionModuleKey($permission))
            ->map(function (Collection $groupPermissions, string $moduleKey): array {
                return [
                    'key' => $moduleKey,
                    'label' => $this->permissionModuleLabel($moduleKey),
                    'permissions' => $groupPermissions
                        ->map(fn (Permission $permission): array => [
                            'id' => (int) $permission->id,
                            'label' => $this->permissionDisplayLabel($permission),
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    private function permissionPriority(Permission $permission): int
    {
        if (str_starts_with($permission->resource, 'hrm_')) {
            return 0;
        }

        if (str_starts_with($permission->resource, 'hrm.')) {
            return 1;
        }

        return 0;
    }

    private function canonicalPermissionKey(Permission $permission): string
    {
        $resource = $permission->resource;

        if (str_starts_with($resource, 'hrm.')) {
            $resource = str_replace('hrm.', 'hrm_', $resource);
        }

        return $resource . '.' . $permission->action;
    }

    private function permissionModuleKey(Permission $permission): string
    {
        return match (true) {
            $permission->resource === 'lead' && str_starts_with($permission->action, 'followup') => 'training-leads',
            $permission->resource === 'lead' && $permission->action === 'transfer.approve' => 'training-leads',
            $permission->resource === 'dashboard' => 'dashboard',
            $permission->resource === 'lead' => 'lead-management',
            $permission->resource === 'web-lead' => 'web-leads',
            $permission->resource === 'registration' => 'registration-management',
            $permission->resource === 'admission' => 'admission-management',
            $permission->resource === 'student' => 'student-management',
            $permission->resource === 'inventory' => 'inventory-management',
            $permission->resource === 'batch' || $permission->resource === 'batch-timetable' => 'batch-management',
            $permission->resource === 'program' => 'programme-management',
            $permission->resource === 'campus' => 'campus-management',
            $permission->resource === 'certificate' => 'certificate-management',
            $permission->resource === 'user' => 'user-management',
            $permission->resource === 'role' => 'role-management',
            $permission->resource === 'permission' => 'permission-management',
            $permission->resource === 'report' => 'reports',
            str_starts_with($permission->resource, 'finance.') => 'finance-management',
            str_starts_with($permission->resource, 'hrm_') || str_starts_with($permission->resource, 'hrm.') => 'human-resources',
            default => str_replace(['.', '_'], '-', $permission->resource),
        };
    }

    private function permissionModuleLabel(string $moduleKey): string
    {
        return [
            'dashboard' => 'Dashboard',
            'lead-management' => 'Lead Management',
            'training-leads' => 'Training Leads',
            'web-leads' => 'Web Leads',
            'registration-management' => 'Registration Management',
            'admission-management' => 'Admission Management',
            'student-management' => 'Student Management',
            'inventory-management' => 'Inventory Management',
            'batch-management' => 'Batches & Time Table',
            'programme-management' => 'Programme Management',
            'campus-management' => 'Campuses / Franchise',
            'certificate-management' => 'Certificate Management',
            'user-management' => 'User Management',
            'role-management' => 'Role Management',
            'permission-management' => 'Permission Management',
            'reports' => 'Reports',
            'finance-management' => 'Finance Management',
            'human-resources' => 'Human Resources',
        ][$moduleKey] ?? Str::headline(str_replace('-', ' ', $moduleKey));
    }

    private function permissionDisplayLabel(Permission $permission): string
    {
        $resourceLabel = $this->permissionResourceLabel($permission);

        return match ($permission->action) {
            'view' => 'View ' . $resourceLabel,
            'create' => 'Create ' . $resourceLabel,
            'update' => 'Update ' . $resourceLabel,
            'delete' => 'Delete ' . $resourceLabel,
            'manage' => 'Manage ' . $resourceLabel,
            'approve' => 'Approve ' . $resourceLabel,
            'reject' => 'Reject ' . $resourceLabel,
            'publish' => 'Publish ' . $resourceLabel,
            'assign' => 'Assign ' . $resourceLabel,
            'request' => 'Request ' . $resourceLabel,
            'process' => 'Process ' . $resourceLabel,
            'close' => 'Close ' . $resourceLabel,
            'import' => 'Import ' . $resourceLabel,
            'upload' => 'Upload ' . $resourceLabel,
            'manage_status' => 'Manage Status',
            'manage_type' => 'Manage Types',
            'manage_balance' => 'Manage Balances',
            'manage_structure' => 'Manage Structure',
            'checkin' => 'Check In',
            'checkout' => 'Check Out',
            'followup.view' => 'View Follow-up',
            'followup.update' => 'Manage Follow-up',
            'transfer.approve' => 'Approve Transfer',
            'send-to-printing' => 'Send To Printing',
            'mark-ready' => 'Mark Ready',
            'mark-delivered' => 'Mark Delivered',
            default => Str::headline(str_replace(['.', '_', '-'], ' ', $permission->action)),
        };
    }

    private function permissionResourceLabel(Permission $permission): string
    {
        return [
            'dashboard' => 'Dashboard',
            'lead' => 'Lead',
            'web-lead' => 'Web Lead',
            'registration' => 'Registration',
            'admission' => 'Admission',
            'student' => 'Student',
            'inventory' => 'Inventory',
            'batch' => 'Batch',
            'batch-timetable' => 'Batch Timetable',
            'program' => 'Program',
            'campus' => 'Campus',
            'certificate' => 'Certificate',
            'user' => 'User',
            'role' => 'Role',
            'permission' => 'Permission',
            'report' => 'Report',
            'finance.dashboard' => 'Finance Dashboard',
            'finance.payee' => 'Payee',
            'finance.payable' => 'Payable',
            'finance.receivable' => 'Receivable',
            'finance.expense' => 'Expense',
            'finance.bill' => 'Utility Bill',
            'finance.rent' => 'Building Rent',
            'finance.utility' => 'Utility',
            'finance.payroll' => 'Payroll',
            'hrm_dashboard' => 'HRM Dashboard',
            'hrm_employee' => 'Employee',
            'hrm_department' => 'Department',
            'hrm_designation' => 'Designation',
            'hrm_shift' => 'Shift',
            'hrm_attendance' => 'Attendance',
            'hrm_leave' => 'Leave',
            'hrm_holiday' => 'Holiday',
            'hrm_payroll' => 'Payroll',
            'hrm_announcement' => 'Announcement',
            'hrm_document' => 'Document',
            'hrm.employee' => 'Employee',
            'hrm.attendance' => 'Attendance',
            'hrm.leave' => 'Leave',
            'hrm.payroll' => 'Payroll',
            'hrm.shift' => 'Shift',
            'hrm.announcement' => 'Announcement',
            'hrm.document' => 'Document',
            'hrm.master' => 'Master',
        ][$permission->resource] ?? Str::headline(str_replace(['.', '_', '-'], ' ', $permission->resource));
    }
}
