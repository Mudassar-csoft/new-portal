<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\HrEmployee;
use App\Models\User;
use App\Models\User\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrmEmployeeCodeGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_code_is_auto_generated_from_campus_and_joining_date_and_ignores_manual_input(): void
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
                'employee_code' => 'MANUAL-OVERRIDE',
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
                'joining_date' => '2026-06-13',
                'employee_code' => 'SHOULD-NOT-SAVE',
            ])
            ->assertRedirect(route('hrm.employees.index'))
            ->assertSessionHasNoErrors();

        $codes = HrEmployee::query()
            ->orderBy('id')
            ->pluck('employee_code')
            ->all();

        $this->assertSame([
            'CIFSD01-13-26-0001',
            'CIFSD01-13-26-0002',
        ], $codes);

        $this->assertDatabaseMissing('hr_employees', [
            'employee_code' => 'MANUAL-OVERRIDE',
        ]);

        $this->assertDatabaseMissing('hr_employees', [
            'employee_code' => 'SHOULD-NOT-SAVE',
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
