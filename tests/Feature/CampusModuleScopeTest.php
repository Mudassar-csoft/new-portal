<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Batch;
use App\Models\Campus;
use App\Models\CoworkingRegistration;
use App\Models\FeeCollection;
use App\Models\Lead;
use App\Models\Program;
use App\Models\Registration;
use App\Models\User;
use App\Models\User\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampusModuleScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_registration_and_admission_pages_only_show_current_campus_records(): void
    {
        $registrationView = $this->createPermission('registration', 'view', 'registration.view');
        $admissionView = $this->createPermission('admission', 'view', 'admission.view');

        $alphaCampus = $this->createCampus('Alpha Campus', 'ALP');
        $betaCampus = $this->createCampus('Beta Campus', 'BET');
        $program = $this->createProgram();
        $alphaBatch = $this->createBatch($alphaCampus, $program, 'ALP-B1');
        $betaBatch = $this->createBatch($betaCampus, $program, 'BET-B1');

        $alphaRegistration = $this->createRegistration($alphaCampus, $program, 'Alpha Registration', '03100000001');
        $betaRegistration = $this->createRegistration($betaCampus, $program, 'Beta Registration', '03100000002');

        $this->createAdmission($alphaCampus, $program, $alphaBatch, $alphaRegistration, 'Alpha Admission', '03200000001');
        $this->createAdmission($betaCampus, $program, $betaBatch, $betaRegistration, 'Beta Admission', '03200000002');

        $user = $this->createScopedUser($alphaCampus, [$registrationView, $admissionView]);

        $this->actingAs($user)
            ->get(route('registration.status'))
            ->assertOk()
            ->assertSee('Alpha Registration')
            ->assertDontSee('Beta Registration');

        $this->actingAs($user)
            ->get(route('admission.status'))
            ->assertOk()
            ->assertSee('Alpha Admission')
            ->assertDontSee('Beta Admission');
    }

    public function test_non_admin_registration_create_form_shows_all_campuses_and_keeps_selected_campus(): void
    {
        $registrationCreate = $this->createPermission('registration', 'create', 'registration.create');

        $alphaCampus = $this->createCampus('Alpha Campus', 'ALP');
        $betaCampus = $this->createCampus('Beta Campus', 'BET');
        $program = $this->createProgram();

        $user = $this->createScopedUser($alphaCampus, [$registrationCreate]);

        $this->actingAs($user)
            ->get(route('registration.create'))
            ->assertOk()
            ->assertSee('Alpha Campus')
            ->assertSee('Beta Campus');

        $this->actingAs($user)
            ->postJson(route('registration.store'), [
                'campus_id' => $betaCampus->id,
                'program_id' => $program->id,
                'student_name' => 'Campus Locked Registration',
                'phone' => '03100000003',
                'guardian_name' => 'Guardian Name',
                'guardian_phone' => '03100000013',
                'cnic' => '3520212345601',
                'passport_number' => 'AB1234568',
                'email' => 'locked.registration@example.test',
                'education' => 'Intermediate',
                'date_of_birth' => '2001-05-10',
                'gender' => 'male',
                'address' => '123 Testing Street, Lahore, Pakistan',
                'remarks' => 'Campus scope test registration.',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'Registration created successfully.');

        $this->assertDatabaseHas('registrations', [
            'student_name' => 'Campus Locked Registration',
            'campus_id' => $betaCampus->id,
        ]);
    }

    public function test_non_admin_admission_create_form_shows_all_campuses_and_keeps_selected_campus(): void
    {
        $admissionCreate = $this->createPermission('admission', 'create', 'admission.create');

        $alphaCampus = $this->createCampus('Alpha Campus', 'ALP');
        $betaCampus = $this->createCampus('Beta Campus', 'BET');
        $program = $this->createProgram();
        $alphaBatch = $this->createBatch($alphaCampus, $program, 'ALP-B4');
        $betaBatch = $this->createBatch($betaCampus, $program, 'BET-B4');

        $user = $this->createScopedUser($alphaCampus, [$admissionCreate]);

        $this->actingAs($user)
            ->get(route('admission.create'))
            ->assertOk()
            ->assertSee('Alpha Campus')
            ->assertSee('Beta Campus');

        $this->actingAs($user)
            ->postJson(route('admission.store'), [
                'campus_id' => $betaCampus->id,
                'program_id' => $program->id,
                'batch_id' => $betaBatch->id,
                'student_name' => 'Cross Campus Admission',
                'phone' => '03200000007',
                'guardian_name' => 'Admission Guardian',
                'guardian_phone' => '03200000017',
                'cnic' => '3520212345603',
                'passport_number' => 'AD1234567',
                'email' => 'cross.admission@example.test',
                'education' => 'Intermediate',
                'date_of_birth' => '2001-05-10',
                'gender' => 'male',
                'country' => 'Pakistan',
                'city' => 'Lahore',
                'area' => 'Johar Town',
                'postal_address' => '123 Testing Street, Lahore, Pakistan',
                'admission_date' => now()->toDateString(),
                'fee_type' => 'full',
                'remarks' => 'Campus scope test admission.',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'Admission created successfully.');

        $this->assertDatabaseHas('admissions', [
            'student_name' => 'Cross Campus Admission',
            'campus_id' => $betaCampus->id,
            'batch_id' => $betaBatch->id,
        ]);

        $this->assertDatabaseMissing('admissions', [
            'student_name' => 'Cross Campus Admission',
            'campus_id' => $alphaCampus->id,
        ]);
    }

    public function test_non_admin_student_records_search_and_detail_are_limited_to_current_campus(): void
    {
        $studentView = $this->createPermission('student', 'view', 'student.view');

        $alphaCampus = $this->createCampus('Alpha Campus', 'ALP');
        $betaCampus = $this->createCampus('Beta Campus', 'BET');
        $program = $this->createProgram();
        $alphaBatch = $this->createBatch($alphaCampus, $program, 'ALP-B2');
        $betaBatch = $this->createBatch($betaCampus, $program, 'BET-B2');

        $alphaRegistration = $this->createRegistration($alphaCampus, $program, 'Scoped Alpha Registration', '03100000004');
        $betaRegistration = $this->createRegistration($betaCampus, $program, 'Scoped Beta Registration', '03100000005');

        $this->createAdmission($alphaCampus, $program, $alphaBatch, $alphaRegistration, 'Scoped Alpha Student', '03200000003');
        $this->createAdmission($betaCampus, $program, $betaBatch, $betaRegistration, 'Scoped Beta Student', '03200000004');

        $this->createLead($alphaCampus, $program, 'Scoped Alpha Lead', '03300000001');
        $this->createLead($betaCampus, $program, 'Scoped Beta Lead', '03300000002');

        $user = $this->createScopedUser($alphaCampus, [$studentView]);

        $this->actingAs($user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('student.records.index', [
                'scope' => 'all_students',
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ]))
            ->assertOk()
            ->assertSee('Scoped Alpha Student')
            ->assertDontSee('Scoped Beta Student');

        $this->actingAs($user)
            ->get(route('student-search.index', ['q' => 'Scoped']))
            ->assertOk()
            ->assertSee('Scoped Alpha Student')
            ->assertSee('Scoped Alpha Registration')
            ->assertSee('Scoped Alpha Lead')
            ->assertDontSee('Scoped Beta Student')
            ->assertDontSee('Scoped Beta Registration')
            ->assertDontSee('Scoped Beta Lead');

        $this->actingAs($user)
            ->get(route('student.show', $alphaRegistration))
            ->assertOk()
            ->assertSee('Scoped Alpha Registration');

        $this->actingAs($user)
            ->get(route('student.show', $betaRegistration))
            ->assertForbidden();
    }

    public function test_non_admin_student_updates_and_coworking_registration_access_are_campus_scoped(): void
    {
        $studentView = $this->createPermission('student', 'view', 'student.view');
        $studentUpdate = $this->createPermission('student', 'update', 'student.update');
        $registrationView = $this->createPermission('registration', 'view', 'registration.view');

        $alphaCampus = $this->createCampus('Alpha Campus', 'ALP');
        $betaCampus = $this->createCampus('Beta Campus', 'BET');
        $program = $this->createProgram();
        $alphaBatch = $this->createBatch($alphaCampus, $program, 'ALP-B3');
        $betaBatch = $this->createBatch($betaCampus, $program, 'BET-B3');

        $alphaRegistration = $this->createRegistration($alphaCampus, $program, 'Alpha Student Record', '03100000006');
        $betaRegistration = $this->createRegistration($betaCampus, $program, 'Beta Student Record', '03100000007');

        $this->createAdmission($alphaCampus, $program, $alphaBatch, $alphaRegistration, 'Alpha Student Record', '03200000005');
        $betaAdmission = $this->createAdmission($betaCampus, $program, $betaBatch, $betaRegistration, 'Beta Student Record', '03200000006');

        $betaFee = FeeCollection::query()->create([
            'registration_id' => $betaRegistration->id,
            'admission_id' => $betaAdmission->id,
            'campus_id' => $betaCampus->id,
            'program_id' => $program->id,
            'fee_type' => 'admission',
            'installment_no' => 1,
            'installments_total' => 2,
            'amount' => 1000,
            'discount_percent' => 0,
            'discount_amount' => 0,
            'net_amount' => 1000,
            'receipt_number' => 'BET-0626-000001',
            'status' => 'pending',
            'due_at' => now()->toDateString(),
        ]);

        $betaCoworking = CoworkingRegistration::query()->create([
            'campus_id' => $betaCampus->id,
            'registration_number' => 'BET-CWS-0626-00001',
            'receipt_number' => 'BET-0626-00001',
            'full_name' => 'Beta Coworking Member',
            'phone' => '03400000001',
            'guardian_name' => 'Guardian',
            'guardian_phone' => '03400000011',
            'cnic' => '3520212345602',
            'email' => 'beta.coworking@example.test',
            'education' => 'Bachelors',
            'date_of_birth' => '1998-01-01',
            'nature_of_work' => 'Consulting',
            'timing' => '09:00 AM - 06:00 PM',
            'gender' => 'female',
            'address' => 'Beta Address',
            'registration_date' => now()->toDateString(),
            'next_due_date' => now()->addMonth()->toDateString(),
            'coworking_charges' => 20000,
            'security_fee' => 10000,
            'remarks' => 'Scoped coworking member.',
            'status' => 'registered',
        ]);

        $user = $this->createScopedUser($alphaCampus, [$studentView, $studentUpdate, $registrationView]);

        $this->actingAs($user)
            ->post(route('student.records.status', $betaAdmission), [
                'status' => 'frozen',
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('student.fee.collect', $betaFee), [
                'paid_amount' => 900,
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('coworking-registrations.show', $betaCoworking))
            ->assertForbidden();
    }

    public function test_user_management_navigation_and_routes_are_admin_only_for_non_admins(): void
    {
        $dashboardView = $this->createPermission('dashboard', 'view', 'dashboard.view');
        $userView = $this->createPermission('user', 'view', 'user.view');
        $userCreate = $this->createPermission('user', 'create', 'user.create');
        $roleView = $this->createPermission('role', 'view', 'role.view');
        $roleCreate = $this->createPermission('role', 'create', 'role.create');

        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $user = $this->createScopedUser($campus, [
            $dashboardView,
            $userView,
            $userCreate,
            $roleView,
            $roleCreate,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('User Management');

        $this->actingAs($user)->get(route('users.index'))->assertForbidden();
        $this->actingAs($user)->get(route('users.create'))->assertForbidden();
        $this->actingAs($user)->get(route('roles.index'))->assertForbidden();
        $this->actingAs($user)->get(route('roles.create'))->assertForbidden();
        $this->actingAs($user)->get(route('login-logs.index'))->assertForbidden();
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

    private function createProgram(): Program
    {
        return Program::query()->create([
            'name' => 'Campus Scoped Program',
            'title' => 'Campus Scoped Program',
            'code' => 'CSP101',
            'program_type' => 'bootcamp',
            'fee' => 50000,
            'duration_weeks' => 12,
            'installments' => 3,
            'status' => 'active',
        ]);
    }

    private function createBatch(Campus $campus, Program $program, string $code): Batch
    {
        return Batch::query()->create([
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'name' => $code . ' Batch',
            'code' => $code,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'session' => 'morning',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'status' => 'active',
        ]);
    }

    private function createLead(Campus $campus, Program $program, string $name, string $phone): Lead
    {
        return Lead::query()->create([
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'type' => 'training',
            'name' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)) . '@example.test',
            'phone' => $phone,
            'city' => 'Lahore',
            'origin' => 'Referral',
            'marketing_source' => 'Referral',
            'status' => 'pending',
        ]);
    }

    private function createRegistration(Campus $campus, Program $program, string $studentName, string $phone): Registration
    {
        return Registration::query()->create([
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'registration_number' => $campus->code . '-0626-01' . substr($phone, -1),
            'receipt_number' => $campus->code . '-0626-00000' . substr($phone, -1),
            'student_name' => $studentName,
            'phone' => $phone,
            'guardian_name' => 'Guardian ' . $studentName,
            'guardian_phone' => '0301000000' . substr($phone, -1),
            'cnic' => '35202123456' . substr($phone, -2),
            'passport_number' => 'PASS' . substr($phone, -4),
            'email' => strtolower(str_replace(' ', '.', $studentName)) . '@example.test',
            'education' => 'Intermediate',
            'date_of_birth' => '2001-01-01',
            'gender' => 'male',
            'address' => '123 Campus Scoped Street',
            'remarks' => 'Campus scoped registration record.',
            'fee' => 2000,
            'discount' => 0,
            'net_payable' => 2000,
            'status' => 'registered',
            'registered_at' => now(),
        ]);
    }

    private function createAdmission(
        Campus $campus,
        Program $program,
        Batch $batch,
        Registration $registration,
        string $studentName,
        string $phone
    ): Admission {
        return Admission::query()->create([
            'registration_id' => $registration->id,
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'batch_id' => $batch->id,
            'student_name' => $studentName,
            'phone' => $phone,
            'guardian_name' => 'Guardian ' . $studentName,
            'guardian_phone' => '0302000000' . substr($phone, -1),
            'cnic' => '35203123456' . substr($phone, -2),
            'passport_number' => 'ADM' . substr($phone, -4),
            'email' => strtolower(str_replace(' ', '.', $studentName)) . '@example.test',
            'education' => 'Intermediate',
            'date_of_birth' => '2001-01-01',
            'gender' => 'male',
            'country' => 'Pakistan',
            'city' => 'Lahore',
            'area' => 'Johar Town',
            'postal_address' => '123 Campus Scoped Street',
            'registration_number' => $registration->registration_number,
            'roll_number' => $campus->code . '-' . $batch->code . '-01' . substr($phone, -1),
            'admission_date' => now()->toDateString(),
            'fee_package' => 50000,
            'discount_amount' => 0,
            'discount_percent' => 0,
            'discounted_fee' => 50000,
            'fee_type' => 'full',
            'student_status' => 'enrolled',
            'status_updated_at' => now(),
            'remarks' => 'Campus scoped admission record.',
            'receipt_number' => $campus->code . '-0626-10000' . substr($phone, -1),
        ]);
    }

    /**
     * @param  array<int, Permission>  $permissions
     */
    private function createScopedUser(Campus $campus, array $permissions): User
    {
        $user = User::factory()->create([
            'campus_id' => $campus->id,
        ]);

        $user->permissions()->sync(collect($permissions)->pluck('id')->all());

        return $user;
    }
}
