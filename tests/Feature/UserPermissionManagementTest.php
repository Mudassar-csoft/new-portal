<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\User\Permission;
use App\Models\User\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserPermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_user_form_groups_permissions_by_module(): void
    {
        $admin = $this->createAdminUser();

        Permission::query()->create([
            'resource' => 'lead',
            'action' => 'create',
            'slug' => 'lead.create',
        ]);

        Permission::query()->create([
            'resource' => 'lead',
            'action' => 'followup.view',
            'slug' => 'lead.followup.view',
        ]);

        $response = $this->actingAs($admin)->get(route('users.create'));

        $response->assertOk();
        $response->assertSee('Lead Management');
        $response->assertSee('Training Leads');
        $response->assertSee('Create Lead');
        $response->assertSee('View Follow-up');
    }

    public function test_admin_can_assign_direct_permissions_when_creating_a_user(): void
    {
        Mail::fake();

        $admin = $this->createAdminUser();
        $memberRole = Role::query()->create([
            'name' => 'Member',
            'slug' => 'member',
            'description' => 'Member role',
        ]);

        $leadCreate = Permission::query()->create([
            'resource' => 'lead',
            'action' => 'create',
            'slug' => 'lead.create',
        ]);

        $leadFollowup = Permission::query()->create([
            'resource' => 'lead',
            'action' => 'followup.view',
            'slug' => 'lead.followup.view',
        ]);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Permissions User',
            'email' => 'permissions@example.com',
            'roles' => [$memberRole->id],
            'permissions' => [$leadCreate->id, $leadFollowup->id],
        ]);

        $response->assertRedirect(route('users.index'));

        $user = User::query()->where('email', 'permissions@example.com')->firstOrFail();

        $this->assertSame([$memberRole->id], $user->roles()->pluck('roles.id')->all());
        $this->assertEqualsCanonicalizing(
            [$leadCreate->id, $leadFollowup->id],
            $user->permissions()->pluck('permissions.id')->all()
        );
    }

    public function test_admin_can_update_direct_permissions_for_an_existing_user(): void
    {
        Mail::fake();

        $admin = $this->createAdminUser();
        $memberRole = Role::query()->create([
            'name' => 'Member',
            'slug' => 'member',
            'description' => 'Member role',
        ]);

        $leadCreate = Permission::query()->create([
            'resource' => 'lead',
            'action' => 'create',
            'slug' => 'lead.create',
        ]);

        $leadFollowup = Permission::query()->create([
            'resource' => 'lead',
            'action' => 'followup.view',
            'slug' => 'lead.followup.view',
        ]);

        $inventoryView = Permission::query()->create([
            'resource' => 'inventory',
            'action' => 'view',
            'slug' => 'inventory.view',
        ]);

        $user = User::factory()->create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
        ]);

        $user->roles()->sync([
            $memberRole->id => ['assigned_by' => $admin->id],
        ]);
        $user->permissions()->sync([$leadCreate->id]);

        $response = $this->actingAs($admin)->put(route('users.update', $user), [
            'name' => 'Existing User Updated',
            'email' => 'existing@example.com',
            'roles' => [$memberRole->id],
            'permissions' => [$leadFollowup->id, $inventoryView->id],
        ]);

        $response->assertRedirect(route('users.index'));

        $user->refresh();

        $this->assertSame('Existing User Updated', $user->name);
        $this->assertEqualsCanonicalizing(
            [$leadFollowup->id, $inventoryView->id],
            $user->permissions()->pluck('permissions.id')->all()
        );
    }

    public function test_admin_role_users_receive_all_permissions_automatically_when_created(): void
    {
        Mail::fake();

        $admin = $this->createAdminUser();
        $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();

        $leadCreate = Permission::query()->create([
            'resource' => 'lead',
            'action' => 'create',
            'slug' => 'lead.create',
        ]);

        $financeDashboardView = Permission::query()->create([
            'resource' => 'finance.dashboard',
            'action' => 'view',
            'slug' => 'finance.dashboard.view',
        ]);

        $inventoryView = Permission::query()->create([
            'resource' => 'inventory',
            'action' => 'view',
            'slug' => 'inventory.view',
        ]);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Admin Permissions User',
            'email' => 'admin-permissions@example.com',
            'roles' => [$adminRole->id],
            'permissions' => [$leadCreate->id],
        ]);

        $response->assertRedirect(route('users.index'));

        $user = User::query()->where('email', 'admin-permissions@example.com')->firstOrFail();

        $this->assertTrue($user->isAdmin());
        $this->assertEqualsCanonicalizing(
            [$leadCreate->id, $financeDashboardView->id, $inventoryView->id],
            $user->permissions()->pluck('permissions.id')->all()
        );
    }

    public function test_user_without_module_permission_gets_403_and_navigation_hides_that_module(): void
    {
        $dashboardView = Permission::query()->create([
            'resource' => 'dashboard',
            'action' => 'view',
            'slug' => 'dashboard.view',
        ]);

        Permission::query()->create([
            'resource' => 'finance.dashboard',
            'action' => 'view',
            'slug' => 'finance.dashboard.view',
        ]);

        $user = User::factory()->create();
        $user->permissions()->sync([$dashboardView->id]);

        $dashboardResponse = $this->actingAs($user)->get(route('dashboard'));

        $dashboardResponse->assertOk();
        $dashboardResponse->assertDontSee('Finance Management');

        $forbiddenResponse = $this->actingAs($user)->get(route('finance.dashboard'));

        $forbiddenResponse->assertForbidden();
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
