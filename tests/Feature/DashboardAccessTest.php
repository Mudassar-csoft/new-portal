<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Campus;
use App\Models\Lead;
use App\Models\Registration;
use App\Models\User;
use App\Models\User\Permission;
use App\Models\User\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_dashboard_is_limited_to_the_users_own_campus(): void
    {
        $dashboardView = $this->createPermission('dashboard', 'view', 'dashboard.view');
        $leadView = $this->createPermission('lead', 'view', 'lead.view');
        $registrationView = $this->createPermission('registration', 'view', 'registration.view');
        $admissionView = $this->createPermission('admission', 'view', 'admission.view');

        $alphaCampus = $this->createCampus('Alpha Campus', 'ALP');
        $betaCampus = $this->createCampus('Beta Campus', 'BET');

        $user = User::factory()->create([
            'campus_id' => $alphaCampus->id,
        ]);
        $user->permissions()->sync([
            $dashboardView->id,
            $leadView->id,
            $registrationView->id,
            $admissionView->id,
        ]);

        $this->seedDashboardRecords($alphaCampus, 'alpha', 1200);
        $this->seedDashboardRecords($betaCampus, 'beta', 1800);

        $this->actingAs($user)
            ->get(route('dashboard.live-data', ['campus_id' => $betaCampus->id]))
            ->assertOk()
            ->assertJsonPath('dashboard.stats.todayLeads', 0)
            ->assertJsonPath('dashboard.stats.totalLeads', 1)
            ->assertJsonPath('dashboard.stats.currentStudents', 1)
            ->assertJsonPath('dashboard.stats.currentMonthCollectionRaw', 1200);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('ALP-Alpha Campus')
            ->assertDontSee('BET-Beta Campus')
            ->assertDontSee(route('dashboard', ['campus_id' => 0]));
    }

    public function test_admin_dashboard_can_switch_between_all_campuses_and_a_single_campus(): void
    {
        $admin = $this->createAdminUser();
        $alphaCampus = $this->createCampus('Alpha Campus', 'ALP');
        $betaCampus = $this->createCampus('Beta Campus', 'BET');

        $this->seedDashboardRecords($alphaCampus, 'alpha', 1200);
        $this->seedDashboardRecords($betaCampus, 'beta', 1800);

        $this->actingAs($admin)
            ->get(route('dashboard.live-data', ['campus_id' => 0]))
            ->assertOk()
            ->assertJsonPath('dashboard.stats.todayLeads', 0)
            ->assertJsonPath('dashboard.stats.totalLeads', 2)
            ->assertJsonPath('dashboard.stats.currentStudents', 2)
            ->assertJsonPath('dashboard.stats.currentMonthCollectionRaw', 3000);

        $this->actingAs($admin)
            ->get(route('dashboard.live-data', ['campus_id' => $betaCampus->id]))
            ->assertOk()
            ->assertJsonPath('dashboard.stats.todayLeads', 0)
            ->assertJsonPath('dashboard.stats.totalLeads', 1)
            ->assertJsonPath('dashboard.stats.currentStudents', 1)
            ->assertJsonPath('dashboard.stats.currentMonthCollectionRaw', 1800);

        $this->actingAs($admin)
            ->get(route('dashboard', ['campus_id' => 0]))
            ->assertOk()
            ->assertSee(route('dashboard', ['campus_id' => 0]))
            ->assertSee('ALP-Alpha Campus')
            ->assertSee('BET-Beta Campus');
    }

    public function test_dashboard_hides_registration_and_admission_sections_without_permission(): void
    {
        $dashboardView = $this->createPermission('dashboard', 'view', 'dashboard.view');
        $leadView = $this->createPermission('lead', 'view', 'lead.view');

        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $user = User::factory()->create([
            'campus_id' => $campus->id,
        ]);
        $user->permissions()->sync([
            $dashboardView->id,
            $leadView->id,
        ]);

        $this->seedDashboardRecords($campus, 'alpha', 1200);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Today Leads')
            ->assertSee(route('leads.index', ['today' => 1]))
            ->assertSee('Recent Leads')
            ->assertSee('Current Month Leads')
            ->assertSee('Pending Recovery')
            ->assertDontSee('Current Students')
            ->assertDontSee('Recent Admissions')
            ->assertDontSee('Today Collection')
            ->assertDontSee(now()->format('F') . ' Collection');

        $this->actingAs($user)
            ->get(route('dashboard.live-data'))
            ->assertOk()
            ->assertJsonPath('dashboard.stats.currentStudents', 0)
            ->assertJsonPath('dashboard.stats.currentMonthAdmissions', 0)
            ->assertJsonPath('dashboard.stats.currentMonthCollectionRaw', 0)
            ->assertJsonPath('dashboard.admissionsActivity.rows', []);
    }

    public function test_training_dashboard_lead_count_excludes_coworking_leads(): void
    {
        $dashboardView = $this->createPermission('dashboard', 'view', 'dashboard.view');
        $leadView = $this->createPermission('lead', 'view', 'lead.view');

        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $user = User::factory()->create([
            'campus_id' => $campus->id,
        ]);
        $user->permissions()->sync([
            $dashboardView->id,
            $leadView->id,
        ]);

        $this->seedDashboardRecords($campus, 'alpha', 1200);
        $this->seedCoworkingLead($campus, 'cw-1');
        $this->seedCoworkingLead($campus, 'cw-2');
        $this->seedCoworkingLead($campus, 'cw-3');

        $this->actingAs($user)
            ->get(route('dashboard.live-data'))
            ->assertOk()
            ->assertJsonPath('dashboard.stats.todayLeads', 0)
            ->assertJsonPath('dashboard.stats.totalLeads', 1)
            ->assertJsonCount(1, 'dashboard.dailyActivity.rows');
    }

    public function test_dashboard_today_leads_link_opens_only_todays_leads_with_actions(): void
    {
        $dashboardView = $this->createPermission('dashboard', 'view', 'dashboard.view');
        $leadView = $this->createPermission('lead', 'view', 'lead.view');

        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $otherCampus = $this->createCampus('Beta Campus', 'BET');
        $user = User::factory()->create([
            'campus_id' => $campus->id,
        ]);
        $user->permissions()->sync([
            $dashboardView->id,
            $leadView->id,
        ]);

        $todayLead = Lead::query()->create([
            'campus_id' => $campus->id,
            'type' => 'training',
            'name' => 'Today Lead',
            'phone' => '03000000111',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $yesterdayLead = Lead::query()->create([
            'campus_id' => $campus->id,
            'type' => 'training',
            'name' => 'Yesterday Lead',
            'phone' => '03000000112',
            'status' => 'pending',
        ]);
        $yesterdayLead->forceFill([
            'created_at' => now()->subDay(),
            'updated_at' => now(),
        ])->saveQuietly();

        Lead::query()->create([
            'campus_id' => $otherCampus->id,
            'type' => 'training',
            'name' => 'Other Campus Lead',
            'phone' => '03000000113',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard.live-data'))
            ->assertOk()
            ->assertJsonPath('dashboard.stats.todayLeads', 1)
            ->assertJsonPath('dashboard.stats.totalLeads', 2);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('leads.index', ['today' => 1]));

        $this->actingAs($user)
            ->get(route('leads.index', ['today' => 1]))
            ->assertOk()
            ->assertSee('Showing today&apos;s leads only.', false)
            ->assertSee('Today Lead')
            ->assertSee(route('leads.show', $todayLead))
            ->assertDontSee('Yesterday Lead')
            ->assertDontSee('Other Campus Lead');
    }

    public function test_dashboard_recent_leads_show_name_as_link_and_date_without_time(): void
    {
        $dashboardView = $this->createPermission('dashboard', 'view', 'dashboard.view');
        $leadView = $this->createPermission('lead', 'view', 'lead.view');

        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $user = User::factory()->create([
            'campus_id' => $campus->id,
        ]);
        $user->permissions()->sync([
            $dashboardView->id,
            $leadView->id,
        ]);

        $createdAt = now()->copy()->setDate(2026, 6, 10)->setTime(14, 26, 0);
        $lead = Lead::query()->create([
            'campus_id' => $campus->id,
            'type' => 'training',
            'name' => 'Linked Dashboard Lead',
            'phone' => '03000000999',
            'status' => 'pending',
        ]);
        $lead->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->saveQuietly();

        $this->actingAs($user)
            ->get(route('dashboard.live-data'))
            ->assertOk()
            ->assertJsonPath('dashboard.dailyActivity.rows.0.student_name', 'Linked Dashboard Lead')
            ->assertJsonPath('dashboard.dailyActivity.rows.0.detail_url', route('leads.show', $lead))
            ->assertJsonPath('dashboard.dailyActivity.rows.0.date_label', '10-Jun-2026');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('leads.show', $lead))
            ->assertSee('Linked Dashboard Lead')
            ->assertSee('10-Jun-2026');
    }

    public function test_dashboard_recent_admissions_show_name_as_student_registration_link(): void
    {
        $dashboardView = $this->createPermission('dashboard', 'view', 'dashboard.view');
        $admissionView = $this->createPermission('admission', 'view', 'admission.view');
        $studentView = $this->createPermission('student', 'view', 'student.view');

        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $user = User::factory()->create([
            'campus_id' => $campus->id,
        ]);
        $user->permissions()->sync([
            $dashboardView->id,
            $admissionView->id,
            $studentView->id,
        ]);

        $this->seedDashboardRecords($campus, 'gamma', 2200);

        $registration = Registration::query()->where('campus_id', $campus->id)->latest('id')->firstOrFail();

        $this->actingAs($user)
            ->get(route('dashboard.live-data'))
            ->assertOk()
            ->assertJsonPath('dashboard.admissionsActivity.rows.0.student_name', 'Student gamma')
            ->assertJsonPath('dashboard.admissionsActivity.rows.0.detail_url', route('student.show', $registration))
            ->assertJsonPath('dashboard.admissionsActivity.rows.0.date_label', now()->format('d-M-Y'));

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('student.show', $registration))
            ->assertSee('Student gamma');
    }

    public function test_dashboard_recent_leads_use_student_registration_link_for_registered_and_enrolled_statuses(): void
    {
        $dashboardView = $this->createPermission('dashboard', 'view', 'dashboard.view');
        $leadView = $this->createPermission('lead', 'view', 'lead.view');
        $studentView = $this->createPermission('student', 'view', 'student.view');

        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $user = User::factory()->create([
            'campus_id' => $campus->id,
        ]);
        $user->permissions()->sync([
            $dashboardView->id,
            $leadView->id,
            $studentView->id,
        ]);

        $this->seedDashboardRecords($campus, 'delta', 2400);

        $lead = Lead::query()->where('campus_id', $campus->id)->latest('id')->firstOrFail();
        $registration = Registration::query()->where('campus_id', $campus->id)->latest('id')->firstOrFail();

        foreach (['registered', 'enrolled'] as $status) {
            $lead->update(['status' => $status]);

            $this->actingAs($user)
                ->get(route('dashboard.live-data'))
                ->assertOk()
                ->assertJsonPath('dashboard.dailyActivity.rows.0.student_name', 'Lead delta')
                ->assertJsonPath('dashboard.dailyActivity.rows.0.detail_url', route('student.show', $registration));
        }

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('student.show', $registration))
            ->assertDontSee(route('leads.show', $lead), false);
    }

    private function createPermission(string $resource, string $action, string $slug): Permission
    {
        return Permission::query()->create([
            'resource' => $resource,
            'action' => $action,
            'slug' => $slug,
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

    private function seedDashboardRecords(Campus $campus, string $suffix, float $netPayable): void
    {
        $lead = Lead::query()->create([
            'campus_id' => $campus->id,
            'type' => 'training',
            'name' => 'Lead ' . $suffix,
            'phone' => '0300000000' . substr($suffix, 0, 1),
            'status' => 'pending',
        ]);
        $lead->forceFill([
            'created_at' => now()->subDay(),
            'updated_at' => now(),
        ])->saveQuietly();

        $registration = Registration::query()->create([
            'lead_id' => $lead->id,
            'campus_id' => $campus->id,
            'registration_number' => 'REG-' . strtoupper($suffix),
            'receipt_number' => 'RCT-' . strtoupper($suffix),
            'student_name' => 'Student ' . $suffix,
            'phone' => '0311111111' . substr($suffix, 0, 1),
            'email' => $suffix . '@example.test',
            'fee' => $netPayable,
            'discount' => 0,
            'net_payable' => $netPayable,
            'status' => 'registered',
            'registered_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Admission::query()->create([
            'registration_id' => $registration->id,
            'campus_id' => $campus->id,
            'student_name' => 'Student ' . $suffix,
            'phone' => '0322222222' . substr($suffix, 0, 1),
            'registration_number' => $registration->registration_number,
            'roll_number' => 'ROLL-' . strtoupper($suffix),
            'admission_date' => now()->toDateString(),
            'fee_package' => $netPayable,
            'discount_amount' => 0,
            'discount_percent' => 0,
            'discounted_fee' => $netPayable,
            'fee_type' => 'full',
            'student_status' => 'enrolled',
            'remarks' => 'Test record',
        ]);
    }

    private function seedCoworkingLead(Campus $campus, string $suffix): void
    {
        Lead::query()->create([
            'campus_id' => $campus->id,
            'type' => 'coworking',
            'name' => 'Coworking ' . $suffix,
            'phone' => '03333333333',
            'status' => 'pending',
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
