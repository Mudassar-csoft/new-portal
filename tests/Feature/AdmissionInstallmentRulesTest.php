<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Batch;
use App\Models\Campus;
use App\Models\FeeCollection;
use App\Models\Program;
use App\Models\User;
use App\Models\User\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmissionInstallmentRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admission_store_rejects_installments_for_programs_that_allow_only_one_payment(): void
    {
        $user = $this->createScopedUser();
        $campus = $this->createCampus('One Pay Campus', 'OPC');
        $program = $this->createProgram('OPC101', 'One Pay Program', 1, 45000);
        $batch = $this->createBatch($campus, $program, 'OPC-B1');

        $this->actingAs($user)
            ->postJson(route('admission.store'), $this->admissionPayload($campus, $program, $batch, [
                'fee_type' => 'installments',
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['fee_type']);
    }

    public function test_admission_store_allows_fewer_installments_than_program_max_when_extra_rows_are_zero(): void
    {
        $user = $this->createScopedUser();
        $campus = $this->createCampus('Three Pay Campus', 'TPC');
        $program = $this->createProgram('TPC101', 'Three Pay Program', 3, 60000);
        $batch = $this->createBatch($campus, $program, 'TPC-B1');

        $this->actingAs($user)
            ->postJson(route('admission.store'), $this->admissionPayload($campus, $program, $batch, [
                'fee_type' => 'installments',
                'installment_amounts' => [30000, 30000, 0],
            ]))
            ->assertOk()
            ->assertJsonPath('status', 'Admission created successfully.');

        $admission = Admission::query()->where('phone', '03000000123')->firstOrFail();
        $fees = FeeCollection::query()
            ->where('admission_id', $admission->id)
            ->where('fee_type', 'admission')
            ->orderBy('installment_no')
            ->get();

        $this->assertCount(2, $fees);
        $this->assertSame([1, 2], $fees->pluck('installment_no')->all());
        $this->assertSame([2, 2], $fees->pluck('installments_total')->all());
        $this->assertSame(['paid', 'pending'], $fees->pluck('status')->all());
        $this->assertSame([30000.0, 30000.0], $fees->map(fn (FeeCollection $fee) => (float) $fee->net_amount)->all());
    }

    public function test_admission_store_rejects_zero_only_installment_amounts_and_shows_the_error_on_the_form(): void
    {
        $user = $this->createScopedUser();
        $campus = $this->createCampus('Zero Error Campus', 'ZEC');
        $program = $this->createProgram('ZEC101', 'Zero Error Program', 3, 60000);
        $batch = $this->createBatch($campus, $program, 'ZEC-B1');

        $this->actingAs($user)
            ->followingRedirects()
            ->from(route('admission.create'))
            ->post(route('admission.store'), $this->admissionPayload($campus, $program, $batch, [
                'fee_type' => 'installments',
                'installment_amounts' => [0, 0, 0],
            ]))
            ->assertSee('Each installment amount must be greater than zero.');
    }

    public function test_admission_store_auto_creates_program_defined_installment_schedule(): void
    {
        $user = $this->createScopedUser();
        $campus = $this->createCampus('Auto Split Campus', 'ASC');
        $program = $this->createProgram('ASC101', 'Auto Split Program', 3, 60000);
        $batch = $this->createBatch($campus, $program, 'ASC-B1');

        $this->actingAs($user)
            ->postJson(route('admission.store'), $this->admissionPayload($campus, $program, $batch, [
                'phone' => '03000000124',
                'guardian_phone' => '03000000125',
                'cnic' => '3520212345679',
                'email' => 'autosplit@example.test',
                'fee_type' => 'installments',
            ]))
            ->assertOk()
            ->assertJsonPath('status', 'Admission created successfully.');

        $admission = Admission::query()->where('phone', '03000000124')->firstOrFail();
        $fees = FeeCollection::query()
            ->where('admission_id', $admission->id)
            ->where('fee_type', 'admission')
            ->orderBy('installment_no')
            ->get();

        $this->assertCount(3, $fees);
        $this->assertSame([1, 2, 3], $fees->pluck('installment_no')->all());
        $this->assertSame([3, 3, 3], $fees->pluck('installments_total')->all());
        $this->assertSame(['paid', 'pending', 'pending'], $fees->pluck('status')->all());
        $this->assertSame([20000.0, 20000.0, 20000.0], $fees->map(fn (FeeCollection $fee) => (float) $fee->net_amount)->all());
    }

    private function createScopedUser(): User
    {
        $permission = Permission::query()->firstOrCreate(
            ['slug' => 'admission.create'],
            [
                'resource' => 'admission',
                'action' => 'create',
            ]
        );

        $user = User::factory()->create();
        $user->permissions()->sync([$permission->id]);

        return $user;
    }

    private function createCampus(string $name, string $code): Campus
    {
        return Campus::query()->create([
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'code' => $code,
        ]);
    }

    private function createProgram(string $code, string $title, int $installments, float $fee): Program
    {
        return Program::query()->create([
            'name' => $title,
            'title' => $title,
            'code' => $code,
            'program_type' => 'bootcamp',
            'fee' => $fee,
            'duration_weeks' => 12,
            'installments' => $installments,
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

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function admissionPayload(Campus $campus, Program $program, Batch $batch, array $overrides = []): array
    {
        return array_merge([
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'batch_id' => $batch->id,
            'student_name' => 'Installment Student',
            'phone' => '03000000123',
            'guardian_name' => 'Guardian Installment',
            'guardian_phone' => '03000000124',
            'cnic' => '3520212345678',
            'passport_number' => 'PASS1234',
            'email' => 'installment.student@example.test',
            'education' => 'Intermediate',
            'date_of_birth' => '2001-01-01',
            'gender' => 'male',
            'country' => 'Pakistan',
            'city' => 'Lahore',
            'area' => 'Johar Town',
            'postal_address' => '123 Installment Street',
            'admission_date' => now()->toDateString(),
            'fee_type' => 'full',
            'remarks' => 'Admission installment test.',
        ], $overrides);
    }
}
