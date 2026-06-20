<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Batch;
use App\Models\Campus;
use App\Models\Program;
use App\Models\Registration;
use App\Models\User;
use App\Models\User\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentRecordIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_student_records_page_uses_only_the_columns_shown_in_the_header(): void
    {
        $studentView = $this->createPermission('student', 'view', 'student.view');
        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $program = $this->createProgram('DS101', 'Data Science');
        $batch = $this->createBatch($campus, $program, 'ALP-B1');
        $registration = $this->createRegistration($campus, $program, 'Ahmad', '03000000991');
        $this->createAdmission($campus, $program, $batch, $registration, 'Ahmad', '03000000991');

        $user = User::factory()->create([
            'campus_id' => $campus->id,
        ]);
        $user->permissions()->sync([$studentView->id]);

        $this->actingAs($user)
            ->get(route('student.records.index', ['scope' => 'active']))
            ->assertOk()
            ->assertSee('Course')
            ->assertSee('Status')
            ->assertSee('Primary Contact')
            ->assertSee("data: 'program_name'", false)
            ->assertSee("data: 'status_badge'", false)
            ->assertSee("data: 'phone'", false)
            ->assertDontSee("data: 'registration_number'", false)
            ->assertDontSee("data: 'admission_date'", false)
            ->assertDontSee("data: 'certificate_status'", false);
    }

    public function test_active_student_records_ajax_returns_course_status_and_primary_contact_fields(): void
    {
        $studentView = $this->createPermission('student', 'view', 'student.view');
        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $program = $this->createProgram('DM101', 'Digital Marketing Pro');
        $batch = $this->createBatch($campus, $program, 'ALP-B2');
        $registration = $this->createRegistration($campus, $program, 'Kashan', '03000000992');
        $this->createAdmission($campus, $program, $batch, $registration, 'Kashan', '03000000992', [
            'roll_number' => 'ALP-B2-01',
            'student_status' => 'enrolled',
        ]);

        $user = User::factory()->create([
            'campus_id' => $campus->id,
        ]);
        $user->permissions()->sync([$studentView->id]);

        $response = $this->actingAs($user)->getJson(
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

        $response->assertOk();

        $row = $response->json('data.0');

        $this->assertSame('Kashan', $row['student_name']);
        $this->assertSame('ALP-B2-01', $row['roll_number']);
        $this->assertSame('Digital Marketing Pro', $row['program_name']);
        $this->assertSame('ALP', $row['campus_code']);
        $this->assertSame('03000000992', $row['phone']);
        $this->assertStringContainsString('Enrolled', $row['status_badge']);
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
            'guardian_phone' => '0301000000' . substr($phone, -1),
            'cnic' => '35202123456' . substr($phone, -2),
            'passport_number' => 'PASS' . substr($phone, -4),
            'email' => strtolower(str_replace(' ', '.', $studentName)) . '@example.test',
            'education' => 'Intermediate',
            'date_of_birth' => '2001-01-01',
            'gender' => 'male',
            'address' => '123 Student Street',
            'remarks' => 'Student record index test registration.',
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
            'remarks' => 'Student record index test admission.',
            'receipt_number' => $campus->code . '-0626-10000' . substr($phone, -1),
        ], $overrides));
    }
}
