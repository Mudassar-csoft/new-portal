<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Batch;
use App\Models\Campus;
use App\Models\Program;
use App\Models\Registration;
use App\Models\User;
use App\Models\User\Permission;
use App\Models\User\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdmissionApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_admission_moves_through_upload_revert_and_approve_workflow(): void
    {
        Storage::fake('public');

        $admissionCreate = $this->createPermission('admission', 'create', 'admission.create');
        $admissionUpdate = $this->createPermission('admission', 'update', 'admission.update');

        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $program = $this->createProgram('TRN901', 'Workflow Program');
        $batch = $this->createBatch($campus, $program, 'ALP-B1');
        $registration = $this->createRegistration($campus, $program, 'Workflow Student', '03200000991');
        $admission = $this->createAdmission($campus, $program, $batch, $registration, 'Workflow Student', '03200000991', [
            'approval_status' => Admission::APPROVAL_STATUS_PENDING,
        ]);

        $creator = $this->createScopedUser($campus, [$admissionCreate, $admissionUpdate]);
        $admin = $this->createAdminUser($campus);

        $this->actingAs($creator)
            ->post(route('admission.documents.upload', $admission), [
                'document_cnic_front' => UploadedFile::fake()->create('cnic-front.jpg', 150, 'image/jpeg'),
                'document_admission_form' => UploadedFile::fake()->create('admission-form.pdf', 200),
                'document_paid_slip' => UploadedFile::fake()->create('paid-slip.jpg', 180, 'image/jpeg'),
            ])
            ->assertRedirect();

        $admission->refresh();

        $this->assertSame(Admission::APPROVAL_STATUS_REQUESTED, $admission->approval_status);
        $this->assertSame($creator->id, $admission->documents_uploaded_by);
        $this->assertNotNull($admission->documents_uploaded_at);
        Storage::disk('public')->assertExists($admission->document_cnic_front_path);
        Storage::disk('public')->assertExists($admission->document_admission_form_path);
        Storage::disk('public')->assertExists($admission->document_paid_slip_path);

        $this->actingAs($admin)
            ->post(route('admission.review', $admission), [
                'review_action' => 'revert',
                'approval_remarks' => 'Paid slip stamp is missing.',
            ])
            ->assertRedirect();

        $admission->refresh();

        $this->assertSame(Admission::APPROVAL_STATUS_PENDING, $admission->approval_status);
        $this->assertSame('Paid slip stamp is missing.', $admission->approval_remarks);
        $this->assertSame($admin->id, $admission->approval_reviewed_by);
        $this->assertNotNull($admission->approval_reviewed_at);

        $this->actingAs($creator)
            ->post(route('admission.documents.upload', $admission), [
                'document_cnic_front' => UploadedFile::fake()->create('cnic-front-updated.jpg', 155, 'image/jpeg'),
                'document_admission_form' => UploadedFile::fake()->create('admission-form-updated.pdf', 240),
                'document_paid_slip' => UploadedFile::fake()->create('paid-slip-updated.jpg', 185, 'image/jpeg'),
            ])
            ->assertRedirect();

        $admission->refresh();

        $this->assertSame(Admission::APPROVAL_STATUS_REQUESTED, $admission->approval_status);
        $this->assertNull($admission->approval_reviewed_by);
        $this->assertNull($admission->approval_reviewed_at);

        $this->actingAs($admin)
            ->post(route('admission.review', $admission), [
                'review_action' => 'approve',
                'approval_remarks' => 'Documents verified and approved.',
            ])
            ->assertRedirect();

        $admission->refresh();

        $this->assertSame(Admission::APPROVAL_STATUS_APPROVED, $admission->approval_status);
        $this->assertSame('Documents verified and approved.', $admission->approval_remarks);
        $this->assertSame($admin->id, $admission->approval_reviewed_by);
        $this->assertNotNull($admission->approval_reviewed_at);
    }

    public function test_student_records_only_return_approved_admissions(): void
    {
        $studentView = $this->createPermission('student', 'view', 'student.view');

        $campus = $this->createCampus('Beta Campus', 'BET');
        $program = $this->createProgram('TRN902', 'Student Filter Program');
        $batch = $this->createBatch($campus, $program, 'BET-B1');

        $approvedRegistration = $this->createRegistration($campus, $program, 'Approved Student', '03200000992');
        $pendingRegistration = $this->createRegistration($campus, $program, 'Pending Student', '03200000993');

        $this->createAdmission($campus, $program, $batch, $approvedRegistration, 'Approved Student', '03200000992', [
            'approval_status' => Admission::APPROVAL_STATUS_APPROVED,
        ]);
        $this->createAdmission($campus, $program, $batch, $pendingRegistration, 'Pending Student', '03200000993', [
            'approval_status' => Admission::APPROVAL_STATUS_PENDING,
        ]);

        $user = $this->createScopedUser($campus, [$studentView]);

        $response = $this->actingAs($user)->getJson(
            route('student.records.index', [
                'scope' => 'all_students',
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ]),
            [
                'X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $response->assertOk();

        $studentNames = collect($response->json('data'))->pluck('student_name')->all();

        $this->assertContains('Approved Student', $studentNames);
        $this->assertNotContains('Pending Student', $studentNames);
    }

    public function test_registration_detail_treats_pending_admission_as_not_yet_enrolled(): void
    {
        $studentView = $this->createPermission('student', 'view', 'student.view');

        $campus = $this->createCampus('Gamma Campus', 'GAM');
        $program = $this->createProgram('TRN903', 'Pending Detail Program');
        $batch = $this->createBatch($campus, $program, 'GAM-B1');
        $registration = $this->createRegistration($campus, $program, 'Pending Detail Student', '03200000994');

        $this->createAdmission($campus, $program, $batch, $registration, 'Pending Detail Student', '03200000994', [
            'approval_status' => Admission::APPROVAL_STATUS_PENDING,
        ]);

        $user = $this->createScopedUser($campus, [$studentView]);

        $this->actingAs($user)
            ->get(route('student.show', $registration))
            ->assertOk()
            ->assertSee('Student is registered only')
            ->assertDontSee('Pending Detail Program');
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
            'guardian_phone' => '0321000000' . substr($phone, -1),
            'cnic' => '35202123456' . substr($phone, -2),
            'passport_number' => 'PASS' . substr($phone, -4),
            'email' => strtolower(str_replace(' ', '.', $studentName)) . '@example.test',
            'education' => 'Intermediate',
            'date_of_birth' => '2001-01-01',
            'gender' => 'male',
            'address' => '123 Workflow Street',
            'remarks' => 'Approval workflow test registration.',
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
            'postal_address' => '123 Workflow Street',
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
            'remarks' => 'Approval workflow test admission.',
            'receipt_number' => $campus->code . '-0626-10000' . substr($phone, -1),
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

    private function createAdminUser(Campus $campus): User
    {
        $user = User::factory()->create([
            'campus_id' => $campus->id,
        ]);

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
