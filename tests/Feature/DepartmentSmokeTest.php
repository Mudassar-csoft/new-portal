<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\User\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DepartmentSmokeTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('departmentRoutes')]
    public function test_department_pages_render_for_admin_users(string $routeName, array $parameters = []): void
    {
        $this->actingAs($this->createAdminUser());

        $response = $this->get(route($routeName, $parameters));

        $response->assertOk();
    }

    public function test_profile_pages_do_not_render_sidebar_menu(): void
    {
        $this->actingAs($this->createAdminUser());

        $this->get(route('profile.show'))
            ->assertOk()
            ->assertDontSee('side-menu-list', false);

        $this->get(route('profile.change-password'))
            ->assertOk()
            ->assertDontSee('side-menu-list', false);
    }

    /**
     * @return array<string, array{0: string, 1?: array<string, mixed>}>
     */
    public static function departmentRoutes(): array
    {
        return [
            'dashboard' => ['dashboard'],
            'leads' => ['leads.index'],
            'web-leads' => ['web-leads.index'],
            'registrations' => ['registration.status'],
            'admissions' => ['admission.status'],
            'student-portal' => ['student.portal'],
            'student-records' => ['student.records.index'],
            'student-attendance' => ['student.attendance.index'],
            'inventory' => ['inventory.index'],
            'finance-dashboard' => ['finance.dashboard'],
            'finance-income' => ['finance.dashboard.income'],
            'finance-expense' => ['finance.dashboard.expense'],
            'finance-payables' => ['finance.dashboard.payables'],
            'finance-receivables' => ['finance.dashboard.receivables'],
            'finance-net-cashflow' => ['finance.dashboard.netcashflow'],
            'finance-expense-form' => ['finance.expense.add'],
            'finance-expense-list' => ['finance.expense.all'],
            'finance-payroll' => ['finance.expense.payroll'],
            'finance-utility-pay' => ['finance.utility.pay'],
            'finance-utility-bills' => ['finance.utility.bills'],
            'finance-utility-types' => ['finance.utility.types'],
            'finance-rent' => ['finance.rent.index'],
            'finance-payees' => ['finance.payees'],
            'finance-receivables-index' => ['finance.receivables'],
            'hrm-dashboard' => ['hrm.dashboard'],
            'hrm-employees' => ['hrm.employees.index'],
            'hrm-masters' => ['hrm.masters.index'],
            'hrm-shifts' => ['hrm.shifts.index'],
            'hrm-attendance' => ['hrm.attendance.index'],
            'hrm-leaves' => ['hrm.leaves.index'],
            'hrm-payroll' => ['hrm.payroll.index'],
            'hrm-announcements' => ['hrm.announcements.index'],
            'hrm-documents' => ['hrm.documents.index'],
            'certificates' => ['certificate.index'],
            'campuses' => ['campus.index'],
            'programs' => ['program.index'],
            'batches' => ['batch.index'],
            'batch-timetable' => ['batch.timetable.index'],
            'users' => ['users.index'],
            'roles' => ['roles.index'],
            'permissions' => ['permissions.index'],
            'profile' => ['profile.show'],
            'student-search' => ['student-search.index'],
            'login-logs' => ['login-logs.index'],
        ];
    }

    private function createAdminUser(): User
    {
        $user = User::factory()->create();
        $role = Role::query()->firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'description' => 'Admin',
                'is_system' => true,
            ]
        );

        $user->roles()->sync([
            $role->id => ['assigned_by' => $user->id],
        ]);

        return $user;
    }
}
