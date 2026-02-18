<?php

namespace Database\Seeders;

use App\Models\User\Permission;
use App\Models\User\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HrmAccessSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['resource' => 'hrm_dashboard', 'action' => 'view'],

            ['resource' => 'hrm_employee', 'action' => 'view'],
            ['resource' => 'hrm_employee', 'action' => 'create'],
            ['resource' => 'hrm_employee', 'action' => 'update'],
            ['resource' => 'hrm_employee', 'action' => 'delete'],
            ['resource' => 'hrm_employee', 'action' => 'manage_status'],

            ['resource' => 'hrm_department', 'action' => 'view'],
            ['resource' => 'hrm_department', 'action' => 'create'],
            ['resource' => 'hrm_designation', 'action' => 'create'],

            ['resource' => 'hrm_shift', 'action' => 'view'],
            ['resource' => 'hrm_shift', 'action' => 'manage'],
            ['resource' => 'hrm_shift', 'action' => 'assign'],

            ['resource' => 'hrm_attendance', 'action' => 'view'],
            ['resource' => 'hrm_attendance', 'action' => 'checkin'],
            ['resource' => 'hrm_attendance', 'action' => 'checkout'],
            ['resource' => 'hrm_attendance', 'action' => 'request'],
            ['resource' => 'hrm_attendance', 'action' => 'approve'],
            ['resource' => 'hrm_attendance', 'action' => 'import'],

            ['resource' => 'hrm_leave', 'action' => 'view'],
            ['resource' => 'hrm_leave', 'action' => 'request'],
            ['resource' => 'hrm_leave', 'action' => 'approve'],
            ['resource' => 'hrm_leave', 'action' => 'manage_type'],
            ['resource' => 'hrm_leave', 'action' => 'manage_balance'],

            ['resource' => 'hrm_holiday', 'action' => 'view'],
            ['resource' => 'hrm_holiday', 'action' => 'manage'],

            ['resource' => 'hrm_payroll', 'action' => 'view'],
            ['resource' => 'hrm_payroll', 'action' => 'process'],
            ['resource' => 'hrm_payroll', 'action' => 'close'],
            ['resource' => 'hrm_payroll', 'action' => 'manage_structure'],

            ['resource' => 'hrm_announcement', 'action' => 'view'],
            ['resource' => 'hrm_announcement', 'action' => 'create'],
            ['resource' => 'hrm_announcement', 'action' => 'publish'],

            ['resource' => 'hrm_document', 'action' => 'view'],
            ['resource' => 'hrm_document', 'action' => 'upload'],
            ['resource' => 'hrm_document', 'action' => 'manage'],
        ];

        DB::transaction(function () use ($permissions): void {
            $permissionIds = collect($permissions)->mapWithKeys(function (array $perm): array {
                $slug = $perm['resource'] . '.' . $perm['action'];
                $permission = Permission::query()->firstOrCreate(
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
                'hr' => $permissionIds->values()->all(),
                'campus-manager' => $permissionIds->only([
                    'hrm_dashboard.view',
                    'hrm_employee.view',
                    'hrm_employee.create',
                    'hrm_employee.update',
                    'hrm_employee.manage_status',
                    'hrm_department.view',
                    'hrm_designation.create',
                    'hrm_shift.view',
                    'hrm_shift.assign',
                    'hrm_attendance.view',
                    'hrm_attendance.checkin',
                    'hrm_attendance.checkout',
                    'hrm_attendance.request',
                    'hrm_attendance.approve',
                    'hrm_leave.view',
                    'hrm_leave.request',
                    'hrm_leave.approve',
                    'hrm_holiday.view',
                    'hrm_announcement.view',
                    'hrm_announcement.create',
                    'hrm_document.view',
                ])->values()->all(),
                'accounts' => $permissionIds->only([
                    'hrm_dashboard.view',
                    'hrm_employee.view',
                    'hrm_attendance.view',
                    'hrm_leave.view',
                    'hrm_payroll.view',
                    'hrm_payroll.process',
                    'hrm_payroll.close',
                    'hrm_payroll.manage_structure',
                    'hrm_document.view',
                    'hrm_document.upload',
                    'hrm_document.manage',
                ])->values()->all(),
                'team-lead' => $permissionIds->only([
                    'hrm_dashboard.view',
                    'hrm_employee.view',
                    'hrm_attendance.view',
                    'hrm_attendance.request',
                    'hrm_attendance.approve',
                    'hrm_leave.view',
                    'hrm_leave.request',
                    'hrm_leave.approve',
                    'hrm_announcement.view',
                ])->values()->all(),
                'employee' => $permissionIds->only([
                    'hrm_dashboard.view',
                    'hrm_employee.view',
                    'hrm_attendance.view',
                    'hrm_attendance.checkin',
                    'hrm_attendance.checkout',
                    'hrm_attendance.request',
                    'hrm_leave.view',
                    'hrm_leave.request',
                    'hrm_document.view',
                    'hrm_document.upload',
                ])->values()->all(),
            ];

            foreach ($roleSets as $slug => $permissionSet) {
                $role = Role::query()->firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => Str::headline($slug),
                        'description' => Str::headline($slug) . ' Role',
                    ]
                );

                $role->permissions()->syncWithoutDetaching($permissionSet);
            }

            $adminRoles = Role::query()->whereIn('slug', ['owner', 'admin'])->get();
            foreach ($adminRoles as $adminRole) {
                $adminRole->permissions()->syncWithoutDetaching($permissionIds->values()->all());
            }
        });
    }
}

