<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Campus;
use App\Models\Program;
use App\Models\Registration;
use App\Models\User;
use App\Models\User\Permission;
use App\Models\User\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CertificateRemarksPermissionTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('permissionSources')]
    public function test_permitted_non_admin_can_preview_and_edit_only_remarks(string $source): void
    {
        $admission = $this->createCertificateAdmission();
        $user = User::factory()->create(['campus_id' => $admission->campus_id]);
        $permission = $this->permission('update');

        if ($source === 'role') {
            $role = Role::query()->create(['name' => 'Certificate Officer', 'slug' => 'certificate-officer']);
            $role->permissions()->attach($permission);
            $user->roles()->attach($role);
        } else {
            $user->permissions()->attach($permission);
        }

        $this->assertFalse($user->isAdmin());

        $this->actingAs($user)
            ->get(route('certificate.edit', $admission))
            ->assertOk()
            ->assertSee('Original certificate remarks.')
            ->assertSee('Save Remarks');

        $this->put(route('certificate.update', $admission), [
            'remarks' => 'Updated by certificate officer.',
            'student_name' => 'Unexpected student change',
            'certificate_status' => Admission::CERTIFICATE_STATUS_DELIVERED,
        ])->assertRedirect(route('certificate.index'))->assertSessionHasNoErrors();

        $admission->refresh();
        $this->assertSame('Updated by certificate officer.', $admission->remarks);
        $this->assertSame('Certificate Student', $admission->student_name);
        $this->assertSame(Admission::CERTIFICATE_STATUS_REQUESTED, $admission->certificate_status);
    }

    public static function permissionSources(): array
    {
        return [['direct'], ['role']];
    }

    public function test_certificate_view_permission_does_not_allow_previewing_or_editing_remarks(): void
    {
        $admission = $this->createCertificateAdmission();
        $user = User::factory()->create(['campus_id' => $admission->campus_id]);
        $user->permissions()->attach($this->permission('view'));

        $this->actingAs($user)->get(route('certificate.index'))
            ->assertOk()
            ->assertDontSee('Preview / Edit Remarks')
            ->assertDontSee('Original certificate remarks.');

        $this->get(route('certificate.edit', $admission))->assertForbidden();
        $this->put(route('certificate.update', $admission), ['remarks' => 'Unauthorized change.'])
            ->assertForbidden();

        $this->assertSame('Original certificate remarks.', $admission->fresh()->remarks);
    }

    public function test_remarks_action_is_available_in_every_certificate_status_list(): void
    {
        $admission = $this->createCertificateAdmission();
        $user = User::factory()->create(['campus_id' => $admission->campus_id]);
        $user->permissions()->attach([$this->permission('view')->id, $this->permission('update')->id]);
        $this->actingAs($user);

        foreach (['all', ...Admission::CERTIFICATE_WORKFLOW_STATUSES] as $scope) {
            $admission->update([
                'certificate_status' => $scope === 'all' ? Admission::CERTIFICATE_STATUS_REQUESTED : $scope,
            ]);

            $this->get(route('certificate.index', ['scope' => $scope]))
                ->assertOk()
                ->assertSee('Preview / Edit Remarks')
                ->assertSee(route('certificate.edit', $admission), false);

            $this->get(route('certificate.edit', $admission))->assertOk();
        }
    }

    public function test_remarks_permission_does_not_allow_access_to_another_campus(): void
    {
        $admission = $this->createCertificateAdmission();
        $otherCampus = Campus::query()->create(['name' => 'Other Campus', 'slug' => 'other-campus', 'code' => 'OTH']);
        $user = User::factory()->create(['campus_id' => $otherCampus->id]);
        $user->permissions()->attach($this->permission('update'));

        $this->actingAs($user)->get(route('certificate.edit', $admission))->assertForbidden();
        $this->put(route('certificate.update', $admission), ['remarks' => 'Wrong campus.'])
            ->assertForbidden();

        $this->assertSame('Original certificate remarks.', $admission->fresh()->remarks);
    }

    #[DataProvider('adminRoles')]
    public function test_administrators_retain_remarks_access_without_explicit_permission(string $roleSlug): void
    {
        $admission = $this->createCertificateAdmission();
        $otherCampus = Campus::query()->create(['name' => 'Other Campus', 'slug' => 'other-campus', 'code' => 'OTH']);
        $admin = User::factory()->create(['campus_id' => $otherCampus->id]);
        $role = Role::query()->firstOrCreate(['slug' => $roleSlug], ['name' => ucfirst($roleSlug)]);
        $admin->roles()->attach($role);

        $this->actingAs($admin)->get(route('certificate.edit', $admission))->assertOk();
        $this->put(route('certificate.update', $admission), ['remarks' => 'Admin updated remarks.'])
            ->assertRedirect(route('certificate.index'));

        $this->assertSame('Admin updated remarks.', $admission->fresh()->remarks);
    }

    public static function adminRoles(): array
    {
        return [['admin'], ['owner']];
    }

    public function test_remarks_permission_does_not_allow_editing_admissions_outside_certificate_workflow(): void
    {
        $admission = $this->createCertificateAdmission();
        $admission->update(['certificate_status' => null]);
        $user = User::factory()->create(['campus_id' => $admission->campus_id]);
        $user->permissions()->attach($this->permission('update'));

        $this->actingAs($user)->get(route('certificate.edit', $admission))->assertNotFound();
        $this->put(route('certificate.update', $admission), ['remarks' => 'Not a certificate.'])
            ->assertNotFound();

        $this->assertSame('Original certificate remarks.', $admission->fresh()->remarks);
    }

    public function test_admin_can_find_grant_and_revoke_remarks_permission_in_user_settings(): void
    {
        $admission = $this->createCertificateAdmission();
        $user = User::factory()->create(['campus_id' => $admission->campus_id]);
        $admin = User::factory()->create();
        $adminRole = Role::query()->firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
        $admin->roles()->attach($adminRole);
        $view = $this->permission('view');
        $update = $this->permission('update');

        $this->actingAs($admin)->get(route('users.edit', $user))
            ->assertOk()
            ->assertSee('Certificate Management')
            ->assertSee('Preview / Edit Remarks');

        $this->get(route('roles.create'))
            ->assertOk()
            ->assertSee('Preview / Edit Remarks');

        $userDetails = [
            'name' => $user->name,
            'email_local' => strstr($user->email, '@', true),
            'campus_id' => $user->campus_id,
        ];

        $this->put(route('users.update', $user), [
            ...$userDetails,
            'permissions' => [$view->id, $update->id],
        ])->assertRedirect(route('users.index'))->assertSessionHasNoErrors();

        $this->actingAs($user->fresh())->get(route('certificate.edit', $admission))->assertOk();
        $this->put(route('certificate.update', $admission), ['remarks' => 'Granted access works.'])
            ->assertRedirect(route('certificate.index'));

        $this->actingAs($admin)->put(route('users.update', $user), [
            ...$userDetails,
            'permissions' => [$view->id],
        ])->assertRedirect(route('users.index'))->assertSessionHasNoErrors();

        $this->actingAs($user->fresh())->get(route('certificate.index'))
            ->assertOk()->assertDontSee('Preview / Edit Remarks');
        $this->get(route('certificate.edit', $admission))->assertForbidden();
        $this->put(route('certificate.update', $admission), ['remarks' => 'Revoked access.'])
            ->assertForbidden();

        $this->assertSame('Granted access works.', $admission->fresh()->remarks);
    }

    private function permission(string $action): Permission
    {
        return Permission::query()->firstOrCreate(
            ['slug' => 'certificate.'.$action],
            ['resource' => 'certificate', 'action' => $action]
        );
    }

    private function createCertificateAdmission(): Admission
    {
        $campus = Campus::query()->create(['name' => 'Main Campus', 'slug' => 'main-campus', 'code' => 'MAIN']);
        $program = Program::query()->create([
            'name' => 'Certificate Program',
            'title' => 'Certificate Program',
            'code' => 'CP101',
            'program_type' => 'bootcamp',
            'fee' => 50000,
            'status' => 'active',
        ]);
        $registration = Registration::query()->create([
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'registration_number' => 'MAIN-REG-001',
            'receipt_number' => 'MAIN-REC-001',
            'student_name' => 'Certificate Student',
            'registered_at' => now(),
        ]);

        return Admission::query()->create([
            'registration_id' => $registration->id,
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'student_name' => $registration->student_name,
            'registration_number' => $registration->registration_number,
            'roll_number' => 'MAIN-ROLL-001',
            'admission_date' => now()->toDateString(),
            'fee_package' => 50000,
            'discount_amount' => 0,
            'discount_percent' => 0,
            'discounted_fee' => 50000,
            'fee_type' => 'full',
            'student_status' => 'completed',
            'certificate_status' => Admission::CERTIFICATE_STATUS_REQUESTED,
            'approval_status' => Admission::APPROVAL_STATUS_APPROVED,
            'status_updated_at' => now(),
            'remarks' => 'Original certificate remarks.',
        ]);
    }
}
