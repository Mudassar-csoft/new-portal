<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\HrEmployee;
use App\Models\User;
use App\Models\User\Permission;
use App\Models\User\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserPermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_user_form_hides_direct_permission_selection(): void
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

        Permission::query()->create([
            'resource' => 'lead',
            'action' => 'transfer.approve',
            'slug' => 'lead.transfer.approve',
        ]);

        Permission::query()->create([
            'resource' => 'lead',
            'action' => 'view',
            'slug' => 'lead.view',
        ]);

        $this->findOrCreatePermission('lead', 'coworking.view', 'lead.coworking.view');

        $response = $this->actingAs($admin)->get(route('users.create'));

        $response->assertOk();
        $response->assertSee('Full Name');
        $response->assertSee('Email Address');
        $response->assertSee('Campus');
        $response->assertSee('Password');
        $response->assertSee('Confirm Password');
        $response->assertSee('Select role');
        $response->assertDontSee('Custom Name');
        $response->assertDontSee('Direct Permissions');
        $response->assertDontSee('Permissions are assigned manually by module. Role-based access stays separate and is not auto-selected here.');
    }

    public function test_create_role_form_groups_permissions_by_module(): void
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

        Permission::query()->create([
            'resource' => 'lead',
            'action' => 'transfer.approve',
            'slug' => 'lead.transfer.approve',
        ]);

        Permission::query()->create([
            'resource' => 'lead',
            'action' => 'view',
            'slug' => 'lead.view',
        ]);

        $this->findOrCreatePermission('lead', 'coworking.view', 'lead.coworking.view');

        $response = $this->actingAs($admin)->get(route('roles.create'));

        $response->assertOk();
        $response->assertSee('Role Permissions');
        $response->assertSee('Lead Management');
        $response->assertSee('Create New Lead');
        $response->assertSee('Training Leads');
        $response->assertSee("Lead's Follow-up");
        $response->assertSee('Transferred Leads');
        $response->assertSee('All Leads');
        $response->assertSee('Coworking Space');
    }

    public function test_admin_can_create_role_and_assign_permissions(): void
    {
        $admin = $this->createAdminUser();

        $leadCreate = Permission::query()->create([
            'resource' => 'lead',
            'action' => 'create',
            'slug' => 'lead.create',
        ]);

        $leadCoworking = $this->findOrCreatePermission('lead', 'coworking.view', 'lead.coworking.view');

        $response = $this->actingAs($admin)->post(route('roles.store'), [
            'name' => 'Lead Supervisor',
            'slug' => 'lead-supervisor',
            'description' => 'Lead module supervisor',
            'permissions' => [$leadCreate->id, $leadCoworking->id],
        ]);

        $response->assertRedirect(route('roles.index'));

        $role = Role::query()->where('slug', 'lead-supervisor')->firstOrFail();

        $this->assertEqualsCanonicalizing(
            [$leadCreate->id, $leadCoworking->id],
            $role->permissions()->pluck('permissions.id')->all()
        );
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

        $campus = Campus::query()->create([
            'name' => 'Permissions Campus',
            'slug' => 'permissions-campus',
            'code' => 'PC',
        ]);
        $employee = $this->createPortalEmployee($campus, 'Permissions User', 'permissions@example.com');

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'employee_id' => $employee->id,
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
            'role_id' => $memberRole->id,
            'permissions' => [$leadCreate->id, $leadFollowup->id],
        ]);

        $response->assertRedirect(route('users.index'));

        $user = User::query()->where('email', 'permissions@example.com')->firstOrFail();

        $this->assertSame([$memberRole->id], $user->roles()->pluck('roles.id')->all());
        $this->assertEqualsCanonicalizing(
            [$leadCreate->id, $leadFollowup->id],
            $user->permissions()->pluck('permissions.id')->all()
        );
        Mail::assertNothingOutgoing();
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

    public function test_admin_can_edit_user_change_role_and_assign_all_campuses(): void
    {
        Mail::fake();

        $admin = $this->createAdminUser();
        $campus = Campus::query()->create([
            'name' => 'Main Campus',
            'slug' => 'main-campus',
            'code' => 'MC',
        ]);

        $oldRole = Role::query()->create([
            'name' => 'Old Role',
            'slug' => 'old-role',
            'description' => 'Old role',
        ]);

        $newRole = Role::query()->create([
            'name' => 'New Role',
            'slug' => 'new-role',
            'description' => 'New role',
        ]);

        $leadCoworking = $this->findOrCreatePermission('lead', 'coworking.view', 'lead.coworking.view');

        $user = User::factory()->create([
            'name' => 'Campus User',
            'email' => 'campus-user@example.com',
            'campus_id' => $campus->id,
        ]);

        $user->roles()->sync([
            $oldRole->id => ['assigned_by' => $admin->id],
        ]);

        $response = $this->actingAs($admin)->put(route('users.update', $user), [
            'name' => 'Campus User Updated',
            'email' => 'campus-user@example.com',
            'campus_id' => '',
            'roles' => [$newRole->id],
            'permissions' => [$leadCoworking->id],
        ]);

        $response->assertRedirect(route('users.index'));

        $user->refresh();

        $this->assertSame('Campus User Updated', $user->name);
        $this->assertNull($user->campus_id);
        $this->assertSame([$newRole->id], $user->roles()->pluck('roles.id')->all());
        $this->assertSame([$leadCoworking->id], $user->permissions()->pluck('permissions.id')->all());

        $listing = $this->actingAs($admin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('users.index', ['scope' => 'active']));

        $listing->assertOk();
        $listing->assertSee('All Campuses');
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

        $campus = Campus::query()->create([
            'name' => 'Admin Campus',
            'slug' => 'admin-campus',
            'code' => 'AC',
        ]);
        $employee = $this->createPortalEmployee($campus, 'Admin Permissions User', 'admin-permissions@example.com');

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'employee_id' => $employee->id,
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
            'role_id' => $adminRole->id,
            'permissions' => [$leadCreate->id],
        ]);

        $response->assertRedirect(route('users.index'));

        $user = User::query()->where('email', 'admin-permissions@example.com')->firstOrFail();

        $this->assertTrue($user->isAdmin());
        $this->assertEqualsCanonicalizing(
            Permission::query()->pluck('id')->all(),
            $user->permissions()->pluck('permissions.id')->all()
        );
        Mail::assertNothingOutgoing();
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

    public function test_sidebar_hides_modules_that_have_no_visible_destinations_for_the_users_permissions(): void
    {
        $dashboardView = Permission::query()->create([
            'resource' => 'dashboard',
            'action' => 'view',
            'slug' => 'dashboard.view',
        ]);

        $certificateApprove = Permission::query()->create([
            'resource' => 'certificate',
            'action' => 'approve',
            'slug' => 'certificate.approve',
        ]);

        $user = User::factory()->create();
        $user->permissions()->sync([$dashboardView->id, $certificateApprove->id]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('Certificate Management');
    }

    public function test_coworking_route_requires_dedicated_coworking_permission(): void
    {
        $trainingFollowup = Permission::query()->create([
            'resource' => 'lead',
            'action' => 'followup.view',
            'slug' => 'lead.followup.view',
        ]);

        $coworkingFollowup = $this->findOrCreatePermission('lead', 'coworking.view', 'lead.coworking.view');

        $trainingUser = User::factory()->create();
        $trainingUser->permissions()->sync([$trainingFollowup->id]);

        $coworkingUser = User::factory()->create();
        $coworkingUser->permissions()->sync([$coworkingFollowup->id]);

        $this->actingAs($trainingUser)
            ->get(route('leads.coworking.followups'))
            ->assertForbidden();

        $this->actingAs($coworkingUser)
            ->get(route('leads.coworking.followups'))
            ->assertOk();
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

    private function findOrCreatePermission(string $resource, string $action, string $slug): Permission
    {
        return Permission::query()->firstOrCreate(
            ['slug' => $slug],
            [
                'resource' => $resource,
                'action' => $action,
            ]
        );
    }

    private function createPortalEmployee(Campus $campus, string $fullName, string $email): HrEmployee
    {
        [$firstName, $lastName] = array_pad(explode(' ', $fullName, 2), 2, null);

        return HrEmployee::query()->create([
            'campus_id' => $campus->id,
            'portal_user' => true,
            'employee_code' => $campus->code . '-13-26-0001',
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'joining_date' => '2026-06-13',
            'employment_type' => 'full_time',
            'status' => 'active',
        ]);
    }
}
