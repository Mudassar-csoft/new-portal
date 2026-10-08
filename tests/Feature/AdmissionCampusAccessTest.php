<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Batch;
use App\Models\Campus;
use App\Models\Lead;
use App\Models\Program;
use App\Models\Registration;
use App\Models\User;
use App\Models\User\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdmissionCampusAccessTest extends TestCase
{
    use RefreshDatabase {
        migrateFreshUsing as baseMigrateFreshUsing;
    }

    private Campus $userCampus;

    private Campus $sourceCampus;

    private Campus $targetCampus;

    private Program $program;

    private Batch $batch;

    private User $user;

    protected function migrateFreshUsing(): array
    {
        // These scenarios need the schema without importing historical student data.
        $schemaMigrations = array_values(array_filter(
            glob(database_path('migrations/*.php')),
            fn (string $path) => ! preg_match('/_(import|reload|rerun|sync_imported|correct_legacy|set_twenty_percent|migrate_legacy)_/', basename($path))
        ));

        return [
            ...$this->baseMigrateFreshUsing(),
            '--path' => $schemaMigrations,
            '--realpath' => true,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->userCampus = $this->createCampus('User Campus', 'USR');
        $this->sourceCampus = $this->createCampus('Source Campus', 'SRC');
        $this->targetCampus = $this->createCampus('Target Campus', 'TGT');
        $this->program = $this->createProgram('NEW');
        $this->batch = $this->createBatch($this->targetCampus, $this->program, 'TGT-B1');
        $this->user = User::factory()->create(['campus_id' => $this->userCampus->id]);

        $permission = Permission::query()->create([
            'resource' => 'admission',
            'action' => 'create',
            'slug' => 'admission.create',
        ]);
        $this->user->permissions()->attach($permission);
        $this->actingAs($this->user);
    }

    #[DataProvider('admissionSources')]
    public function test_campus_user_can_admit_at_another_campus_with_any_source(string $source): void
    {
        $payload = $this->admissionPayload();
        $sourceParameters = [];
        $lead = null;
        $registration = null;
        $sourceAdmission = null;

        if ($source !== 'direct') {
            $sourceProgram = $source === 'admission' ? $this->createProgram('OLD') : $this->program;
            $lead = Lead::query()->create([
                'campus_id' => $this->sourceCampus->id,
                'program_id' => $sourceProgram->id,
                'type' => 'training',
                'name' => $payload['student_name'],
                'phone' => $payload['phone'],
                'email' => $payload['email'],
                'status' => $source === 'admission' ? 'enrolled' : 'pending',
            ]);

            if (in_array($source, ['lead', 'registered_lead'], true)) {
                $sourceParameters['lead_id'] = $lead->id;
            }

            if (in_array($source, ['registered_lead', 'registration', 'admission'], true)) {
                $registration = Registration::query()->create([
                    ...Arr::only($payload, [
                        'student_name', 'phone', 'guardian_name', 'guardian_phone',
                        'cnic', 'email', 'education', 'date_of_birth', 'gender', 'remarks',
                    ]),
                    'lead_id' => $lead->id,
                    'campus_id' => $this->sourceCampus->id,
                    'program_id' => $sourceProgram->id,
                    'registration_number' => 'SRC-REG-001',
                    'receipt_number' => 'SRC-RECEIPT-001',
                    'address' => $payload['postal_address'],
                    'status' => 'registered',
                    'registered_at' => now(),
                ]);

                if ($source === 'registration') {
                    $sourceParameters['source_registration_id'] = $registration->id;
                }

                if ($source === 'admission') {
                    $sourceBatch = $this->createBatch($this->sourceCampus, $sourceProgram, 'SRC-B1');
                    $sourceAdmission = Admission::query()->create([
                        ...Arr::except($payload, ['payment_method']),
                        'registration_id' => $registration->id,
                        'campus_id' => $this->sourceCampus->id,
                        'program_id' => $sourceProgram->id,
                        'batch_id' => $sourceBatch->id,
                        'registration_number' => $registration->registration_number,
                        'roll_number' => 'SRC-SRC-B1-001',
                        'receipt_number' => 'SRC-ADM-001',
                        'fee_package' => 10000,
                        'discount_amount' => 0,
                        'discount_percent' => 0,
                        'discounted_fee' => 10000,
                        'student_status' => 'enrolled',
                    ]);
                    $sourceParameters['source_admission_id'] = $sourceAdmission->id;
                }
            }
        }

        $form = $this->get(route('admission.create', $sourceParameters))->assertOk();
        foreach ([$this->userCampus, $this->sourceCampus, $this->targetCampus] as $campus) {
            $form->assertSee('value="'.$campus->id.'"', false)
                ->assertSee($campus->name);
        }
        $form->assertSee('name="campus_id" required>', false);

        $preview = $this->getJson(route('admission.preview-numbers', [
            'campus_id' => $this->targetCampus->id,
            'program_id' => $this->program->id,
            'batch_id' => $this->batch->id,
            'lead_id' => $lead?->id,
        ]))->assertOk();
        $this->assertStringStartsWith('TGT-TGT-B1-', $preview->json('roll_number'));
        $this->assertStringStartsWith('TGT-', $preview->json('receipt_number'));
        if ($registration && ! $sourceAdmission) {
            $preview->assertJsonPath('registration_number', $registration->registration_number);
        }

        $this->postJson(route('admission.store'), [...$payload, ...$sourceParameters])
            ->assertOk()
            ->assertJsonPath('status', 'Admission created successfully.');

        $admission = Admission::query()->where('program_id', $this->program->id)->sole();
        $this->assertSame($this->targetCampus->id, $admission->campus_id);
        $this->assertSame($this->batch->id, $admission->batch_id);
        $this->assertStringStartsWith('TGT-TGT-B1-', $admission->roll_number);
        $this->assertStringStartsWith('TGT-', $admission->receipt_number);
        $this->assertDatabaseHas('fee_collections', [
            'admission_id' => $admission->id,
            'campus_id' => $this->targetCampus->id,
            'program_id' => $this->program->id,
            'fee_type' => 'admission',
            'created_by' => $this->user->id,
            'payment_method' => 'cash',
        ]);

        if ($registration) {
            $this->assertSame($registration->id, $admission->registration_id);
            $this->assertDatabaseCount('registrations', 1);
        }
        if ($sourceAdmission) {
            $this->assertSame($this->sourceCampus->id, $registration->fresh()->campus_id);
            $this->assertSame($sourceProgram->id, $registration->fresh()->program_id);
            $this->assertSame($this->sourceCampus->id, $sourceAdmission->fresh()->campus_id);
        }
    }

    public static function admissionSources(): array
    {
        return [
            'new student' => ['direct'],
            'lead from another campus' => ['lead'],
            'registered lead from another campus' => ['registered_lead'],
            'registration from another campus' => ['registration'],
            'previous admission from another campus' => ['admission'],
        ];
    }

    public function test_batch_must_belong_to_the_selected_campus(): void
    {
        $payload = $this->admissionPayload();
        $payload['batch_id'] = $this->createBatch($this->userCampus, $this->program, 'USR-B1')->id;

        $this->postJson(route('admission.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('batch_id');
        $this->assertDatabaseCount('admissions', 0);
        $this->assertDatabaseCount('registrations', 0);
        $this->assertDatabaseCount('fee_collections', 0);
    }

    public function test_previous_campus_selection_is_preserved_after_validation(): void
    {
        $this->withSession(['_old_input' => ['campus_id' => $this->targetCampus->id]])
            ->get(route('admission.create'))
            ->assertOk()
            ->assertSee('value="'.$this->targetCampus->id.'" selected', false);
    }

    private function admissionPayload(): array
    {
        return [
            'campus_id' => $this->targetCampus->id,
            'program_id' => $this->program->id,
            'batch_id' => $this->batch->id,
            'student_name' => 'Cross Campus Student',
            'phone' => '03200000123',
            'guardian_name' => 'Student Guardian',
            'guardian_phone' => '03200000124',
            'cnic' => '3520212345678',
            'email' => 'cross.campus@example.test',
            'education' => 'Intermediate',
            'date_of_birth' => '2001-01-01',
            'gender' => 'male',
            'postal_address' => '123 Testing Street',
            'admission_date' => now()->toDateString(),
            'fee_type' => 'full',
            'payment_method' => 'cash',
            'remarks' => 'Cross campus admission.',
        ];
    }

    private function createCampus(string $name, string $code): Campus
    {
        return Campus::query()->create(['name' => $name, 'slug' => strtolower($code), 'code' => $code]);
    }

    private function createProgram(string $code): Program
    {
        return Program::query()->create([
            'name' => $code.' Course',
            'title' => $code.' Course',
            'code' => $code,
            'fee' => 10000,
            'duration_weeks' => 12,
            'installments' => 1,
            'status' => 'active',
        ]);
    }

    private function createBatch(Campus $campus, Program $program, string $code): Batch
    {
        return Batch::query()->create([
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'name' => $code.' Batch',
            'code' => $code,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'status' => 'active',
        ]);
    }
}
