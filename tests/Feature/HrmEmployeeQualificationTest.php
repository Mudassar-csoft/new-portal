<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\HrEmployee;
use App\Models\User;
use App\Models\User\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrmEmployeeQualificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_qualification_is_saved_and_filterable(): void
    {
        $admin = $this->createAdminUser();
        $campus = $this->createCampus('Faisalabad Campus', 'CIFSD01');

        $this->actingAs($admin)
            ->from(route('hrm.employees.index'))
            ->post(route('hrm.employees.store'), [
                'campus_id' => $campus->id,
                'portal_user' => '0',
                'first_name' => 'Ali',
                'last_name' => 'Khan',
                'joining_date' => '2026-06-13',
                'qualification' => 'MBA',
            ])
            ->assertRedirect(route('hrm.employees.index'))
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->from(route('hrm.employees.index'))
            ->post(route('hrm.employees.store'), [
                'campus_id' => $campus->id,
                'portal_user' => '0',
                'first_name' => 'Sara',
                'last_name' => 'Noor',
                'joining_date' => '2026-06-14',
                'qualification' => 'BSc',
            ])
            ->assertRedirect(route('hrm.employees.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('hr_employees', [
            'first_name' => 'Ali',
            'qualification' => 'MBA',
        ]);

        $this->actingAs($admin)
            ->get(route('hrm.employees.index', ['qualification' => 'MBA']))
            ->assertOk()
            ->assertSee('Filter Employees')
            ->assertSee('MBA')
            ->assertDontSee('BSc');
    }

    public function test_admin_can_update_employee_from_grid_edit_action(): void
    {
        $admin = $this->createAdminUser();
        $campus = $this->createCampus('Lahore Campus', 'CLHE01');

        $employee = HrEmployee::query()->create([
            'campus_id' => $campus->id,
            'employee_code' => 'CLHE01-01-26-0001',
            'first_name' => 'Zara',
            'last_name' => 'Iqbal',
            'joining_date' => '2026-06-01',
            'qualification' => 'B.Com',
            'employment_type' => 'full_time',
            'status' => 'active',
            'portal_user' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('hrm.employees.index'))
            ->assertOk()
            ->assertSee('Actions')
            ->assertSee('data-target="#editEmployeeModal-' . $employee->id . '"', false);

        $this->actingAs($admin)
            ->from(route('hrm.employees.index'))
            ->put(route('hrm.employees.update', $employee), [
                'campus_id' => $campus->id,
                'first_name' => 'Zara',
                'last_name' => 'Iqbal',
                'portal_user' => '1',
                'joining_date' => '2026-06-01',
                'qualification' => 'MBA Finance',
                'employment_type' => 'contract',
                'status' => 'inactive',
            ])
            ->assertRedirect(route('hrm.employees.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('hr_employees', [
            'id' => $employee->id,
            'qualification' => 'MBA Finance',
            'employment_type' => 'contract',
            'status' => 'inactive',
            'portal_user' => true,
        ]);
    }

    private function createCampus(string $name, string $code): Campus
    {
        return Campus::query()->create([
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'code' => $code,
        ]);
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
