<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Batch;
use App\Models\BatchTimetable;
use App\Models\Campus;
use App\Models\CoworkingRegistration;
use App\Models\FeeCollection;
use App\Models\FinanceBuildingRent;
use App\Models\Program;
use App\Models\Registration;
use App\Models\User;
use App\Models\User\Permission;
use App\Models\User\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOnlyEditAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_users_cannot_open_or_submit_edit_actions_even_with_update_permissions(): void
    {
        $batchUpdate = $this->createPermission('batch', 'update', 'batch.update');
        $registrationUpdate = $this->createPermission('registration', 'update', 'registration.update');
        $studentUpdate = $this->createPermission('student', 'update', 'student.update');
        $rentUpdate = $this->createPermission('finance.rent', 'update', 'finance.rent.update');

        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $program = $this->createProgram();
        $batch = $this->createBatch($campus, $program, 'ALP-B1');
        $coworking = $this->createCoworkingRegistration($campus, 'Alpha Coworking Member', '03400000021');
        $registration = $this->createRegistration($campus, $program, 'Alpha Student', '03100000021');
        $admission = $this->createAdmission($campus, $program, $batch, $registration, 'Alpha Student', '03200000021');
        $fee = $this->createFeeCollection($campus, $program, $registration, $admission);
        $rent = $this->createRent($campus);

        $user = $this->createScopedUser($campus, [$batchUpdate, $registrationUpdate, $studentUpdate, $rentUpdate]);

        $this->actingAs($user)->get(route('batch.edit', $batch))->assertForbidden();
        $this->actingAs($user)->get(route('coworking-registrations.edit', $coworking))->assertForbidden();
        $this->actingAs($user)->post(route('student.fee.update', $fee), [
            'net_amount' => 900,
            'paid_at' => now()->toDateString(),
        ])->assertForbidden();
        $this->actingAs($user)->patch(route('finance.rent.update', $rent), [
            'campus_id' => $campus->id,
            'agreement_date' => now()->toDateString(),
            'address' => 'Updated Address',
            'rent_amount' => 25000,
            'increment_percentage' => 5,
            'advance_payment' => 0,
            'is_active' => 1,
        ])->assertForbidden();
    }

    public function test_admin_can_open_edit_forms_for_records_from_any_campus(): void
    {
        $admin = $this->createAdminUser();

        $alphaCampus = $this->createCampus('Alpha Campus', 'ALP');
        $betaCampus = $this->createCampus('Beta Campus', 'BET');
        $program = $this->createProgram();
        $betaBatch = $this->createBatch($betaCampus, $program, 'BET-B1');
        $betaCoworking = $this->createCoworkingRegistration($betaCampus, 'Beta Coworking Member', '03400000022');

        $this->actingAs($admin)
            ->get(route('batch.edit', $betaBatch))
            ->assertOk()
            ->assertSee('Edit Batch');

        $this->actingAs($admin)
            ->get(route('coworking-registrations.edit', $betaCoworking))
            ->assertOk()
            ->assertSee('Edit Coworking Space Registration');
    }

    public function test_timetable_edit_query_is_ignored_for_non_admin_users(): void
    {
        $timetableView = $this->createPermission('batch-timetable', 'view', 'batch-timetable.view');

        $campus = $this->createCampus('Alpha Campus', 'ALP');
        $program = $this->createProgram();
        $batch = $this->createBatch($campus, $program, 'ALP-B2');
        $entry = BatchTimetable::query()->create([
            'batch_id' => $batch->id,
            'day_of_week' => 'monday',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'lab' => 'Lab 1',
            'instructor' => 'Instructor',
            'topic' => 'Orientation',
            'status' => 'active',
        ]);

        $user = $this->createScopedUser($campus, [$timetableView]);
        $admin = $this->createAdminUser();

        $this->actingAs($user)
            ->get(route('batch.timetable.index', ['edit' => $entry->id]))
            ->assertOk()
            ->assertSee('Add Time Table Slot')
            ->assertDontSee('Edit Time Table Slot')
            ->assertDontSee('Update Slot');

        $this->actingAs($admin)
            ->get(route('batch.timetable.index', ['edit' => $entry->id]))
            ->assertOk()
            ->assertSee('Edit Time Table Slot')
            ->assertSee('Update Slot');
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
            'name' => 'Admin Edit Program',
            'title' => 'Admin Edit Program',
            'code' => 'AEP101',
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
            'address' => '123 Admin Edit Street',
            'remarks' => 'Admin edit registration record.',
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
            'postal_address' => '123 Admin Edit Street',
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
            'remarks' => 'Admin edit admission record.',
            'receipt_number' => $campus->code . '-0626-10000' . substr($phone, -1),
        ]);
    }

    private function createFeeCollection(Campus $campus, Program $program, Registration $registration, Admission $admission): FeeCollection
    {
        return FeeCollection::query()->create([
            'registration_id' => $registration->id,
            'admission_id' => $admission->id,
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'fee_type' => 'admission',
            'installment_no' => 1,
            'installments_total' => 2,
            'amount' => 1000,
            'discount_percent' => 0,
            'discount_amount' => 0,
            'net_amount' => 1000,
            'receipt_number' => $campus->code . '-0626-900001',
            'status' => 'pending',
            'due_at' => now()->toDateString(),
        ]);
    }

    private function createCoworkingRegistration(Campus $campus, string $name, string $phone): CoworkingRegistration
    {
        return CoworkingRegistration::query()->create([
            'campus_id' => $campus->id,
            'registration_number' => $campus->code . '-CWS-0626-00001',
            'receipt_number' => $campus->code . '-0626-00001',
            'full_name' => $name,
            'phone' => $phone,
            'guardian_name' => 'Guardian ' . $name,
            'guardian_phone' => '0341000000' . substr($phone, -1),
            'cnic' => '35202123456' . substr($phone, -2),
            'email' => strtolower(str_replace(' ', '.', $name)) . '@example.test',
            'education' => 'Bachelors',
            'date_of_birth' => '1998-01-01',
            'nature_of_work' => 'Consulting',
            'timing' => '09:00 AM - 06:00 PM',
            'gender' => 'female',
            'address' => 'Coworking Street',
            'registration_date' => now()->toDateString(),
            'next_due_date' => now()->addMonth()->toDateString(),
            'coworking_charges' => 20000,
            'security_fee' => 10000,
            'remarks' => 'Coworking registration for admin edit access test.',
            'status' => 'registered',
        ]);
    }

    private function createRent(Campus $campus): FinanceBuildingRent
    {
        return FinanceBuildingRent::query()->create([
            'campus_id' => $campus->id,
            'agreement_date' => now()->toDateString(),
            'address' => 'Rent Address',
            'rent_amount' => 25000,
            'increment_percentage' => 5,
            'current_amount' => 26250,
            'advance_payment' => 0,
            'is_active' => true,
            'remarks' => 'Rent setup for admin edit access test.',
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
