<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Campus;
use App\Models\CoworkingRegistration;
use App\Models\CoworkingRegistrationReceipt;
use App\Models\FeeCollection;
use App\Models\Lead;
use App\Models\Registration;
use App\Models\User;
use App\Models\User\Permission;
use App\Models\User\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

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

    public function test_dashboard_collection_combines_registration_admission_and_coworking_amounts(): void
    {
        $admin = $this->createAdminUser();
        $campus = $this->createCampus('Gamma Campus', 'GAM');

        ['registration' => $registration, 'admission' => $admission] = $this->seedDashboardRecords($campus, 'gamma', 1200);
        $this->seedAdmissionFee($campus, $registration, $admission, 3400);
        $this->seedCoworkingChargeReceipt($campus, 5600);

        $response = $this->actingAs($admin)
            ->get(route('dashboard.live-data', ['campus_id' => $campus->id]))
            ->assertOk();

        $dashboard = $response->json('dashboard');
        $monthPoints = $dashboard['incomeRanges']['month']['points'] ?? [];
        $yearPoints = $dashboard['incomeRanges']['year']['points'] ?? [];
        $monthRangeTotal = array_sum(array_map(fn (array $point) => (float) ($point[1] ?? 0), $monthPoints));
        $yearRangeTotal = array_sum(array_map(fn (array $point) => (float) ($point[1] ?? 0), $yearPoints));

        $this->assertSame(10200.0, (float) ($dashboard['stats']['currentMonthCollectionRaw'] ?? 0));
        $this->assertSame(10200.0, (float) ($dashboard['incomeSummary']['today'] ?? 0));
        $this->assertSame(10200.0, (float) ($dashboard['incomeSummary']['week'] ?? 0));
        $this->assertSame(10200.0, (float) ($dashboard['incomeSummary']['month'] ?? 0));
        $this->assertSame(10200.0, $monthRangeTotal);
        $this->assertSame(10200.0, $yearRangeTotal);
    }

    public function test_dashboard_today_income_graph_counts_midnight_paid_rows_using_recorded_creation_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-20 14:30:00'));

        $admin = $this->createAdminUser();
        $campus = $this->createCampus('Delta Campus', 'DEL');

        ['registration' => $registration, 'admission' => $admission] = $this->seedDashboardRecords($campus, 'delta', 1500);
        $this->seedAdmissionFee($campus, $registration, $admission, 2500);
        $this->seedCoworkingChargeReceipt($campus, 3500);

        FeeCollection::query()
            ->where('registration_id', $registration->id)
            ->where('fee_type', 'registration')
            ->update(['paid_at' => now()->copy()->startOfDay()]);

        FeeCollection::query()
            ->where('admission_id', $admission->id)
            ->where('fee_type', 'admission')
            ->update(['paid_at' => now()->copy()->startOfDay()]);

        CoworkingRegistrationReceipt::query()
            ->where('campus_id', $campus->id)
            ->where('receipt_type', 'coworking_charge')
            ->update(['paid_at' => now()->copy()->startOfDay()]);

        $response = $this->actingAs($admin)
            ->get(route('dashboard.live-data', ['campus_id' => $campus->id]))
            ->assertOk();

        $dashboard = $response->json('dashboard');
        $todayPoints = $dashboard['incomeRanges']['today']['points'] ?? [];
        $todayTotal = array_sum(array_map(fn (array $point) => (float) ($point[1] ?? 0), $todayPoints));

        $this->assertSame(7500.0, (float) ($dashboard['incomeSummary']['today'] ?? 0));
        $this->assertSame(7500.0, $todayTotal);
        $this->assertTrue(collect($todayPoints)->contains(fn (array $point) => (float) ($point[1] ?? 0) > 0));
    }

    public function test_dashboard_today_income_graph_uses_pakistan_time_buckets(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-20 18:00:00', 'UTC'));

        $admin = $this->createAdminUser();
        $campus = $this->createCampus('Echo Campus', 'ECH');

        ['registration' => $registration, 'admission' => $admission] = $this->seedDashboardRecords($campus, 'echo', 1500);
        $this->seedAdmissionFee($campus, $registration, $admission, 2500);
        $this->seedCoworkingChargeReceipt($campus, 3500);

        $registrationFee = FeeCollection::query()
            ->where('registration_id', $registration->id)
            ->where('fee_type', 'registration')
            ->firstOrFail();
        $admissionFee = FeeCollection::query()
            ->where('admission_id', $admission->id)
            ->where('fee_type', 'admission')
            ->firstOrFail();
        $coworkingReceipt = CoworkingRegistrationReceipt::query()
            ->where('campus_id', $campus->id)
            ->where('receipt_type', 'coworking_charge')
            ->firstOrFail();

        $registrationFee->forceFill([
            'paid_at' => Carbon::parse('2026-06-20 00:00:00', 'UTC'),
            'created_at' => Carbon::parse('2026-06-20 08:15:00', 'UTC'),
        ])->saveQuietly();

        $admissionFee->forceFill([
            'paid_at' => Carbon::parse('2026-06-20 00:00:00', 'UTC'),
            'created_at' => Carbon::parse('2026-06-20 10:15:00', 'UTC'),
        ])->saveQuietly();

        $coworkingReceipt->forceFill([
            'paid_at' => Carbon::parse('2026-06-20 00:00:00', 'UTC'),
            'created_at' => Carbon::parse('2026-06-20 12:30:00', 'UTC'),
        ])->saveQuietly();

        $response = $this->actingAs($admin)
            ->get(route('dashboard.live-data', ['campus_id' => $campus->id]))
            ->assertOk();

        $todayPoints = collect($response->json('dashboard.incomeRanges.today.points') ?? [])
            ->mapWithKeys(fn (array $point) => [(string) ($point[0] ?? '') => (float) ($point[1] ?? 0)])
            ->all();

        $this->assertSame(0.0, (float) ($todayPoints['08 AM'] ?? 0.0));
        $this->assertSame(1500.0, (float) ($todayPoints['12 PM'] ?? 0.0));
        $this->assertSame(2500.0, (float) ($todayPoints['02 PM'] ?? 0.0));
        $this->assertSame(3500.0, (float) ($todayPoints['04 PM'] ?? 0.0));
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

    /**
     * @return array{lead: Lead, registration: Registration, admission: Admission}
     */
    private function seedDashboardRecords(Campus $campus, string $suffix, float $netPayable): array
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

        $admission = Admission::query()->create([
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
            'approval_status' => Admission::APPROVAL_STATUS_APPROVED,
            'student_status' => 'enrolled',
            'remarks' => 'Test record',
        ]);

        FeeCollection::query()->create([
            'lead_id' => $lead->id,
            'registration_id' => $registration->id,
            'campus_id' => $campus->id,
            'fee_type' => 'registration',
            'amount' => $netPayable,
            'discount_percent' => 0,
            'discount_amount' => 0,
            'net_amount' => $netPayable,
            'receipt_number' => $registration->receipt_number,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return compact('lead', 'registration', 'admission');
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

    private function seedAdmissionFee(Campus $campus, Registration $registration, Admission $admission, float $amount): void
    {
        FeeCollection::query()->create([
            'lead_id' => $registration->lead_id,
            'registration_id' => $registration->id,
            'admission_id' => $admission->id,
            'campus_id' => $campus->id,
            'fee_type' => 'admission',
            'amount' => $amount,
            'discount_percent' => 0,
            'discount_amount' => 0,
            'net_amount' => $amount,
            'receipt_number' => $admission->receipt_number,
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    private function seedCoworkingChargeReceipt(Campus $campus, float $amount): void
    {
        $registration = CoworkingRegistration::query()->create([
            'campus_id' => $campus->id,
            'registration_number' => 'CW-' . $campus->code . '-001',
            'receipt_number' => 'CWR-' . $campus->code . '-001',
            'full_name' => 'Coworking Member',
            'phone' => '03330000001',
            'guardian_name' => 'Coworking Guardian',
            'guardian_phone' => '03330000002',
            'cnic' => '3520212345699',
            'email' => 'coworking.member@example.test',
            'education' => 'Graduate',
            'date_of_birth' => '1998-01-01',
            'nature_of_work' => 'Freelancer',
            'timing' => 'Morning',
            'gender' => 'male',
            'address' => 'Coworking Street',
            'registration_date' => now()->toDateString(),
            'next_due_date' => now()->addMonth()->toDateString(),
            'coworking_charges' => $amount,
            'security_fee' => 0,
            'status' => 'registered',
        ]);

        CoworkingRegistrationReceipt::query()->create([
            'coworking_registration_id' => $registration->id,
            'campus_id' => $campus->id,
            'receipt_type' => 'coworking_charge',
            'receipt_number' => 'CWRC-' . $campus->code . '-001',
            'amount' => $amount,
            'paid_at' => now(),
            'notes' => 'Dashboard coworking collection test.',
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
