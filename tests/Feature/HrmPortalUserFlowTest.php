<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\HrEmployee;
use App\Models\User;
use App\Models\User\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrmPortalUserFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_user_employee_appears_in_user_create_dropdown_and_is_linked_on_user_creation(): void
    {
        $admin = $this->createAdminUser();
        $role = Role::query()->create([
            'name' => 'Member',
            'slug' => 'member',
            'description' => 'Member role',
        ]);

        $campus = $this->createCampus('Faisalabad Campus', 'CIFSD01');

        $portalEmployee = HrEmployee::query()->create([
            'campus_id' => $campus->id,
            'portal_user' => true,
            'employee_code' => 'CIFSD01-13-26-0001',
            'first_name' => 'Ali',
            'last_name' => 'Khan',
            'joining_date' => '2026-06-13',
            'employment_type' => 'full_time',
            'status' => 'active',
        ]);

        HrEmployee::query()->create([
            'campus_id' => $campus->id,
            'portal_user' => false,
            'employee_code' => 'CIFSD01-13-26-0002',
            'first_name' => 'Sara',
            'last_name' => 'Noor',
            'joining_date' => '2026-06-13',
            'employment_type' => 'full_time',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('users.create'))
            ->assertOk()
            ->assertSee('Ali Khan')
            ->assertDontSee('Sara Noor');

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'employee_id' => $portalEmployee->id,
                'email_local' => 'ali.khan',
                'password' => 'secret12345',
                'password_confirmation' => 'secret12345',
                'role_id' => $role->id,
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHasNoErrors();

        $user = User::query()->where('email', 'ali.khan@career.edu.pk')->firstOrFail();

        $this->assertSame('Ali Khan', $user->name);
        $this->assertSame($campus->id, $user->campus_id);
        $this->assertSame([$role->id], $user->roles()->pluck('roles.id')->all());

        $portalEmployee->refresh();
        $this->assertSame($user->id, $portalEmployee->user_id);
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
