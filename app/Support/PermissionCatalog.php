<?php

namespace App\Support;

use App\Models\User\Permission;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PermissionCatalog
{
    /**
     * @return array<int, array{
     *     key: string,
     *     label: string,
     *     sections: array<int, array{label: string|null, permissions: array<int, array{id: int, label: string}>}>,
     *     permissions: array<int, array{id: int, label: string}>
     * }>
     */
    public static function grouped(): array
    {
        $permissions = Permission::query()
            ->orderBy('resource')
            ->orderBy('action')
            ->get()
            ->sortBy(fn (Permission $permission) => sprintf(
                '%02d:%s',
                self::priority($permission),
                self::canonicalKey($permission)
            ))
            ->unique(fn (Permission $permission) => self::canonicalKey($permission));

        return self::groupedFromCollection($permissions);
    }

    /**
     * @param  Collection<int, Permission>  $permissions
     * @return array<int, array{
     *     key: string,
     *     label: string,
     *     sections: array<int, array{label: string|null, permissions: array<int, array{id: int, label: string}>}>,
     *     permissions: array<int, array{id: int, label: string}>
     * }>
     */
    public static function groupedFromCollection(Collection $permissions): array
    {
        $permissionsBySlug = $permissions->keyBy(fn (Permission $permission) => self::canonicalKey($permission));
        $sidebarPermissions = collect(self::sidebarPermissionEntries())
            ->map(function (array $entry) use ($permissionsBySlug): ?array {
                $permission = collect($entry['slugs'])
                    ->map(fn (string $slug) => $permissionsBySlug->get($slug))
                    ->first();

                if (!$permission) {
                    return null;
                }

                return [
                    'id' => (int) $permission->id,
                    'moduleKey' => $entry['moduleKey'],
                    'moduleLabel' => self::moduleLabel($entry['moduleKey']),
                    'moduleOrder' => self::moduleSortOrder($entry['moduleKey']),
                    'section' => $entry['section'],
                    'sectionOrder' => $entry['sectionOrder'],
                    'order' => $entry['order'],
                    'label' => $entry['label'],
                ];
            })
            ->filter();

        $usedPermissionIds = $sidebarPermissions
            ->pluck('id')
            ->map(fn (mixed $id) => (int) $id)
            ->all();

        $remainingPermissions = $permissions
            ->reject(fn (Permission $permission): bool => in_array((int) $permission->id, $usedPermissionIds, true))
            ->map(function (Permission $permission): array {
                $meta = self::permissionMeta($permission);

                return [
                    'id' => (int) $permission->id,
                    'moduleKey' => $meta['moduleKey'],
                    'moduleLabel' => self::moduleLabel($meta['moduleKey']),
                    'moduleOrder' => self::moduleSortOrder($meta['moduleKey']),
                    'section' => $meta['section'],
                    'sectionOrder' => $meta['sectionOrder'],
                    'order' => $meta['order'],
                    'label' => $meta['label'],
                ];
            });

        return $sidebarPermissions
            ->concat($remainingPermissions)
            ->groupBy('moduleKey')
            ->map(function (Collection $groupPermissions, string $moduleKey): array {
                $sortedPermissions = $groupPermissions
                    ->sortBy(fn (array $permission): string => sprintf(
                        '%03d:%03d:%s',
                        $permission['sectionOrder'],
                        $permission['order'],
                        $permission['label']
                    ))
                    ->values();

                $sections = $sortedPermissions
                    ->groupBy(fn (array $permission) => $permission['section'] ?? '__default')
                    ->map(function (Collection $sectionPermissions, string $sectionKey): array {
                        $first = $sectionPermissions->first();

                        return [
                            'label' => $sectionKey === '__default' ? null : $first['section'],
                            'sectionOrder' => $first['sectionOrder'],
                            'permissions' => $sectionPermissions
                                ->map(fn (array $permission): array => [
                                    'id' => $permission['id'],
                                    'label' => $permission['label'],
                                ])
                                ->values()
                                ->all(),
                        ];
                    })
                    ->sortBy('sectionOrder')
                    ->values()
                    ->map(function (array $section): array {
                        unset($section['sectionOrder']);

                        return $section;
                    })
                    ->all();

                $flatPermissions = collect($sections)
                    ->flatMap(fn (array $section) => $section['permissions'])
                    ->values()
                    ->all();

                return [
                    'key' => $moduleKey,
                    'label' => self::moduleLabel($moduleKey),
                    'moduleOrder' => (int) ($sortedPermissions->first()['moduleOrder'] ?? self::moduleSortOrder($moduleKey)),
                    'sections' => $sections,
                    'permissions' => $flatPermissions,
                ];
            })
            ->sortBy('moduleOrder')
            ->values()
            ->map(function (array $group): array {
                unset($group['moduleOrder']);

                return $group;
            })
            ->all();
    }

    /**
     * @return array<int, array{
     *     moduleKey: string,
     *     section: string|null,
     *     sectionOrder: int,
     *     order: int,
     *     label: string,
     *     slugs: array<int, string>
     * }>
     */
    private static function sidebarPermissionEntries(): array
    {
        return [
            ['moduleKey' => 'dashboard', 'section' => null, 'sectionOrder' => 10, 'order' => 10, 'label' => 'Dashboard', 'slugs' => ['dashboard.view']],

            ['moduleKey' => 'lead-management', 'section' => null, 'sectionOrder' => 10, 'order' => 10, 'label' => 'Create New Lead', 'slugs' => ['lead.create']],
            ['moduleKey' => 'lead-management', 'section' => 'Training Leads', 'sectionOrder' => 20, 'order' => 10, 'label' => "Lead's Follow-up", 'slugs' => ['lead.followup.view']],
            ['moduleKey' => 'lead-management', 'section' => 'Training Leads', 'sectionOrder' => 20, 'order' => 20, 'label' => 'Transferred Leads', 'slugs' => ['lead.transfer.approve']],
            ['moduleKey' => 'lead-management', 'section' => 'Training Leads', 'sectionOrder' => 20, 'order' => 30, 'label' => 'All Leads', 'slugs' => ['lead.view']],
            ['moduleKey' => 'lead-management', 'section' => 'Coworking Space', 'sectionOrder' => 30, 'order' => 10, 'label' => "Lead's Follow-up", 'slugs' => ['lead.coworking.view']],
            ['moduleKey' => 'lead-management', 'section' => 'Web Leads', 'sectionOrder' => 40, 'order' => 10, 'label' => 'Web Leads', 'slugs' => ['web-lead.view']],

            ['moduleKey' => 'registration-management', 'section' => null, 'sectionOrder' => 10, 'order' => 10, 'label' => 'All Registration / Fee Voucher Preview', 'slugs' => ['registration.view']],
            ['moduleKey' => 'registration-management', 'section' => null, 'sectionOrder' => 10, 'order' => 20, 'label' => 'Create Registration / Create Coworking Registration', 'slugs' => ['registration.create']],

            ['moduleKey' => 'admission-management', 'section' => null, 'sectionOrder' => 10, 'order' => 10, 'label' => 'All Admissions', 'slugs' => ['admission.view']],
            ['moduleKey' => 'admission-management', 'section' => null, 'sectionOrder' => 10, 'order' => 20, 'label' => 'Create Admission', 'slugs' => ['admission.create']],
            ['moduleKey' => 'admission-management', 'section' => null, 'sectionOrder' => 10, 'order' => 30, 'label' => 'Review Admission Requests', 'slugs' => ['admission.review']],

            ['moduleKey' => 'student-management', 'section' => null, 'sectionOrder' => 10, 'order' => 10, 'label' => 'Student Records', 'slugs' => ['student.view']],
            ['moduleKey' => 'student-management', 'section' => null, 'sectionOrder' => 10, 'order' => 20, 'label' => 'Drop Student', 'slugs' => ['student.drop']],

            ['moduleKey' => 'batch-management', 'section' => 'Batches', 'sectionOrder' => 10, 'order' => 10, 'label' => 'Create Batch', 'slugs' => ['batch.create']],
            ['moduleKey' => 'batch-management', 'section' => 'Batches', 'sectionOrder' => 10, 'order' => 20, 'label' => 'All Batches', 'slugs' => ['batch.view']],
            ['moduleKey' => 'batch-management', 'section' => 'Time Table', 'sectionOrder' => 20, 'order' => 10, 'label' => 'Manage Time Table', 'slugs' => ['batch-timetable.view']],

            ['moduleKey' => 'programme-management', 'section' => null, 'sectionOrder' => 10, 'order' => 10, 'label' => 'Create Program', 'slugs' => ['program.create']],
            ['moduleKey' => 'programme-management', 'section' => null, 'sectionOrder' => 10, 'order' => 20, 'label' => 'All Programmes', 'slugs' => ['program.view']],

            ['moduleKey' => 'campus-management', 'section' => null, 'sectionOrder' => 10, 'order' => 10, 'label' => 'Create Campus / Franchise', 'slugs' => ['campus.create']],
            ['moduleKey' => 'campus-management', 'section' => null, 'sectionOrder' => 10, 'order' => 20, 'label' => 'All Campuses / Franchise', 'slugs' => ['campus.view']],

            ['moduleKey' => 'human-resources', 'section' => null, 'sectionOrder' => 10, 'order' => 10, 'label' => 'HRM Dashboard', 'slugs' => ['hrm_dashboard.view', 'hrm.dashboard.view']],
            ['moduleKey' => 'human-resources', 'section' => null, 'sectionOrder' => 10, 'order' => 20, 'label' => 'Employee Master / Profile', 'slugs' => ['hrm_employee.view', 'hrm.employee.view']],
            ['moduleKey' => 'human-resources', 'section' => null, 'sectionOrder' => 10, 'order' => 30, 'label' => 'Departments / Designations / Holidays', 'slugs' => ['hrm_department.view', 'hrm.master.view']],
            ['moduleKey' => 'human-resources', 'section' => null, 'sectionOrder' => 10, 'order' => 40, 'label' => 'Attendance (Daily)', 'slugs' => ['hrm_attendance.view', 'hrm.attendance.view']],
            ['moduleKey' => 'human-resources', 'section' => null, 'sectionOrder' => 10, 'order' => 50, 'label' => 'Leave Management', 'slugs' => ['hrm_leave.view', 'hrm.leave.view']],
            ['moduleKey' => 'human-resources', 'section' => null, 'sectionOrder' => 10, 'order' => 60, 'label' => 'Payroll', 'slugs' => ['hrm_payroll.view', 'hrm.payroll.view']],
            ['moduleKey' => 'human-resources', 'section' => null, 'sectionOrder' => 10, 'order' => 70, 'label' => 'Shift / Timetable', 'slugs' => ['hrm_shift.view', 'hrm.shift.view']],
            ['moduleKey' => 'human-resources', 'section' => null, 'sectionOrder' => 10, 'order' => 80, 'label' => 'Announcements / Notifications', 'slugs' => ['hrm_announcement.view', 'hrm.announcement.view']],
            ['moduleKey' => 'human-resources', 'section' => null, 'sectionOrder' => 10, 'order' => 90, 'label' => 'Documents', 'slugs' => ['hrm_document.view', 'hrm.document.view']],

            ['moduleKey' => 'finance-management', 'section' => null, 'sectionOrder' => 10, 'order' => 10, 'label' => 'Dashboard', 'slugs' => ['finance.dashboard.view']],
            ['moduleKey' => 'finance-management', 'section' => 'Expense', 'sectionOrder' => 20, 'order' => 10, 'label' => 'Add Expense / Add Expense Type', 'slugs' => ['finance.expense.create']],
            ['moduleKey' => 'finance-management', 'section' => 'Expense', 'sectionOrder' => 20, 'order' => 20, 'label' => 'Utility Bills', 'slugs' => ['finance.utility.view', 'finance.bill.view']],
            ['moduleKey' => 'finance-management', 'section' => 'Expense', 'sectionOrder' => 20, 'order' => 30, 'label' => 'Building Rent Setup / Rent Expenses', 'slugs' => ['finance.rent.view']],
            ['moduleKey' => 'finance-management', 'section' => 'Expense', 'sectionOrder' => 20, 'order' => 40, 'label' => 'Marketing / Asset Purchase / All Expenses', 'slugs' => ['finance.expense.view']],
            ['moduleKey' => 'finance-management', 'section' => 'Expense', 'sectionOrder' => 20, 'order' => 50, 'label' => 'Payroll', 'slugs' => ['finance.payroll.view']],
            ['moduleKey' => 'finance-management', 'section' => null, 'sectionOrder' => 30, 'order' => 10, 'label' => 'Supplier & Payee', 'slugs' => ['finance.payee.view']],
            ['moduleKey' => 'finance-management', 'section' => null, 'sectionOrder' => 30, 'order' => 20, 'label' => 'Payables', 'slugs' => ['finance.payable.view']],
            ['moduleKey' => 'finance-management', 'section' => null, 'sectionOrder' => 30, 'order' => 30, 'label' => 'Invoices / Receivables', 'slugs' => ['finance.receivable.view']],

            ['moduleKey' => 'inventory-management', 'section' => null, 'sectionOrder' => 10, 'order' => 10, 'label' => 'Feed Campus Inventory', 'slugs' => ['inventory.create']],
            ['moduleKey' => 'inventory-management', 'section' => null, 'sectionOrder' => 10, 'order' => 20, 'label' => 'Campus Stock Register', 'slugs' => ['inventory.view']],

            ['moduleKey' => 'certificate-management', 'section' => null, 'sectionOrder' => 10, 'order' => 10, 'label' => 'Request Certificate', 'slugs' => ['certificate.create']],
            ['moduleKey' => 'certificate-management', 'section' => null, 'sectionOrder' => 10, 'order' => 20, 'label' => 'Certificate Status Lists / All Certificates', 'slugs' => ['certificate.view']],

            ['moduleKey' => 'user-management', 'section' => 'Users', 'sectionOrder' => 10, 'order' => 10, 'label' => 'Create User', 'slugs' => ['user.create']],
            ['moduleKey' => 'user-management', 'section' => 'Users', 'sectionOrder' => 10, 'order' => 20, 'label' => 'Users / User Activities', 'slugs' => ['user.view']],
            ['moduleKey' => 'user-management', 'section' => 'Roles', 'sectionOrder' => 20, 'order' => 10, 'label' => 'Create Role', 'slugs' => ['role.create', 'role.manage']],
            ['moduleKey' => 'user-management', 'section' => 'Roles', 'sectionOrder' => 20, 'order' => 20, 'label' => 'Roles', 'slugs' => ['role.view', 'role.manage']],
        ];
    }

    /**
     * @return array{moduleKey: string, section: string|null, sectionOrder: int, order: int, label: string}
     */
    private static function permissionMeta(Permission $permission): array
    {
        $key = self::canonicalKey($permission);

        return match ($key) {
            'dashboard.view' => self::meta('dashboard', null, 'Dashboard', 10, 10),

            'lead.create' => self::meta('lead-management', null, 'Create New Lead', 10, 10),
            'lead.update' => self::meta('lead-management', null, 'Update Lead Record', 10, 20),
            'lead.delete' => self::meta('lead-management', null, 'Delete Lead Record', 10, 30),
            'lead.followup.view' => self::meta('lead-management', 'Training Leads', "Lead's Follow-up", 20, 10),
            'lead.followup.update' => self::meta('lead-management', 'Training Leads', "Update Lead's Follow-up", 20, 20),
            'lead.transfer.approve' => self::meta('lead-management', 'Training Leads', 'Transferred Leads', 20, 30),
            'lead.view' => self::meta('lead-management', 'Training Leads', 'All Leads', 20, 40),
            'lead.coworking.view' => self::meta('lead-management', 'Coworking Space', "Lead's Follow-up", 30, 10),

            'web-lead.view' => self::meta('lead-management', 'Web Leads', 'Web Leads', 40, 10),
            'web-lead.create' => self::meta('lead-management', 'Web Leads', 'Create Web Lead', 40, 20),
            'web-lead.update' => self::meta('lead-management', 'Web Leads', 'Update Web Lead', 40, 30),
            'web-lead.delete' => self::meta('lead-management', 'Web Leads', 'Delete Web Lead', 40, 40),

            'registration.create' => self::meta('registration-management', null, 'Create Registration / Create Coworking Registration', 10, 10),
            'registration.view' => self::meta('registration-management', null, 'All Registration / Fee Voucher Preview', 10, 20),
            'registration.update' => self::meta('registration-management', null, 'Update Registration', 10, 30),
            'registration.delete' => self::meta('registration-management', null, 'Delete Registration', 10, 40),

            'admission.create' => self::meta('admission-management', null, 'Create Admission', 10, 10),
            'admission.view' => self::meta('admission-management', null, 'All Admissions', 10, 20),
            'admission.review' => self::meta('admission-management', null, 'Review Admission Requests', 10, 25),
            'admission.update' => self::meta('admission-management', null, 'Update Admission', 10, 30),
            'admission.delete' => self::meta('admission-management', null, 'Delete Admission', 10, 40),

            'student.view' => self::meta('student-management', null, 'Student Records', 10, 10),
            'student.create' => self::meta('student-management', null, 'Create Student Record', 10, 20),
            'student.drop' => self::meta('student-management', null, 'Drop Student', 10, 25),
            'student.update' => self::meta('student-management', null, 'Update Student Record', 10, 30),
            'student.delete' => self::meta('student-management', null, 'Delete Student Record', 10, 40),

            'batch.create' => self::meta('batch-management', 'Batches', 'Create Batch', 10, 10),
            'batch.view' => self::meta('batch-management', 'Batches', 'Batch Lists', 10, 20),
            'batch.update' => self::meta('batch-management', 'Batches', 'Update Batch', 10, 30),
            'batch.delete' => self::meta('batch-management', 'Batches', 'Delete Batch', 10, 40),
            'batch-timetable.view' => self::meta('batch-management', 'Time Table', 'Manage Time Table', 20, 10),
            'batch-timetable.create' => self::meta('batch-management', 'Time Table', 'Create Time Table', 20, 20),
            'batch-timetable.update' => self::meta('batch-management', 'Time Table', 'Update Time Table', 20, 30),
            'batch-timetable.delete' => self::meta('batch-management', 'Time Table', 'Delete Time Table', 20, 40),

            'program.create' => self::meta('programme-management', null, 'Create Program', 10, 10),
            'program.view' => self::meta('programme-management', null, 'Programme Lists', 10, 20),
            'program.update' => self::meta('programme-management', null, 'Update Program', 10, 30),
            'program.delete' => self::meta('programme-management', null, 'Delete Program', 10, 40),

            'campus.create' => self::meta('campus-management', null, 'Create Campus / Franchise', 10, 10),
            'campus.view' => self::meta('campus-management', null, 'Campus / Franchise Lists', 10, 20),
            'campus.update' => self::meta('campus-management', null, 'Update Campus / Franchise', 10, 30),
            'campus.delete' => self::meta('campus-management', null, 'Delete Campus / Franchise', 10, 40),

            'inventory.create' => self::meta('inventory-management', null, 'Feed Campus Inventory', 10, 10),
            'inventory.view' => self::meta('inventory-management', null, 'Campus Stock Register', 10, 20),
            'inventory.update' => self::meta('inventory-management', null, 'Update Inventory', 10, 30),
            'inventory.delete' => self::meta('inventory-management', null, 'Delete Inventory', 10, 40),

            'certificate.create' => self::meta('certificate-management', null, 'Request Certificate', 10, 10),
            'certificate.view' => self::meta('certificate-management', null, 'All Certificates', 10, 20),
            'certificate.approve' => self::meta('certificate-management', null, 'Approve Certificate Request', 10, 30),
            'certificate.reject' => self::meta('certificate-management', null, 'Reject Certificate Request', 10, 40),
            'certificate.send-to-printing' => self::meta('certificate-management', null, 'Send To Printing', 10, 50),
            'certificate.mark-ready' => self::meta('certificate-management', null, 'Mark Ready', 10, 60),
            'certificate.mark-delivered' => self::meta('certificate-management', null, 'Mark Delivered', 10, 70),
            'certificate.update' => self::meta('certificate-management', null, 'Update Certificate', 10, 80),
            'certificate.delete' => self::meta('certificate-management', null, 'Delete Certificate', 10, 90),

            'user.create' => self::meta('user-management', 'Users', 'Create User', 10, 10),
            'user.view' => self::meta('user-management', 'Users', 'Users', 10, 20),
            'user.update' => self::meta('user-management', 'Users', 'Update User', 10, 30),
            'user.delete' => self::meta('user-management', 'Users', 'Delete User', 10, 40),

            'role.create' => self::meta('user-management', 'Roles', 'Create Role', 20, 10),
            'role.view' => self::meta('user-management', 'Roles', 'Roles', 20, 20),
            'role.manage' => self::meta('user-management', 'Roles', 'Manage Roles', 20, 30),
            'role.update' => self::meta('user-management', 'Roles', 'Update Role', 20, 40),
            'role.delete' => self::meta('user-management', 'Roles', 'Delete Role', 20, 50),

            'permission.view' => self::meta('user-management', 'Permissions', 'Permissions', 30, 10),
            'permission.manage' => self::meta('user-management', 'Permissions', 'Manage Permissions', 30, 20),
            'permission.create' => self::meta('user-management', 'Permissions', 'Create Permission', 30, 30),
            'permission.update' => self::meta('user-management', 'Permissions', 'Update Permission', 30, 40),
            'permission.delete' => self::meta('user-management', 'Permissions', 'Delete Permission', 30, 50),

            'finance.dashboard.view' => self::meta('finance-management', 'Dashboard', 'Dashboard', 10, 10),
            'finance.dashboard.create' => self::meta('finance-management', 'Dashboard', 'Create Finance Dashboard Record', 10, 20),
            'finance.dashboard.update' => self::meta('finance-management', 'Dashboard', 'Update Finance Dashboard Record', 10, 30),
            'finance.dashboard.delete' => self::meta('finance-management', 'Dashboard', 'Delete Finance Dashboard Record', 10, 40),

            'finance.expense.create' => self::meta('finance-management', 'Expense', 'Add Expense / Add Expense Type', 20, 10),
            'finance.expense.view' => self::meta('finance-management', 'Expense', 'Marketing / Asset Purchase / All Expenses', 20, 20),
            'finance.expense.update' => self::meta('finance-management', 'Expense', 'Update Expense', 20, 30),
            'finance.expense.delete' => self::meta('finance-management', 'Expense', 'Delete Expense', 20, 40),

            'finance.utility.create' => self::meta('finance-management', 'Utility Bills', 'Pay Bill / Add Bill Type', 30, 10),
            'finance.utility.view' => self::meta('finance-management', 'Utility Bills', 'Utility Bill Access', 30, 20),
            'finance.utility.update' => self::meta('finance-management', 'Utility Bills', 'Update Utility Bill', 30, 30),
            'finance.utility.delete' => self::meta('finance-management', 'Utility Bills', 'Delete Utility Bill', 30, 40),
            'finance.bill.create' => self::meta('finance-management', 'Utility Bills', 'Add New Bill', 30, 50),
            'finance.bill.view' => self::meta('finance-management', 'Utility Bills', 'Bills Register', 30, 60),
            'finance.bill.update' => self::meta('finance-management', 'Utility Bills', 'Update Bill', 30, 70),
            'finance.bill.delete' => self::meta('finance-management', 'Utility Bills', 'Delete Bill', 30, 80),

            'finance.rent.create' => self::meta('finance-management', 'Building Rent', 'Building Rent Setup', 40, 10),
            'finance.rent.view' => self::meta('finance-management', 'Building Rent', 'Building Rent Setup / Rent Expenses', 40, 20),
            'finance.rent.update' => self::meta('finance-management', 'Building Rent', 'Update Building Rent', 40, 30),
            'finance.rent.delete' => self::meta('finance-management', 'Building Rent', 'Delete Building Rent', 40, 40),

            'finance.payroll.create' => self::meta('finance-management', 'Payroll', 'Payroll', 50, 10),
            'finance.payroll.view' => self::meta('finance-management', 'Payroll', 'Payroll Records', 50, 20),
            'finance.payroll.update' => self::meta('finance-management', 'Payroll', 'Update Payroll', 50, 30),
            'finance.payroll.delete' => self::meta('finance-management', 'Payroll', 'Delete Payroll', 50, 40),

            'finance.payee.create' => self::meta('finance-management', 'Supplier & Payee', 'Supplier & Payee', 60, 10),
            'finance.payee.view' => self::meta('finance-management', 'Supplier & Payee', 'Supplier & Payee Records', 60, 20),
            'finance.payee.update' => self::meta('finance-management', 'Supplier & Payee', 'Update Supplier / Payee', 60, 30),
            'finance.payee.delete' => self::meta('finance-management', 'Supplier & Payee', 'Delete Supplier / Payee', 60, 40),

            'finance.payable.view' => self::meta('finance-management', 'Payables', 'Payables', 70, 10),
            'finance.payable.create' => self::meta('finance-management', 'Payables', 'Create Payable', 70, 20),
            'finance.payable.update' => self::meta('finance-management', 'Payables', 'Update Payable', 70, 30),
            'finance.payable.delete' => self::meta('finance-management', 'Payables', 'Delete Payable', 70, 40),

            'finance.receivable.view' => self::meta('finance-management', 'Receivables', 'Invoices / Receivables', 80, 10),
            'finance.receivable.create' => self::meta('finance-management', 'Receivables', 'Create Invoice / Receivable', 80, 20),
            'finance.receivable.update' => self::meta('finance-management', 'Receivables', 'Update Invoice / Receivable', 80, 30),
            'finance.receivable.delete' => self::meta('finance-management', 'Receivables', 'Delete Invoice / Receivable', 80, 40),

            'hrm_dashboard.view' => self::meta('human-resources', 'HRM Dashboard', 'HRM Dashboard', 10, 10),
            'hrm_employee.view' => self::meta('human-resources', 'Employee Master / Profile', 'Employee Master / Profile', 20, 10),
            'hrm_employee.create' => self::meta('human-resources', 'Employee Master / Profile', 'Create Employee', 20, 20),
            'hrm_employee.update' => self::meta('human-resources', 'Employee Master / Profile', 'Update Employee', 20, 30),
            'hrm_employee.delete' => self::meta('human-resources', 'Employee Master / Profile', 'Delete Employee', 20, 40),
            'hrm_employee.manage_status' => self::meta('human-resources', 'Employee Master / Profile', 'Manage Employee Status', 20, 50),

            'hrm_department.view' => self::meta('human-resources', 'Departments / Designations / Holidays', 'Department Records', 30, 10),
            'hrm_department.create' => self::meta('human-resources', 'Departments / Designations / Holidays', 'Create Department', 30, 20),
            'hrm_designation.create' => self::meta('human-resources', 'Departments / Designations / Holidays', 'Create Designation', 30, 30),
            'hrm_holiday.view' => self::meta('human-resources', 'Departments / Designations / Holidays', 'Holiday Records', 30, 40),
            'hrm_holiday.manage' => self::meta('human-resources', 'Departments / Designations / Holidays', 'Manage Holidays', 30, 50),
            'hrm_master.view' => self::meta('human-resources', 'Departments / Designations / Holidays', 'Master Records', 30, 60),
            'hrm_master.create' => self::meta('human-resources', 'Departments / Designations / Holidays', 'Create Master Record', 30, 70),
            'hrm_master.update' => self::meta('human-resources', 'Departments / Designations / Holidays', 'Update Master Record', 30, 80),
            'hrm_master.delete' => self::meta('human-resources', 'Departments / Designations / Holidays', 'Delete Master Record', 30, 90),

            'hrm_attendance.view' => self::meta('human-resources', 'Attendance (Daily)', 'Attendance (Daily)', 40, 10),
            'hrm_attendance.checkin' => self::meta('human-resources', 'Attendance (Daily)', 'Check In', 40, 20),
            'hrm_attendance.checkout' => self::meta('human-resources', 'Attendance (Daily)', 'Check Out', 40, 30),
            'hrm_attendance.request' => self::meta('human-resources', 'Attendance (Daily)', 'Attendance Requests', 40, 40),
            'hrm_attendance.approve' => self::meta('human-resources', 'Attendance (Daily)', 'Approve Attendance', 40, 50),
            'hrm_attendance.import' => self::meta('human-resources', 'Attendance (Daily)', 'Import Attendance', 40, 60),

            'hrm_leave.view' => self::meta('human-resources', 'Leave Management', 'Leave Management', 50, 10),
            'hrm_leave.request' => self::meta('human-resources', 'Leave Management', 'Leave Requests', 50, 20),
            'hrm_leave.approve' => self::meta('human-resources', 'Leave Management', 'Approve Leave', 50, 30),
            'hrm_leave.manage_type' => self::meta('human-resources', 'Leave Management', 'Manage Leave Types', 50, 40),
            'hrm_leave.manage_balance' => self::meta('human-resources', 'Leave Management', 'Manage Leave Balances', 50, 50),

            'hrm_payroll.view' => self::meta('human-resources', 'Payroll', 'Payroll', 60, 10),
            'hrm_payroll.process' => self::meta('human-resources', 'Payroll', 'Process Payroll', 60, 20),
            'hrm_payroll.close' => self::meta('human-resources', 'Payroll', 'Close Payroll', 60, 30),
            'hrm_payroll.manage_structure' => self::meta('human-resources', 'Payroll', 'Manage Payroll Structure', 60, 40),

            'hrm_shift.view' => self::meta('human-resources', 'Shift / Timetable', 'Shift / Timetable', 70, 10),
            'hrm_shift.manage' => self::meta('human-resources', 'Shift / Timetable', 'Manage Shifts', 70, 20),
            'hrm_shift.assign' => self::meta('human-resources', 'Shift / Timetable', 'Assign Shifts', 70, 30),

            'hrm_announcement.view' => self::meta('human-resources', 'Announcements / Notifications', 'Announcements / Notifications', 80, 10),
            'hrm_announcement.create' => self::meta('human-resources', 'Announcements / Notifications', 'Create Announcement', 80, 20),
            'hrm_announcement.publish' => self::meta('human-resources', 'Announcements / Notifications', 'Publish Announcement', 80, 30),

            'hrm_document.view' => self::meta('human-resources', 'Documents', 'Documents', 90, 10),
            'hrm_document.upload' => self::meta('human-resources', 'Documents', 'Upload Document', 90, 20),
            'hrm_document.manage' => self::meta('human-resources', 'Documents', 'Manage Documents', 90, 30),

            'report.leads' => self::meta('reports', null, 'Lead Reports', 10, 10),
            'report.admissions' => self::meta('reports', null, 'Admission Reports', 10, 20),
            'report.students' => self::meta('reports', null, 'Student Reports', 10, 30),
            'report.batches' => self::meta('reports', null, 'Batch Reports', 10, 40),
            'report.programs' => self::meta('reports', null, 'Programme Reports', 10, 50),
            'report.campuses' => self::meta('reports', null, 'Campus Reports', 10, 60),
            'report.hr' => self::meta('reports', null, 'HR Reports', 10, 70),
            'report.finance' => self::meta('reports', null, 'Finance Reports', 10, 80),
            'report.marketing' => self::meta('reports', null, 'Marketing Reports', 10, 90),
            'report.events' => self::meta('reports', null, 'Event Reports', 10, 100),
            'report.certificates' => self::meta('reports', null, 'Certificate Reports', 10, 110),

            default => self::fallbackMeta($permission),
        };
    }

    /**
     * @return array{moduleKey: string, section: string|null, sectionOrder: int, order: int, label: string}
     */
    private static function fallbackMeta(Permission $permission): array
    {
        return self::meta(
            self::moduleKey($permission),
            self::sectionLabel($permission),
            self::displayLabel($permission),
            self::sectionSortOrder($permission),
            900
        );
    }

    /**
     * @return array{moduleKey: string, section: string|null, sectionOrder: int, order: int, label: string}
     */
    private static function meta(string $moduleKey, ?string $section, string $label, int $sectionOrder, int $order): array
    {
        return [
            'moduleKey' => $moduleKey,
            'section' => $section,
            'sectionOrder' => $sectionOrder,
            'order' => $order,
            'label' => $label,
        ];
    }

    private static function sectionLabel(Permission $permission): ?string
    {
        return match (true) {
            str_starts_with(self::canonicalKey($permission), 'finance.dashboard.') => 'Dashboard',
            str_starts_with(self::canonicalKey($permission), 'finance.expense.') => 'Expense',
            str_starts_with(self::canonicalKey($permission), 'finance.bill.') || str_starts_with(self::canonicalKey($permission), 'finance.utility.') => 'Utility Bills',
            str_starts_with(self::canonicalKey($permission), 'finance.rent.') => 'Building Rent',
            str_starts_with(self::canonicalKey($permission), 'finance.payroll.') => 'Payroll',
            str_starts_with(self::canonicalKey($permission), 'finance.payee.') => 'Supplier & Payee',
            str_starts_with(self::canonicalKey($permission), 'finance.payable.') => 'Payables',
            str_starts_with(self::canonicalKey($permission), 'finance.receivable.') => 'Receivables',

            str_starts_with(self::canonicalKey($permission), 'hrm_dashboard.') => 'HRM Dashboard',
            str_starts_with(self::canonicalKey($permission), 'hrm_employee.') => 'Employee Master / Profile',
            str_starts_with(self::canonicalKey($permission), 'hrm_department.')
                || str_starts_with(self::canonicalKey($permission), 'hrm_designation.')
                || str_starts_with(self::canonicalKey($permission), 'hrm_holiday.')
                || str_starts_with(self::canonicalKey($permission), 'hrm_master.') => 'Departments / Designations / Holidays',
            str_starts_with(self::canonicalKey($permission), 'hrm_attendance.') => 'Attendance (Daily)',
            str_starts_with(self::canonicalKey($permission), 'hrm_leave.') => 'Leave Management',
            str_starts_with(self::canonicalKey($permission), 'hrm_payroll.') => 'Payroll',
            str_starts_with(self::canonicalKey($permission), 'hrm_shift.') => 'Shift / Timetable',
            str_starts_with(self::canonicalKey($permission), 'hrm_announcement.') => 'Announcements / Notifications',
            str_starts_with(self::canonicalKey($permission), 'hrm_document.') => 'Documents',
            default => null,
        };
    }

    private static function sectionSortOrder(Permission $permission): int
    {
        return match (self::sectionLabel($permission)) {
            null => 10,
            'Dashboard', 'HRM Dashboard' => 10,
            'Employee Master / Profile', 'Expense' => 20,
            'Training Leads' => 20,
            'Coworking Space' => 30,
            'Web Leads' => 40,
            'Batches' => 10,
            'Time Table' => 20,
            'Users' => 10,
            'Roles' => 20,
            'Permissions' => 30,
            'Utility Bills' => 30,
            'Building Rent' => 40,
            'Payroll' => 50,
            'Supplier & Payee' => 60,
            'Payables' => 70,
            'Receivables' => 80,
            'Departments / Designations / Holidays' => 30,
            'Attendance (Daily)' => 40,
            'Leave Management' => 50,
            'Shift / Timetable' => 60,
            'Announcements / Notifications' => 70,
            'Documents' => 80,
            default => 90,
        };
    }

    private static function moduleSortOrder(string $moduleKey): int
    {
        return [
            'dashboard' => 10,
            'lead-management' => 20,
            'registration-management' => 30,
            'admission-management' => 40,
            'student-management' => 50,
            'batch-management' => 60,
            'programme-management' => 70,
            'campus-management' => 80,
            'human-resources' => 90,
            'finance-management' => 100,
            'inventory-management' => 110,
            'certificate-management' => 120,
            'user-management' => 130,
            'reports' => 140,
        ][$moduleKey] ?? 900;
    }

    private static function priority(Permission $permission): int
    {
        if (str_starts_with($permission->resource, 'hrm_')) {
            return 0;
        }

        if (str_starts_with($permission->resource, 'hrm.')) {
            return 1;
        }

        return 0;
    }

    private static function canonicalKey(Permission $permission): string
    {
        $resource = $permission->resource;

        if (str_starts_with($resource, 'hrm.')) {
            $resource = str_replace('hrm.', 'hrm_', $resource);
        }

        return $resource . '.' . $permission->action;
    }

    private static function moduleKey(Permission $permission): string
    {
        return match (true) {
            $permission->resource === 'dashboard' => 'dashboard',
            $permission->resource === 'lead' => 'lead-management',
            $permission->resource === 'web-lead' => 'lead-management',
            $permission->resource === 'registration' => 'registration-management',
            $permission->resource === 'admission' => 'admission-management',
            $permission->resource === 'student' => 'student-management',
            $permission->resource === 'inventory' => 'inventory-management',
            $permission->resource === 'batch' || $permission->resource === 'batch-timetable' => 'batch-management',
            $permission->resource === 'program' => 'programme-management',
            $permission->resource === 'campus' => 'campus-management',
            $permission->resource === 'certificate' => 'certificate-management',
            $permission->resource === 'user' || $permission->resource === 'role' || $permission->resource === 'permission' => 'user-management',
            $permission->resource === 'report' => 'reports',
            str_starts_with($permission->resource, 'finance.') => 'finance-management',
            str_starts_with($permission->resource, 'hrm_') || str_starts_with($permission->resource, 'hrm.') => 'human-resources',
            default => str_replace(['.', '_'], '-', $permission->resource),
        };
    }

    private static function moduleLabel(string $moduleKey): string
    {
        return [
            'dashboard' => 'Dashboard',
            'lead-management' => 'Lead Management',
            'registration-management' => 'Registration Management',
            'admission-management' => 'Admission Management',
            'student-management' => 'Student Management',
            'inventory-management' => 'Inventory Management',
            'batch-management' => 'Batches & Time Table',
            'programme-management' => 'Programmes',
            'campus-management' => 'Campuses / Franchise',
            'certificate-management' => 'Certificate Management',
            'user-management' => 'User Management',
            'reports' => 'Reports',
            'finance-management' => 'Finance Management',
            'human-resources' => 'Human Resources',
        ][$moduleKey] ?? Str::headline(str_replace('-', ' ', $moduleKey));
    }

    private static function displayLabel(Permission $permission): string
    {
        if ($permission->resource === 'lead' && $permission->action === 'coworking.view') {
            return 'View Coworking Follow-up';
        }

        return match ($permission->action) {
            'view' => 'View ' . self::resourceLabel($permission),
            'create' => 'Create ' . self::resourceLabel($permission),
            'update' => 'Update ' . self::resourceLabel($permission),
            'delete' => 'Delete ' . self::resourceLabel($permission),
            'manage' => 'Manage ' . self::resourceLabel($permission),
            'approve' => 'Approve ' . self::resourceLabel($permission),
            'reject' => 'Reject ' . self::resourceLabel($permission),
            'publish' => 'Publish ' . self::resourceLabel($permission),
            'assign' => 'Assign ' . self::resourceLabel($permission),
            'request' => 'Request ' . self::resourceLabel($permission),
            'process' => 'Process ' . self::resourceLabel($permission),
            'close' => 'Close ' . self::resourceLabel($permission),
            'import' => 'Import ' . self::resourceLabel($permission),
            'upload' => 'Upload ' . self::resourceLabel($permission),
            'manage_status' => 'Manage Status',
            'manage_type' => 'Manage Types',
            'manage_balance' => 'Manage Balances',
            'manage_structure' => 'Manage Structure',
            'checkin' => 'Check In',
            'checkout' => 'Check Out',
            'followup.view' => 'View Lead Follow-up',
            'followup.update' => 'Manage Lead Follow-up',
            'transfer.approve' => 'Approve Lead Transfer',
            'send-to-printing' => 'Send To Printing',
            'mark-ready' => 'Mark Ready',
            'mark-delivered' => 'Mark Delivered',
            default => Str::headline(str_replace(['.', '_', '-'], ' ', $permission->action)),
        };
    }

    private static function resourceLabel(Permission $permission): string
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
