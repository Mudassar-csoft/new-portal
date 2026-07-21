<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\User\Permission;
use App\Models\User\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = $this->permissionCatalog();

        DB::transaction(function () use ($permissions) {
            $permissionIds = collect($permissions)->mapWithKeys(function ($perm) {
                $slug = "{$perm['resource']}.{$perm['action']}";
                $permission = Permission::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'resource' => $perm['resource'],
                        'action' => $perm['action'],
                        'description' => Str::headline($slug),
                    ]
                );
                return [$slug => $permission->id];
            });

            $roleSets = [
                'owner' => $permissionIds->values(),
                'admin' => $permissionIds->values(),
                'member' => $permissionIds->filter(function ($id, $slug) {
                    return !str_contains($slug, 'user.')
                        && !str_contains($slug, 'role.')
                        && !str_contains($slug, 'permission.')
                        && $slug !== 'lead.followup.not-interesting';
                })->values(),
                'read-only' => $permissionIds->filter(function ($id, $slug) {
                    return str_contains($slug, '.view');
                })->values(),
            ];

            $systemRoleSlugs = ['owner', 'admin'];

            $roles = collect($roleSets)->mapWithKeys(function ($permIds, $slug) use ($systemRoleSlugs) {
                $role = Role::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => Str::headline($slug),
                        'description' => ucfirst($slug),
                        'is_system' => in_array($slug, $systemRoleSlugs, true),
                    ]
                );

                // Keep is_system in sync if the role already existed
                if (in_array($slug, $systemRoleSlugs, true) && !$role->is_system) {
                    $role->update(['is_system' => true]);
                }

                $role->permissions()->sync($permIds);
                return [$slug => $role];
            });

            $user = User::firstOrCreate(
                ['email' => 'admin@example.com'],
                [
                    'name' => 'Admin User',
                    'password' => bcrypt('password'),
                ]
            );

            $user->roles()->sync([$roles['admin']->id]);
        });
    }

    /**
     * @return array<int, array{resource: string, action: string}>
     */
    private function permissionCatalog(): array
    {
        $catalog = [];

        // Standard CRUD per resource
        $crudResources = [
            'lead', 'web-lead', 'registration', 'admission',
            'campus', 'program', 'batch', 'batch-timetable',
            'inventory', 'student',
            'certificate',
            'user', 'role', 'permission',
        ];
        foreach ($crudResources as $resource) {
            foreach (['view', 'create', 'update', 'delete'] as $action) {
                $catalog[] = ['resource' => $resource, 'action' => $action];
            }
        }

        // Lead follow-up
        $catalog[] = ['resource' => 'lead', 'action' => 'followup.view'];
        $catalog[] = ['resource' => 'lead', 'action' => 'followup.update'];
        $catalog[] = ['resource' => 'lead', 'action' => 'followup.not-interesting'];
        $catalog[] = ['resource' => 'lead', 'action' => 'transfer.approve'];
        $catalog[] = ['resource' => 'lead', 'action' => 'coworking.view'];

        // Certificate workflow transitions
        foreach (['approve', 'reject', 'send-to-printing', 'mark-ready', 'mark-delivered'] as $action) {
            $catalog[] = ['resource' => 'certificate', 'action' => $action];
        }

        // Admission workflow transitions
        $catalog[] = ['resource' => 'admission', 'action' => 'review'];
        $catalog[] = ['resource' => 'student', 'action' => 'drop'];

        // Role / Permission management
        $catalog[] = ['resource' => 'role', 'action' => 'manage'];
        $catalog[] = ['resource' => 'permission', 'action' => 'manage'];

        // HRM sub-modules
        $hrmResources = [
            'hrm.employee', 'hrm.attendance', 'hrm.leave', 'hrm.payroll',
            'hrm.shift', 'hrm.announcement', 'hrm.document', 'hrm.master',
        ];
        foreach ($hrmResources as $resource) {
            foreach (['view', 'create', 'update', 'delete'] as $action) {
                $catalog[] = ['resource' => $resource, 'action' => $action];
            }
        }

        // Finance sub-modules
        $financeResources = [
            'finance.dashboard', 'finance.payee', 'finance.payable', 'finance.receivable',
            'finance.expense', 'finance.bill', 'finance.rent', 'finance.utility', 'finance.payroll',
        ];
        foreach ($financeResources as $resource) {
            foreach (['view', 'create', 'update', 'delete'] as $action) {
                $catalog[] = ['resource' => $resource, 'action' => $action];
            }
        }

        // Dashboard
        $catalog[] = ['resource' => 'dashboard', 'action' => 'view'];

        // Reports
        foreach (['leads', 'admissions', 'students', 'batches', 'programs', 'campuses', 'hr', 'finance', 'marketing', 'events', 'certificates'] as $section) {
            $catalog[] = ['resource' => 'report', 'action' => $section];
        }

        return $catalog;
    }
}
