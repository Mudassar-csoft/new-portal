<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Batch;
use App\Models\Campus;
use App\Models\FeeCollection;
use App\Models\Program;
use App\Models\Registration;
use App\Models\User;
use App\Models\User\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentDropWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_drop_student_route_requires_student_drop_permission(): void
    {
        $studentUpdate = $this->createPermission('student', 'update', 'student.update');
        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $program = $this->createProgram('DM101', 'Digital Marketing');
        $batch = $this->createBatch($campus, $program, 'ALP-B1');
        $registration = $this->createRegistration($campus, $program, 'Areeba', '03000000101');
        $admission = $this->createAdmission($campus, $program, $batch, $registration, 'Areeba', '03000000101');

        $user = $this->createUser($campus, [$studentUpdate]);

        $this->actingAs($user)
            ->post(route('student.records.drop', $admission), [
                'drop_reason' => 'Unable to continue classes.',
            ])
            ->assertForbidden();
    }

    public function test_drop_student_marks_pending_admission_fees_as_bad_debt_and_stores_reason(): void
    {
        $studentDrop = $this->createPermission('student', 'drop', 'student.drop');
        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $program = $this->createProgram('WD101', 'Web Development');
        $batch = $this->createBatch($campus, $program, 'ALP-B2');
        $registration = $this->createRegistration($campus, $program, 'Bilal', '03000000102');
        $admission = $this->createAdmission($campus, $program, $batch, $registration, 'Bilal', '03000000102');

        $pendingFee = $this->createFeeCollection($campus, $program, $registration, $admission, [
            'status' => 'pending',
            'receipt_number' => 'ALP-0726-000101',
        ]);
        $paidFee = $this->createFeeCollection($campus, $program, $registration, $admission, [
            'status' => 'paid',
            'paid_at' => now(),
            'receipt_number' => 'ALP-0726-000102',
        ]);
        $registrationFee = $this->createFeeCollection($campus, $program, $registration, null, [
            'fee_type' => 'registration',
            'status' => 'pending',
            'receipt_number' => 'ALP-0726-000103',
        ]);

        $user = $this->createUser($campus, [$studentDrop]);

        $this->actingAs($user)
            ->post(route('student.records.drop', $admission), [
                'drop_reason' => 'Student requested permanent withdrawal.',
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Student dropped and pending fee moved to bad debt.');

        $admission->refresh();
        $pendingFee->refresh();
        $paidFee->refresh();
        $registrationFee->refresh();

        $this->assertSame('dropped', $admission->student_status);
        $this->assertStringContainsString('Student requested permanent withdrawal.', (string) $admission->remarks);
        $this->assertSame('baddebt', $pendingFee->status);
        $this->assertStringContainsString('Student requested permanent withdrawal.', (string) $pendingFee->notes);
        $this->assertSame('paid', $paidFee->status);
        $this->assertSame('pending', $registrationFee->status);
    }

    public function test_drop_student_action_only_shows_for_users_with_drop_permission(): void
    {
        $studentView = $this->createPermission('student', 'view', 'student.view');
        $studentUpdate = $this->createPermission('student', 'update', 'student.update');
        $studentDrop = $this->createPermission('student', 'drop', 'student.drop');
        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $program = $this->createProgram('GD101', 'Graphic Design');
        $batch = $this->createBatch($campus, $program, 'ALP-B3');
        $registration = $this->createRegistration($campus, $program, 'Hina', '03000000103');

        $this->createAdmission($campus, $program, $batch, $registration, 'Hina', '03000000103');

        $updateUser = $this->createUser($campus, [$studentView, $studentUpdate]);
        $dropUser = $this->createUser($campus, [$studentView, $studentDrop]);

        $updateResponse = $this->actingAs($updateUser)->getJson(
            route('student.records.index', [
                'scope' => 'active',
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ]),
            [
                'X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $updateResponse->assertOk();
        $updateActions = (string) $updateResponse->json('data.0.actions');
        $this->assertStringContainsString('Freeze Course', $updateActions);
        $this->assertStringNotContainsString('Drop Student', $updateActions);

        $dropResponse = $this->actingAs($dropUser)->getJson(
            route('student.records.index', [
                'scope' => 'active',
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ]),
            [
                'X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $dropResponse->assertOk();
        $dropActions = (string) $dropResponse->json('data.0.actions');
        $this->assertStringContainsString('Drop Student', $dropActions);
        $this->assertStringNotContainsString('Freeze Course', $dropActions);
    }

    private function createUser(?Campus $campus, array $permissions): User
    {
        $user = User::factory()->create([
            'campus_id' => $campus?->id,
        ]);

        $user->permissions()->sync(collect($permissions)->pluck('id')->all());

        return $user;
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
            'registration_number' => $campus->code . '-0726-01' . substr($phone, -1),
            'receipt_number' => $campus->code . '-0726-00000' . substr($phone, -1),
            'student_name' => $studentName,
            'phone' => $phone,
            'gender' => 'female',
            'cnic' => '35202123456' . substr($phone, -2),
            'address' => $studentName . ' Address',
            'education' => 'Intermediate',
            'fee' => 2000,
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
            'registration_number' => $registration->registration_number,
            'roll_number' => $campus->code . '-' . $batch->code . '-01',
            'admission_date' => now()->toDateString(),
            'fee_package' => 50000,
            'discount_amount' => 0,
            'discount_percent' => 0,
            'discounted_fee' => 50000,
            'fee_type' => 'monthly',
            'student_status' => 'enrolled',
            'approval_status' => Admission::APPROVAL_STATUS_APPROVED,
            'status_updated_at' => now(),
            'remarks' => '',
        ], $overrides));
    }

    private function createFeeCollection(
        Campus $campus,
        Program $program,
        Registration $registration,
        ?Admission $admission,
        array $overrides = []
    ): FeeCollection {
        return FeeCollection::query()->create(array_merge([
            'registration_id' => $registration->id,
            'admission_id' => $admission?->id,
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'fee_type' => 'admission',
            'installment_no' => 1,
            'installments_total' => 3,
            'amount' => 1000,
            'discount_percent' => 0,
            'discount_amount' => 0,
            'net_amount' => 1000,
            'receipt_number' => $campus->code . '-0726-900001',
            'status' => 'pending',
            'due_at' => now()->toDateString(),
        ], $overrides));
    }
}
