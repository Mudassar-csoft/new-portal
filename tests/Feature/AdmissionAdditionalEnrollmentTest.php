<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Batch;
use App\Models\Campus;
use App\Models\FeeCollection;
use App\Models\Lead;
use App\Models\Program;
use App\Models\Registration;
use App\Models\User;
use App\Models\User\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmissionAdditionalEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admission_status_only_shows_collect_installment_for_admissions_with_pending_fee(): void
    {
        $admissionView = $this->createPermission('admission', 'view', 'admission.view');
        $studentView = $this->createPermission('student', 'view', 'student.view');

        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $program = $this->createProgram('TRN401', 'Current Programme');
        $batch = $this->createBatch($campus, $program, 'ALP-B1');

        $fullRegistration = $this->createRegistration($campus, $program, 'Full Pay Student', '03100000101');
        $fullAdmission = $this->createAdmission($campus, $program, $batch, $fullRegistration, 'Full Pay Student', '03200000101', [
            'fee_type' => 'full',
        ]);
        $this->createAdmissionFee($campus, $program, $fullRegistration, $fullAdmission, [
            'status' => 'paid',
            'installment_no' => null,
            'installments_total' => null,
            'net_amount' => 50000,
        ]);

        $pendingRegistration = $this->createRegistration($campus, $program, 'Pending Installment Student', '03100000102');
        $pendingAdmission = $this->createAdmission($campus, $program, $batch, $pendingRegistration, 'Pending Installment Student', '03200000102', [
            'fee_type' => 'installments',
        ]);
        $this->createAdmissionFee($campus, $program, $pendingRegistration, $pendingAdmission, [
            'status' => 'pending',
            'installment_no' => 2,
            'installments_total' => 3,
            'net_amount' => 15000,
        ]);

        $user = $this->createScopedUser($campus, [$admissionView, $studentView]);

        $response = $this->actingAs($user)->get(route('admission.status', ['scope' => 'approved']));

        $response->assertOk()
            ->assertSee('Full Pay Student')
            ->assertSee('Pending Installment Student')
            ->assertSee(route('student.show', $pendingRegistration), false)
            ->assertSee('source_admission_id=' . $pendingAdmission->id, false)
            ->assertSee('source_registration_id=' . $pendingRegistration->id, false);

        $this->assertSame(1, substr_count($response->getContent(), 'Collect Fee Installment'));
    }

    public function test_enroll_another_course_form_prefills_previous_student_data_and_hides_current_course(): void
    {
        $admissionCreate = $this->createPermission('admission', 'create', 'admission.create');

        $campus = $this->createCampus('Beta Campus', 'BET');
        $currentProgram = $this->createProgram('TRN402', 'Current Enrolled Course');
        $otherProgram = $this->createProgram('TRN403', 'Second Course');
        $batch = $this->createBatch($campus, $currentProgram, 'BET-B1');
        $lead = $this->createLead($campus, $currentProgram, 'Source Student', '03200000111');
        $registration = $this->createRegistration($campus, $currentProgram, 'Source Student', '03200000111', [
            'lead_id' => $lead->id,
            'guardian_name' => 'Guardian Source',
            'guardian_phone' => '03200000999',
            'cnic' => '3520212345671',
            'address' => '22 Source Street, Lahore',
            'education' => 'BSCS',
            'gender' => 'female',
        ]);
        $admission = $this->createAdmission($campus, $currentProgram, $batch, $registration, 'Source Student', '03200000111', [
            'guardian_name' => 'Guardian Source',
            'guardian_phone' => '03200000999',
            'cnic' => '3520212345671',
            'postal_address' => '22 Source Street, Lahore',
            'education' => 'BSCS',
            'gender' => 'female',
        ]);

        $user = $this->createScopedUser($campus, [$admissionCreate]);

        $this->actingAs($user)
            ->get(route('admission.create', [
                'source_admission_id' => $admission->id,
                'source_registration_id' => $registration->id,
            ]))
            ->assertOk()
            ->assertDontSee('Student details were loaded from the previous admission.')
            ->assertDontSee('The current enrolled course is hidden here. Choose another course.')
            ->assertSee('value="Source Student"', false)
            ->assertSee('value="03200000111"', false)
            ->assertSee('value="Guardian Source"', false)
            ->assertSee('value="3520212345671"', false)
            ->assertSee('22 Source Street, Lahore')
            ->assertSee('Second Course')
            ->assertDontSee('Current Enrolled Course');
    }

    public function test_enroll_another_course_creates_a_new_registration_without_overwriting_the_existing_course(): void
    {
        $admissionCreate = $this->createPermission('admission', 'create', 'admission.create');

        $campus = $this->createCampus('Gamma Campus', 'GAM');
        $currentProgram = $this->createProgram('TRN404', 'First Course');
        $otherProgram = $this->createProgram('TRN405', 'Second Course');
        $currentBatch = $this->createBatch($campus, $currentProgram, 'GAM-B1');
        $otherBatch = $this->createBatch($campus, $otherProgram, 'GAM-B2');
        $lead = $this->createLead($campus, $currentProgram, 'Second Course Student', '03200000121');
        $registration = $this->createRegistration($campus, $currentProgram, 'Second Course Student', '03200000121', [
            'lead_id' => $lead->id,
            'guardian_name' => 'Guardian Second',
            'guardian_phone' => '03200000888',
            'cnic' => '3520212345672',
            'address' => '44 Another Street, Lahore',
            'education' => 'Intermediate',
            'gender' => 'male',
        ]);
        $admission = $this->createAdmission($campus, $currentProgram, $currentBatch, $registration, 'Second Course Student', '03200000121', [
            'guardian_name' => 'Guardian Second',
            'guardian_phone' => '03200000888',
            'cnic' => '3520212345672',
            'postal_address' => '44 Another Street, Lahore',
        ]);

        $user = $this->createScopedUser($campus, [$admissionCreate]);

        $this->actingAs($user)
            ->postJson(route('admission.store'), [
                'lead_id' => $lead->id,
                'source_registration_id' => $registration->id,
                'source_admission_id' => $admission->id,
                'campus_id' => $campus->id,
                'program_id' => $otherProgram->id,
                'batch_id' => $otherBatch->id,
                'student_name' => 'Second Course Student',
                'phone' => '03200000121',
                'guardian_name' => 'Guardian Second',
                'guardian_phone' => '03200000888',
                'cnic' => '3520212345672',
                'passport_number' => 'PASS6721',
                'email' => 'second.course.student@example.test',
                'education' => 'Intermediate',
                'date_of_birth' => '2001-01-01',
                'gender' => 'male',
                'country' => 'Pakistan',
                'city' => 'Lahore',
                'area' => 'Johar Town',
                'postal_address' => '44 Another Street, Lahore',
                'admission_date' => now()->toDateString(),
                'fee_type' => 'full',
                'remarks' => 'Enroll into another course.',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'Admission created successfully.');

        $registration->refresh();

        $this->assertSame($currentProgram->id, $registration->program_id);
        $this->assertSame(2, Registration::query()->where('cnic', '3520212345672')->count());
        $this->assertSame(2, Admission::query()->where('cnic', '3520212345672')->count());
        $this->assertDatabaseHas('registrations', [
            'cnic' => '3520212345672',
            'program_id' => $otherProgram->id,
            'student_name' => 'Second Course Student',
        ]);
        $this->assertDatabaseHas('admissions', [
            'cnic' => '3520212345672',
            'program_id' => $otherProgram->id,
            'batch_id' => $otherBatch->id,
        ]);
    }

    public function test_enroll_another_course_rejects_the_same_course(): void
    {
        $admissionCreate = $this->createPermission('admission', 'create', 'admission.create');

        $campus = $this->createCampus('Delta Campus', 'DEL');
        $program = $this->createProgram('TRN406', 'Only Course');
        $batch = $this->createBatch($campus, $program, 'DEL-B1');
        $lead = $this->createLead($campus, $program, 'Blocked Course Student', '03200000131');
        $registration = $this->createRegistration($campus, $program, 'Blocked Course Student', '03200000131', [
            'lead_id' => $lead->id,
            'guardian_name' => 'Guardian Blocked',
            'guardian_phone' => '03200000777',
            'cnic' => '3520212345673',
            'address' => '55 Blocked Street, Lahore',
        ]);
        $admission = $this->createAdmission($campus, $program, $batch, $registration, 'Blocked Course Student', '03200000131', [
            'guardian_name' => 'Guardian Blocked',
            'guardian_phone' => '03200000777',
            'cnic' => '3520212345673',
            'postal_address' => '55 Blocked Street, Lahore',
        ]);

        $user = $this->createScopedUser($campus, [$admissionCreate]);

        $this->actingAs($user)
            ->postJson(route('admission.store'), [
                'lead_id' => $lead->id,
                'source_registration_id' => $registration->id,
                'source_admission_id' => $admission->id,
                'campus_id' => $campus->id,
                'program_id' => $program->id,
                'batch_id' => $batch->id,
                'student_name' => 'Blocked Course Student',
                'phone' => '03200000131',
                'guardian_name' => 'Guardian Blocked',
                'guardian_phone' => '03200000777',
                'cnic' => '3520212345673',
                'passport_number' => 'PASS6731',
                'email' => 'blocked.course.student@example.test',
                'education' => 'Intermediate',
                'date_of_birth' => '2001-01-01',
                'gender' => 'male',
                'country' => 'Pakistan',
                'city' => 'Lahore',
                'area' => 'Johar Town',
                'postal_address' => '55 Blocked Street, Lahore',
                'admission_date' => now()->toDateString(),
                'fee_type' => 'full',
                'remarks' => 'Try same course again.',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['program_id']);
    }

    public function test_admission_store_normalizes_dashed_cnic_before_saving(): void
    {
        $admissionCreate = $this->createPermission('admission', 'create', 'admission.create');

        $campus = $this->createCampus('Epsilon Campus', 'EPS');
        $program = $this->createProgram('TRN407', 'CNIC Normalization Course');
        $batch = $this->createBatch($campus, $program, 'EPS-B1');
        $user = $this->createScopedUser($campus, [$admissionCreate]);

        $this->actingAs($user)
            ->postJson(route('admission.store'), [
                'campus_id' => $campus->id,
                'program_id' => $program->id,
                'batch_id' => $batch->id,
                'student_name' => 'Dashed CNIC Student',
                'phone' => '03200000141',
                'guardian_name' => 'Guardian Dashed',
                'guardian_phone' => '03200000666',
                'cnic' => '35202-1234567-1',
                'passport_number' => 'PASS1411',
                'email' => 'dashed.cnic.student@example.test',
                'education' => 'Intermediate',
                'date_of_birth' => '2001-01-01',
                'gender' => 'male',
                'country' => 'Pakistan',
                'city' => 'Lahore',
                'area' => 'Johar Town',
                'postal_address' => '66 Dashed Street, Lahore',
                'admission_date' => now()->toDateString(),
                'fee_type' => 'full',
                'remarks' => 'Store admission with dashed CNIC.',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'Admission created successfully.');

        $this->assertDatabaseHas('registrations', [
            'student_name' => 'Dashed CNIC Student',
            'cnic' => '3520212345671',
        ]);
        $this->assertDatabaseHas('admissions', [
            'student_name' => 'Dashed CNIC Student',
            'cnic' => '3520212345671',
        ]);
    }

    public function test_direct_admission_auto_creates_a_training_lead_and_links_the_registration(): void
    {
        $admissionCreate = $this->createPermission('admission', 'create', 'admission.create');

        $campus = $this->createCampus('Zeta Campus', 'ZET');
        $program = $this->createProgram('TRN408', 'Direct Admission Course');
        $batch = $this->createBatch($campus, $program, 'ZET-B1');
        $user = $this->createScopedUser($campus, [$admissionCreate]);

        $this->actingAs($user)
            ->postJson(route('admission.store'), [
                'campus_id' => $campus->id,
                'program_id' => $program->id,
                'batch_id' => $batch->id,
                'student_name' => 'Direct Admission Student',
                'phone' => '03200000151',
                'guardian_name' => 'Guardian Direct',
                'guardian_phone' => '03200000555',
                'cnic' => '3520212345679',
                'passport_number' => 'PASS1511',
                'email' => 'direct.admission.student@example.test',
                'education' => 'Intermediate',
                'date_of_birth' => '2001-01-01',
                'gender' => 'male',
                'country' => 'Pakistan',
                'city' => 'Lahore',
                'area' => 'Johar Town',
                'postal_address' => '151 Direct Street, Lahore',
                'admission_date' => now()->toDateString(),
                'fee_type' => 'full',
                'remarks' => 'Created directly from admission form.',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'Admission created successfully.');

        $lead = Lead::query()->where('phone', '03200000151')->firstOrFail();
        $registration = Registration::query()->where('phone', '03200000151')->firstOrFail();
        $admission = Admission::query()->where('phone', '03200000151')->firstOrFail();

        $this->assertSame('training', $lead->type);
        $this->assertSame('enrolled', $lead->status);
        $this->assertSame($lead->id, $registration->lead_id);
        $this->assertSame($registration->id, $admission->registration_id);
        $this->assertSame(Admission::APPROVAL_STATUS_PENDING, $admission->approval_status);

        $this->assertDatabaseHas('lead_followups', [
            'lead_id' => $lead->id,
            'stage' => 'registered',
            'lead_status' => 'registered',
        ]);
        $this->assertDatabaseHas('lead_followups', [
            'lead_id' => $lead->id,
            'stage' => 'enroll',
            'lead_status' => 'enrolled',
        ]);
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

    private function createProgram(string $code, string $title): Program
    {
        return Program::query()->create([
            'name' => $title,
            'title' => $title,
            'code' => $code,
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
            'details' => [
                'guardian_name' => 'Guardian ' . $name,
                'guardian_phone' => '03210000000',
                'cnic' => '3520212345600',
                'education' => 'Intermediate',
                'gender' => 'male',
                'postal_address' => 'Lead Address',
            ],
        ]);
    }

    private function createRegistration(Campus $campus, Program $program, string $studentName, string $phone, array $overrides = []): Registration
    {
        return Registration::query()->create(array_merge([
            'lead_id' => null,
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'registration_number' => $campus->code . '-0626-01' . substr($phone, -1),
            'receipt_number' => $campus->code . '-0626-00000' . substr($phone, -1),
            'student_name' => $studentName,
            'phone' => $phone,
            'guardian_name' => 'Guardian ' . $studentName,
            'guardian_phone' => '0321000000' . substr($phone, -1),
            'cnic' => '35202123456' . substr($phone, -2),
            'passport_number' => 'PASS' . substr($phone, -4),
            'email' => strtolower(str_replace(' ', '.', $studentName)) . '@example.test',
            'education' => 'Intermediate',
            'date_of_birth' => '2001-01-01',
            'gender' => 'male',
            'address' => '123 Student Street',
            'remarks' => 'Admission additional enrollment test registration.',
            'fee' => 2000,
            'discount' => 0,
            'net_payable' => 2000,
            'status' => 'registered',
            'registered_at' => now(),
        ], $overrides));
    }

    private function createAdmission(
        Campus $campus,
        Program $program,
        Batch $batch,
        Registration $registration,
        string $studentName,
        string $phone,
        array $overrides = []
    ): Admission {
        return Admission::query()->create(array_merge([
            'registration_id' => $registration->id,
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'batch_id' => $batch->id,
            'student_name' => $studentName,
            'phone' => $phone,
            'guardian_name' => 'Guardian ' . $studentName,
            'guardian_phone' => '0322000000' . substr($phone, -1),
            'cnic' => '35203123456' . substr($phone, -2),
            'passport_number' => 'ADM' . substr($phone, -4),
            'email' => strtolower(str_replace(' ', '.', $studentName)) . '@example.test',
            'education' => 'Intermediate',
            'date_of_birth' => '2001-01-01',
            'gender' => 'male',
            'country' => 'Pakistan',
            'city' => 'Lahore',
            'area' => 'Johar Town',
            'postal_address' => '123 Student Street',
            'registration_number' => $registration->registration_number,
            'roll_number' => $campus->code . '-' . $batch->code . '-01' . substr($phone, -1),
            'admission_date' => now()->toDateString(),
            'fee_package' => 50000,
            'discount_amount' => 0,
            'discount_percent' => 0,
            'discounted_fee' => 50000,
            'fee_type' => 'full',
            'approval_status' => Admission::APPROVAL_STATUS_APPROVED,
            'student_status' => 'enrolled',
            'status_updated_at' => now(),
            'remarks' => 'Admission additional enrollment test record.',
            'receipt_number' => $campus->code . '-0626-10000' . substr($phone, -1),
        ], $overrides));
    }

    private function createAdmissionFee(Campus $campus, Program $program, Registration $registration, Admission $admission, array $overrides = []): FeeCollection
    {
        return FeeCollection::query()->create(array_merge([
            'lead_id' => $registration->lead_id,
            'registration_id' => $registration->id,
            'admission_id' => $admission->id,
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'fee_type' => 'admission',
            'installment_no' => 1,
            'installments_total' => 3,
            'amount' => 15000,
            'discount_percent' => 0,
            'discount_amount' => 0,
            'net_amount' => 15000,
            'receipt_number' => $admission->receipt_number,
            'status' => 'pending',
            'paid_at' => null,
            'due_at' => now()->toDateString(),
            'created_by' => null,
            'notes' => 'Pending installment for action menu test.',
        ], $overrides));
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
