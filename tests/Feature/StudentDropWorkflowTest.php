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

    public function test_reenroll_only_action_shows_for_dropped_frozen_incomplete_suspended_and_cancelled_students(): void
    {
        $studentView = $this->createPermission('student', 'view', 'student.view');
        $studentUpdate = $this->createPermission('student', 'update', 'student.update');
        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $program = $this->createProgram('UI101', 'UI Design');
        $batch = $this->createBatch($campus, $program, 'ALP-B4');

        $statusStudents = [
            'Dropped Student' => 'dropped',
            'Frozen Student' => 'frozen',
            'Incomplete Student' => 'incomplete',
            'Suspended Student' => 'suspended',
            'Cancelled Student' => 'admission_cancelled',
            'Enrolled Student' => 'enrolled',
        ];

        $index = 0;

        foreach ($statusStudents as $studentName => $status) {
            $phone = '03000001' . str_pad((string) $index, 2, '0', STR_PAD_LEFT);
            $registration = $this->createRegistration($campus, $program, $studentName, $phone);

            $this->createAdmission($campus, $program, $batch, $registration, $studentName, $registration->phone, [
                'roll_number' => $campus->code . '-' . $batch->code . '-' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'student_status' => $status,
            ]);
            $index++;
        }

        $user = $this->createUser($campus, [$studentView, $studentUpdate]);

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

        $reenrollActionCount = collect($response->json('data'))
            ->filter(fn (array $row) => str_contains((string) ($row['actions'] ?? ''), 'Enroll Now'))
            ->count();
        $transferActionCount = collect($response->json('data'))
            ->filter(fn (array $row) => str_contains((string) ($row['actions'] ?? ''), 'Transfer Campus &amp; Batch'))
            ->count();
        $freezeActionCount = collect($response->json('data'))
            ->filter(fn (array $row) => str_contains((string) ($row['actions'] ?? ''), 'Freeze Course'))
            ->count();

        $this->assertSame(5, $reenrollActionCount);
        $this->assertSame(1, $transferActionCount);
        $this->assertSame(1, $freezeActionCount);
    }

    public function test_reenroll_allows_supported_statuses_and_restores_unpaid_fee_rows_to_pending(): void
    {
        $studentUpdate = $this->createPermission('student', 'update', 'student.update');
        $sourceCampus = $this->createCampus('Source Campus', 'SRC');
        $targetCampus = $this->createCampus('Target Campus', 'TGT');
        $program = $this->createProgram('WD201', 'Advanced Web Development');
        $sourceBatch = $this->createBatch($sourceCampus, $program, 'SRC-B1');
        $targetBatch = $this->createBatch($targetCampus, $program, 'TGT-B1');
        $user = $this->createUser($sourceCampus, [$studentUpdate]);

        foreach (Admission::REENROLLABLE_STATUSES as $index => $status) {
            $studentNumber = 200 + $index;
            $phone = '03000000' . str_pad((string) $studentNumber, 3, '0', STR_PAD_LEFT);
            $studentName = 'Reenroll Student ' . ($index + 1);
            $registration = $this->createRegistration($sourceCampus, $program, $studentName, $phone);
            $admission = $this->createAdmission($sourceCampus, $program, $sourceBatch, $registration, $studentName, $phone, [
                'roll_number' => $sourceCampus->code . '-' . $sourceBatch->code . '-' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'student_status' => $status,
            ]);

            $pendingFee = $this->createFeeCollection($sourceCampus, $program, $registration, $admission, [
                'installment_no' => 1,
                'status' => 'pending',
            ]);
            $badDebtFee = $this->createFeeCollection($sourceCampus, $program, $registration, $admission, [
                'installment_no' => 2,
                'status' => 'baddebt',
            ]);
            $cancelledFee = $this->createFeeCollection($sourceCampus, $program, $registration, $admission, [
                'installment_no' => 3,
                'status' => 'cancel',
                'net_amount' => 0,
                'amount' => 0,
            ]);
            $paidFee = $this->createFeeCollection($sourceCampus, $program, $registration, $admission, [
                'installment_no' => 4,
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $this->actingAs($user)
                ->getJson(route('student.records.reenroll.meta', $admission))
                ->assertOk()
                ->assertJsonPath('admission.id', $admission->id);

            $this->actingAs($user)
                ->post(route('student.records.reenroll.store', $admission), [
                    'campus_id' => $targetCampus->id,
                    'batch_id' => $targetBatch->id,
                ])
                ->assertRedirect()
                ->assertSessionHas('status', function (?string $message): bool {
                    return str_contains((string) $message, 'Student enrolled to TGT - Target Campus / TGT-B1 - TGT-B1 Batch.')
                        && str_contains((string) $message, 'Pending, bad debt, and cancelled fee moved to pending.');
                });

            $admission->refresh();
            $pendingFee->refresh();
            $badDebtFee->refresh();
            $cancelledFee->refresh();
            $paidFee->refresh();

            $this->assertSame('enrolled', $admission->student_status);
            $this->assertSame($targetCampus->id, $admission->campus_id);
            $this->assertSame($targetBatch->id, $admission->batch_id);
            $this->assertSame('pending', $pendingFee->status);
            $this->assertSame($targetCampus->id, $pendingFee->campus_id);
            $this->assertSame($targetBatch->program_id, $pendingFee->program_id);
            $this->assertStringContainsString('Fee moved to pending because the student was enrolled again', (string) $pendingFee->notes);
            $this->assertSame('pending', $badDebtFee->status);
            $this->assertSame($targetCampus->id, $badDebtFee->campus_id);
            $this->assertSame($targetBatch->program_id, $badDebtFee->program_id);
            $this->assertSame('pending', $cancelledFee->status);
            $this->assertSame($targetCampus->id, $cancelledFee->campus_id);
            $this->assertSame($targetBatch->program_id, $cancelledFee->program_id);
            $this->assertSame('paid', $paidFee->status);
            $this->assertSame($sourceCampus->id, $paidFee->campus_id);
        }
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
        return Permission::query()->firstOrCreate(
            ['slug' => $slug],
            [
                'resource' => $resource,
                'action' => $action,
            ]
        );
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
