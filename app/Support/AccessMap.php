<?php

namespace App\Support;

use App\Models\User;

class AccessMap
{
    /**
     * @return array<string, array<int, string>>
     */
    public static function modulePermissions(): array
    {
        $crud = fn (string $resource): array => [
            $resource . '.view',
            $resource . '.create',
            $resource . '.update',
            $resource . '.delete',
        ];

        return [
            'dashboard' => ['dashboard.view'],
            'lead-management' => $crud('lead'),
            'training-leads' => ['lead.followup.view', 'lead.followup.update', 'lead.transfer.approve'],
            'coworking-space' => ['lead.coworking.view'],
            'web-leads' => $crud('web-lead'),
            'registration-management' => $crud('registration'),
            'admission-management' => $crud('admission'),
            'student-management' => $crud('student'),
            'inventory-management' => $crud('inventory'),
            'batch-management' => array_merge($crud('batch'), $crud('batch-timetable')),
            'programme-management' => $crud('program'),
            'campus-management' => $crud('campus'),
            'certificate-management' => array_merge($crud('certificate'), [
                'certificate.approve',
                'certificate.reject',
                'certificate.send-to-printing',
                'certificate.mark-ready',
                'certificate.mark-delivered',
            ]),
            'user-management' => array_merge($crud('user'), ['user.view']),
            'role-management' => array_merge($crud('role'), ['role.manage']),
            'permission-management' => array_merge($crud('permission'), ['permission.manage']),
            'reports' => [
                'report.leads',
                'report.admissions',
                'report.students',
                'report.batches',
                'report.programs',
                'report.campuses',
                'report.hr',
                'report.finance',
                'report.marketing',
                'report.events',
                'report.certificates',
            ],
            'finance-management' => array_merge(
                $crud('finance.dashboard'),
                $crud('finance.payee'),
                $crud('finance.payable'),
                $crud('finance.receivable'),
                $crud('finance.expense'),
                $crud('finance.bill'),
                $crud('finance.rent'),
                $crud('finance.utility'),
                $crud('finance.payroll')
            ),
            'human-resources' => [
                'hrm_dashboard.view',
                'hrm_employee.view',
                'hrm_employee.create',
                'hrm_employee.update',
                'hrm_employee.delete',
                'hrm_employee.manage_status',
                'hrm_department.view',
                'hrm_department.create',
                'hrm_designation.create',
                'hrm_shift.view',
                'hrm_shift.manage',
                'hrm_shift.assign',
                'hrm_attendance.view',
                'hrm_attendance.checkin',
                'hrm_attendance.checkout',
                'hrm_attendance.request',
                'hrm_attendance.approve',
                'hrm_attendance.import',
                'hrm_leave.view',
                'hrm_leave.request',
                'hrm_leave.approve',
                'hrm_leave.manage_type',
                'hrm_leave.manage_balance',
                'hrm_holiday.view',
                'hrm_holiday.manage',
                'hrm_payroll.view',
                'hrm_payroll.process',
                'hrm_payroll.close',
                'hrm_payroll.manage_structure',
                'hrm_announcement.view',
                'hrm_announcement.create',
                'hrm_announcement.publish',
                'hrm_document.view',
                'hrm_document.upload',
                'hrm_document.manage',
                'hrm.employee.view',
                'hrm.employee.create',
                'hrm.employee.update',
                'hrm.employee.delete',
                'hrm.attendance.view',
                'hrm.attendance.create',
                'hrm.attendance.update',
                'hrm.attendance.delete',
                'hrm.leave.view',
                'hrm.leave.create',
                'hrm.leave.update',
                'hrm.leave.delete',
                'hrm.payroll.view',
                'hrm.payroll.create',
                'hrm.payroll.update',
                'hrm.payroll.delete',
                'hrm.shift.view',
                'hrm.shift.create',
                'hrm.shift.update',
                'hrm.shift.delete',
                'hrm.announcement.view',
                'hrm.announcement.create',
                'hrm.announcement.update',
                'hrm.announcement.delete',
                'hrm.document.view',
                'hrm.document.create',
                'hrm.document.update',
                'hrm.document.delete',
                'hrm.master.view',
                'hrm.master.create',
                'hrm.master.update',
                'hrm.master.delete',
            ],
        ];
    }

    /**
     * @return array<int, array{route: string, permissions: array<int, string>, parameters?: array<string, mixed>}>
     */
    public static function landingRoutes(): array
    {
        return [
            ['route' => 'dashboard', 'permissions' => ['dashboard.view']],
            ['route' => 'leads.index', 'permissions' => ['lead.view']],
            ['route' => 'leads.create', 'permissions' => ['lead.create']],
            ['route' => 'leads.followups', 'permissions' => ['lead.followup.view']],
            ['route' => 'leads.coworking.followups', 'permissions' => ['lead.coworking.view']],
            ['route' => 'web-leads.index', 'permissions' => ['web-lead.view']],
            ['route' => 'registration.status', 'permissions' => ['registration.view']],
            ['route' => 'admission.status', 'permissions' => ['admission.view']],
            ['route' => 'student.records.index', 'permissions' => ['student.view']],
            ['route' => 'batch.index', 'permissions' => ['batch.view']],
            ['route' => 'program.index', 'permissions' => ['program.view']],
            ['route' => 'campus.index', 'permissions' => ['campus.view']],
            ['route' => 'hrm.dashboard', 'permissions' => ['hrm_dashboard.view', 'hrm.dashboard.view']],
            ['route' => 'finance.dashboard', 'permissions' => ['finance.dashboard.view']],
            ['route' => 'inventory.index', 'permissions' => ['inventory.view']],
            ['route' => 'certificate.index', 'permissions' => ['certificate.view']],
            ['route' => 'users.index', 'permissions' => ['user.view']],
            ['route' => 'roles.index', 'permissions' => ['role.view', 'role.manage']],
            ['route' => 'permissions.index', 'permissions' => ['permission.view', 'permission.manage']],
            ['route' => 'profile.show', 'permissions' => []],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function permissionsForModule(string $moduleKey): array
    {
        return self::modulePermissions()[$moduleKey] ?? [];
    }

    /**
     * @return array<int, string>
     */
    public static function financeExpenseManagePermissions(?string $category = null): array
    {
        $base = ['finance.expense.update'];

        return match ((string) $category) {
            'payroll' => array_merge($base, ['finance.payroll.update']),
            'utility' => array_merge($base, ['finance.utility.update', 'finance.bill.update']),
            'rent' => array_merge($base, ['finance.rent.update']),
            default => $base,
        };
    }

    public static function firstAccessibleRouteFor(User $user): string
    {
        foreach (self::landingRoutes() as $candidate) {
            $permissions = $candidate['permissions'] ?? [];
            if ($permissions === [] || $user->hasAnyPermission($permissions)) {
                return route($candidate['route'], $candidate['parameters'] ?? []);
            }
        }

        return route('profile.show');
    }
}
